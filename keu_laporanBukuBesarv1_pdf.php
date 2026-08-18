<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');

$pt=$_GET['pt'];
$gudang=$_GET['gudang'];
$tanggal1=$_GET['tanggal1'];
$tanggal2=$_GET['tanggal2'];
$akundari=$_GET['akundari'];
$akunsampai=$_GET['akunsampai'];
$regional=$_GET['regional'];
$optNmOrg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
        
//$periode buat filter keu_saldobulanan, $bulan buat nentuin field-nya
$qwe=explode("-",$tanggal1);
$periode=$qwe[2].$qwe[1];
$bulan=$qwe[1];

//balik tanggal
$qwe=explode("-",$tanggal1);
$tanggal1=$qwe[2]."-".$qwe[1]."-".$qwe[0];
$qwe=explode("-",$tanggal2);
$tanggal2=$qwe[2]."-".$qwe[1]."-".$qwe[0];


###tambahan indra
//bentuk tanggal 1 untuk veriv
$qwer=explode("-",$tanggal1);
$tglverivsatu=$qwer[2];

//bentuk tangal 1 diawal bulan untuk sum db-kr bentuk sawal
$tglsatu=$qwer[2]."-".$qwer[1]."-01";

//hitung tanggal kemarin
$tglx =  str_replace("-","",$tanggal1);
$tglkemarin = strtotime('-1 day',strtotime($tglx));
$tglkemarin = date('Y-m-d', $tglkemarin);
##tutup tambah indra


// exclude laba rugi tahun berjalan
$str="select noakundebet from ".$dbname.".keu_5parameterjurnal
    where kodeaplikasi = 'CLM' ";
