<?php
include_once('lib/fpdf.php');
include_once('lib/zLib.php');

class zPdfMaster extends FPDF {
    public $_align;
    public $_length;
    public $_colArr;
    public $_title;
    public $_noThead;
        public $_finReport = false;

    function zPdfMaster($ori,$unit,$format) {
        parent::FPDF($ori,$unit,$format);
        $this->_noThead = false;
    }

    function Header() {
        global $conn;
        global $dbname;
        global $bulan;
        global $tahun;
        global $owlPDO;
        # Alamat & No Telp
        $query = selectQuery($dbname,'organisasi','alamat,telepon',"kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
        $orgData = fetchData($query);

        $sPeriode=$owlPDO->query("select distinct periode from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_SESSION['empl']['lokasitugas']."'  and tutupbuku = 0");
        $sPeriode->setFetchMode(PDO::FETCH_ASSOC);
        $rPeriode=$sPeriode->fetch();  
        $width = $this->w - $this->lMargin - $this->rMargin;
        $height = 12;

                if($this->_finReport) {
                        $this->SetFont('Arial','B',8);
                        $this->Cell($width,$height,$_SESSION['org']['namaorganisasi'],0,1,'R');
                        $this->SetFont('Arial','U',12);
                        $this->Cell($width,$height,strtoupper($this->_title),0,1,'C');

                        $this->Ln();
                        //$this->Line($this->lMargin,$this->GetY(),$this->w - $this->rMargin,$this->GetY());
                } else {
						$arrHead = setheadreport('',$_SESSION['empl']['lokasitugas']);
						$path=$arrHead['logo'];
                        // $path='images/logo.jpg';
						
						// $this->Image($path,65,5,30);
		                $this->Image($path,$this->lMargin,$this->tMargin-5,0,45);	
                        $this->SetFont('Arial','B',9);
                        $this->SetFillColor(255,255,255);	
                        $this->SetX(90);   
                        $this->Cell($width-100,$height,$_SESSION['org']['namaorganisasi'],0,1,'L');	 
                        $this->SetX(90); 		
                        $this->Cell($width-100,$height,$orgData[0]['alamat'],0,1,'L');	
                        $this->SetX(90); 			
                        $this->Cell($width-100,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
                        $this->Line($this->lMargin,$this->tMargin+($height*4),
                                $this->lMargin+$width,$this->tMargin+($height*4));
                        $this->Ln($height*1.5);
                        $this->SetFont('Arial','',12);
                        $this->Cell($width,$height,strtoupper($this->_title),0,1,'C');
                }
        $this->SetFont('Arial','I',6);
        $this->SetFont('Arial','',8);
        if($this->_noThead==false) {
            $this->Ln();
            $this->SetFont('Arial','B',9);	
            $this->SetFillColor(220,220,220);
            foreach($this->_colArr as $key=>$head) {
                if(isset($_SESSION['lang'][$head])) {
                                        $this->Cell($this->_length[$key]/100*$width,$height,$_SESSION['lang'][$head],1,0,'C',1);
                                } else {
                                        $this->Cell($this->_length[$key]/100*$width,$height,ucfirst($head),1,0,'C',1);
                                }
            }
            $this->Ln();
        }
    }

    function Footer()
    {
        global $dbname;
         global $owlPDO;;
        $sPeriode=$owlPDO->query("select distinct periode from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_SESSION['empl']['lokasitugas']."' and tutupbuku = 0");
        $sPeriode->setFetchMode(PDO::FETCH_ASSOC);
        $rPeriode=$sPeriode->fetch();
        $width = $this->w - $this->lMargin - $this->rMargin;
        $height = 12;
        $this->SetY(-20);
        $this->SetFont('Arial','I',7);
        $this->Cell(1,$height,'Page '.$this->PageNo(),'T',0,'L');
        $str = "Printed by ".$_SESSION['standard']['username']."[".$_SESSION['empl']['lokasitugas']."]".
                ":".$rPeriode['periode']." at ".date('d-m-Y H:i:s');
        $this->Cell($width-1,$height,$str,'T',0,'R');
    }
    function setAttr1($cTitle,$cAlign,$cLength,$cColArr) {
        $this->_align = $cAlign;
        $this->_length = $cLength;
        $this->_colArr = $cColArr;
        $this->_title = $cTitle;
    }
}
?>