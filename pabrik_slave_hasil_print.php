<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/fpdf.php');

$proses = $_GET['proses'];
$param = $_POST;


/** Report Prep **/
# Data
$cols = 'tanggal,kodeorg,kodetangki,kuantitas,suhu,cporendemen,cpoffa,'.
    'cpokdair,cpokdkot,kernelquantity,kernelrendemen,kernelkdair,kernelkdkot,kernelffa';
$colArr = explode(',',$cols);
$where = "kodeorg='".$_SESSION['empl']['lokasitugas']."' order by tanggal, kodetangki";
$query = selectQuery($dbname,'pabrik_masukkeluartangki',$cols,$where);
$data = fetchData($query);

$title = $_SESSION['lang']['pabrikhasil'];
$align = explode(",","L,L,L,L,R,R,R,R,R,R,R,R,R,R,R");
$length = explode(",","8,6,10,6,6,6,6,6,6,6,6,6,6,6,6");

# Options
$whereOrg = "kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whereOrg,'0',true);

# Data Show
$dataShow = $data;
foreach($dataShow as $key=>$row) {
    $dataShow[$key]['kodeorg'] = $optOrg[$row['kodeorg']];
    $dataShow[$key]['tanggal'] = tanggalnormal($row['tanggal']);
	$dataShow[$key]['kuantitas'] = number_format($row['kuantitas'],2);
	$dataShow[$key]['kernelquantity'] = number_format($row['kernelquantity'],2);
}

/** Output Format **/
switch($proses) {
    case 'pdf':
        class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
                
                
				$arrHead = setheadreport('',$_SESSION['org']['kodeorganisasi']);
				
                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 15;
                $path=$arrHead['logo'];
                $this->Image($path,$this->lMargin,($this->tMargin-12),0,55);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(110);   
                $this->Cell($width-100,$height,$arrHead['nama'],0,1,'L');	 
                $this->SetX(110); 		
                $this->Cell($width-100,$height,$arrHead['alamat'],0,1,'L');	
                $this->SetX(110); 			
                $this->Cell($width-100,$height,"Tel: ".$arrHead['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
				$this->lMargin+$width,$this->tMargin+($height*4));
					
                $this->Ln();
				
				
                $this->Ln();
                $this->SetFont('Arial','',8);
                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['kodeorg'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(45/100*$width,$height,$_SESSION['empl']['lokasitugas'],'',0,'L');
                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['periode'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(15/100*$width,$height,
                    numToMonth($_SESSION['org']['period']['bulan'],'I','long')." ".
                    $_SESSION['org']['period']['tahun'],0,0,'L');
                $this->Ln();
                
                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['user'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(45/100*$width,$height,$_SESSION['standard']['username'],'',0,'L');
                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['tanggal'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(15/100*$width,$height,date('d-m-Y H:i:s'),'',1,'L');		
                
                $this->Ln();
                $this->SetFont('Arial','U',12);
                $this->Cell($width,$height,strtoupper($title),0,1,'C');	
                $this->Ln();	
                $this->SetFont('Arial','B',5);	
                $this->SetFillColor(220,220,220);
                foreach($colArr as $key=>$head) {
                    $this->Cell($length[$key]/100*$width,$height,$_SESSION['lang'][$head],1,0,'C',1);
                }
                $this->Ln();
            }
                
            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
        $pdf=new PDF('L','pt','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
	$pdf->AddPage();
        
        $pdf->SetFillColor(255,255,255);
	$pdf->SetFont('Arial','',5);
        foreach($dataShow as $key=>$row) {    
            $i=0;
            foreach($row as $cont) {
                $pdf->Cell($length[$i]/100*$width,$height,$cont,1,0,$align[$i],1);
                $i++;
            }
            $pdf->Ln();
        }
	
        $pdf->Output();
        break;
    case 'excel':
        break;
    default:
    break;
}
?>