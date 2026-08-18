<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');


$pt=$_GET['pt'];
$gudang=$_GET['gudang'];
$periode=tanggalsystemn($_GET['periode']);
$periode1=tanggalsystemn($_GET['periode1']);
$revisi=$_GET['revisi'];
$regional=$_GET['regional'];
$kdKel=$_GET['kdKel'];

$ref=$_GET['ref'];
$ket=$_GET['ket'];
$nojurnal=$_GET['nojurnal'];
$optNmOrg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$kdKelSch="";
if($kdKel!='')
{
   $kdKelSch=" and a.nojurnal like '%".$kdKel."%'  "; 
}

if($regional=='' && $gudang=='')
{
    $kdOrgSch=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)=4)";
}
else if($regional!='' && $gudang=='')
{

     $kdOrgSch=" and a.kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."'"
            . " and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')) "; 

}
else
{
    $kdOrgSch=" and a.kodeorg='".$gudang."'";
}

if($ref!='')
{
    $refKet.=" and a.noreferensi like '%".$ref."%'";
}

if($ket!='')
{
    $refKet.=" and a.keterangan like '%".$ket."%' ";
}

if($nojurnal!='')
{
    $nojurnalsch.=" and a.nojurnal like '%".$nojurnal."%' ";
}

// kamus tahun tanam
$aresta=$owlPDO->query("SELECT kodeorg, tahuntanam FROM ".$dbname.".setup_blok");
$aresta->setFetchMode(PDO::FETCH_ASSOC);
while($res=$aresta->fetch())
{
    $tahuntanam[$res['kodeorg']]=$res['tahuntanam'];
}   

//=================================================
if($periode=='')
     $periode=substr($_SESSION['org']['period']['start'],0,7);
