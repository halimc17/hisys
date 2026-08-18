<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');
include_once('lib/terbilang.php');

// Class PDF Custom Surat Jalan
class sjPdf extends FPDF {
	public $dataH;
	public $dataD;
	public $franco;
	
	function Header()
	{
		global $dataH;
		global $dataD;
		global $franco;

		//ambil nama pt
		$arrHead = setheadreport('',$dataH['kodept']);
	
		
		$width = $this->w - $this->lMargin - $this->rMargin;
		$height = 12;
		$path=$arrHead['logopalma'];
		$this->Image($path,$this->lMargin,($this->tMargin-8),0,46);
		$this->SetFont('Arial','B',9);
		$this->SetFillColor(255,255,255);
		$this->Ln(8);
		$this->SetX(100);   
		$this->Cell($width-100,$height,$arrHead['nama'],0,1,'L');	 
		$this->SetX(100); 		
		$this->Cell($width-100,$height,$arrHead['alamat'],0,1,'L');	
		$this->SetX(100); 			
		$this->Cell($width-100,$height,"Tel: ".$arrHead['telepon'],0,1,'L');	
		$this->Line($this->lMargin,$this->tMargin+($height*5),
		$this->lMargin+$width,$this->tMargin+($height*5));
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

$proses = $_GET['proses'];
$param = $_GET;

/** Report Prep **/
$where = "nosj='".$param['nosj']."'";
$cols = 'kodept,kodebarang,jenis,jumlah,satuanpo,nopo,nopp';

$colArr = explode(',',$cols);
$query = selectQuery($dbname,'log_suratjalandt',$cols,$where,'nosj desc');
$data = fetchData($query);
$resData = $data;
$barang = '';
foreach($data as $row) {
	if(!empty($barang)) {$barang .= ',';}
	$barang .= "'".$row['kodebarang']."'";
}

// Header
$queryH = selectQuery($dbname,'log_suratjalanht','*',$where);
$dataH = fetchData($queryH);
$dataH = $dataH[0];
$tmpTgl = explode('-',$dataH['tanggal']);
$tglStr = date('d F Y',mktime(0,0,0,$tmpTgl[1],$tmpTgl[2],$tmpTgl[0]));

// Get Kota
$qOrg = selectQuery($dbname,'organisasi','namaorganisasi,wilayahkota',"kodeorganisasi='".$dataH['kodeorg']."'");
$resOrg = fetchData($qOrg);

$nmOrg = $resOrg[0]['namaorganisasi'];

if($resOrg[0]['wilayahkota']=='')
{
    $kota = ".......................";
}
else
{
    $kota = ucfirst(strtolower($resOrg[0]['wilayahkota']));
}

// Option
$optBarang = array();
$optPartnum = array();
if(!empty($barang)) {
	$qBarang = selectQuery($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang in (".$barang.")");
	$resBarang = fetchData($qBarang);
	foreach($resBarang as $row) {
		$optBarang[$row['kodebarang']] = $row['namabarang'];
	}
}

// Franco
$qFranco = selectQuery($dbname,'organisasi','*',"kodeorganisasi like '".$dataH['franco']."%' and tipe like 'GUDANG%'");
$resFranco = fetchData($qFranco);
$franco = $resFranco[0];

$align = explode(",","L,L,R,L,L,L");
$length = explode(",","5,8,34,8,7,19,19");

/** Output Format **/
switch($proses) {
    case 'pdf':
        $pdf=new sjPdf('P','pt','A4');
        $pdf->_kopOnly = true;
		$pdf->_kodeOrg = $dataH['kodept'];
		$pdf->dataH = $dataH;
		$pdf->dataD = $data;
		$pdf->franco = $franco;
		$pdf->_logoOrg = $dataH['kodept'];
		$pdf->_orgName = $nmOrg;
		$pdf->_noThead = true;
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
		$pdf->AddPage();
		
		// Title
		$pdf->SetY(100);
		$pdf->SetFont('Arial','BU',15);
		$pdf->Cell($width,15,strtoupper($_SESSION['lang']['suratjalan']),0,1,'C');
		$pdf->SetFont('Arial','',8);
		$pdf->Cell($width,10,'No. '.$param['nosj'],0,1,'C');
		$pdf->Ln();
		
		// Kop
		$arrto = setheadreport(substr($dataH['franco'],0,4),'');
		$pdf->SetFont('Arial','',9);
		$pdf->Cell(5/100*$width,$height,'TO  :  ',0,0,'L');
		$pdf->Cell(70/100*$width,$height,$arrto['nama'],0,0,'L');
		$pdf->Cell(40/100*$width,$height,$tglStr,0,0,'L');
		$pdf->Ln();
		
		$pdf->Cell(5/100*$width,$height,'',0,0,'L');
		$pdf->Cell(70/100*$width,$height,$franco['namaorganisasi'],0,0,'L');
		$pdf->Ln();
		
		$pdf->Cell(10/100*$width,$height,'',0,0,'L');
		$pdf->Cell(10/100*$width,$height,'',0,0,'L');
		// $pdf->MultiCell(40/100*$width,$height,'UP : '.$franco['contact'].' ('.$franco['handphone'].')',0,'J');
		$pdf->Ln();
		
		// Narasi
		$pdf->Cell($width,$height,'Bersama ini kami kirimkan barang - barang dibawah ini :',0,0,'L');
		$pdf->Ln();
        
        $pdf->SetFillColor(200,200,200);
		$pdf->SetFont('Arial','B',8);
		
		// Table Header
		$pdf->Cell(5/100*$width,$height,'NO',1,0,'C',1);
		$pdf->Cell(42/100*$width,$height,strtoupper($_SESSION['lang']['namabarang']),1,0,'C',1);
		$pdf->Cell(8/100*$width,$height,'QTY',1,0,'C',1);
		$pdf->Cell(7/100*$width,$height,strtoupper($_SESSION['lang']['satuan']),1,0,'C',1);
		$pdf->Cell(19/100*$width,$height,'PO NO',1,0,'C',1);
		$pdf->Cell(19/100*$width,$height,'PR NO',1,0,'C',1);
		$pdf->Ln();
		
		$pdf->SetFillColor(255,255,255);
		$pdf->SetFont('Arial','',8);
		$i=0;
		$beginY = $pdf->GetY();
		$notPlY = $pdf->GetY();
		foreach($resData as $row) {
			if($pdf->GetY()>760) {
				$pdf->Line($pdf->lMargin,$beginY,$pdf->lMargin,$endY);
				$currLen = 0;
				for($j=0;$j<8;$j++) {
					$currLen += $length[$j]/100*$width;
					$pdf->Line($pdf->lMargin+$currLen,$beginY,
							   $pdf->lMargin+$currLen,$endY);
				}
				$pdf->AddPage();
				$beginY = $pdf->GetY();
				$pdf->Line($pdf->lMargin,$beginY,$pdf->lMargin+$width,$beginY);
			}
			
			$endY = $currY = $pdf->GetY();
			$pdf->Cell(5/100*$width,$height,$i+1,0,0,'R');
			$pdf->Cell(8/100*$width,$height,$row['kodebarang'],0,0,'L');
			if(isset($optBarang[$row['kodebarang']])) {
				$pdf->MultiCell(34/100*$width,$height,$optBarang[$row['kodebarang']],0,'J');
				$endY = $pdf->GetY();
				$pdf->SetY($currY);
				$pdf->SetX($pdf->GetX()+47/100*$width);
			} else {
				$pdf->Cell(34/100*$width,$height,'',0,0,'L');
			}
			$pdf->Cell(8/100*$width,$height,hidezerodecimal($row['jumlah'],2),0,0,'R');
			$pdf->Cell(7/100*$width,$height,$row['satuanpo'],0,0,'L');
			$pdf->Cell(19/100*$width,$height,$row['nopo'],0,0,'L');
			$pdf->Cell(19/100*$width,$height,$row['nopp'],0,0,'L');
			if(!isset($optBarang[$row['kodebarang']])) {
				$pdf->Ln();
				$endY = $pdf->GetY();
			}
			if($endY>$pdf->GetY()) {
				$pdf->SetY($endY);
			}
			if(substr($row['kodebarang'],0,2)!='PL') {
				$notPlY = $pdf->GetY();
			}
			$pdf->Line($pdf->lMargin,$endY,$pdf->lMargin+$width,$endY);
			$i++;
        }
		
		if(!empty($resData)) {
			$pdf->Line($pdf->lMargin,$beginY,$pdf->lMargin,$endY);
			$currLen = 0;
			for($i=0;$i<7;$i++) {
				$currLen += $length[$i]/100*$width;
				if($i==1) {
					$pdf->Line($pdf->lMargin+$currLen,$beginY,
						$pdf->lMargin+$currLen,$notPlY);
				} else {
					$pdf->Line($pdf->lMargin+$currLen,$beginY,
						$pdf->lMargin+$currLen,$endY);
				}
			}
		}
		
		// Space untuk penandatangan
		if($pdf->GetY()>620) {
			$pdf->AddPage();
		}
		$pdf->Ln($height*2);
		
		$pdf->Cell(10/100*$width,$height,'',0,0,'C');
		$pdf->Cell(20/100*$width,$height,'CHECKED BY',0,0,'L');
		$pdf->Cell(70/100*$width,$height,': '.$dataH['checkedby'],0,0,'L');
		$pdf->Ln();
		
		$pdf->Cell(10/100*$width,$height,'',0,0,'C');
		$pdf->Cell(20/100*$width,$height,'DRIVER',0,0,'L');
		$pdf->Cell(70/100*$width,$height,': '.$dataH['driver'].' Ph.'.$dataH['hpdriver'],0,0,'L');
		$pdf->Ln($height*2);
		
		// Narasi penutup
		// $pdf->Cell($width,$height,'Barang-barang tersebut akan dikirim ke  '.$pdf->_orgName,0,1,'L');
		// $pdf->Cell($width,$height,'yang berada di ',0,0,'L');
		// $pdf->Ln($height*2);
		$pdf->Cell($width,$height,'Demikian Surat Pengantar Barang ini di buat untuk dipergunakan dengan semestinya.',0,0,'L');
		$pdf->Ln($height*2);
		
		$pdf->Cell(25/100*$width,$height,$_SESSION['lang']['pengirim'],0,0,'C');
		$pdf->Cell(25/100*$width,$height,$_SESSION['lang']['mengetahui'],0,0,'C');
		$pdf->Cell(25/100*$width,$height,'Transporter',0,0,'C');
		$pdf->Cell(25/100*$width,$height,$_SESSION['lang']['penerima'],0,0,'C');
		$pdf->Ln($height*4);
		
		$optpickirim = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$dataH['pengirim']."'");
		$pdf->Cell(25/100*$width,$height,$optpickirim[$dataH['pengirim']],0,0,'C');
		$pdf->Cell(25/100*$width,$height,'',0,0,'C');
		$pdf->Cell(25/100*$width,$height,($dataH['nopol']==''?'':$dataH['jeniskend'].' : '.$dataH['nopol']),0,0,'C');
		$pdf->Cell(25/100*$width,$height,($dataH['penerima']=='Diterima Oleh'?'':$dataH['penerima']),0,1,'C');
		
		$optExpeditor = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$dataH['expeditor']."'");
		$pdf->Cell(25/100*$width,$height,tanggalnormal($dataH['tanggal']),0,0,'C');
		$pdf->Cell(25/100*$width,$height,'',0,0,'C');
		$pdf->Cell(25/100*$width,$height,$optExpeditor[$dataH['expeditor']],0,0,'C');
		$pdf->Cell(25/100*$width,$height,'',0,0,'C');
                
                $pdf->Ln($height*2);
				
				$pdf->Cell(100/100*$width,$height,'','B',0,'C',1);
				$pdf->Ln($height*2);
                $pdf->Cell($width,$height,'AGAR DIPERHATIKAN ASPEK - ASPEK K3 (KESELAMATAN KESEHATAN KERJA) DALAM PENANGANAN BARANG',0,0,'C');
                
                
		
        $pdf->Output();
        break;
    case 'excel':
        break;
    default:
    break;
}
?>