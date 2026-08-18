<?
require_once('config/connection.php');
$mobileValid = false;
if(isset($_POST['par']) || isset($_GET['par'])){
	$validasiPostMobile = explode(" ", $_POST['par']);
	$validasiGetMobile = explode(" ", $_GET['par']);
	if($validasiGetMobile[0] == "owlApp" or $validasiPostMobile[0] == "owlApp"){
		$mobileValid = true;
		$strlang=$owlPDO->query("select legend,ID from ".$dbname.".bahasa order by legend");
		$strlang->setFetchMode(PDO::FETCH_NUM);
		while($barlang=$strlang->fetch()) {
			$_SESSION['lang'][$barlang[0]]=$barlang[1];
		}
	};
}

if($mobileValid == false){//untuk redirec dari mobile
	require_once('master_validation.php');
}
require_once('lib/nangkoelib.php');
require_once('lib/zFunction.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
$nodok = checkPostGet('notransaksi','0');
$optOrganisasi = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

$str="select * from ".$dbname.".log_transaksiht where notransaksi='".$_GET['notransaksi']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	$kodept=$bar->kodept;
	$kodegudang=$bar->kodegudang;
	$userid=$bar->user;
	$posted=$bar->postedby;
	$aproved1=$bar->persetujuan1;
	$aproved2=$bar->persetujuan2;
	$stt1=$bar->hasilpersetujuan1;
	$stt2=$bar->hasilpersetujuan2;
	$status=$bar->post;
	$tanggal=$bar->tanggal;
	$lastupdate=$bar->lastupdate;
	$idsupplier=$bar->idsupplier;
	$nosj=$bar->nosj;
	$nofaktur=$bar->nofaktur;	
	$nopo=$bar->nopo;		
	if($status==0)
	 $status='Not Confirm';
	else
	 $status='Confirmed'; 
	//ambil nama pt
}

//=============
if (!class_exists('PDFGRN')){
	//create Header
	class PDFGRN extends FPDF
	{
		
		function Header()
		{
			global $conn;
			global $dbname;
			global $nodok;
			global $userid;
			global $posted;
			global $aproved1;
			global $aproved2;
			global $stt1;
			global $stt2;
			global $tanggal;
			global $lastupdate;
			global $idsupplier;
			global $nosj;
			global $nofaktur;
			global $kodegudang;
			global $nopo;
			global $namasupplier;
			global $owlPDO;	
			global $optOrganisasi;	
			
			$str="select * from ".$dbname.".log_transaksiht where notransaksi='".$_GET['notransaksi']."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while($bar=$res->fetch())
			{
				$kodept=$bar->kodept;
				$kodegudang=$bar->kodegudang;
				$userid=$bar->user;
				$posted=$bar->postedby;
				$aproved1=$bar->persetujuan1;
				$aproved2=$bar->persetujuan2;
				$stt1=$bar->hasilpersetujuan1;
				$stt2=$bar->hasilpersetujuan2;
				$status=$bar->post;
				$tanggal=$bar->tanggal;
				$lastupdate=$bar->lastupdate;
				$idsupplier=$bar->idsupplier;
				$nosj=$bar->nosj;
				$nofaktur=$bar->nofaktur;	
				$nopo=$bar->nopo;		
				if($status==0)
				 $status='Not Confirm';
				else
				 $status='Confirmed'; 
				//ambil nama pt
			}
			$str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$kodept."'";
			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_OBJ);
			while($bar1=$res1->fetch())
			{
				 $namapt=$bar1->namaorganisasi;
			  $alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
			  $telp=$bar1->telepon;				 
			} 
			
			$arrHead = setheadreport($kodept,$kodept);
			$arrHeadUnit = setheadreport('',$kodeunit);
			$path=$arrHead['logo'];
			
			//$path='images/logo.jpg';
			$this->Image($path,10,5,0,20);
			$this->SetFont('Arial','B',10);
			$this->SetFillColor(255,255,255);	
			$this->SetY(10);
			$this->SetX(45); 
			$this->Cell(60,5,$namapt,0,1,'L');	 
			$this->SetX(45); 		
			$this->MultiCell(150,5,$alamatpt,0,'L');	
			$this->SetX(45); 			
			$this->Cell(60,5,"Tel: ".$telp,0,1,'L');	
			$this->SetFont('Arial','',15);
			$this->SetY(40);		
			$this->Cell(190,5,strtoupper($_SESSION['lang']['bapb']),0,1,'C');
			$this->SetFont('Arial','',6); 
			$this->SetY(27);
			$this->SetX(163);
			$this->Cell(30,10,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'L');		
			$this->Line(10,27,200,27);
			$this->Ln(20);
			$this->SetFont('Arial','',9);		
			$this->Cell(30,4,$_SESSION['lang']['sloc'],0,0,'L'); 
			$this->Cell(40,4,": ".$optOrganisasi[$kodegudang]." - ".$kodegudang,0,1,'L'); 
			$this->Cell(30,4,$_SESSION['lang']['docnum'],0,0,'L'); 
			$this->Cell(40,4,": ".$_GET['notransaksi'],0,1,'L'); 				
			$this->Cell(30,4,$_SESSION['lang']['docstatus'],0,0,'L'); 
			$this->Cell(40,4,": ".$status,0,1,'L'); 		  
		}
		
		function Footer()
		{
			$this->SetY(-15);
			$this->SetFont('Arial','I',8);
			$this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
		}

	}
}

	$pdf=new PDFGRN('P','mm','A4');
	$pdf->AddPage();
	