try{$str=$owlPDO->query($str);$str->setFetchMode(PDO::FETCH_OBJ);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
while($bar=$str->fetch())
{
    $clm=$bar->noakundebet;
}

if($regional=='' && $gudang=='')
{
    $wheregudang=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
}
else if($regional!='' && $gudang=='')
{
    $wheregudang=" and kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."')";
}
else
{
    $wheregudang=" and kodeorg='".$gudang."'";
}

//hitung total transaksi yang sudah ada
$iTran="select sum(debet)-sum(kredit) as transaksi,noakun from ".$dbname.".keu_jurnaldt_vw where "
        . " noakun != '".$clm."' and tanggal between '".$tglsatu."' and '".$tglkemarin."' "
        . " and noakun >= '".$akundari."' and noakun <= '".$akunsampai."' ".$wheregudang." "
        . " group by noakun";
try{$iTran=$owlPDO->query($iTran);$iTran->setFetchMode(PDO::FETCH_OBJ);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
while($dTran=$str->fetch())
{
    $totaltran[$dTran->noakun]+=$dTran->transaksi;
}


$str="select * from ".$dbname.".keu_saldobulanan where noakun != '".$clm."' and periode = '".$periode."' and noakun >= '".$akundari."' and noakun <= '".$akunsampai."' ".$wheregudang." order by noakun";
try{$str=$owlPDO->query($str);$str->setFetchMode(PDO::FETCH_OBJ);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
while($bar=$str->fetch())
{
    $qwe="awal".$bulan;
    $saldoawal[$bar->noakun]+=$bar->$qwe;
    $aqun[$bar->noakun]=$bar->noakun;
}
// ambil data
$isidata=array();
$str="select * from ".$dbname.".keu_jurnaldt_vw where noakun != '".$clm."' and tanggal >= '".$tanggal1."' and tanggal <= '".$tanggal2."' and noakun >= '".$akundari."' and noakun <= '".$akunsampai."' ".$wheregudang." order by noakun, tanggal";
try{$str=$owlPDO->query($str);$str->setFetchMode(PDO::FETCH_OBJ);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
while($bar=$str->fetch())
{
    $qwe=$bar->nojurnal.$bar->noakun.$bar->nourut;
    $isidata[$qwe][nojur]=$bar->nojurnal;
    $isidata[$qwe][tangg]=$bar->tanggal;
    $isidata[$qwe][noaku]=$bar->noakun;
    $isidata[$qwe][noreferensi]=$bar->noreferensi;
    $isidata[$qwe][nodok]=$bar->nodok;
    $isidata[$qwe][keter]=$bar->keterangan;
    $isidata[$qwe][debet]=$bar->debet;
    $isidata[$qwe][kredi]=$bar->kredit;
    $aqun[$bar->noakun]=$bar->noakun;
}

// kamus nama akun
$str="select noakun,namaakun from ".$dbname.".keu_5akun
    where level = '5' and noakun!='".$clm."'";
try{$str=$owlPDO->query($str);$str->setFetchMode(PDO::FETCH_OBJ);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
while($bar=$str->fetch())
{
    $namaakun[$bar->noakun]=$bar->namaakun;
}

if(!empty($isidata)) foreach($isidata as $c=>$key) {
    $sort_noaku[] = $key['noaku'];
    $sort_tangg[] = $key['tangg'];
    $sort_debet[] = $key['debet'];
    $sort_nojur[] = $key['nojur'];
}

// sort
if(!empty($isidata))array_multisort($sort_noaku, SORT_ASC, $sort_tangg, SORT_ASC, $sort_debet, SORT_DESC, $sort_nojur, SORT_ASC, $isidata);
if(!empty($aqun))asort($aqun);

//=================================================
class PDF extends FPDF {
    function Header() {
        global $pt;
        global $gudang;
        global $tanggal1;
        global $tanggal2;
        global $optNmOrg;
		$width = $this->w - $this->lMargin - $this->rMargin;
        $this->SetFont('Arial','B',9); 
            $this->Cell($width,3,$optNmOrg[$pt],'',1,'R');
            $this->Cell($width,3,$optNmOrg[$gudang],'',1,'R');
        $this->SetFont('Arial','B',10);
            $this->Cell(190,3,strtoupper($_SESSION['lang']['laporanbukubesar']),0,1,'C');
        $this->SetFont('Arial','',7);
            $this->Cell(238,3,' ','',0,'R');
            $this->Cell(15,3,$_SESSION['lang']['tanggal'],'',0,'L');
            $this->Cell(2,3,':','',0,'L');
            $this->Cell(35,3,date('d-m-Y H:i'),0,1,'L'); if($gudang=='')$gudang='All';
            $this->Cell(238,3,'UNIT : '.$gudang,'',0,'L');
            $this->Cell(15,3,$_SESSION['lang']['page'],'',0,'L');
            $this->Cell(2,3,':','',0,'L');
            $this->Cell(35,3,$this->PageNo(),'',1,'L');
            $this->Cell(238,3,"Tanggal : ".tanggalnormal($tanggal1).' sampai '.tanggalnormal($tanggal2),'',0,'L'); 
            $this->Cell(15,3,'User','',0,'L');
            $this->Cell(2,3,' : ','',0,'L');
            $this->Cell(35,3,$_SESSION['standard']['username'],'',1,'L');
        $this->Ln();
        $this->SetFont('Arial','',7);
            $this->Cell(10,5,$_SESSION['lang']['nomor'],1,0,'C');
            $this->Cell(35,5,$_SESSION['lang']['nojurnal'],1,0,'C');	
            $this->Cell(15,5,$_SESSION['lang']['tanggal'],1,0,'C');	
            $this->Cell(15,5,$_SESSION['lang']['noakun'],1,0,'C');
            $this->Cell(36,5,$_SESSION['lang']['noreferensi'],1,0,'C');	
            $this->Cell(90,5,$_SESSION['lang']['keterangan'],1,0,'C');	
            $this->Cell(25,5,$_SESSION['lang']['debet'],1,0,'C');
            $this->Cell(25,5,$_SESSION['lang']['kredit'],1,0,'C');
            $this->Cell(25,5,$_SESSION['lang']['saldo'],1,0,'C');
        $this->Ln();						
        $this->Ln();						

    }
}
//================================

    $pdf=new PDF('L','mm','A4');
    $pdf->AddPage();
    
// tampilin daftar akun
if(!empty($aqun))foreach($aqun as $akyun){
    $subsalwal=$saldoawal[$akyun];
    $totaldebet=0;
    $totalkredit=0;
    $subsalak=$subsalwal;
    if($tglverivsatu!='01')
    {
        $salwal=$subsalwal+$totaltran[$akyun];
    }
    else
    {
        $salwal=$subsalwal;
    }
    //$salwal=$subsalwal;
    $grandsalwal+=$subsalwal;
        $pdf->Cell(10,5,'',0,0,'C');
        $pdf->Cell(35,5,'',0,0,'L');
        $pdf->Cell(15,5,'',0,0,'L');
        $pdf->Cell(15,5,$akyun,'B',0,'L');
        $pdf->Cell(36,5,'','B',0,'L');
        $pdf->Cell(90,5,$namaakun[$akyun],'B',0,'L');
        $pdf->Cell(75,5,number_format($salwal,2),'B',1,'R');	
// tampilin jurnal daftar akun    
    if(!empty($isidata))foreach($isidata as $baris)
    {
        if($baris[noaku]==$akyun){
            $no+=1;
            $pdf->Cell(10,5,$no,0,0,'C');
            $pdf->Cell(35,5,$baris[nojur],0,0,'L');
            $pdf->Cell(15,5,tanggalnormal($baris[tangg]),0,0,'L');
            $pdf->Cell(15,5,$baris[noaku],0,0,'L');
            $pdf->Cell(36,5,$baris[noreferensi],0,0,'L');
            
            $pdf->Cell(90,5,$baris[keter],0,0,'L');				
//            $pdf->Cell(20,5,number_format($salwal),0,0,'R');	
            
            $pdf->Cell(25,5,number_format($baris[debet],2),0,0,'R');
            $totaldebet+=$baris[debet];
            $grandtotaldebet+=$baris[debet];
            $pdf->Cell(25,5,number_format($baris[kredi],2),0,0,'R');
            $totalkredit+=$baris[kredi];
            $grandtotalkredit+=$baris[kredi];
            $salwal=$salwal+($baris[debet])-($baris[kredi]);
            $pdf->Cell(25,5,number_format($salwal,2),0,1,'R');
            $subsalak=$salwal;
        }
    } 
// subtotal    
        $pdf->Cell(200.5,5,'Sub Total','B',0,'R');				
//        $pdf->Cell(20,5,number_format($subsalwal),0,0,'R');
        $pdf->Cell(25,5,number_format($totaldebet,2),'TB',0,'R');
        $pdf->Cell(25,5,number_format($totalkredit,2),'TB',0,'R');	
        $pdf->Cell(25,5,number_format($subsalak,2),'TB',1,'R');
}

	// total
	setIt($grandsalwal,0);
	setIt($grandtotaldebet,0);
	setIt($grandtotalkredit,0);
                $grandsalak=$grandsalwal+$grandtotaldebet-$grandtotalkredit;
	$pdf->Cell(200.5,5,'Grand Total',0,0,'R');				
	$pdf->Cell(25,5,number_format($grandtotaldebet,2),0,0,'R');
	$pdf->Cell(25,5,number_format($grandtotalkredit,2),0,0,'R');	
	$pdf->Cell(25,5,number_format($grandsalak,2),0,1,'R');
    
$pdf->Output();		
?>