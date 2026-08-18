<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zFunction.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
$nodok=$_GET['notransaksi'];
$optNmoRg=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$optSupplier=makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$optKary=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
//=============

//create Header
class PDF extends FPDF
{
	
	function Header()
	{
 	global $conn;
	global $dbname;
	global $nodok;
        global $userid;
	global $posted;
	global $tanggal;
	global $idsupplier;
	global $pengetahui;
	global $penerima;
	global $kodegudang;
	global $nopo;
	global $namasupplier;
        global $idFranco;
        global $pengirim;
        global $kpd;
        global $optSupplier;
        global $optKary;
        global $expend;
	
		$pt='';
		$namapt='';
		$alamatpt='';
		$telp='';
		$kodegudang='';
		$status=0;
		$str="select * from ".$dbname.".log_pengiriman_ht where nosj='".$_GET['srtJalan']."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $bar=$res->fetch();

		
			$kodept=substr($bar->nosj,18,3);
			$kodegudang=$bar->kodegudang;
			$userid=$bar->user;
                        $penerima=$bar->namapenerima;
                        $pengetahui=$bar->mengetahui;
			$posted=$bar->postedby;
			$status=$bar->tipetransaksi;
			$tanggal=tanggalnormal($bar->tanggalkirim);
			$idsupplier=$bar->idsupplier;
			$nosj=$bar->nosj;
			$nofaktur=$bar->nofaktur;	
			$nopo=$bar->nopo;
                        $idFranco=$bar->id_franco;
                        $pengirim=$bar->pengirim;
                        $kpd=$bar->kepada;
                        $expend=$bar->expeditor;
                        
			 
			//ambil nama pt
			   
			   $str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$kodept."'";
			   $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
		       $res1->setFetchMode(PDO::FETCH_OBJ);
			   while($bar1=$res1->fetch()){
			   	 $namapt=$bar1->namaorganisasi;
				 $alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
				 $telp=$bar1->telepon;				 
			   } 
		
		$path='images/logo.jpg';
	    $this->Image($path,15,5,40);	
		$this->SetFont('Arial','B',10);
		$this->SetFillColor(255,255,255);	
		$this->SetX(55);   
	    $this->Cell(60,5,$namapt,0,1,'L');	 
		$this->SetX(55); 		
	    $this->Cell(60,5,$alamatpt,0,1,'L');	
		$this->SetX(55); 			
		$this->Cell(60,5,"Tel: ".$telp,0,1,'L');	
		$this->SetFont('Arial','',15);
		$this->SetY(30);		
	    $this->Cell(190,5,strtoupper($_SESSION['lang']['basj']),0,1,'C');
		$this->SetFont('Arial','',6); 
		$this->SetY(27);
		$this->SetX(163);
        $this->Cell(30,10,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'L');		
		$this->Line(10,27,200,27);	
	    $this->SetFont('Arial','',9);		
//		$this->Cell(30,4,$_SESSION['lang']['sloc'],0,0,'L'); 
//		$this->Cell(40,4,": ".$kodegudang,0,1,'L'); 
		$this->Cell(30,4,"No.Surat Jalan",0,0,'L'); 
		$this->Cell(40,4,": ".$nosj,0,1,'L'); 				
                $this->Cell(30,4,$_SESSION['lang']['tanggal'],0,0,'L'); 
		$this->Cell(40,4,": ".$tanggal,0,1,'L'); 		  
	}
	
	function Footer()
	{
	    $this->SetY(-15);
	    $this->SetFont('Arial','I',8);
	    $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
	}

}

	$pdf=new PDF('P','mm','A4');
	$pdf->AddPage();
			
