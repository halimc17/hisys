<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');

$pt=$_GET['pt'];
$gudang=$_GET['gudang'];
$tgl1=$_GET['tgl1'];
$tgl2=$_GET['tgl2'];

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
        global $gudang;
        global $periode;
        global $tgl1;
        global $tgl2;
        global $owlPDO;

            $sAlmat="select namaorganisasi,alamat,telepon from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
                $qAlamat=$owlPDO->query($sAlmat) or die(print " Gagal: ".PDOException::getMessage());
                $qAlamat->setFetchMode(PDO::FETCH_ASSOC);
                $rAlamat=$qAlamat->fetch();
            $width = $this->w - $this->lMargin - $this->rMargin;
            $height = 11;
            $path='images/logo.jpg';
            $this->Image($path,$this->lMargin,$this->tMargin,0,55);
            $this->SetFont('Arial','B',9);
            $this->SetFillColor(255,255,255);	
            $this->SetX(100);   
            $this->Cell($width-100,$height,$rAlamat['namaorganisasi'],0,1,'L');	 
            $this->SetX(100); 		
            $this->Cell($width-100,$height,$rAlamat['alamat'],0,1,'L');	
            $this->SetX(100); 			
            $this->Cell($width-100,$height,"Tel: ".$rAlamat['telepon'],0,1,'L');	
            $this->Line($this->lMargin,$this->tMargin+($height*4),
                $this->lMargin+$width,$this->tMargin+($height*4));
            $this->Ln();	
            $this->Ln();
            $this->SetFont('Arial','B',11);
            $this->Cell($width,$height, $_SESSION['lang']['laporanpanen'],0,1,'C');	
            $this->Cell($width,$height, $_SESSION['lang']['periode'].":".$tgl1." S/d ".$tgl2 ." ".$_SESSION['lang']['unit'].":" .($gudang!=''?$gudang:$_SESSION['lang']['all']),0,1,'C');	
            $this->SetFont('Arial','',8);

            $this->Ln();
            $this->SetFont('Arial','B',7);	
            $this->SetFillColor(220,220,220);

            $this->Cell(3/100*$width,$height,'No',1,0,'C',1);
            $this->Cell(8/100*$width,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);
            $this->Cell(6/100*$width,$height,$_SESSION['lang']['afdeling'],1,0,'C',1);
            $this->Cell(10/100*$width,$height,$_SESSION['lang']['lokasi'],1,0,'C',1);
            $this->Cell(8/100*$width,$height,$_SESSION['lang']['tahuntanam'],1,0,'C',1);
            $this->Cell(6/100*$width,$height,$_SESSION['lang']['janjang'],1,0,'C',1);
            $this->Cell(8/100*$width,$height,$_SESSION['lang']['beratBersih'],1,0,'C',1);
            $this->Cell(10/100*$width,$height,$_SESSION['lang']['upahkerja'],1,0,'C',1);	
            $this->Cell(10/100*$width,$height,$_SESSION['lang']['upahpenalty'],1,0,'C',1);	
            $this->Cell(8/100*$width,$height,$_SESSION['lang']['premibasis'],1,0,'C',1);	
            $this->Cell(8/100*$width,$height,$_SESSION['lang']['upahpremi'],1,0,'C',1);	
            $this->Cell(6/100*$width,$height,$_SESSION['lang']['jumlahhk'],1,0,'C',1);	
            $this->Cell(8/100*$width,$height,$_SESSION['lang']['penalti'],1,1,'C',1);		            
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
    $height = 9;
    $pdf->AddPage();
    $pdf->SetFillColor(255,255,255);
    $pdf->SetFont('Arial','',7);
    if($gudang=='')
    {
        $str="select a.tanggal,a.tahuntanam,a.unit,a.kodeorg,sum(a.hasilkerja) as jjg,sum(a.hasilkerjakg) as berat,
              sum(a.upahkerja) as upah,sum(a.upahpenalty) as upahpenalty, sum(a.premibasis) as premibasis,
              sum(a.upahpremi) as premi,sum(a.rupiahpenalty) as penalty,count(a.karyawanid) as jumlahhk  
              from ".$dbname.".kebun_prestasi_vw a
              left join ".$dbname.".organisasi c on substr(a.kodeorg,1,4)=c.kodeorganisasi
              where c.induk = '".$pt."'  and a.tanggal between ".tanggalsystem($tgl1)." and ".tanggalsystem($tgl2)." 
              and a.jurnal=1 
              group by a.tanggal,a.kodeorg";
    }
    else
    {
        $where='';
        if($gudang != $_SESSION['empl']['lokasitugas']){                
            $where=" and a.jurnal=1";
        }
        $str="select a.tanggal,a.tahuntanam,a.unit,a.kodeorg,sum(a.hasilkerja) as jjg,sum(a.hasilkerjakg) as berat,
              sum(a.upahkerja) as upah,sum(a.upahpenalty) as upahpenalty, sum(a.premibasis) as premibasis,
              sum(a.upahpremi) as premi,sum(a.rupiahpenalty) as penalty,count(a.karyawanid) as jumlahhk  
              from ".$dbname.".kebun_prestasi_vw a
              where unit = '".$gudang."'  and a.tanggal between ".tanggalsystem($tgl1)." and ".tanggalsystem($tgl2)." 
              ".$where." 
              group by a.tanggal, a.kodeorg";
    }  
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
	$no=0;
	$totberat=$totUpah=$totUpahpenalty=$totJjg=$totPremi=$totPremibasis=$totHk=$totPenalty=0;
    while($bar=$res->fetch())
    {
        $periode=date('Y-m-d H:i:s');
        $tanggal=$bar->tanggal; 
        $kodeorg 	=$bar->kodeorg;
        $no+=1;
        $pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);
        $pdf->Cell(8/100*$width,$height,tanggalnormal($tanggal),1,0,'C',1);
        $pdf->Cell(6/100*$width,$height,substr($kodeorg,0,6),1,0,'L',1);
        $pdf->Cell(10/100*$width,$height,$kodeorg,1,0,'L',1);
        $pdf->Cell(8/100*$width,$height,$bar->tahuntanam,1,0,'C',1);	
        $pdf->Cell(6/100*$width,$height,number_format($bar->jjg,0),1,0,'R',1);	
        $pdf->Cell(8/100*$width,$height,number_format($bar->berat,2),1,0,'R',1);
        $pdf->Cell(10/100*$width,$height,number_format($bar->upah,2),1,0,'R',1);	
        $pdf->Cell(10/100*$width,$height,number_format($bar->upahpenalty,2),1,0,'R',1);	
        $pdf->Cell(8/100*$width,$height,number_format($bar->premibasis,2),1,0,'R',1);	
        $pdf->Cell(8/100*$width,$height,number_format($bar->premi,2),1,0,'R',1);	
        $pdf->Cell(6/100*$width,$height,number_format($bar->jumlahhk,0),1,0,'R',1);	
        $pdf->Cell(8/100*$width,$height,number_format($bar->penalty,2),1,1,'R',1);	

        $totberat+=$bar->berat;
        $totUpah+=$bar->upah;
        $totJjg+=$bar->jjg;
        $totPremi+=$bar->premi;
        $totHk+=$bar->jumlahhk;
        $totPenalty+=$bar->penalty;
        $totUpahpenalty+=$bar->upahpenalty;
        $totPremibasis+=$bar->premibasis;

    }	
    $pdf->Cell(35/100*$width,$height,"Total",1,0,'R',1);
    $pdf->Cell(6/100*$width,$height,number_format($totJjg,0),1,0,'R',1);
    $pdf->Cell(8/100*$width,$height,number_format($totberat,2),1,0,'R',1);
    $pdf->Cell(10/100*$width,$height,number_format($totUpah,2),1,0,'R',1);
    $pdf->Cell(10/100*$width,$height,number_format($totUpahpenalty,2),1,0,'R',1);
    $pdf->Cell(8/100*$width,$height,number_format($totPremibasis,2),1,0,'R',1);
    $pdf->Cell(8/100*$width,$height,number_format($totPremi,2),1,0,'R',1);
    $pdf->Cell(6/100*$width,$height,number_format($totHk,0),1,0,'R',1);
    $pdf->Cell(8/100*$width,$height,number_format($totPenalty,2),1,1,'R',1);
            
    $pdf->Output();

?>