//ambil kelengkapan
//ambil supplier
     $stry="select * from ".$dbname.".log_5supplier where supplierid='".$idsupplier."'";
	 $namasupplier=$idsupplier;
	 $resy=$owlPDO->query($stry) or die(print " Gagal: ".PDOException::getMessage());
	 $resy->setFetchMode(PDO::FETCH_OBJ);
	 while($bary=$resy->fetch())
	 {
	 	$namasupplier=$bary->namasupplier;
	 }
     $hari=hari($tanggal,$_SESSION['language']);
	 $tanggal=tanggalnormal($tanggal);
	 $lastupdate=tanggalnormal($lastupdate);
	 $resc=str_replace("#DATE_REPARAM#",$hari.", ".$tanggal,$_SESSION['lang']['prebapb']);
	 $resc=str_replace("#SLOC_PARAM#",$optOrganisasi[$kodegudang]." - ".$kodegudang,$resc);
	 $resc=str_replace("#VENDOR_PARAM#",$namasupplier,$resc);
    $pdf->Ln();
    $pdf->Ln();	
    $pdf->MultiCell(170,5,$resc,0,'L');	
		$pdf->Cell(30,4,$_SESSION['lang']['nopo'],0,0,'L'); 
		$pdf->Cell(40,4,": ".$nopo,0,1,'L'); 
		if($nosj != ''){
			$pdf->Cell(30,4,$_SESSION['lang']['suratjalan'],0,0,'L'); 
			$pdf->Cell(40,4,": ".$nosj,0,1,'L'); 
		}
		if($nofaktur != ''){
			$pdf->Cell(30,4,$_SESSION['lang']['faktur'],0,0,'L'); 
			$pdf->Cell(40,4,": ".$nofaktur,0,1,'L'); 		
		}
//    $pdf->Ln();
	$pdf->SetFont('Arial','B',9);	
	$pdf->SetFillColor(220,220,220);
    $pdf->Cell(8,5,'No',1,0,'L',1);
    $pdf->Cell(30,5,$_SESSION['lang']['kodebarang'],1,0,'C',1);
    $pdf->Cell(100,5,$_SESSION['lang']['namabarang'],1,0,'C',1);	
    $pdf->Cell(20,5,$_SESSION['lang']['satuan'],1,0,'C',1);		
    $pdf->Cell(20,5,$_SESSION['lang']['kuantitas'],1,1,'C',1);	
	$pdf->SetFillColor(255,255,255);
	    $pdf->SetFont('Arial','',9);
		
		$str="select * from ".$dbname.".log_transaksidt where notransaksi='".$_GET['notransaksi']."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$no=0;
		while($bar=$res->fetch())
		{
			$no+=1;
			
			$kodebarang=$bar->kodebarang;
			$satuan=$bar->satuan;
			$jumlah=$bar->jumlah;
			$catatan=$bar->catatan;
		   $namabarang='';
		   $strv="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$bar->kodebarang."'";
		   $resv=$owlPDO->query($strv) or die(print " Gagal: ".PDOException::getMessage());
		   $resv->setFetchMode(PDO::FETCH_OBJ);
		   while($barv=$resv->fetch())
		   {
		   	$namabarang=$barv->namabarang;
		   }
				if($catatan!=''){	
					$pdf->Cell(8,5,$no,'TRL',0,'L',1);
					$pdf->Cell(30,5,$kodebarang,'TRL',0,'L',1);
					$pdf->Cell(100,5,printSpecialChar($namabarang),'TRL',0,'L',1);	
					$pdf->Cell(20,5,$satuan,'TRL',0,'L',1);	
					$pdf->Cell(20,5,number_format($jumlah,2,'.',','),'TRL',1,'R',1);
					$pdf->SetFont('Arial','I',8);
					$pdf->Cell(8,5,'','BRL',0,'L',1);
					$pdf->Cell(30,5,'','BRL',0,'L',1);
					$pdf->Cell(100,5,'Catatan : '.$catatan,'BRL',0,'L',1);	
					$pdf->Cell(20,5,'','BRL',0,'L',1);	
					$pdf->Cell(20,5,'','BRL',1,'R',1);
				}else{
					$pdf->SetFont('Arial','',9);
					$pdf->Cell(8,5,$no,1,0,'L',1);
					$pdf->Cell(30,5,$kodebarang,1,0,'L',1);
					$pdf->Cell(100,5,printSpecialChar($namabarang),1,0,'L',1);	
					$pdf->Cell(20,5,$satuan,1,0,'L',1);	
					$pdf->Cell(20,5,number_format($jumlah,2,'.',','),1,1,'R',1);
				}
			    	   
		}