//ambil kelengkapan
//ambil supplier
	$stry="select distinct namasupplier from ".$dbname.".log_5supplier where supplierid='".$idsupplier."'";
	$namasupplier=$idsupplier;
	$resy=$owlPDO->query($stry) or die(print " Gagal: ".PDOException::getMessage());
	$resy->setFetchMode(PDO::FETCH_OBJ);
	while($bary=$resy->fetch()){
        $namasupplier=$bary->namasupplier;
 	}
    $pdf->SetFillColor(255,255,255);
    $sFranco="select distinct contact,alamat,handphone from ".$dbname.".setup_franco where id_franco='".$idFranco."'";
    $qFranco=$owlPDO->query($sFranco) or die(print " Gagal: ".PDOException::getMessage());
	$qFranco->setFetchMode(PDO::FETCH_ASSOC);
    $rFranco=$qFranco->fetch();
    $pdf->Ln(5);	
    $pdf->Cell(30,4,"Kepada Yth,",0,1,'L'); 
    //$pdf->Cell(170,4,ucfirst($optNmoRg[$kpd]),0,1,'L'); 
    $pdf->Cell(170,4,ucfirst($rFranco['contact']),0,1,'L'); 
    $pdf->MultiCell(170,4,ucfirst($rFranco['alamat']),0,1,'J'); 
    $pdf->Cell(30,4,$rFranco['handphone'],0,1,'L'); 
    $pdf->Ln(5);
   
    
		$pdf->Cell(30,4,$_SESSION['lang']['nopo'],0,0,'L'); 
		$pdf->Cell(40,4,": ".$nopo,0,1,'L'); 
                $sListPo="select distinct nopo from ".$dbname.".log_pengiriman_dt where nosj='".$_GET['srtJalan']."'";
                $qListPo=$owlPDO->query($sListPo) or die(print " Gagal: ".PDOException::getMessage());
				$qListPo->setFetchMode(PDO::FETCH_ASSOC);
                while($rListPo=$qListPo->fetch()){
                    $no+=1;
                    $pdf->Cell(5,4,$no.".",0,0,'L'); 
                    $pdf->Cell(30,4,$rListPo['nopo'],0,1,'L'); 
                }
        $pdf->Ln(5);
	$pdf->SetFont('Arial','B',9);	
	$pdf->SetFillColor(220,220,220);
    $pdf->Cell(8,5,'No',1,0,'L',1);
    $pdf->Cell(42,5,$_SESSION['lang']['nopo'],1,0,'C',1);
    $pdf->Cell(28,5,$_SESSION['lang']['kodebarang'],1,0,'C',1);
    $pdf->Cell(80,5,$_SESSION['lang']['namabarang'],1,0,'C',1);	
    $pdf->Cell(15,5,$_SESSION['lang']['satuan'],1,0,'C',1);		
    $pdf->Cell(18,5,$_SESSION['lang']['kuantitas'],1,1,'C',1);	
	$pdf->SetFillColor(255,255,255);
	    $pdf->SetFont('Arial','',9);
		
		$str="select * from ".$dbname.".log_pengiriman_dt where nosj='".$_GET['srtJalan']."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$no=0;
		while($bar=$res->fetch()){
			$no+=1;
			
			$kodebarang=$bar->kodebarang;
			$satuan=$bar->satuan;
			$jumlah=$bar->jumlahbrg;
		   $namabarang='';
                    $strv="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$bar->kodebarang."'";	
                    $resv=$owlPDO->query($strv) or die(print " Gagal: ".PDOException::getMessage());
					$resv->setFetchMode(PDO::FETCH_OBJ);	
                    $barv=$resv->fetch();
                    $namabarang=$barv->namabarang;
		   
			    $pdf->Cell(8,5,$no,1,0,'L',1);
                            $pdf->Cell(42,5,$bar->nopo,1,0,'L',1);
			    $pdf->Cell(28,5,$kodebarang,1,0,'C',1);
			    $pdf->Cell(80,5,$namabarang,1,0,'L',1);	
			    $pdf->Cell(15,5,$satuan,1,0,'L',1);	
				$pdf->Cell(18,5,number_format($jumlah,2,'.',','),1,1,'R',1);		
			    	   
		}
//footer================================
//                $dtpenerima=namakaryawan($dbname,$conn,$penerima);
//                $dtpengetahui=namakaryawan($dbname,$conn,$pengetahui);
        $dptinY=$pdf->GetY();
        $dptinX=$pdf->GetX();
        $pdf->SetY($dptinY+25);
        $pdf->SetX($dptinX);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','B',8);
        if(substr($expend,0,1)=='S'){
            $dert=$_SESSION['lang']['expeditor'];
            $duer=$optSupplier[$expend];
        }else{
            $dert=$_SESSION['lang']['dibawa'];
            $duer=$optKary[$expend];
        }
       
        $pdf->Cell(50,$height,$_SESSION['lang']['penerima'].",",0,0,'C',0);
        $pdf->Cell(70,$height,$dert.",",0,0,'C',0);
        $pdf->Cell(65,$height,$_SESSION['lang']['pengirim'].",",0,1,'C',0);
        $pdf->ln(25);
        $pdf->SetFont('Arial','U',8);
        $pdf->Cell(50,$height,$penerima,0,0,'C',0);
        $pdf->SetFont('Arial','U',8);
        $pdf->Cell(70,$height,$duer,0,0,'C',0);
        $pdf->SetFont('Arial','U',8);
        $pdf->Cell(65,$height,$optNmoRg[$pengirim],0,1,'C',0);

	$pdf->Output();
?>
