<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/zLib.php');

$urlefil=checkPostGet('urlefil','0');
$tmp=explode(',',$_GET['column']);
$nodph=$tmp[0];
$nourut=$tmp[1];

$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmSup=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
$nmFranco=makeOption($dbname,'setup_franco','id_franco,franco_name');
$kdPt=makeOption($dbname,'log_prapoht','nopp,pt');


$iOrder="select * from ".$dbname.".log_perintaanhargaht a 
	left join ".$dbname.".log_permintaanhargadt b on a.nomor=b.nomor 
	where a.nomor='".$nodph."'";
$nOrder=$owlPDO->query($iOrder) or die(print " Gagal: ".PDOException::getMessage());
$nOrder->setFetchMode(PDO::FETCH_ASSOC);
$dOrder=$nOrder->fetch();
$kodept=substr($dOrder['nomor'], 16,3);



$iOrderJum="select count(*) as jumlah from ".$dbname.".log_permintaanhargadt  "
		. "where nomor='".$nodph."' and nourut='".$nourut."' ";
$nOrderJum=$owlPDO->query($iOrderJum) or die(print " Gagal: ".PDOException::getMessage());
$nOrderJum->setFetchMode(PDO::FETCH_ASSOC);
$dOrderJum=$nOrderJum->fetch();

if(!class_exists('PDF')){
	//create Header
	class PDF extends FPDF{
		function Header(){}
		
		function Footer(){
			$this->SetY(-15);
			$this->SetFont('Arial','I',8);
			$this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
		}

	}
}

$pdf=new PDF('P','mm','A4');
$pdf->AddPage();

$pdf->Ln();
        
$arrHead = setheadreport($kodept);
$path=$arrHead['logo'];
$pdf->Image($path,15,3,20);	

$pdf->SetFont('Arial','B',12);
$pdf->SetY(10);
$pdf->Cell(190,5,'Daftar Perbandingan Harga',0,1,'C');	
$pdf->Ln();
$pdf->SetFont('Arial','B',8);
$pdf->Cell(190,5,$nodph,0,0,'C');
$pdf->SetX(180);
$pdf->Cell(1,5,'Date : '.tanggalnormal($dOrder['tanggal']),0,1,'L');
$pdf->Ln();	
	
$pdf->Line(10, 30, 200, 30);
        
        
        
$pdf->SetFont('Arial','',7); 
        
$pdf->Ln();
$pdf->Cell(15,5,'Nama PT.',0,0,'L');
$pdf->Cell(10,5,':',0,0,'C');
$pdf->Cell(80,5,$nmOrg[$kdPt[$dOrder['nopp']]],0,0,'L');


$pdf->Cell(15,5,'Dept',0,0,'L');
$pdf->Cell(10,5,':',0,0,'C');
$pdf->Cell(10,5,$nmOrg[substr($dOrder['nopp'],15,4)],0,1,'L');


$pdf->Cell(15,5,'No.PP.',0,0,'L');
$pdf->Cell(10,5,':',0,0,'C');
$pdf->Cell(80,5,$dOrder['nopp'],0,0,'L');


$pdf->Cell(15,5,'Qty',0,0,'L');
$pdf->Cell(10,5,':',0,0,'C');
$pdf->Cell(10,5,$dOrderJum['jumlah'],0,1,'L');

$pdf->Cell(15,5,'',0,0,'L');
$pdf->Cell(10,5,'',0,0,'C');
$pdf->Cell(80,5,'',0,0,'L');


$pdf->Cell(15,5,'Approx Value',0,0,'L');
$pdf->Cell(10,5,':',0,0,'C');
$pdf->Cell(10,5,$dOrder['matauang'].'. '.number_format($dOrder['nilaipermintaan'],2),0,1,'L');

$pdf->SetFont('Arial','',5);
$pdf->Cell(15,5,'(Bank by price lowest quotation)',0,0,'L');


$pdf->SetFont('Arial','',7);



$pdf->Ln();

$awalXjudulno=$pdf->GetX();
$awalYjudulatas=$pdf->GetY();
$pdf->Cell(10,5,'No',1,0,'C');

$awalXjudulsup=$pdf->GetX();
$pdf->Cell(50,5,'Supplier & Place',1,0,'C');

$awalXjudulbp=$pdf->GetX();
$pdf->Cell(35,5,'Basic Price',1,0,'C');

$awalXjudulppn=$pdf->GetX();
$pdf->Cell(30,5,'PPN 10%',1,0,'C');

$awalXjudulfp=$pdf->GetX();
$pdf->Cell(35,5,'Final Price',1,0,'C');

$awalXjudulpy=$pdf->GetX();
$akhirXjudulpy=$pdf->GetX()+35;
$pdf->Cell(35,5,'Payment',1,1,'C');


