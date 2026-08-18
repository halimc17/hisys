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
	$pabrik=$_GET['pabrik'];
	$statId=$_GET['statId'];
	$periode=substr($_GET['periode'],0,7);
	$kdBrg=checkPostGet('kdBrg','');
	$msnId=checkPostGet('msnId','');
//=============

//create Header
class PDF extends FPDF
{
	
	function Header()
	{
 	global $conn;
	global $dbname;
    global $userid;
	global $pabrik;
	global $statId;
	global $periode;
	global $kdBrg;
	global $msnId;
			   $str1="select * from ".$dbname.".organisasi where induk='MHO' and tipe='PT'"; 
			   $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			   $res1->setFetchMode(PDO::FETCH_OBJ);
			   while($bar1=$res1->fetch())
			   {
			   	 $nama=$bar1->namaorganisasi;
				 $alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
				 $telp=$bar1->telepon;				 
			   }    
				$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pabrik."'";
				$namapt='COMPANY NAME';
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			   	$res->setFetchMode(PDO::FETCH_OBJ);
				while($bar=$res->fetch())
				{
				$namapt=strtoupper($bar->namaorganisasi);
				}
				$sNm="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$statId."'";
				$qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
			   	$qNm->setFetchMode(PDO::FETCH_ASSOC);
				$rNm=$qNm->fetch();
				
	
		$path='images/logo.jpg';
	    $this->Image($path,15,5,22);	
		$this->SetFont('Arial','B',10);
		$this->SetFillColor(255,255,255);	
		$this->SetX(42);   
	    $this->Cell(60,5,$nama,0,1,'L');	 
		$this->SetX(42); 		
	    $this->Cell(60,5,$alamatpt,0,1,'L');	
		$this->SetX(42); 			
		$this->Cell(60,5,"Tel: ".$telp,0,1,'L');	
		$this->Ln();
		$this->SetFont('Arial','B',8); 
		$this->Cell(20,5,$namapt,'',1,'L');
        $this->SetFont('Arial','',8);
		$this->Line(10,30,290,30);	
			$this->Cell(35,5,$_SESSION['lang']['pabrik'],'',0,'L');
			$this->Cell(2,5,':','',0,'L');
			$this->Cell(80,5,$namapt,'',0,'L');		
			$this->Cell(20,5,$_SESSION['lang']['statasiun'],'',0,'L');
			$this->Cell(2,5,':','',0,'L');
			$this->Cell(35,5,$rNm['namaorganisasi'],0,1,'L');
			//		$this->Cell(140,5,' ','',0,'R');	
			//			$this->Cell(140,5,' ','',0,'R');
				
			$this->Cell(35,5,'User','',0,'L');
			$this->Cell(2,5,':','',0,'L');
			$this->Cell(100,5,$_SESSION['standard']['username'],'',1,'L');

		
			//	        $this->Ln();	
			if($periode!='0')
			{
				$this->Cell(15,5,$_SESSION['lang']['periode'],'',0,'L');
				$this->Cell(2,5,':','',0,'L');
				$this->Cell(35,5,$periode,'',0,'L');		
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

	$pdf=new PDF('L','mm','A4');
	$pdf->AddPage();
			
	$pdf->Ln();
	
	$pdf->SetFont('Arial','U',15);
	$pdf->SetY(60);
	$pdf->Cell(250,5,$_SESSION['lang']['pemeliharaanMesinReport'],0,1,'C');	
	$pdf->Ln();	
	$pdf->SetFont('Arial','B',9);	
	$pdf->SetFillColor(220,220,220);
    $pdf->Cell(8,5,'No',1,0,'L',1);
	$pdf->Cell(40,5,$_SESSION['lang']['notransaksi'],1,0,'C',1);	
	$pdf->Cell(20,5,$_SESSION['lang']['tanggal'],1,0,'C',1);
	$pdf->Cell(25,5,$_SESSION['lang']['kodebarang'],1,0,'C',1);
	$pdf->Cell(60,5,$_SESSION['lang']['namabarang'],1,0,'C',1);		
	$pdf->Cell(15,5,$_SESSION['lang']['satuan'],1,0,'C',1);
	$pdf->Cell(15,5,$_SESSION['lang']['jumlah'],1,0,'C',1);
	$pdf->Cell(15,5,$_SESSION['lang']['shift'],1,0,'C',1);
	$pdf->Cell(25,5,$_SESSION['lang']['mesin'],1,0,'C',1);
	$pdf->Cell(30,5,$_SESSION['lang']['jammulai'],1,0,'C',1);
	$pdf->Cell(30,5,$_SESSION['lang']['jamselesai'],1,1,'C',1);
	
	
	//$pdf->Cell(25,5,'Total',1,1,'C',1);
	$pdf->SetFillColor(255,255,255);
	$pdf->SetFont('Arial','',9);

	if($periode=='0')
	{
		$str="select a.*,b.* from ".$dbname.".pabrik_rawatmesinht a inner join ".$dbname.".pabrik_rawatmesindt b on a.notransaksi=b.notransaksi 
		where a.pabrik='".$pabrik."' and a.statasiun='".$statId."'  order by a.tanggal asc";
	}
	elseif($periode!='0')
	{
		$str="select a.*,b.* from ".$dbname.".pabrik_rawatmesinht a inner join ".$dbname.".pabrik_rawatmesindt b on a.notransaksi=b.notransaksi 
		where a.pabrik='".$pabrik."' and a.statasiun='".$statId."' and tanggal like '%".$periode."%' order by a.tanggal asc";
	}
	
		$re=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	    $re->setFetchMode(PDO::FETCH_ASSOC);
		$no=0;
		while($res=$re->fetch())
		{
			$no+=1;
			$sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$res['kodebarang']."'";
			$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
	    	$qBrg->setFetchMode(PDO::FETCH_ASSOC);
			$rBrg=$qBrg->fetch();
		   	
			$pdf->Cell(8,5,$no,1,0,'L',1);
			$pdf->Cell(40,5,$res['notransaksi'],1,0,'C',1);	
			$pdf->Cell(20,5,tanggalnormal($res['tanggal']),1,0,'C',1);
			$pdf->Cell(25,5,$res['kodebarang'],1,0,'C',1);
			$pdf->Cell(60,5,$rBrg['namabarang'],1,0,'L',1);		
			$pdf->Cell(15,5,$res['satuan'],1,0,'C',1);
			$pdf->Cell(15,5,$res['jumlah'],1,0,'C',1);
			$pdf->Cell(15,5,$res['shift'],1,0,'C',1);
			$pdf->Cell(25,5,$res['mesin'],1,0,'C',1);
			$pdf->Cell(30,5,tanggalnormald($res['jammulai']),1,0,'C',1);
			$pdf->Cell(30,5,tanggalnormald($res['jamselesai']),1,1,'C',1);
		}
		

	
	$pdf->Output();
?>
