<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');
include_once('lib/terbilang.php');

$proses=$_GET['proses'];
$param=$_GET;

#=============================== Header =======================================
$where = "notadebet='".$param['notadebet']."'";
$queryH = selectQuery($dbname,'keu_notadebet_ht','*',$where);
$resH = fetchData($queryH);
$dataH = $resH[0];

#=============================== Detail =======================================

# Data
$col1 = '*';
$query_detail = selectQuery($dbname,'keu_notadebet_dt',$col1,$where);
$red          = fetchData($query_detail);

$col2 = 'noakun,namaakun';
$queryakun = selectQuery($dbname,'keu_5akun',$col2,'');
$rea          = fetchData($queryakun);

function find_dom($array,$code,$compare,$findout){
    $result = "";
    foreach($array as $r){
        if($code == $r[$compare]){
            $result = $r[$findout];
            break;
        }
    }
    return $result;
}

$optPt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$dataH['kodeorg']."'");
$title = "NOTA DEBET";

	class PDF extends FPDF
    {
        function Header()
        {
            global $conn;
            global $dbname;
            global $userid;
            global $notransaksi;
            global $kodevhc;
            global $posting;
            global $kodept;
            global $param;
            global $owlPDO;
            global $dataH;
            global $title;


            //ambil nama pt
			$arrHead = setheadreport('',$dataH['kodeorg']);
		
			
            $width = $this->w - $this->lMargin - $this->rMargin;
            $height = 5;
            $path=$arrHead['logo'];
            $this->Image($path,$this->lMargin,($this->tMargin-8),0,20);
            $this->SetFont('Arial','B',9);
            $this->SetFillColor(255,255,255);	
			$this->Ln(-5);
            $this->SetX(35);   
            $this->Cell($width-100,$height,$arrHead['nama'],0,1,'L');	 
            $this->SetX(35); 		
            $this->Cell($width-100,$height,$arrHead['alamat'],0,1,'L');	
            $this->SetX(35); 			
            $this->Cell($width-100,$height,"Tel: ".$arrHead['telepon'],0,1,'L');	
            $this->Line($this->lMargin,$this->tMargin+($height*3),
			$this->lMargin+$width,$this->tMargin+($height*3));
			$this->Ln();
			$this->Ln();
			$this->SetFont('Arial','',10);
			$this->Cell($width,$height,$title,0,1,'C');	 
			
            $this->Ln();
                            
        }

        function Footer()
        {
            $width = $this->w - $this->lMargin - $this->rMargin;
            $height = 12;
            $this->SetY(-20);
            $this->SetFont('Arial','I',7);
            $this->Cell(1,$height,'Page '.$this->PageNo(),'T',0,'L');
            $str = "Printed by ".$_SESSION['standard']['username']."[".$_SESSION['empl']['lokasitugas']."]".
                    ":".@$rPeriode['periode']." at ".date('d-m-Y H:i:s');
            $this->Cell($width-1,$height,$str,'T',0,'R');
        }
    }
	
	
	$pdf=new PDF('P','mm','A4');
    // $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
    $height = 4;
	
    // $pdf->_noThead=true;
    $pdf->_title = $title;
    // $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;

    $pdf->AddPage();
    $pdf->SetFillColor(255,255,255);
    $pdf->SetFont('Arial','',9);

    $pdf->Ln();
    // Header
    $startY = $pdf->GetY();
	$width1=30;
    $pdf->Cell($width1,$height,$_SESSION['lang']['notadebet'],0,0,'L');
	$pdf->Cell(5,$height,':',0,0,'L');
    $pdf->Cell(25,$height,$dataH['notadebet'],0,1,'L');
    
    $pdf->Cell($width1,$height,$_SESSION['lang']['pt'],0,0,'L');
	$pdf->Cell(5,$height,':',0,0,'L');
    $pdf->Cell(25,$height,$optPt[$dataH['kodeorg']],0,1,'L');
    
    $pdf->Cell($width1,$height,$_SESSION['lang']['tanggal'],0,0,'L');
	$pdf->Cell(5,$height,':',0,0,'L');
    $pdf->Cell(25,$height,tanggalnormal($dataH['tanggal']),0,1,'L');
    
    $pdf->Cell($width1,$height,$_SESSION['lang']['keterangan'],0,0,'L');
	$pdf->Cell(5,$height,':',0,0,'L');
    $pdf->MultiCell(70,$height,$dataH['keterangan'],0,'J');
    
    $pdf->Cell($width1,$height,$_SESSION['lang']['nilaiinvoice'],0,0,'L');
    $pdf->Cell(5,$height,':',0,0,'L');
    $pdf->Cell(25,$height,number_format($dataH['nilaiinvoice'],2),0,1,'L');
	
	$pdf->Cell($width1,$height,$_SESSION['lang']['unit'],0,0,'L');
    $pdf->Cell(5,$height,':',0,0,'L');
    $pdf->Cell(25,$height,$dataH['unit'],0,1,'L');

    $optNmPosting=makeOption($dbname,"datakaryawan","karyawanid,namakaryawan","karyawanid='".$dataH['postingby']."'");
    $pdf->Cell($width1,$height,$_SESSION['lang']['dipostingoleh'],0,0,'L');
    $pdf->Cell(5,$height,':',0,0,'L');
    $pdf->Cell(25,$height,$optNmPosting[$dataH['postingby']],0,1,'L');
			
	$startYhdetail = $pdf->GetY() + 10;  
	//## Data Detail ##
	if(count($red) > 0){
		$pdf->SetXY(10,$startYhdetail);
		$awalxdetail=$pdf->GetX();
		$setpanjang=200;
		$pdf->Cell(80,$height,$_SESSION['lang']['noakun'],1,0,'L');
		$onexdetail=$pdf->GetX();
		$pdf->Cell(30,$height,$_SESSION['lang']['nilai'],1,0,'L');
		$twoxdetail=$pdf->GetX();
		$pdf->Cell(40,$height,$_SESSION['lang']['kodevhc'],1,0,'L');
		$treexdetail=$pdf->GetX();
		$pdf->Cell(40,$height,$_SESSION['lang']['kodeasset'],1,0,'L');
		$akhirYhdetail=$pdf->GetY();
		$akhirXhdetail=$pdf->GetX();
		$jml = 0;
		foreach($red as $row) {
			
			$heightdetail = $height;
			$no = $heightdetail;
			$startYdetail = $pdf->GetY()+5;
			$pdf->SetXY(10,$startYdetail);
			$awalxdetailL=$pdf->GetX();
			$awalydetailL=$pdf->GetY();
			
			$pdf->Cell(80,$heightdetail,$row['noakun']."-".find_dom($rea,$row['noakun'],'noakun','namaakun'),0,0,'L');
			$pdf->Cell(30,$heightdetail,number_format($row['nilai'],0),0,0,'R');
			$pdf->Cell(40,$heightdetail,$row['kodevhc'],0,0,'L');
			$pdf->Cell(40,$heightdetail,$row['kodeasset'],0,0,'L');
			// $jml = $jml + $row['nilai'];
			@$jml+=$row['nilai'];
			@$no+=$no;
		}
		
		$akhirydetailL=$pdf->GetY() + $no; 
		$pdf->Line($onexdetail, $akhirYhdetail, $onexdetail, $akhirydetailL);
		$pdf->Line($twoxdetail, $akhirYhdetail, $twoxdetail, $akhirydetailL);
		$pdf->Line($treexdetail, $akhirYhdetail, $treexdetail, $akhirydetailL);
		
		$pdf->Line($awalxdetail, $akhirYhdetail, $awalxdetail, $akhirydetailL);
		$pdf->Line($akhirXhdetail, $akhirYhdetail, $akhirXhdetail, $akhirydetailL);
		$pdf->Line($awalxdetail, $akhirydetailL, $akhirXhdetail, $akhirydetailL);
		
		$startYhdetail = $pdf->GetY() + $no;  
		$pdf->SetXY(10,$startYhdetail);
		$totalXhdetail=$pdf->GetX();
		$pdf->Cell(80,$height,$_SESSION['lang']['total'],1,0,'L');
		$pdf->Cell(30,$height,number_format($jml,0),1,0,'R');
		$pdf->Cell(80,$height,'',1,0,'L');
		
	}

    $optNmuser=makeOption($dbname,"datakaryawan","karyawanid,namakaryawan","karyawanid='".$dataH['updateby']."'");

    $pdf->Ln(15);
    $pdf->SetX(40);
    $pdf->Cell(50,$height,'Pembuat',0,0,'L');
    $pdf->Cell(50,$height,'',0,0,'L');
    $pdf->Cell(50,$height,$_SESSION['lang']['menyetujui'],0,1,'L');
    $pdf->SetX(40);
    $pdf->Cell(50,20,'',0,0,'L');
    $pdf->Cell(50,20,'',0,0,'L');
    $pdf->Cell(50,20,'',0,1,'L');
    $pdf->SetX(40);
    $pdf->Cell(50,$height,$optNmuser[$dataH['updateby']],0,0,'L');  
    $pdf->Cell(50,$height,'',0,'L');  
    $pdf->Cell(50,$height,$optNmPosting[$dataH['postingby']],0,1,'L');  

    //sisi kanan
    $xawalkanan=115;
    $pdf->SetXY($xawalkanan,$startY);
    $awalx=$pdf->GetX();
    $setpanjang=85;
	
    $pdf->Cell(85,$height,$_SESSION['lang']['tagihan'],1,1,'L');
                  
    $pdf->SetX($xawalkanan); 				
    $pdf->Cell(30,$height,$_SESSION['lang']['noinvoice'],0,0,'L');
	$pdf->Cell(5,$height,':',0,0,'L');
    $pdf->Cell(50,$height,$dataH['noinvoice_referensi'],0,1,'L');
    
    $optNmsp = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$dataH['kodesupplier']."'");
    $pdf->SetX($xawalkanan);    
    $pdf->Cell(30,$height,$_SESSION['lang']['supplier'],0,0,'L');
	$pdf->Cell(5,$height,':',0,0,'L');
    $pdf->MultiCell(50,$height,$optNmsp[$dataH['kodesupplier']],0,'L');
    
    $pdf->SetX($xawalkanan);
    $pdf->Cell(30,$height,$_SESSION['lang']['matauang'],0,0,'L');
	$pdf->Cell(5,$height,':',0,0,'L');
    $pdf->Cell(50,$height,(isset($resSupp[0]['matauang'])? $resSupp[0]['matauang']: 'IDR'),0,1,'L');
    
    $pdf->SetX($xawalkanan);    
    $pdf->Cell(30 ,$height,$_SESSION['lang']['kurs'],'B',0,'L');
	$pdf->Cell(5,$height,':','B',0,'L');
    $pdf->Cell(50,$height,$dataH['kurs'],'B',1,'L');
            
	$endY = $pdf->GetY();
	
	//$pdf->Rect($pdf->lMargin+49.5/100*$width,$startY-1,50.5/100*$width,$endY-$startY-7);
	//$pdf->Line($pdf->lMargin+49.5/100*$width,$startY+11,$pdf->lMargin+$width,$startY+11);
	
    $pdf->Line($awalx, $startY, $awalx, $endY);
    $pdf->Line($awalx+$setpanjang, $startY, $awalx+$setpanjang, $endY);
			
	$pdf->Ln($height);
	
    $pdf->Output();

?>