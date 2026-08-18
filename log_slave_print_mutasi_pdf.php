<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zFunction.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
$nodok=$_GET['notransaksi'];

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
	global $lastupdate;
	global $kodegudang;
	global $kegudang;
	global $catatan;
	global $kenama;
	global $owlPDO;
	
	global $driver;
	
                       
	
		$pt='';
		$namapt='';
		$alamatpt='';
		$telp='';
		$kodegudang='';
		$status=0;
		$str="select * from ".$dbname.".log_transaksiht where notransaksi='".$_GET['notransaksi']."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())
		{
			$kodept=$bar->kodept;
			$kodegudang=$bar->kodegudang;
			$userid=$bar->user;
			$posted=$bar->postedby;
			$status=$bar->post;
			$tanggal=$bar->tanggal;
			$lastupdate=$bar->lastupdate;
			$kegudang=$bar->gudangx;	
			$catatan=$bar->keterangan;
			$nosj=$bar->nosj;
			
			$driver=$bar->driver;
			$hpdriver=$bar->hpdriver;
			$nokendaraan=$bar->nokendaraan;
			$nopol=$bar->nopol;
			$jeniskendaraan=$bar->jeniskendaraan;
			$expeditor=$bar->expeditor;
			$nosj=$bar->nosj;
                       
                       
			if($status==0)
			 $status='Not Confirm';
			else
			 $status='Confirmed'; 
			//ambil nama pt
			   
			   $str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$kodept."'";
			   $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			   $res1->setFetchMode(PDO::FETCH_OBJ);
			   while($bar1=$res1->fetch())
			   {
			   	 $namapt=$bar1->namaorganisasi;
				 $alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
				 $telp=$bar1->telepon;				 
			   } 
		}	
                
                $kenama=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
		$arrHead = setheadreport(getindukPT(substr($kodegudang,0,4)),getindukPT(substr($kodegudang,0,4)));
				
		$width = $this->w - $this->lMargin - $this->rMargin;
		$height = 5;
		$path=$arrHead['logo'];
		$this->Image($path,$this->lMargin,($this->tMargin-8),0,19);
		$this->SetFont('Arial','B',9);
		$this->SetFillColor(255,255,255);	
		$this->SetXY(40,7);   
		$this->Cell($width-100,$height,$arrHead['nama'],0,1,'L');	 
		$this->SetX(40); 		
		$this->Cell($width-100,$height,$arrHead['alamat'],0,1,'L');	
		$this->SetX(40); 			
		$this->Cell($width-100,$height,"Tel: ".$arrHead['telepon'],0,1,'L');	
		$this->Line($this->lMargin,$this->tMargin+($height*3.5),
		$this->lMargin+$width,$this->tMargin+($height*3.5));
		$this->Ln();
		$this->Ln();
                
		// $path='images/logo.jpg';
	    // $this->Image($path,15,5,18);	
		// $this->SetFont('Arial','B',10);
		// $this->SetFillColor(255,255,255);	
		// $this->SetY(5);   
		// $this->SetX(40);   
	    // $this->Cell(60,5,$namapt,0,1,'L');	 
		// $this->SetX(40); 		
	    // $this->MultiCell(150,5,$alamatpt,0,'L');	
		// $this->SetX(40); 			
		// $this->Cell(60,5,"Tel: ".$telp,0,1,'L');	
		$this->SetFont('Arial','',15);
		$this->SetY(30);		
	    $this->Cell(190,5,strtoupper($_SESSION['lang']['dokmutasi']),0,1,'C');
		$this->SetFont('Arial','',6); 
		$this->SetY(27);
		$this->SetX(163);
        $this->Cell(30,10,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'L');		
		// $this->Line(10,27,200,27);	
		$this->SetY(50);
	    $this->SetFont('Arial','',9);		
		$this->Cell(30,4,$_SESSION['lang']['sloc'],0,0,'L'); 
		$this->Cell(40,4,": ".$kodegudang." - ".$kenama[$kodegudang]."" ,0,1,'L'); 
		$this->Cell(30,4,$_SESSION['lang']['docnum'],0,0,'L'); 
		$this->Cell(40,4,": ".$nodok,0,1,'L'); 				
		$this->Cell(30,4,$_SESSION['lang']['docstatus'],0,0,'L'); 
		$this->Cell(40,4,": ".$status,0,1,'L'); 	
                
		$this->Cell(30,4,$_SESSION['lang']['tujuan'],0,0,'L'); 
		$this->Cell(40,4,": ".$kegudang." - ".$kenama[$kegudang].", UNIT ".$kenama[substr($kegudang,0,4)]." " ,0,1,'L'); 
		if($nosj != ''){
			$this->Cell(30,4,$_SESSION['lang']['suratjalan'],0,0,'L'); 
			$this->Cell(40,4,": ".$nosj,0,1,'L'); 	
		}	
		
		if($expeditor != ''){
			$this->Cell(30,4,$_SESSION['lang']['expeditor'],0,0,'L'); 
			$this->Cell(40,4,": ".$expeditor,0,1,'L'); 	
		}
		
		if($jeniskendaraan != ''){
			$this->Cell(30,4,'Jenis Kendaraan',0,0,'L'); 
			$this->Cell(40,4,": ".$jeniskendaraan,0,1,'L'); 
		}

		if($nopol != ''){
			$this->Cell(30,4,$_SESSION['lang']['nopol'],0,0,'L'); 
			$this->Cell(40,4,": ".$nopol,0,1,'L');
		}
		
		if($driver != ''){
			$this->Cell(30,4,$_SESSION['lang']['supir'],0,0,'L'); 
			$this->Cell(40,4,": ".$driver,0,1,'L'); 	
		}
		if($hpdriver != ''){
			$this->Cell(30,4,'HP Driver',0,0,'L'); 
			$this->Cell(40,4,": ".$hpdriver,0,1,'L'); 
		}
                       
		/*
		if($nosj != ""){
			
			$nosj="select * from ".$dbname.".log_suratjalanht where nosj='".$nosj."' limit 1";
			$rnosj=$owlPDO->query($nosj) or die(print " Gagal: ".PDOException::getMessage());
			$rnosj->setFetchMode(PDO::FETCH_OBJ);
			while($brnosj=$rnosj->fetch())
			{
				//surat jalan bila ada
				$this->Cell(30,4,$_SESSION['lang']['suratjalan'],0,0,'L'); 
				$this->Cell(40,4,": ".$brnosj->nosj,0,1,'L'); 	
				
				$this->Cell(30,4,$_SESSION['lang']['expeditor'],0,0,'L'); 
				$this->Cell(40,4,": ".$brnosj->expeditor,0,1,'L'); 	
				
				$this->Cell(30,4,'Jenis Kendaraan',0,0,'L'); 
				$this->Cell(40,4,": ".$brnosj->jeniskend,0,1,'L'); 

				$this->Cell(30,4,$_SESSION['lang']['nopol'],0,0,'L'); 
				$this->Cell(40,4,": ".$brnosj->nopol,0,1,'L');
				
				$this->Cell(30,4,$_SESSION['lang']['supir'],0,0,'L'); 
				$this->Cell(40,4,": ".$brnosj->driver,0,1,'L'); 	
				
				$this->Cell(30,4,'HP Driver',0,0,'L'); 
				$this->Cell(40,4,": ".$brnosj->hpdriver,0,1,'L'); 
			}
		}
		*/
		 		
                
	}
	
	function Footer()
	{
	    $this->SetY(-15);
	    $this->SetFont('Arial','I',8);
	    $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
	}

}

