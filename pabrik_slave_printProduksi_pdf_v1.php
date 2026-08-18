<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
include_once('lib/nangkoelib.php');

	$tampil=$_GET['tampil'];
	$pabrik=$_GET['pabrik'];
	$periode=$_GET['periode'];
	

class PDF extends FPDF {
    function Header() {
       global $namapt;
       global $periode;
       global $pabrik;
	   
        $this->SetFont('Arial','B',8); 
		$this->Cell(20,5,$namapt,'',1,'L');
        $this->SetFont('Arial','B',12);
		$this->Cell(275,5,strtoupper($_SESSION['lang']['rprodksiPabrikharian']),0,1,'C');
		$this->Cell(275,5,$_SESSION['lang']['periode'].' : '.substr($periode,5,2).'-'.substr($periode,0,4),0,1,'C');	
                //$this->Cell(275,5,strtoupper($_SESSION['lang']['rprodksiPabrik']),0,1,'C');
		$this->Cell(275,5,$_SESSION['lang']['pabrik'].' : '.$pabrik,0,1,'C');
		$this->SetFont('Arial','',8);
		$this->Cell(230,5,$_SESSION['lang']['tanggal'],0,0,'R');
		$this->Cell(2,5,':','',0,'L');
		$this->Cell(35,5,date('d-m-Y H:i'),0,1,'L');
		$this->Cell(230,5,$_SESSION['lang']['page'],'',0,'R');
		$this->Cell(2,5,':','',0,'L');
		$this->Cell(35,5,$this->PageNo(),'',1,'L');
		$this->Cell(230,3,'User','',0,'R');
		$this->Cell(2,3,':','',0,'L');
		$this->Cell(35,3,$_SESSION['standard']['username'],'',1,'L');
        $this->Ln();
        $this->SetFont('Arial','',7);
	  		
		$this->SetFont('Arial','B',8);
		$this->Cell(5,10,'No.',1,0,'C');
		//$this->Cell(25,10,$_SESSION['lang']['kodeorganisasi'],1,0,'C');
             if($pabrik==$_SESSION['lang']['all']){
		$this->Cell(20,10,$_SESSION['lang']['tanggal'],1,0,'C');
		$this->Cell(25,10,$_SESSION['lang']['tersedia'],1,0,'C');	
		$this->Cell(40,5,$_SESSION['lang']['tbsdiolah'],1,0,'C');
		$this->Cell(40,5,$_SESSION['lang']['cpo'],1,0,'C');
		$this->Cell(40,5,$_SESSION['lang']['kernel'],1,1,'C');
		$this->setX(60);
		$this->SetFont('Arial','',6);
                $this->Cell(20,5,$_SESSION['lang']['hi'],1,0,'C');
		$this->Cell(20,5,$_SESSION['lang']['sdhi'],1,0,'C');
		$this->Cell(20,5,$_SESSION['lang']['cpo'].'(Kg) HI',1,0,'C');
                $this->Cell(20,5,$_SESSION['lang']['cpo'].'(Kg) S/D',1,0,'C');
		$this->Cell(20,5,$_SESSION['lang']['kernel'].'(Kg) '.$_SESSION['lang']['hi'],1,0,'C');
                $this->Cell(20,5,$_SESSION['lang']['kernel'].'(Kg) S/D',1,0,'C');
                $y=$this->getY()-5;
                $x=$this->GetX();
                $this->SetY($y);
                $this->SetX($x);
                $this->Cell(15,10,$_SESSION['lang']['sisa'].'(Kg) ',1,1,'C');
             }
             else{
                $this->Cell(20,10,$_SESSION['lang']['tanggal'],1,0,'C');
		$this->Cell(25,10,$_SESSION['lang']['tersedia'],1,0,'C');	
		$this->Cell(40,5,$_SESSION['lang']['tbsdiolah'],1,0,'C');	
		$this->Cell(100,5,$_SESSION['lang']['cpo'],1,0,'C');
		$this->Cell(100,5,$_SESSION['lang']['kernel'],1,1,'C');
		$this->setX(60);
		$this->SetFont('Arial','',6);
                $this->Cell(20,5,$_SESSION['lang']['hi'],1,0,'C');
		$this->Cell(20,5,$_SESSION['lang']['sdhi'],1,0,'C');
		$this->Cell(20,5,$_SESSION['lang']['cpo'].'(Kg) '.$_SESSION['lang']['hi'],1,0,'C');
                $this->Cell(20,5,$_SESSION['lang']['cpo'].'(Kg) S/D',1,0,'C');
		$this->Cell(15,5,$_SESSION['lang']['oer'].'(%)',1,0,'C');
		$this->Cell(15,5,'(FFa)(%)',1,0,'C');
		$this->Cell(15,5,$_SESSION['lang']['kotoran'].'(%)',1,0,'C');	
		$this->Cell(15,5,$_SESSION['lang']['kadarair'].'(%)',1,0,'C');
		$this->Cell(20,5,$_SESSION['lang']['kernel'].'(Kg) '.$_SESSION['lang']['hi'],1,0,'C');
                $this->Cell(20,5,$_SESSION['lang']['kernel'].'(Kg) S/D',1,0,'C');
		$this->Cell(15,5,$_SESSION['lang']['oer'].'(%)',1,0,'C');
		$this->Cell(15,5,'(Broken)(%)',1,0,'C');
		$this->Cell(15,5,$_SESSION['lang']['kotoran'].'(%)',1,0,'C');	
		$this->Cell(15,5,$_SESSION['lang']['kadarair'].'(%)',1,0,'C');
                $y=$this->getY()-5;
                $x=$this->GetX();
                $this->SetY($y);
                $this->SetX($x);
                $this->Cell(20,10,$_SESSION['lang']['jampengolahan'],1,0,'C');
                $this->Cell(20,10,$_SESSION['lang']['jamstagnasi'],1,0,'C');
                $this->Cell(15,10,$_SESSION['lang']['sisa'].'(Kg) ',1,1,'C');
             }

    }
}
//================================

	$pdf=new PDF('L','mm','LEGAL');
	$pdf->AddPage();
	$pdf->SetFont('Arial','',7);


	//bulanan
	
        if($pabrik==$_SESSION['lang']['all']){
            $str="select tanggal,sum(tbsdiolah) as tbsdiolah,sum(sisahariini) as sisahariini,sum(oer) as oer,sum(oerpk) as oerpk,
            sum(tbsmasuk) as tbsmasuk,sum(sisatbskemarin) as sisatbskemarin, kodeorg
            from ".$dbname.".pabrik_produksi where tanggal like '".$periode."%' and kodeorg like '%%'
            group by tanggal order by tanggal asc";
        }
        else{    
            $str="select * from ".$dbname.".pabrik_produksi where tanggal like '".$periode."%' and kodeorg='".$pabrik."'
            order by tanggal asc";
        }
     
        
        
    $res2=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res2->setFetchMode(PDO::FETCH_ASSOC);
    while($datArr=$res2->fetch())
    {    
        $tbs[$datArr['kodeorg']][$datArr['tanggal']]=$datArr['tbsdiolah'];
        $jmOer[$datArr['kodeorg']][$datArr['tanggal']]=$datArr['oer'];
        $jmOerPk[$datArr['kodeorg']][$datArr['tanggal']]=$datArr['oerpk'];
    }
	$no=0;
        $tgl=1;
        
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        { 
			if(strlen($tgl)==1)
			{
				$agl="0".$tgl;
			}
			$tglServ=substr($bar->tanggal,0,8);
			setIt($tbs[$bar->kodeorg][$tglServ.$agl+1],0);
			setIt($tbs[$bar->kodeorg][$bar->tanggal],0);
			setIt($des,0);
			setIt($oerTotal,0);
			setIt($oerpkTotal,0);
			setIt($siKps,0);
            $tbsSd=$tbs[$bar->kodeorg][$tglServ.$agl+1];
            $tbsSd2=$tbs[$bar->kodeorg][$bar->tanggal];
            $tbsTot=$tbsSd2+$tbsSd;
            $des+=$tbsTot;
			
            //get cpo
			setIt($jmOer[$bar->kodeorg][$tglServ.$agl+1],0);
			setIt($jmOer[$bar->kodeorg][$bar->tanggal],0);
            $oerSd=$jmOer[$bar->kodeorg][$tglServ.$agl+1];
            $oerSd2=$jmOer[$bar->kodeorg][$bar->tanggal];
            $oerTot=$oerSd2+$oerSd;
            $oerTotal+=$oerTot;
            
            //get pk
			setIt($jmOerPk[$bar->kodeorg][$tglServ.$agl+1],0);
			setIt($jmOerPk[$bar->kodeorg][$bar->tanggal],0);
            $oerpkSd=$jmOerPk[$bar->kodeorg][$tglServ.$agl+1];
            $oerpkSd2=$jmOerPk[$bar->kodeorg][$bar->tanggal];
            $oerpkTot=$oerpkSd+$oerpkSd2;
            $oerpkTotal+=$oerpkTot;
            
            
            $sJamPeng=$sJamStag=0;
            $aOlah="select sum(jamdinasbruto) as jampengolahan, sum(jamstagnasi) as jamstagnasi from ".$dbname.".pabrik_pengolahan 
               where kodeorg='".$bar->kodeorg."' and tanggal='".$bar->tanggal."'";
            $bOlah=$owlPDO->query($aOlah) or die(print " Gagal: ".PDOException::getMessage());
            $bOlah->setFetchMode(PDO::FETCH_ASSOC);
            $cOlah=$bOlah->fetch();
                        
                         @$rPengolahan['jampengolahan']=$cOlah['jampengolahan']; 
                        $sJamPeng+=$rPengolahan['jampengolahan'];
		   
		   
		   @$rPengolahan['jamstagnasi']=$cOlah['jamstagnasi']; 
           $sJamStag+=$rPengolahan['jamstagnasi']; 
                        
            
           
         $no+=1;	
		$pdf->Cell(5,5,$no,1,0,'C');
		//$pdf->Cell(25,5,$bar->kodeorg,1,0,'C');
             if($pabrik==$_SESSION['lang']['all']){
		$pdf->Cell(20,5,tanggalnormal($bar->tanggal),1,0,'C');
		$pdf->Cell(25,5,number_format($bar->tbsmasuk+$bar->sisatbskemarin,0,'.',','),1,0,'R');	
		$pdf->Cell(20,5,number_format($bar->tbsdiolah,0,'.',',.'),1,0,'R');	
                $pdf->Cell(20,5,number_format($des,0,'.',',.'),1,0,'R');		//	
		$pdf->Cell(20,5,number_format($bar->oer,0,'.',','),1,0,'R');
                $pdf->Cell(20,5,number_format($oerTotal,0,'.',','),1,0,'R');				
		$pdf->Cell(20,5,number_format($bar->oerpk,0,'.',','),1,0,'R');
                $pdf->Cell(20,5,number_format($oerpkTotal,0,'.',','),1,0,'R');	
                $pdf->Cell(15,5,number_format($bar->sisahariini,2,'.',','),1,1,'R');
             }
             else{
                $pdf->Cell(20,5,tanggalnormal($bar->tanggal),1,0,'C');
		$pdf->Cell(25,5,number_format($bar->tbsmasuk+$bar->sisatbskemarin,0,'.',','),1,0,'R');	
		$pdf->Cell(20,5,number_format($bar->tbsdiolah,0,'.',',.'),1,0,'R');	
                $pdf->Cell(20,5,number_format($des,0,'.',',.'),1,0,'R');
		$pdf->Cell(20,5,number_format($bar->oer,0,'.',','),1,0,'R');
                $pdf->Cell(20,5,number_format($oerTotal,0,'.',','),1,0,'R');
		$pdf->Cell(15,5,(@number_format($bar->oer/$bar->tbsdiolah*100,2,'.',',')),1,0,'R');
		$pdf->Cell(15,5,$bar->ffa,1,0,'R');
		$pdf->Cell(15,5,$bar->kadarkotoran,1,0,'R');	
		$pdf->Cell(15,5,$bar->kadarair,1,0,'R');		
		$pdf->Cell(20,5,number_format($bar->oerpk,0,'.',','),1,0,'R');
                $pdf->Cell(20,5,number_format($oerpkTotal,0,'.',','),1,0,'R');
		$pdf->Cell(15,5,(@number_format($bar->oerpk/$bar->tbsdiolah*100,2,'.',',')),1,0,'R');
		$pdf->Cell(15,5,$bar->ffapk,1,0,'R');
		$pdf->Cell(15,5,$bar->kadarkotoranpk,1,0,'R');	
		$pdf->Cell(15,5,$bar->kadarairpk,1,0,'R');	
                $pdf->Cell(20,5,number_format($rPengolahan['jampengolahan'],2,'.','.'),1,0,'R');	
                $pdf->Cell(20,5,  number_format($rPengolahan['jamstagnasi'],2,'.','.'),1,0,'R');
                $pdf->Cell(15,5,number_format($bar->sisahariini,2,'.',','),1,1,'R');
             }
        }
	$pdf->Output();	
	
?>