<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
//require_once('lib/zFunction.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/zLib.php');

$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];


$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');

//=============

//create Header
class PDF extends FPDF {
	function Header() {
        global $conn;
        global $dbname;
		global $userid;
        global $posted;
        global $tanggal;
        global $norek_sup;
        global $cpsn;
        global $npwp_sup;
        global $nm_kary;
        global $nm_pt;
        global $nmSupplier;
        global $almtSupplier;
        global $tlpSupplier;
        global $faxSupplier;
        global $nopo;
        global $tglPo;
        global $kdBank;
        global $an;
        global $arrlp;
        global $lokalpusat;
        global $nmlokalpusat;
		global $optNmkry;
		global $kotasup;
		global $owlPDO;
                
      
		$str1="select namaorganisasi,alamat,wilayahkota,telepon from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'";
		$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_OBJ);
		while($bar1=$res1->fetch()){
			$namapt=$bar1->namaorganisasi;
			$alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
			$telp=$bar1->telepon;				 
		} 
		

        $this->SetMargins(15,10,0);
		$path='images/logo.jpg';
		$this->Image($path,15,3,0,25);	
		$this->SetFont('Arial','B',9);
		$this->SetFillColor(255,255,255);	
		$this->SetX(55);   
		$this->Cell(60,5,$namapt,0,1,'L');	 
		$this->SetX(55); 		
		$this->MultiCell(120,5,$alamatpt,0,'L');	
		$this->SetX(55); 			
		$this->Cell(60,5,"Tel: ".$telp,0,1,'L');	
		$this->SetFont('Arial','B',7);
		$currY = $this->GetY();
		$this->Line(15,$currY,205,$currY);	
		$this->SetFont('Arial','',6); 	
		$this->SetX(163);
        $this->Cell(30,10,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'L');
    }
	
    function Footer() {
		$this->SetY(-15);
		$this->SetFont('Arial','I',8);
		$this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
	}
}


$str="select * from ".$dbname.".pabrikasi_cutoffht where kodepabrikasi='".$_GET['column']."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();




$pdf=new PDF('P','mm','A4');
$height=5;
$pdf->AddPage();

$pdf->SetFont('Arial','B',10);
$pdf->Cell(200,$height,'PABRIKASI CUT OFF',0,0,'C'); 
$pdf->Ln(10);
$pdf->SetFont('Arial','',8);	

$pdf->Cell(35,$height,'Kode Pabrikasi',0,0,'L'); 
$pdf->Cell(40,$height,': '.$bar['kodepabrikasi'],0,1,'L'); 	

$pdf->Cell(35,$height,'Tanggal Cut Off',0,0,'L'); 
$pdf->Cell(40,$height,': '.tanggalnormal($bar['tanggalcutoff']),0,1,'L');

$pdf->Cell(35,$height,'Total Biaya',0,0,'L'); 
$pdf->Cell(40,$height,': '.number_format($bar['total']),0,1,'L');

$pdf->Cell(35,$height,'Status',0,0,'L'); 
$pdf->Cell(40,$height,': '.$bar['status'],0,1,'L');

$pdf->ln();
$pdf->ln();

$pdf->SetFont('Arial','B',8);	
$pdf->SetFillColor(220,220,220);
$pdf->Cell(8,$height,'No',1,0,'L',1);
$pdf->Cell(60,$height,$_SESSION['lang']['namabarang'],1,0,'C',1);	
$pdf->Cell(15,$height,'Qty',1,0,'C',1);
$pdf->Cell(10,$height,'%',1,0,'C',1);
$pdf->Cell(30,$height,$_SESSION['lang']['hargasatuan'],1,0,'C',1);	
$pdf->Cell(30,$height,$_SESSION['lang']['total'],1,0,'C',1);
$pdf->Cell(30,$height,'Diterima Gudang',1,1,'C',1);		
$pdf->SetFillColor(255,255,255);
$pdf->SetFont('Arial','',8);

$str="select * from ".$dbname.".pabrikasi_cutoffdt where kodepabrikasi='".$_GET['column']."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$no+=1;
	$pdf->Cell(8,$height,$no,1,0,'L',1);
	$pdf->Cell(60,$height,$nmbrg[$bar['kodebarang']],1,0,'L',1);	
	$pdf->Cell(15,$height,$bar['jumlah'],1,0,'R',1);
	$pdf->Cell(10,$height,$bar['persenbeban'],1,0,'R',1);
	$pdf->Cell(30,$height,number_format($bar['hargasatuan'],2),1,0,'R',1);	
	$pdf->Cell(30,$height,number_format($bar['hargatotal']),1,0,'R',1);
	$pdf->Cell(30,$height,number_format($bar['jumlahterimagudang']),1,1,'R',1);		
}


$pdf->Output();