/*
    print"<pre>";
	print_r($_SESSION);
	print"</pre>";
*/
	$pdf=new PDF('P','mm','A4');
	$pdf->AddPage();
			
//ambil kelengkapan

     $hari=hari($tanggal,$_SESSION['language']);
	 $tanggal=tanggalnormal($tanggal);
	 $lastupdate=tanggalnormal($lastupdate);
	 $resc=str_replace("#DATE_REPARAM#",$hari.", ".$tanggal,$_SESSION['lang']['premutasi']);
	 $resc=strtoupper(str_replace("#SLOC_PARAM#",$kodegudang." - ".$kenama[$kodegudang],$resc));
    
	$pdf->Ln();
    $pdf->Ln();	
    $pdf->MultiCell(170,5,$resc,0,'L');	 		 		
    $pdf->Ln();
	$pdf->SetFont('Arial','B',9);	
	$pdf->SetFillColor(220,220,220);
    $pdf->Cell(8,5,'No',1,0,'L',1);
    $pdf->Cell(30,5,$_SESSION['lang']['kodebarang'],1,0,'C',1);
    $pdf->Cell(85,5,$_SESSION['lang']['namabarang'],1,0,'C',1);	
    $pdf->Cell(18,5,$_SESSION['lang']['satuan'],1,0,'C',1);		
    $pdf->Cell(20,5,$_SESSION['lang']['kuantitas'],1,1,'C',1);	
	//$pdf->Cell(25,5,$_SESSION['lang']['tujuan'],1,1,'C',1);
	$pdf->SetFillColor(255,255,255);
	    $pdf->SetFont('Arial','',9);
		
		$str="select * from ".$dbname.".log_transaksidt where notransaksi='".$_GET['notransaksi']."' order by waktutransaksi asc ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$no=0;
		while($bar=$res->fetch())
		{
			$no+=1;
			
			$kodebarang=$bar->kodebarang;
			$satuan=$bar->satuan;
			$jumlah=$bar->jumlah;
		   $namabarang='';
		   $strv="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$bar->kodebarang."'";
		   $resv=$owlPDO->query($strv) or die(print " Gagal: ".PDOException::getMessage());
		   $resv->setFetchMode(PDO::FETCH_OBJ);
		   while($barv=$resv->fetch())
		   {
		   	$namabarang=$barv->namabarang;
		   }
			    $pdf->Cell(8,5,$no,1,0,'L',1);
			    $pdf->Cell(30,5,$kodebarang,1,0,'L',1);
			    $pdf->Cell(85,5,printSpecialChar($namabarang),1,0,'L',1);	
			    $pdf->Cell(18,5,$satuan,1,0,'L',1);	
				$pdf->Cell(20,5,numberformat_kasih_koma($jumlah),1,1,'R',1);
			//	$pdf->Cell(25,5,$kegudang,1,1,'C',1);		
			    	   
		}
       $pdf->MultiCell(170,5,"Note: ".$catatan,0,'L');			
//footer================================
        $pdf->Ln();
//get user;
		$optUser=makeOption($dbname, 'user', 'karyawanid,namauser');
       $namakaryawan=namakaryawan($dbname,$conn,$userid);		

		if($posted!='')
			$posted2=namakaryawan($dbname,$conn,$posted);		
		else
			$posted2='';
	   	
		$pdf->Cell(20,4,$_SESSION['lang']['posted'],0,0,'L'); 
		$pdf->Cell(40,4,": ".$posted2." / ".$lastupdate,0,1,'L');	

	$pdf->ln();
	
	$pdf->Cell(60,4,$_SESSION['lang']['dbuat_oleh'].",",0,0,'C'); 
	$pdf->Cell(60,4,$_SESSION['lang']['supir'].",",0,0,'C'); 
	$pdf->Cell(60,4,$_SESSION['lang']['penerima'].",",0,0,'C'); 
	$pdf->ln(20);
	$pdf->Cell(60,4,"(".$namakaryawan.")",0,0,'C'); 
	$pdf->Cell(60,4,"(".$driver.")",0,0,'C'); 
	$pdf->Cell(60,4,"(                                 )",0,0,'C'); 

		
	$pdf->Output();
?>
