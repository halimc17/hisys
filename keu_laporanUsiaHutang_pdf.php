<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

//$pt="PMO";
	$pt=$_GET['pt'];
	$gudang=$_GET['gudang'];
	$tanggalpivot=$_GET['tanggalpivot'];
	$tanggalpivot2=$_GET['tanggalpivot2'];
if($_POST['tanggaljt']!='')$tanggaljt=  tanggalsystemn($_GET['tanggaljt']);        
        $tanggalv=  tanggalsystemn($_GET['tanggalpivot']);
        $tanggalv2=  tanggalsystemn($_GET['tanggalpivot2']);
        
        $statuspo=$_GET['statuspo'];
        $supkontran=$_GET['supkontran'];
$kodesupplier=$_GET['kodesupplier'];
$nopodt=$_GET['nopodt'];
$rekening=$_GET['rekening'];
$jenis=$_GET['jenis'];
$unit=$_GET['unit'];
$status=$_GET['status'];
        
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt='Seluruhnya';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	$namapt=strtoupper($bar->namaorganisasi);
}
	
     
if($gudang!=''){
	$whereGudang = " and a.kodeorg = '".$gudang."'";
}else{
	$whereGudang = "";
}

if($pt!=''){
    $wherePt = " and a.kodeorg = '".$pt."'"; 
}else{
	$wherePt = "";
}

if($statuspo!='')
{
	if($statuspo==1)
	{
		$wherePo = " and b.lokalpusat = '1'";	
	}
	else
	{
		$wherePo = " and (b.lokalpusat = '0' or b.lokalpusat is null)";
	}
}
$wherePo = "";
if($supkontran!=''){
	$wheresupkontran = " and left(a.kodesupplier,1) = '".$supkontran."'";
}else{
	$wheresupkontran = "";
}

if($kodesupplier!=''){
	$wheresup = " and a.kodesupplier like '".$kodesupplier."%'";
}else{
	$wheresup = "";
}
if($tanggaljt!=''){
    $wheretanggaljt = " and a.jatuhtempo = '".$tanggaljt."' ";
}else{
    $wheretanggaljt = "";
}
if($jenis!=''){
    $wherejenis = " and a.tipeinvoice = '".$jenis."' ";
}else{
    $wherejenis = "";
}
// if($unit!=''){
//     $whereunit = " and a.unit = '".$unit."' ";
// }else{
//     $whereunit = "";
// }


$str = "select a.* from ".$dbname.".aging_sch_vw a 
		left join ".$dbname.".log_poht b 
		on a.nopo = b.nopo
                where a.posting=1 and a.tanggal >= '".$tanggalv."' and a.tanggal <= '".$tanggalv2."' and (a.nilaiinvoice > dibayar or a.dibayar is NULL)  and a.tipeinvoice not in ('um','p21','p22','p23','p25','ps4','upd','pjd') "
        . " ".$whereGudang." ".$wherePt." ".$wherePo." ".$wheresupkontran." ".$wheresup." ".$wheretanggaljt." ".$wherejenis." ".$whereunit." ".$wherestatus." and a.nopo like '%".$nopodt."%' ";
	//where a.tanggal > '2011-12-31' and (a.nilaiinvoice > dibayar or a.dibayar is NULL) ".$whereGudang." ".$wherePt." ".$wherePo."";

    
//echo $str;
$arrJnsTag=array();
$sjenisTag="select * from ".$dbname.".keu_5jenistagihan ";
$rJenisTag=fetchData($sjenisTag);
foreach ($rJenisTag as $key => $val) {
  $arrJnsTag[$val['kode']]=$val['jurnal'];
}


