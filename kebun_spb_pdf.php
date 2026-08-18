<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');
include_once('lib/zMysql.php');

$pt=checkPostGet('pt','');
$periode=checkPostGet('periode','');
//$pt=substr($pt,0,4);
//$periode=substr($_GET['periode'],0,7);
//ambil namapt
//=================================================

class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
				global $pt;
				global $periode;
				
                
				$noSpb=checkPostGet('column','');
				$nospb=substr($noSpb,8,6);
                
                # Alamat & No Telp
                $query = selectQuery($dbname,'organisasi','alamat,telepon',
                    "kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
                $orgData = fetchData($query);
                
                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 12;
                $arrHead = setheadreport('',$_SESSION['org']['kodeorganisasi']);
                $path=$arrHead['logo'];
                // $path='images/logo.jpg';
                $this->Image($path,$this->lMargin,$this->tMargin,0,45);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(100);   
                $this->Cell($width-100,$height,$_SESSION['org']['namaorganisasi'],0,1,'L');	 
                $this->SetX(100); 		
                $this->Cell($width-100,$height,$orgData[0]['alamat'],0,1,'L');	
                $this->SetX(100); 			
                $this->Cell($width-100,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
                    $this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();
                
                $this->SetFont('Arial','',8);
                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['kodeorg'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(45/100*$width,$height,$pt,'',0,'L');
                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['user'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(15/100*$width,$height,
                   $_SESSION['standard']['username'],0,0,'L');
                $this->Ln();
				$query2 = selectQuery($dbname,'organisasi','namaorganisasi',
				"kodeorganisasi='".$pt."'");
				$orgData2 = fetchData($query2);
                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['namaorganisasi'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(45/100*$width,$height,$orgData2[0]['namaorganisasi'],'',0,'L');
              	$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['tanggal'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
               	$this->Cell(15/100*$width,$height,date('d-m-Y H:i:s'),'',1,'L');		
                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['periode'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(45/100*$width,$height,$periode,'',0,'L');
				
                $this->Ln();
                $this->SetFont('Arial','U',12);
                $this->Cell($width,$height,strtoupper($_SESSION['lang']['listSpb']),0,1,'C');	
                $this->Ln();	
				
                $this->SetFont('Arial','B',9);	
                $this->SetFillColor(220,220,220);
			   // $this->Cell(10/100*$width,$height,'No',1,0,'C',1);
                /*foreach($colArr as $key=>$head) {
                    $this->Cell($length[$key]/100*$width,$height,$_SESSION['lang'][$head],1,0,'C',1);
                }*/
				$this->Cell(3/100*$width,$height,'No',1,0,'C',1);
				$this->Cell(15/100*$width,$height,$_SESSION['lang']['nospb'],1,0,'C',1);	
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);	
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['blok'],1,0,'C',1);	
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['janjang'],1,0,'C',1);
                                $this->Cell(10/100*$width,$height,$_SESSION['lang']['kgwb'],1,0,'C',1);
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['bjr'],1,0,'C',1);
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['brondolan'],1,0,'C',1);		
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['mentah'],1,0,'C',1);
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['busuk'],1,0,'C',1);
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['matang'],1,0,'C',1);
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['lewatmatang'],1,1,'C',1);
            
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
		$pdf->SetFont('Arial','',9);
		
		if(strlen($pt)<6)
		{
			$kdOrg="substr(b.blok,1,4)";
		}
		else
		{
			$kdOrg="substr(b.blok,1,6)";
		}
		$str="select a.tanggal,b.* from ".$dbname.".kebun_spbht a inner join ".$dbname.".kebun_spbdt b on a.nospb=b.nospb 
		where a.tanggal like '%".$periode."%' and ".$kdOrg."='".$pt."' order by a.tanggal asc "; 
		$re=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$re->setFetchMode(PDO::FETCH_ASSOC);
		if($re)
		{
			$no=0;
			while($res=$re->fetch())
			{
				$no+=1;
				
				$pdf->Cell(3/100*$width,$height,$no,1,0,'L',1);
				$pdf->Cell(15/100*$width,$height,$res['nospb'],1,0,'L',1);	
				$pdf->Cell(8/100*$width,$height,tanggalnormal($res['tanggal']),1,0,'C',1);	
				$pdf->Cell(8/100*$width,$height,$res['blok'],1,0,'L',1);	
				$pdf->Cell(8/100*$width,$height,number_format($res['jjg'],2),1,0,'L',1);
                                $pdf->Cell(10/100*$width,$height,number_format($res['kgwb'],2),1,0,'L',1);
				$pdf->Cell(8/100*$width,$height,number_format($res['bjr'],2),1,0,'L',1);
				$pdf->Cell(10/100*$width,$height,number_format($res['brondolan'],2),1,0,'L',1);		
				$pdf->Cell(8/100*$width,$height,number_format($res['mentah'],2),1,0,'L',1);
				$pdf->Cell(8/100*$width,$height,number_format($res['busuk'],2),1,0,'L',1);
				$pdf->Cell(8/100*$width,$height,number_format($res['matang'],2),1,0,'L',1);
				$pdf->Cell(8/100*$width,$height,number_format($res['lewatmatang'],2),1,1,'L',1);	
			   
			}
		}
		else
		{
			$pdf->Cell(102/100*$width,$height,$_SESSION['lang']['datanotfound'],1,1,'C',1);	
		}
	
        $pdf->Output();
?>