class PDF extends FPDF {
    function Header() {
        global $pt;
        global $gudang;
        global $optNmOrg;
        global $periode,$periode1,$revisi;
        $this->SetFont('Arial','B',12);
                $this->Cell(190,3,strtoupper($_SESSION['lang']['laporanjurnal']),0,1,'C');
        $this->SetFont('Arial','B',8);
        $tmpX = $this->GetX();
                $tmpY = $this->GetY();
                $this->SetXY($this->w-30,4);
                $this->Cell(20,3,$optNmOrg[$pt],'',1,'R');
                $this->SetFont('Arial','',8);
                //$this->SetX($this->w-50);
                //$this->Cell(20,3,$optNmOrg[$gudang],'',1,'L');
                //$this->SetX($this->w-50);
                //$this->Cell(20,3,$periode."-".$periode1,'',1,'L');

        $this->SetXY($tmpX,$tmpY);
                //$this->Cell(155,3,$optNmOrg[$pt].":". $optNmOrg[$gudang].":".$periode."-".$periode1."",0,0,'L');
                $this->Cell(15,3,$_SESSION['lang']['tanggal'],'',0,'L');
                $this->Cell(2,3,':','',0,'L');
                $this->Cell(35,3,date('d-m-Y H:i'),0,1,'L');
                $this->Cell(155,3,$_SESSION['lang']['revisi'].":".$revisi,'',0,'L');
                $this->Cell(15,3,$_SESSION['lang']['page'],'',0,'L');
                $this->Cell(2,3,':','',0,'L');
                $this->Cell(35,3,$this->PageNo(),'',1,'L');
                $this->Cell(155,3,' ','',0,'R');
                $this->Cell(15,3,'User','',0,'L');
                $this->Cell(2,3,':','',0,'L');
                $this->Cell(35,3,$_SESSION['standard']['username'],'',1,'L');
        $this->Ln();
        $this->SetFont('Arial','',6);
                $this->Cell(5,5,'No.',1,0,'C');
                $this->Cell(30,5,$_SESSION['lang']['nojurnal'],1,0,'C');			
                $this->Cell(16,5,$_SESSION['lang']['tanggal'],1,0,'C');	
                $this->Cell(14,5,$_SESSION['lang']['noakun'],1,0,'C');	
                $this->Cell(35,5,$_SESSION['lang']['namaakun'],1,0,'C');	
                $this->Cell(40,5,$_SESSION['lang']['uraian'],1,0,'C');
                $this->Cell(25,5,$_SESSION['lang']['debet'],1,0,'C');
                $this->Cell(25,5,$_SESSION['lang']['kredit'],1,0,'C');
        $this->Ln();						
        $this->Ln();						

    }
}
//================================

    $pdf=new PDF('P','mm','A4');
        $pdf->AddPage();

    $salakqty	=0;
    $masukqty	=0;
    $keluarqty	=0;
    $sawalQTY	=0;
    $sdebet	= $skredit = 0;
        $height=3;

        $str=$owlPDO->query("select a.*,b.namaakun from ".$dbname.".keu_jurnaldt_vw a
        left join ".$dbname.".keu_5akun b
        on a.noakun=b.noakun
        where a.tanggal between '".$periode."' and '".$periode1."'
        ".$kdOrgSch."
        and a.nojurnal NOT LIKE '%CLSM%' ".$kdKelSch." ".$nojurnalsch."
        and a.revisi<='".$revisi."' ".$refKet."
        order by a.nojurnal");   
         $str->setFetchMode(PDO::FETCH_OBJ);
         $numrows=owlBaris($str);
        $no=0;
        if($numrows<1){
                echo$_SESSION['lang']['tidakditemukan'];
        }else{
                $pdf->SetY(32);
                while($bar=$str->fetch()){
                        $no += 1;
                        $tanggal = $bar->tanggal;
                        $noakun	= $bar->noakun;
                        $nojurnal = $bar->nojurnal;
                        $keterangan = $bar->keterangan;
                        $namaakun = $bar->namaakun;
                        $jumlah = $bar->jumlah;

                        if ($jumlah >= 0){
                                $debet	= $jumlah;
                                $kredit	= 0;
                        }else{
                                $debet	= 0;
                                $kredit	= $jumlah*-1;
                        }

                        $awalY=$pdf->GetY();
                        $pdf->SetX(1000);
                        $pdf->MultiCell(35, $height, $namaakun, '0', 'L');
                        $akhirYakun=$pdf->GetY();
                        $pdf->SetX(1000);
                        $pdf->MultiCell(40, $height, $keterangan, '0', 'L');
                        $akhirYketerangan=$pdf->GetY();
                        // $pdf->SetX(1000);
                        // $pdf->MultiCell(5, $height, $no, '0', 'L');
                        // $akhirY0=$pdf->GetY();
                        $akhirY = max($akhirYketerangan,$akhirYakun);
                        $height2=$akhirY-$awalY;
                        $pdf->SetY($awalY);		


                        $pdf->__currentY=$pdf->GetY();
                        $pdf->MultiCell(5,$height,$no,0,'C');
                        $pdf->SetXY($pdf->GetX()+5, $pdf->__currentY);
                        $pdf->MultiCell(35,$height,$nojurnal,0,'L');
                        $pdf->SetXY($pdf->GetX()+35, $pdf->__currentY);
                        $pdf->MultiCell(18,$height,tanggalnormal($tanggal),0,'C');
                        $pdf->SetXY($pdf->GetX()+53, $pdf->__currentY);		
                        $pdf->MultiCell(12,$height,$noakun,0,'L');
                        $pdf->SetXY($pdf->GetX()+65, $pdf->__currentY);
                        $pdf->MultiCell(35,$height,$namaakun,0,'L');
                        $pdf->SetXY($pdf->GetX()+100, $pdf->__currentY);
                        $pdf->MultiCell(40,$height,$keterangan,0,'L');
                        $pdf->SetXY($pdf->GetX()+140, $pdf->__currentY);
                        $pdf->MultiCell(25,$height,number_format($debet,2,'.',','),0,'R');
                        $pdf->SetXY($pdf->GetX()+165, $pdf->__currentY);
                        $pdf->MultiCell(25,$height,number_format($kredit,2,'.',','),0,'R');
                        $pdf->SetXY($pdf->GetX()+190, $pdf->__currentY);
                        $pdf->Ln($height2-$height);
                        if($pdf->__currentY>250){
                                $pdf->AddPage();
                                $pdf->SetY(32);
                        }
                        $sdebet += $debet;
                        $skredit += $kredit;
                }
                $pdf->Cell(140,$height,' ',0,0,'L');
                $pdf->Cell(25,$height,'-------------------------',0,0,'R');	
                $pdf->Cell(25,$height,'-------------------------',0,1,'R');	
                $pdf->Cell(140,$height,'T O T A L   : ',0,0,'R');
                $pdf->Cell(25,$height,number_format($sdebet,2,'.',','),0,0,'R');	
                $pdf->Cell(25,$height,number_format($skredit,2,'.',','),0,1,'R');	
                $pdf->Cell(140,$height,' ',0,0,'L');
                $pdf->Cell(25,$height,'-------------------------',0,0,'R');	
                $pdf->Cell(25,$height,'-------------------------',0,1,'R');	
        }
        $pdf->Output();	
?>