//=================================================
class PDF extends FPDF {
    function Header() {
		global $namapt;
		global $tanggalpivot;
		global $owlPDO;
		$width = $this->w - $this->lMargin - $this->rMargin;
        $this->SetFont('Arial','B',8); 
		$this->Cell($width,3,$namapt." per ".$tanggalpivot,'',1,'R');
        $this->SetFont('Arial','B',12);
		$this->Cell(280,3,strtoupper($_SESSION['lang']['usiahutang']),0,1,'C');
        $this->SetFont('Arial','',8);
		$this->Cell(225,3,' ','',0,'R');
		$this->Cell(15,3,$_SESSION['lang']['tanggal'],'',0,'L');
		$this->Cell(2,3,':','',0,'L');
		$this->Cell(35,3,date('d-m-Y H:i'),0,1,'L');
		$this->Cell(225,3,' ','',0,'R');
		$this->Cell(15,3,$_SESSION['lang']['page'],'',0,'L');
		$this->Cell(2,3,':','',0,'L');
		$this->Cell(35,3,$this->PageNo(),'',1,'L');
		$this->Cell(225,3,' ','',0,'R');
		$this->Cell(15,3,'User','',0,'L');
		$this->Cell(2,3,':','',0,'L');
		$this->Cell(35,3,$_SESSION['standard']['username'],'',1,'L');
        $this->Ln();
        $this->SetFont('Arial','',8);
		$this->Cell(17,5,$_SESSION['lang']['nourut'],'LTR',0,'C');	
		$this->Cell(55,5,$_SESSION['lang']['noinvoice'],'T',0,'L');	
		$this->Cell(25,5,'Contract Value',1,0,'C');	
		$this->Cell(29,5,'Invoice Value',1,0,'C');	
		$this->Cell(26,5,$_SESSION['lang']['belumjatuhtempo'],'LTR',0,'C');	
		$this->Cell(100,5,$_SESSION['lang']['sudahjatuhtempo'],1,0,'C');
		$this->Cell(25,5,$_SESSION['lang']['dibayar'],'LTR',0,'C');
//		$this->Cell(25,5,'Jumlah Hari','LTR',0,'C');
        $this->Ln();						
		$this->Cell(17,5,$_SESSION['lang']['tanggal'],'LBR',0,'C');	
		$this->Cell(55,5,$_SESSION['lang']['namasupplier'],1,0,'L');	
		$this->Cell(25,5,'PO/Contract No.','LBR',0,'C');	
		$this->Cell(29,5,$_SESSION['lang']['tgljatuhtempo'],'LBR',0,'C');	
		$this->Cell(26,5,'','LBR',0,'C');	
		$this->Cell(25,5,'1-30 '.$_SESSION['lang']['hari'],1,0,'C');
		$this->Cell(25,5,'31-60 '.$_SESSION['lang']['hari'],1,0,'C');
		$this->Cell(25,5,'61-90 '.$_SESSION['lang']['hari'],1,0,'C');
		$this->Cell(25,5,'over 90 '.$_SESSION['lang']['hari'],1,0,'C');
		$this->Cell(25,5,'Outstanding','LBR',0,'C');
//		$this->Cell(25,5,'Outstanding','LBR',0,'C');
        $this->Ln();						
    }
            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
}
function tanggalbiasa($_q)
{
 $_q=str_replace("-","",$_q);
 $_retval=substr($_q,4,4)."-".substr($_q,2,2)."-".substr($_q,0,2);
 return($_retval);
}
function tanggalbiasa2($_q)
{
 $_q=str_replace("-","",$_q);
 $_retval=substr($_q,6,2)."-".substr($_q,4,2)."-".substr($_q,0,4);
 return($_retval);
}