$pdf->SetFont('Arial','',7);
$awalYbanget=$pdf->GetY();
$iData="select * from ".$dbname.".log_perintaanhargaht where nomor='".$nodph."' ";

$nData=$owlPDO->query($iData) or die(print " Gagal: ".PDOException::getMessage());
$nData->setFetchMode(PDO::FETCH_ASSOC);
while($dData=$nData->fetch()){	
	$height=5;
	$no+=1;
	$pdf->SetY($awalYbanget);
	
	$pdf->Cell(10,$height,$no,0,0,'C');
	
	#buat iterasi dlu di atas untuk nama supplier
	$awalYsup=$pdf->GetY();
	$pdf->MultiCell(50, $height, $nmSup[$dData['supplierid']], '0', 'L');
	$akhirYsup=$pdf->GetY();
	$akhirXsup=$pdf->GetX()+60;
	$heightysup=$akhirYsup-$awalYsup;
	$pdf->SetXY($akhirXsup,$awalYsup);
	
	
	$pdf->Cell(35,$height,$dData['matauang'].'. '.number_format($dData['subtotal'],2),0,0,'R');
	$pdf->Cell(30,$height,$dData['matauang'].'. '.number_format($dData['ppn'],2),0,0,'R');
	$pdf->Cell(35,$height,$dData['matauang'].'. '.number_format($dData['nilaipermintaan'],2),0,0,'R');
	$arrOptTerm2 =  makeOption($dbname, 'log_5syaratbayar', 'kode,keterangan,jenis','',4);
	$arrOptTerm=array("1"=>"Tunai","2"=>"Kerdit 2 Minggu","3"=>"Kredit 1 Bulan","4"=>"Termin","5"=>"DP");
	
	if($dData['sisbayar']!='0'){
		$hasilSyaratBayar = $arrOptTerm[$dData['sisbayar']];
	}else{
		if($dData['sisbayar2']!=''){
			$hasilSyaratBayar = $arrOptTerm2[$dData['sisbayar2']];
		}else{
			$hasilSyaratBayar = "";
		}
	}
	$pdf->MultiCell(35,$height,$hasilSyaratBayar,0,'L');
	
	//$pdf->MultiCell(35, 5, $dData['catatan'],0,'L');
   
	$akhirXcat=$pdf->getX()+195;
	$pdf->SetX(20);
	$pdf->MultiCell(35, $height,$dData['catatan'],0,'L');
	
	$akhirYcat=$pdf->GetY();
	
	if($akhirYsup>=$akhirYcat)
	{
		$akhirYbanget=$akhirYsup;
	}
	else
	{
		$akhirYbanget=$akhirYcat;
	}
	
	$pdf->Line(10, $akhirYbanget, $akhirXcat, $akhirYbanget);
	
	$awalYbanget=$akhirYbanget;
}
        
$akhirYloop=$pdf->GetY();

$pdf->Line($awalXjudulno, $awalYjudulatas, $awalXjudulno, $akhirYloop);
$pdf->Line($awalXjudulsup, $awalYjudulatas, $awalXjudulsup, $akhirYloop);

$pdf->Line($awalXjudulbp, $awalYjudulatas, $awalXjudulbp, $akhirYloop);
$pdf->Line($awalXjudulppn, $awalYjudulatas, $awalXjudulppn, $akhirYloop);
$pdf->Line($awalXjudulfp, $awalYjudulatas, $awalXjudulfp, $akhirYloop);

$pdf->Line($awalXjudulpy, $awalYjudulatas, $awalXjudulpy, $akhirYloop);



$pdf->Line($akhirXjudulpy, $awalYjudulatas, $akhirXjudulpy, $akhirYloop);

$pdf->Ln();
$pdf->SetFont('Arial','U',7);

$pdf->Cell(10,5,'Remarks :',0,1,'L');

$pdf->SetFont('Arial','B',7);
$pdf->Cell(10,5,'Di Order ke : '.$nmSup[$dOrder['supplierid']],0,0,'L');

$pdf->SetFont('Arial','',8);
$pdf->SetX(100);
$pdf->Cell(10,5,'Details of last Procurment :',0,1,'L');

$pdf->SetFont('Arial','B',7);
$pdf->Cell(10,5,'dengan alasan :',0,1,'L');
$pdf->SetFont('Arial','',7);
$ambily=$pdf->GetY();
$pdf->MultiCell(80, 5, $dOrder['catatanmenang'].' franco '.$nmFranco[$dOrder['id_franco']]);
 
