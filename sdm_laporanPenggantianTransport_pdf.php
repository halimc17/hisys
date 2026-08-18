<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');
	
$notransaksi = checkPostGet('notransaksi','');
$str="select a.*,sum(b.jlhbbm) as bbm,c.namakaryawan from ".$dbname.".sdm_penggantiantransport a
      left join ".$dbname.".sdm_penggantiantransportdt b 
	  on a.notransaksi=b.notransaksi
	  left join ".$dbname.".datakaryawan c
	  on a.karyawanid=c.karyawanid
	   where a.notransaksi='".$notransaksi."'
	  group by notransaksi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
	 


	
	
//=================================================
class PDF extends FPDF {
    function Header() {
    	global $notransaksi;
        $this->SetFont('Arial','B',8); 
        $this->SetFont('Arial','B',12);
		$this->Cell(190,5,strtoupper($_SESSION['lang']['penggantiantransport']),0,1,'C');
        $this->SetFont('Arial','',8);


			$this->Cell(35,5,'','',0,'L');
			$this->Cell(2,5,'','',0,'L');
			$this->Cell(100,5,'','',0,'L');		
			$this->Cell(15,5,$_SESSION['lang']['print'],'',0,'L');
			$this->Cell(2,5,':','',0,'L');
			$this->Cell(35,5,date('d-m-Y H:i'),'',1,'L');

			$this->Cell(35,5,$_SESSION['lang']['notransaksi'],'',0,'L');
			$this->Cell(2,5,':','',0,'L');
			$this->Cell(100,5,$notransaksi,'',0,'L');		
			$this->Cell(15,5,'User','',0,'L');
			$this->Cell(2,5,':','',0,'L');
			$this->Cell(35,5,$_SESSION['standard']['username'],'',1,'L');

     
		 
	    $this->SetFont('Arial','',6);
		///$this->Cell(5,5,'No.',1,0,'C');		
			
		
		//$this->Cell(35,5,$_SESSION['lang']['karyawan'],1,0,'C');						
		//$this->Cell(15,5,$_SESSION['lang']['totalbiaya'],1,0,'C');
		//$this->Cell(15,5,$_SESSION['lang']['dibayar'],1,0,'C');		
		//$this->Cell(15,5,$_SESSION['lang']['transport'],1,0,'C');
		//$this->Cell(15,5,$_SESSION['lang']['perawatan'],1,0,'C');
		//$this->Cell(15,5,$_SESSION['lang']['toll'],1,0,'C');
		//$this->Cell(15,5,$_SESSION['lang']['lain'],1,0,'C');
		//$this->Cell(35,5,$_SESSION['lang']['keterangan'],1,1,'C');
    }
}
//================================

	$pdf=new PDF('P','mm','A4');
	$pdf->AddPage();
	
$no=0;
while($bar=$res->fetch())
{
	$no+=1;
		//$pdf->Cell(5,5,$no,0,0,'C');
		$pdf->Cell(15,5,$_SESSION['lang']['periode'],0,0,'L');
		$pdf->Cell(1,5,":",0,0,'L');
		$pdf->Cell(10,5,substr($bar->periode,5,2)."-".substr($bar->periode,0,4),0,1,'L');
		
		$pdf->Cell(15,5,$_SESSION['lang']['py'],0,0,'L');
		$pdf->Cell(1,5,":",0,0,'L');
		$pdf->Cell(10,5,$bar->alokasi,0,1,'L');
		
		$pdf->Cell(15,5,$_SESSION['lang']['karyawan'],0,0,'L');
		$pdf->Cell(1,5,":",0,0,'L');
		$pdf->Cell(30,5,$bar->namakaryawan,0,1,'L');
		
		$pdf->Cell(15,5,$_SESSION['lang']['keterangan'],0,0,'L');
		$pdf->Cell(1,5,":",0,0,'L');
		$pdf->Cell(30,5,$bar->keterangan,0,1,'L');
		
		
		//$pdf->Cell(35,5,$bar->namakaryawan,0,0,'L');						
		//$pdf->Cell(15,5,number_format($bar->totalklaim,2,',','.'),0,0,'R');
		//$pdf->Cell(15,5,number_format($bar->dibayar,2,',','.'),0,0,'R');		
		//$pdf->Cell(15,5,number_format($bar->trans,2,',','.'),0,0,'R');
		//$pdf->Cell(15,5,number_format($bar->perawatan,2,',','.'),0,0,'R');
		//$pdf->Cell(15,5,number_format($bar->toll,2,',','.'),0,0,'R');
		//$pdf->Cell(15,5,number_format($bar->bylain,2,',','.'),0,0,'R');
		//$pdf->Cell(35,5,$bar->keterangan,0,1,'L');	
}	
//detail bbm=============================

        $pdf->Ln();
		$pdf->Cell(25,5,'DETAIL BBM:',0,1,'L');
		$pdf->SetFont('Arial','',6);
		$pdf->Cell(5,5,'No.',1,0,'C');		
		$pdf->Cell(15,5,$_SESSION['lang']['tanggal'],1,0,'C');	
		$pdf->Cell(15,5,$_SESSION['lang']['vhc_kmhm_awal'],1,0,'C');
		$pdf->Cell(15,5,$_SESSION['lang']['vhc_kmhm_akhir'],1,0,'C');
		$pdf->Cell(20,5,$_SESSION['lang']['jumlah'],1,0,'C');
		$pdf->Cell(20,5,$_SESSION['lang']['total'],1,1,'C');
		
		$str="select * from  ".$dbname.".sdm_penggantiantransportdt where notransaksi='".$notransaksi."'";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$no=$tbbm=$tharga=0;
		while($bar=$res->fetch())
		{
		  $no+=1;
		  	$pdf->Cell(5,5,$no,0,0,'R');		
			$pdf->Cell(15,5,tanggalnormal($bar->tanggal),0,0,'C');
			$pdf->Cell(15,5,number_format($bar->kmawal),0,0,'C');
			$pdf->Cell(15,5,number_format($bar->kmakhir),0,0,'C');
			$pdf->Cell(20,5,number_format($bar->jlhbbm,2,',','.'),0,0,'R');
			$pdf->Cell(20,5,number_format($bar->hargatotal,2,',','.'),0,1,'R');
			$tbbm+=$bar->jlhbbm;
			$tharga+=$bar->hargatotal;
		}
		  	$pdf->Cell(5,5,'',0,0,'R');		
			$pdf->Cell(15,5,'TOTAL','T',0,'C');
			$pdf->Cell(15,5,'','T',0,'C');
			$pdf->Cell(15,5,'','T',0,'C');
			$pdf->Cell(20,5,number_format($tbbm,2,',','.'),'T',0,'R');
			$pdf->Cell(20,5,number_format($tharga,2,',','.'),'T',1,'R');
	
$pdf->Output();	
	
?>