//================================
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$numrows=owlBaris($res);
	$no=0;
	if($numrows<1)
	{
		echo$_SESSION['lang']['tidakditemukan'];
	}
	else
	{
	$pdf=new PDF('L','mm','A4');
	$pdf->AddPage();
            $total0=$total30=$total60=$total90=$total100=$totaldibayar=0;
            $totalinvoice=0;
		while($bar=$res->fetch())
		{
			$namasupplier	=$bar->namasupplier;
			$noinvoice	=$bar->noinvoice; 
			$tanggal	=$bar->tanggal; 
			$jatuhtempo 	=$bar->jatuhtempo;
                        $nopokontrak    =$bar->nopo;
                        if($arrJnsTag[$bar->tipeinvoice]==1){
                          $nopokontrak    =$bar->noinvoicesupplier;
                        }
                        $nilaipo        =$bar->kurs*$bar->subtotal;
                        $nilaikontrak   =$bar->kurs*$bar->nilaikontrak;
			$nilaiinvoice 	=$bar->kurs*$bar->nilaiinvoice;
                        $totalinvoice+=$nilaiinvoice;
			$dibayar 	=$bar->kurs*$bar->dibayar;
                        $sisainvoice    =$nilaiinvoice-$dibayar;
                        $nilaipokontrak =$nilaipo;
                        if($nilaikontrak>0)$nilaipokontrak=$nilaikontrak;
//			$date1=date('Y-m-d');
			$date1=tanggalbiasa($tanggalpivot);
			$diff =(strtotime($jatuhtempo)-strtotime($date1));
			$outstd =floor(($diff)/(60*60*24));
//			if($outstd<1)$outstd=0;
                        
                        
			/*$flag0=$flag15=$flag30=$flag45=$flag100=0;
			if($outstd!=0)$outstd*=-1;
			if($outstd<=0)$flag0=1; 
			if(($outstd>=1)and($outstd<=15))$flag15=1;
			if(($outstd>=16)and($outstd<=30))$flag30=1;
			if(($outstd>=31)and($outstd<=45))$flag45=1;
			if($outstd>45)$flag100=1;
                        if($flag0==1)$total0+=$sisainvoice;
                        if($flag15==1)$total15+=$sisainvoice;
                        if($flag30==1)$total30+=$sisainvoice;
                        if($flag45==1)$total45+=$sisainvoice;
                        if($flag100==1)$total100+=$sisainvoice;*/
                        
                            $flag0=$flag30=$flag60=$flag90=$flag100=0;
                            if($outstd!=0)$outstd*=-1;
                            if($outstd<=0)$flag0=1; 
                            if(($outstd>=1)and($outstd<=30))$flag30=1;
                            if(($outstd>=31)and($outstd<=60))$flag60=1;
                            if(($outstd>=61)and($outstd<=90))$flag90=1;
                            if($outstd>90)$flag100=1;
                            if($flag0==1){$total0+=$sisainvoice;}
                            if($flag30==1){$total30+=$sisainvoice;}
                            if($flag60==1){$total60+=$sisainvoice;}
                            if($flag90==1){$total90+=$sisainvoice;}
                            if($flag100==1){$total100+=$sisainvoice;}
                        
                        
                        
                        $totaldibayar+=$dibayar;
			if($jatuhtempo=='0000-00-00'){ $outstd=''; $jatuhtempo=''; }else{ $jatuhtempo=tanggalnormal($jatuhtempo); }
//			if($dibayar>=$nilaiinvoice)continue;
			$no+=1;
		
		$pdf->Cell(17,5,$no,'T',0,'L');	
//		$pdf->Cell(40,5,$namasupplier,0,0,'L');	
		$pdf->Cell(55,5,$noinvoice,'T',0,'L');	
		$pdf->Cell(25,5,number_format($nilaipokontrak,2),'T',0,'R');
		$pdf->Cell(29,5,number_format($nilaiinvoice,2),'T',0,'R');
		$dummy=0;
		if($flag0==1)$dummy=number_format($sisainvoice,2);	
		$pdf->Cell(26,5,$dummy,'T',0,'R'); $dummy='';
		if($flag30==1)$dummy=number_format($sisainvoice,2);	
		$pdf->Cell(25,5,$dummy,'T',0,'R'); $dummy='';
		if($flag60==1)$dummy=number_format($sisainvoice,2);	
		$pdf->Cell(25,5,$dummy,'T',0,'R'); $dummy='';
		if($flag90==1)$dummy=number_format($sisainvoice,2);	
		$pdf->Cell(25,5,$dummy,'T',0,'R'); $dummy='';
		if($flag100==1)$dummy=number_format($sisainvoice,2);	
		$pdf->Cell(25,5,$dummy,'T',0,'R'); $dummy='';
		$pdf->Cell(25,5,number_format($dibayar,2,'.',','),'T',1,'R');	
//		$pdf->Cell(25,5,$outstd,'LTR',1,'R');	
		
		$pdf->__currentY=$pdf->GetY();
		$pdf->MultiCell(17,5,tanggalbiasa2($tanggal),0,'L');
		$pdf->SetXY($pdf->GetX()+17, $pdf->__currentY);
		$pdf->MultiCell(55,5,$namasupplier,0,'L');	
		$pdf->SetXY($pdf->GetX()+17+55, $pdf->__currentY);
//		$pdf->Cell(55,5,$noinvoice,'LBR',0,'L');	
		$pdf->MultiCell(25,5,$nopokontrak,0,'L');
		$pdf->SetXY($pdf->GetX()+17+55+25, $pdf->__currentY);
		$pdf->Cell(29,5,$jatuhtempo,0,0,'R');
		$pdf->Cell(26,5,'',0,0,'R');
		$pdf->Cell(25,5,'',0,0,'R');	
		$pdf->Cell(25,5,'',0,0,'R');	
		$pdf->Cell(25,5,'',0,0,'R');	
		$pdf->Cell(25,5,'',0,0,'R');	
		$pdf->Cell(25,5,$outstd." Hari",0,1,'R');
		$pdf->Ln();
//		$pdf->Cell(25,5,'','LBR',1,'R');	
		}
//		$pdf->Cell(14,5,"",'LTR',0,'L');	
//		$pdf->Cell(40,5,$namasupplier,0,0,'L');	
		$pdf->Cell(96,5,"TOTAL",'TB',0,'C');	
//		$pdf->Cell(25,5,"",'',0,'L');
		$dummy=number_format($totalinvoice,2);	
		$pdf->Cell(29,5,$dummy,'TB',0,'R');
		$dummy=number_format($total0,2);	
		$pdf->Cell(26,5,$dummy,'TB',0,'R');
		$dummy=number_format($total30,2);	
		$pdf->Cell(25,5,$dummy,'TB',0,'R');
		$dummy=number_format($total60,2);	
		$pdf->Cell(25,5,$dummy,'TB',0,'R');
		$dummy=number_format($total90,2);	
		$pdf->Cell(25,5,$dummy,'TB',0,'R');
		$dummy=number_format($total100,2);	
		$pdf->Cell(25,5,$dummy,'TB',0,'R');
		$pdf->Cell(25,5,number_format($totaldibayar,2,'.',','),'TB',0,'R');
//		$pdf->Cell(25,5,"", TRB,1,'R');	

                $pdf->Output();	
	}

		
?>