//footer================================
        $pdf->Ln();


// if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
// {
	// $namakaryawan=namakaryawan($dbname,$conn,$userid);		
	// $pdf->Cell(20,4,$_SESSION['lang']['dbuat_oleh'],0,0,'L'); 
    // $pdf->Cell(30,4,"[ ........................ ]",0,0,'L'); 
	// $pdf->Cell(40,4,": ".$namakaryawan,0,1,'L'); 
    
	// $pdf->Ln();
	
	// $pdf->Cell(20,4,'Diperiksa',0,0,'L'); 
    // $pdf->Cell(30,4,"[ ........................ ]",0,0,'L'); 
	// $pdf->Cell(40,4,": ",0,1,'L'); 
    // $pdf->Ln();
	
	// if($posted!='')
		// $posted=namakaryawan($dbname,$conn,$posted);		
	// else
		// $posted='';
	   	// $pdf->Cell(20,4,'Diposting',0,0,'L'); 
        // $pdf->Cell(30,4,"[ ........................ ]",0,0,'L');                 
		// $pdf->Cell(40,4,": ".$posted,0,1,'L');
        
        // $pdf->Ln();
                
	   	// $pdf->Cell(20,4,$_SESSION['lang']['mengetahui'],0,0,'L'); 
		// $pdf->Cell(30,4,"[ ........................ ]",0,0,'L');      
		// $pdf->Cell(40,4,": Head Of Procurement",0,1,'L');
// }
// else
// {
	//get user;
	$namakaryawan=namakaryawan($dbname,$conn,$userid);	
	
	$subgdg = explode('/',$nopo);
	$countApp = getCountApproval('GR',substr($kodegudang,0,4));
	$widthApp = 178 /($countApp+2);
	$locimg = ($widthApp/2) - 3;
	
	$pdf->Cell($widthApp,4,$_SESSION['lang']['dbuat_oleh'],0,0,'C'); 
	for($i=1;$i<=$countApp;$i++)
	{
		$pdf->Cell($widthApp,4,$_SESSION['lang']['persetujuan']." ".$i,0,0,'C'); 
	}
	$pdf->Cell($widthApp,4,$_SESSION['lang']['posted'],0,0,'C'); 
	$pdf->Ln();
	$y=$pdf->GetY();
	
	$pdf->Ln(20);
	
	$ttdpembuat = makeOption($dbname,'setup_ttd','karyawanid,file',"karyawanid='".$userid."'");
	$ttdposting = makeOption($dbname,'setup_ttd','karyawanid,file',"karyawanid='".$posted."'");
	
	$ttd1 = $ttdpembuat[$userid];
	$ttd4 = $ttdposting[$posted];
	
	if($posted!='')
		$posted=namakaryawan($dbname,$conn,$posted);		
	else
		$posted='';
	
	//Tanda Tangan dan Nama Karyawan
	if(isset($ttd1))
		$pdf->Image($ttd1, $locimg, $y, 0, 20);
	$pdf->Cell($widthApp,4,$namakaryawan,0,0,'C'); 
	
	for($i=1;$i<=$countApp;$i++)
	{
		$locimg = $locimg + $widthApp;
		$arrDetail = detailApprove($i,$nodok,'GR');
		
		$ttdApp = makeOption($dbname,'setup_ttd','karyawanid,file',"karyawanid='".$arrDetail['karyawanid']."'");
		
		if($arrDetail['status']==0 || $arrDetail['status']==2)
		{
			$pdf->Cell($widthApp,4,'',0,0,'C');
		}
		else
		{
			if(isset($ttdApp[$arrDetail['karyawanid']]))
			{
				$pdf->Image($ttdApp[$arrDetail['karyawanid']], $locimg, $y, 0, 20);
			}
			$pdf->Cell($widthApp,4,$arrDetail['nama'],0,0,'C');
		} 
	}
	
	if(isset($ttd4))
		$pdf->Image($ttd4, ($locimg+$widthApp), $y, 0, 20);
	$pdf->Cell($widthApp,4,$posted,0,0,'C'); 

	//Tanda Tangan dan Nama Karyawan



	$pdf->Ln();
	$pdf->Cell($widthApp,4,$tanggal,0,0,'C'); 
	
	for($i=1;$i<=$countApp;$i++)
	{
		$arrDetail = detailApprove($i,$nodok,'GR');
		if($arrDetail['status']==0 || $arrDetail['status']==2)
		{
			$pdf->Cell($widthApp,4,'',0,0,'C');
		}
		else
		{
			$pdf->Cell($widthApp,4,tanggalnormal($arrDetail['tanggal']),0,0,'C');
		} 
	}
	
	if($posted!='')
	$pdf->Cell($widthApp,4,$lastupdate,0,0,'C'); 

	$urlefil=checkPostGet('urlefil','0');
	if($urlefil=='0'){
		$pdf->Output();
	}else{
		$pdf->Output($urlefil);
	}
?>