$yakhircatatan=$pdf->GetY();
 
        
$pdf->SetXY(100, $ambily);
$optNamaSup = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$dOrder['supplierid']."'");
$pdf->Cell(25,5,'Supplier',0,0,'L');
$pdf->Cell(5,5,':',0,0,'L');
$pdf->Cell(10,5,$optNamaSup[$dOrder['supplierid']],0,1,'L');
$pdf->Ln();
$pdf->SetX(100);
$pdf->Cell(25,5,'Price',0,0,'L');
$pdf->Cell(5,5,':',0,0,'L');
$pdf->Cell(10,5,'Quotation',0,1,'L');
$yakhirquote=$pdf->GetY();
	

if($yakhircatatan>=$yakhirquote)
{
	$ybaru=$yakhircatatan;
}
else
{
	$ybaru=$yakhirquote;	
}

$pdf->SetY($ybaru);
$pdf->Ln();
	
	
if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{
	$manager="Manager Proc";
	$dir="Dir. HR GA";
}
else
{
	$manager="ROA";
	$dir="General Manager";
}
	
   
   
$nopplist=  explode('/', $nodph);
$unit=$nopplist[4];
$panjangunit=  strlen($unit);
		
          
            
if($panjangunit==3)//berarti dph pusat
{
	if($dOrder['nilaipermintaan'] >= 100000000)
	{
		$pdf->Cell(30,30,'','0',0,'C');
		$pdf->Cell(60,30,'','1',0,'C');
		$pdf->Cell(60,30,'','1',1,'C');
		$pdf->Cell(30,30,'','0',0,'C');
		$pdf->Cell(60,5,'Purchaser','1',0,'C');
		$pdf->Cell(60,5,'Manager Proc','1',1,'C');
		$pdf->Ln(5);

		$pdf->Cell(60,30,'','1',0,'C');
		$pdf->Cell(60,30,'','1',0,'C');
		$pdf->Cell(60,30,'','1',1,'C');
		$pdf->Cell(60,5,'Dir. HR GS','1',0,'C');
		$pdf->Cell(60,5,'Dir. Operasional','1',0,'C');
		$pdf->Cell(60,5,'Dir. Keuangan','1',1,'C');

		$pdf->Ln(5);
		$pdf->Cell(60,30,'','0',0,'C');

		$pdf->Cell(60,30,'','1',1,'C');
		$pdf->Cell(60,5,'','0',0,'C');
		$pdf->Cell(60,5,'Direktur Utama','1',0,'C');

	}
	else
	{
		$pdf->Cell(60,30,'','1',0,'C');
		$pdf->Cell(60,30,'','1',0,'C');
		$pdf->Cell(60,30,'','1',1,'C');
		$pdf->Cell(60,5,'Purchaser','1',0,'C');
		$pdf->Cell(60,5,'Manager Proc','1',0,'C');
		$pdf->Cell(60,5,'Dir. HR GS','1',1,'C');
	}
}
else
{
	if($dOrder['nilaipermintaan'] >= 100000000)
	{
		$pdf->Cell(30,30,'','0',0,'C');
		$pdf->Cell(60,30,'','1',0,'C');
		$pdf->Cell(60,30,'','1',1,'C');
		$pdf->Cell(30,30,'','0',0,'C');
		$pdf->Cell(60,5,'Purchaser','1',0,'C');
		$pdf->Cell(60,5,'Manager Proc','1',1,'C');
		$pdf->Ln(5);

		$pdf->Cell(60,30,'','1',0,'C');
		$pdf->Cell(60,30,'','1',0,'C');
		$pdf->Cell(60,30,'','1',1,'C');
		$pdf->Cell(60,5,'ROA','1',0,'C');
		$pdf->Cell(60,5,'General Manager','1',0,'C');
		$pdf->Cell(60,5,'Dir. HR GS','1',1,'C');

		$pdf->Ln(5);
		$pdf->Cell(60,30,'','1',0,'C');
		$pdf->Cell(60,30,'','1',0,'C');
		$pdf->Cell(60,30,'','1',1,'C');
		$pdf->Cell(60,5,'Dir. Operasional','1',0,'C');
		$pdf->Cell(60,5,'Dir. Keuangan','1',0,'C');
		$pdf->Cell(60,5,'Dir. Utama','1',1,'C');

	}
	else
	{
		$pdf->Cell(60,30,'','1',0,'C');
		$pdf->Cell(60,30,'','1',0,'C');
		$pdf->Cell(60,30,'','1',1,'C');
		$pdf->Cell(60,5,'Purchaser','1',0,'C');
		$pdf->Cell(60,5,'ROA','1',0,'C');
		$pdf->Cell(60,5,'General Manager','1',1,'C');
	}
}


# Print Out
if($urlefil=='0'){
	$pdf->Output();
}else{
	$pdf->Output($urlefil);
}

?>