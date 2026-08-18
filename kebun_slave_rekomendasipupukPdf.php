<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
//require_once('lib/zFunction.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');

$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];
//=============

 class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
				global $periode;
				global $owlPDO;
                
				//$periode=$_GET['column'];

                
                # Alamat & No Telp
                $query = selectQuery($dbname,'organisasi','alamat,telepon',
                    "kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
                $orgData = fetchData($query);
                
                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 12;
                $path='images/logo.jpg';
                $this->Image($path,$this->lMargin,$this->tMargin,0,55);
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
                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['user'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(45/100*$width,$height,ucfirst($_SESSION['standard']['username']),'',0,'L');
             /*   $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['periode'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(15/100*$width,$height, $periode,0,0,'L');*/
                $this->Ln();
                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['tanggal'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(45/100*$width,$height,date('d-m-Y H:i:s'),'',0,'L');
              	
              
				
                $this->Ln();
                $this->SetFont('Arial','U',12);
                $this->Cell($width,$height,$_SESSION['lang']['rekomendasiPupuk'],0,1,'C');	
                $this->Ln();	
				
                $this->SetFont('Arial','B',9);	
                $this->SetFillColor(220,220,220);
			   // $this->Cell(10/100*$width,$height,'No',1,0,'C',1);
                /*foreach($colArr as $key=>$head) {
                    $this->Cell($length[$key]/100*$width,$height,$_SESSION['lang'][$head],1,0,'C',1);
                }*/
				$this->Cell(3/100*$width,$height,'No',1,0,'C',1);
				$this->Cell(12/100*$width,$height,$_SESSION['lang']['tahunpupuk'],1,0,'C',1);	
				$this->Cell(21/100*$width,$height,$_SESSION['lang']['afdeling'],1,0,'C',1);
				$this->Cell(12/100*$width,$height,$_SESSION['lang']['blok'],1,0,'C',1);						
				$this->Cell(13/100*$width,$height,$_SESSION['lang']['tahuntanam'],1,0,'C',1);
				$this->Cell(16/100*$width,$height,$_SESSION['lang']['jenisPupuk'],1,0,'C',1);		
				$this->Cell(7/100*$width,$height,$_SESSION['lang']['dosis'],1,0,'C',1);
				$this->Cell(7/100*$width,$height,$_SESSION['lang']['satuan'],1,0,'C',1);
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['jenisbibit'],1,1,'C',1);
            
            }
                
            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
        $pdf=new PDF('P','pt','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
		$pdf->AddPage();
		$pdf->SetFillColor(255,255,255);
		$pdf->SetFont('Arial','',9);
		
		$str="select * from ".$dbname.".".$_GET['table']." where substring(kodeorg,1,4)='".$_SESSION['empl']['lokasitugas']."' order by periodepemupukan asc"; //echo $str;exit();
        $re = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $re->setFetchMode(PDO::FETCH_ASSOC);        
		$no=0;
		while($res=$re->fetch())
		{
			$skdBrg="select  namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$res['kodebarang']."'";//echo $skdBrg;
            $qkdBrg = $owlPDO->query($skdBrg) or die(print " Gagal: " . PDOException::getMessage());
            $qkdBrg->setFetchMode(PDO::FETCH_ASSOC);              
			$rBrg=$qkdBrg->fetch();
			
			$sBibit="select jenisbibit  from ".$dbname.".setup_jenisbibit where jenisbibit='".$res['jenisbibit']."'" ;
            $qBibit = $owlPDO->query($sBibit) or die(print " Gagal: " . PDOException::getMessage());
            $qBibit->setFetchMode(PDO::FETCH_ASSOC);             
			$rBibit=$qBibit->fetch();
			
			$sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$res['kodeorg']."'";
            $qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
            $qOrg->setFetchMode(PDO::FETCH_ASSOC);             
			$rOrg=$qOrg->fetch();	
			
			$no+=1;
			$pdf->Cell(3/100*$width,$height,$no,1,0,'L',1);
			$pdf->Cell(12/100*$width,$height,$res['periodepemupukan'],1,0,'L',1);	
			$pdf->Cell(21/100*$width,$height,$rOrg['namaorganisasi'],1,0,'L',1);		
			$pdf->Cell(12/100*$width,$height,$res['blok'],1,0,'L',1);					
			$pdf->Cell(13/100*$width,$height,$res['tahuntanam'],1,0,'C',1);
			$pdf->Cell(16/100*$width,$height,$rBrg['namabarang'],1,0,'L',1);		
			$pdf->Cell(7/100*$width,$height,$res['dosis'],1,0,'R',1);
			$pdf->Cell(7/100*$width,$height,$rBrg['satuan'],1,0,'L',1);
			$pdf->Cell(10/100*$width,$height,$rBibit['jenisbibit'],1,1,'L',1);
    	   
		}
	
        $pdf->Output();
?>