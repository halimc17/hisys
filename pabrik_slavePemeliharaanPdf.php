<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
//require_once('lib/zFunction.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');

/*echo "<pre>";
print_r($_GET);
echo "</pre>";
*/
$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];
//=============

//create Header
class PDF extends FPDF
{
	
	function Header()
	{
 	global $conn;
	global $dbname;
    global $userid;
	global $noTrans;
	global $rtgl;
	global $shift;
	global $sts;
	global $msn;
	global $jmMulai;
	global $jmAkhir;
	global $kegiatan;
	global $posting;

			
			$noTrans=$_GET['column'];

			$sql="select * from ".$dbname.".".$_GET['table']."  where notransaksi='".$noTrans."' ";// echo $sql; exit();
			//echo $str;exit();
			$res=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			$bar=$res->fetch();
			
			
				$rtgl=tanggalnormal($bar->tanggal);
				$shift=$bar->shift;
				$sts=$bar->statasiun;
				$msn=$bar->mesin;
				$jmMulai=tanggalnormald($bar->jammulai);
				$jmAkhir=tanggalnormald($bar->jamselesai);
				$kegiatan=$bar->kegiatan;
				$posting=$bar->statPost;
				
				
			   $str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'"; 
			   $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			   $res1->setFetchMode(PDO::FETCH_OBJ);
			   while($bar1=$res1->fetch()){
			   	 $nama=$bar1->namaorganisasi;
				 $alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
				 $telp=$bar1->telepon;				 
			   }    
			   	$kdDiv=substr($noTrans,5,6);
				$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kdDiv."'";
				$namapt='COMPANY NAME';
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			   	$res->setFetchMode(PDO::FETCH_OBJ);
				while($bar=$res->fetch()){
					$namapt=strtoupper($bar->namaorganisasi);
				}	
				
		$path='images/logo.jpg';
	    $this->Image($path,15,5,0,25);	
		$this->SetFont('Arial','B',10);
		$this->SetFillColor(255,255,255);	
		$this->SetX(55);   
	    $this->Cell(60,5,$nama,0,1,'L');	 
		$this->SetX(55); 		
	    $this->Cell(60,5,$alamatpt,0,1,'L');	
		$this->SetX(55); 			
		$this->Cell(60,5,"Tel: ".$telp,0,1,'L');	
		$this->Ln();
		$this->Ln();
		
		$this->SetFont('Arial','B',13); 
		$this->Cell(190,5,strtoupper($_SESSION['lang']['pemeliharaanMesin']),0,1,'C');
		$this->Ln();
		if($posting<1)
		{
			$this->Cell(190,5,$_SESSION['lang']['belumposting'],0,1,'C');
		}
		$this->SetFont('Arial','B',8);
		$this->Cell(20,5,$namapt,'',1,'L');
		$this->SetFont('Arial','',8);
			$this->Cell(35,5,$_SESSION['lang']['notransaksi'],'',0,'L');
			$this->Cell(2,5,':','',0,'L');
			$this->Cell(100,5,$noTrans,'',0,'L');		
			$this->Cell(15,5,$_SESSION['lang']['tanggal'],'',0,'L');
			$this->Cell(2,5,':','',0,'L');
			$this->Cell(35,5,$rtgl,0,1,'L');
			//		$this->Cell(140,5,' ','',0,'R');
			$this->Cell(35,5,$_SESSION['lang']['shift'],'',0,'L');
			$this->Cell(2,5,':','',0,'L');
			$this->Cell(100,5,$shift,'',0,'L');		
			$this->Cell(15,5,$_SESSION['lang']['statasiun'],'',0,'L');
			$this->Cell(2,5,':','',0,'L');
			$this->Cell(35,5,$sts,'',1,'L');
			
			//			$this->Cell(140,5,' ','',0,'R');
			$this->Cell(35,5,$_SESSION['lang']['dari'],'',0,'L');
			$this->Cell(2,5,':','',0,'L');
			$this->Cell(100,5,$jmMulai,'',0,'L');		
			$this->Cell(15,5,$_SESSION['lang']['sampai'],'',0,'L');
			$this->Cell(2,5,':','',0,'L');
			$this->Cell(35,5,$jmAkhir,'',1,'L');
			//	        $this->Ln();	
			$this->Cell(35,5,$_SESSION['lang']['mesin'],'',0,'L');
			$this->Cell(2,5,':','',0,'L');
			$this->Cell(100,5,$msn,'',0,'L');		
			$this->Cell(15,5,'User','',0,'L');
			$this->Cell(2,5,':','',0,'L');
			$this->Cell(35,5,$_SESSION['standard']['username'],'',1,'L');
			$this->Line(10,30,205,30);	
			$this->Cell(35,5,$_SESSION['lang']['kegiatan'],'',0,'L');
			$this->Cell(2,5,':','',0,'L');
			$this->Cell(100,5,$kegiatan,'',0,'L');

     $this->Ln();
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

	$pdf->Ln();
	$str="select * from ".$dbname.".pabrik_rawatmesindt   where notransaksi='".$noTrans."'"; //echo $str;exit();
	$re=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$re->setFetchMode(PDO::FETCH_ASSOC);
	$rBaris=owlBaris($re);
	if($rBaris!=0)
	{

	$pdf->SetFont('Arial','U',15);
	//$pdf->SetY(75);
	$pdf->Cell(190,5,$_SESSION['lang']['detail'],0,1,'C');	
	$pdf->Ln();	
	$pdf->SetFont('Arial','B',9);	
	$pdf->SetFillColor(220,220,220);
    $pdf->Cell(8,5,'No',1,0,'L',1);
	$pdf->Cell(30,5,$_SESSION['lang']['kodebarang'],1,0,'C',1);	
	$pdf->Cell(60,5,$_SESSION['lang']['namabarang'],1,0,'C',1);					
	$pdf->Cell(20,5,$_SESSION['lang']['satuan'],1,0,'C',1);
	$pdf->Cell(20,5,$_SESSION['lang']['jumlah'],1,0,'C',1);		
	$pdf->Cell(55,5,$_SESSION['lang']['keterangan'],1,1,'C',1);
	//$pdf->Cell(25,5,'Total',1,1,'C',1);
	$pdf->SetFillColor(255,255,255);
	$pdf->SetFont('Arial','',9);
		
				$no=0;
			while($res=$re->fetch())
			{
				$no+=1;
				$sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$res['kodebarang']."'";
				$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
				$qBrg->setFetchMode(PDO::FETCH_ASSOC);
				$rBrg=$qBrg->fetch();
				
				$pdf->Cell(8,5,$no,1,0,'L',1);
				$pdf->Cell(30,5,$res['kodebarang'],1,0,'L',1);	
				$pdf->Cell(60,5,$rBrg['namabarang'],1,0,'L',1);					
				$pdf->Cell(20,5,$res['satuan'],1,0,'L',1);
				$pdf->Cell(20,5,number_format ($res['jumlah'],2),1,0,'R',1);		
				$pdf->Cell(55,5,$res['keterangan'],1,1,'L',1);	
			   
			}
			
		}
	
	$pdf->Output();
?>
