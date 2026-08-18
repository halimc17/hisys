<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');

$proses = $_GET['proses'];
$tipe=$_GET['tipe'];
$param = $_GET;

$notran=$param['notransaksi'];

/** Report Prep **/
$cols = array();

# Prestasi
//$col1 = 'nik,kodekegiatan,kodeorg,hasilkerja,jumlahhk,upahkerja,upahpremi,umr';
$col1 = 'tanggal,nik,a.kodeorg,hasilkerja,jumlahhk,upahkerja,upahpenalty,upahpremi,premibasis,rupiahpenalty,luaspanen';
$cols[] = explode(',',$col1);
$query="select a.*,b.*,a.kodeorg from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where a.notransaksi='".$param['notransaksi']."'";
//exit("Error".$query);
$data[] = fetchData($query);
$align[] = explode(",","L,L,L,R,R,R,R,R");
$length[] = explode(",","10,10,15,10,10,15,15,15");



//getNamaKaryawanawan
$sDtKaryawn="select karyawanid,namakaryawan from ".$dbname.".datakaryawan order by namakaryawan asc";
$rData=fetchData($sDtKaryawn);
foreach($rData as $brKary =>$rNamakaryawan){
    $RnamaKary[$rNamakaryawan['karyawanid']]=$rNamakaryawan['namakaryawan'];
}

$sOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi order by namaorganisasi asc";
//exit("Error".$sOrg);
$rDataOrg=fetchData($sOrg);
foreach($rDataOrg as $brOrg =>$rNamaOrg){
    $rNmOrg[$rNamaOrg['kodeorganisasi']]=$rNamaOrg['namaorganisasi'];
}

   
$title = strtoupper($_SESSION['lang']['panen']);
$titleDetail = array($_SESSION['lang']['prestasi'],$_SESSION['lang']['absensi'],$_SESSION['lang']['material']);

// Init Total
$totJanjang=$totUpahKerja=$totUpahKerjapenalty=$totUpahPremi=0;
$totUpahPremibasis=$totUpahDenda=$totLuas=$totSisa=0;

/** Output Format **/
switch($proses) {
    case 'pdf':
        $pdf=new zPdfMaster('L','pt','A4');
        $pdf->_noThead=true;
        $pdf->setAttr1($title,$align,$length,array());
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
        $pdf->AddPage();
        $pdf->Ln();
        $pdf->SetFillColor(255,255,255);  
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell($width,$height,$_SESSION['lang']['notransaksi']." : ".$param['notransaksi'],0,1,'L',1);
        $pdf->SetFillColor(220,220,220);
        $pdf->SetFont('Arial','B',8);
		$getX = $pdf->GetX();
		$getY = $pdf->GetY();
        $pdf->MultiCell(6/100*$width,$height*3,$_SESSION['lang']['tanggal'],1,'C',1);
		$pdf->SetXY($getX+(6/100*$width),$getY);
		$getX = $pdf->GetX();
        $pdf->MultiCell(13/100*$width,$height*3,$_SESSION['lang']['namakaryawan'],1,'C',1);
		$pdf->SetXY($getX+(13/100*$width),$getY);
		$getX = $pdf->GetX();
        $pdf->MultiCell(6/100*$width,$height*3,$_SESSION['lang']['blok'],1,'C',1);
		$pdf->SetXY($getX+(6/100*$width),$getY);
		$getX = $pdf->GetX();
        $pdf->MultiCell(5/100*$width,$height*3,$_SESSION['lang']['jjg'],1,'C',1);
		$pdf->SetXY($getX+(5/100*$width),$getY);
		$getX = $pdf->GetX();
        $pdf->MultiCell(5/100*$width,$height*3,$_SESSION['lang']['luas'],1,'C',1);
        $pdf->SetXY($getX+(5/100*$width),$getY);
		$getX = $pdf->GetX();
		$pdf->MultiCell(5/100*$width,$height*3,$_SESSION['lang']['hk2'],1,'C',1);
		$pdf->SetXY($getX+(5/100*$width),$getY);
		$getX = $pdf->GetX();
		$pdf->MultiCell(7/100*$width,$height*3,$_SESSION['lang']['upahkerja'],1,'C',1);
		$pdf->SetXY($getX+(7/100*$width),$getY);
		$getX = $pdf->GetX();
        $pdf->MultiCell(7/100*$width,$height*3,$_SESSION['lang']['upahpenalty'],1,'C',1);
		$pdf->SetXY($getX+(7/100*$width),$getY);
		$getX = $pdf->GetX();
        $pdf->MultiCell(6/100*$width,$height*3,'Premi SB1',1,'C',1);
		$pdf->SetXY($getX+(6/100*$width),$getY);
		$getX = $pdf->GetX();
        $pdf->MultiCell(6/100*$width,$height*3,'Premi SB2',1,'C',1);
		$pdf->SetXY($getX+(6/100*$width),$getY);
		$getX = $pdf->GetX();
        $pdf->MultiCell(6/100*$width,$height*3,'Premi LB1',1,'C',1);
        $pdf->SetXY($getX+(6/100*$width),$getY);
		$getX = $pdf->GetX();
		$pdf->MultiCell(6/100*$width,$height*3,'Premi LB2',1,'C',1);
        $pdf->SetXY($getX+(6/100*$width),$getY);
		$getX = $pdf->GetX();
		$pdf->MultiCell(7/100*$width,$height*3,$_SESSION['lang']['brondol'],1,'C',1);
        $pdf->SetXY($getX+(7/100*$width),$getY);
		$getX = $pdf->GetX();
		$pdf->MultiCell(7/100*$width,$height*3,$_SESSION['lang']['rupiahpenalty'],1,'C',1);
		$pdf->SetXY($getX+(7/100*$width),$getY);
		$getX = $pdf->GetX();
        $pdf->MultiCell(8/100*$width,$height*3,$_SESSION['lang']['total'],1,'C',1);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',8);
		
        $qData=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qData->setFetchMode(PDO::FETCH_ASSOC);        
        while($rData=$qData->fetch()){
            $pdf->Cell(6/100*$width,$height,tanggalnormal($rData['tanggal']),1,0,'C',1);
            $pdf->Cell(13/100*$width,$height,getNamaKaryawan($rData['nik']),1,0,'L',1);
            $pdf->Cell(6/100*$width,$height,getNamaOrg($rData['kodeorg']),1,0,'C',1);
            $pdf->Cell(5/100*$width,$height,$rData['hasilkerja'],1,0,'R',1);
            $pdf->Cell(5/100*$width,$height,number_format($rData['luaspanen'],2),1,0,'R',1);
            $pdf->Cell(5/100*$width,$height,number_format($rData['jumlahhk'],2),1,0,'R',1);
            $pdf->Cell(7/100*$width,$height,number_format($rData['upahkerja'],0),1,0,'R',1);
            $pdf->Cell(7/100*$width,$height,number_format($rData['upahpenalty'],0),1,0,'R',1);
            //$pdf->Cell(6/100*$width,$height,number_format($rData['upahpremi'],0),1,0,'R',1);
            $pdf->Cell(6/100*$width,$height,number_format($rData['premibasis'],0),1,0,'R',1);
            $pdf->Cell(6/100*$width,$height,number_format($rData['premibasis2'],0),1,0,'R',1);
            $pdf->Cell(6/100*$width,$height,number_format($rData['upahpremilebihbasis'],0),1,0,'R',1);
            $pdf->Cell(6/100*$width,$height,number_format($rData['upahpremilebihbasis2'],0),1,0,'R',1);
            $pdf->Cell(7/100*$width,$height,number_format($rData['premibrondol'],0),1,0,'R',1);
            $pdf->Cell(7/100*$width,$height,number_format($rData['rupiahpenalty'],0),1,0,'R',1);
            $sisa=($rData['upahkerja']+$rData['premibasis']+$rData['premibasis2']+$rData['upahpremi']+$rData['upahpremilebihbasis']+$rData['upahpremilebihbasis2']+$rData['premibrondol'])-$rData['rupiahpenalty']-$rData['upahpenalty'];
            $pdf->Cell(8/100*$width,$height,number_format($sisa,0),1,1,'R',1);
            $totJanjang+=$rData['hasilkerja'];
            $totUpahKerja+=$rData['upahkerja'];
            $totUpahKerjapenalty+=$rData['upahpenalty'];
            $totUpahPremi+=$rData['upahpremi'];
            $totUpahPremibasis+=$rData['premibasis'];
            $totUpahPremibasis2+=$rData['premibasis2'];
            $totUpahPremiLbasis+=$rData['upahpremilebihbasis'];
            $totUpahPremiLbasis2+=$rData['upahpremilebihbasis2'];
            $totUpahDenda+=$rData['rupiahpenalty'];
            $totLuas+=$rData['luaspanen'];
            $totHk+=$rData['jumlahhk'];
            $totbrd+=$rData['premibrondol'];
            $totSisa+=$sisa;
        }
        $pdf->Cell(25/100*$width,$height,$_SESSION['lang']['total'],1,0,'C',1);
        $pdf->Cell(5/100*$width,$height,number_format($totJanjang,0),1,0,'R',1);
        $pdf->Cell(5/100*$width,$height,number_format($totLuas,2),1,0,'R',1);
        $pdf->Cell(5/100*$width,$height,number_format($totHk,2),1,0,'R',1);
        $pdf->Cell(7/100*$width,$height,number_format($totUpahKerja,0),1,0,'R',1);
        $pdf->Cell(7/100*$width,$height,number_format($totUpahKerjapenalty,0),1,0,'R',1);
        //$pdf->Cell(7/100*$width,$height,number_format($totUpahPremi,0),1,0,'R',1);
        $pdf->Cell(6/100*$width,$height,number_format($totUpahPremibasis,0),1,0,'R',1);
        $pdf->Cell(6/100*$width,$height,number_format($totUpahPremibasis2,0),1,0,'R',1);
        $pdf->Cell(6/100*$width,$height,number_format($totUpahPremiLbasis,0),1,0,'R',1);
        $pdf->Cell(6/100*$width,$height,number_format($totUpahPremiLbasis2,0),1,0,'R',1);
        $pdf->Cell(7/100*$width,$height,number_format($totbrd,0),1,0,'R',1);
        $pdf->Cell(7/100*$width,$height,number_format($totUpahDenda,0),1,0,'R',1);
        $pdf->Cell(8/100*$width,$height,number_format($totSisa,0),1,1,'R',1);
		
     
		 
		$pdf->Ln();
        $pdf->SetFillColor(255,255,255);  
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell($width,$height,$_SESSION['lang']['absensi'],0,1,'L',1);
        $pdf->SetFillColor(220,220,220);
        $pdf->SetFont('Arial','B',8);
		$getX = $pdf->GetX();
		$getY = $pdf->GetY();
        $pdf->MultiCell(20/100*$width,$height*3,$_SESSION['lang']['namakaryawan'],1,'C',1);
		$pdf->SetXY($getX+(20/100*$width),$getY);
		$getX = $pdf->GetX();
		$pdf->MultiCell(8/100*$width,$height*3,$_SESSION['lang']['hk2'],1,'C',1);
		$pdf->SetXY($getX+(8/100*$width),$getY);
		$getX = $pdf->GetX();
		$pdf->MultiCell(10/100*$width,$height*3,$_SESSION['lang']['upahkerja'],1,'C',1);
		$pdf->SetXY($getX+(10/100*$width),$getY);
		$getX = $pdf->GetX();
        $pdf->MultiCell(10/100*$width,$height*3,$_SESSION['lang']['upahpremi'],1,'C',1);
		$pdf->SetXY($getX+(10/100*$width),$getY);
		$getX = $pdf->GetX();
        $pdf->MultiCell(13/100*$width,$height*3,$_SESSION['lang']['total'],1,'C',1);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',8);
		
		$totUpahPremi=$totHk=$totUpahKerja=$totSisa=0;
		$query="select * from ".$dbname.".sdm_absensidt where norefrensi='".$param['notransaksi']."'";
		$qData=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qData->setFetchMode(PDO::FETCH_ASSOC);        
        while($rData=$qData->fetch()){
            $pdf->Cell(20/100*$width,$height,$RnamaKary[$rData['karyawanid']],1,0,'L',1);
            $pdf->Cell(8/100*$width,$height,number_format($rData['hk'],2),1,0,'R',1);
            $pdf->Cell(10/100*$width,$height,number_format($rData['umr'],0),1,0,'R',1);
            $pdf->Cell(10/100*$width,$height,number_format($rData['premi'],0),1,0,'R',1);
            $sisa=$rData['umr']+$rData['premi'];
            $pdf->Cell(13/100*$width,$height,number_format($sisa,0),1,1,'R',1);
            $totUpahPremi+=$rData['upahpremi'];
            $totHk+=$rData['hk'];
            $totUpahKerja+=$rData['umr'];
            $totSisa+=$sisa;
        }
        $pdf->Cell(20/100*$width,$height,$_SESSION['lang']['total'],1,0,'C',1);
        $pdf->Cell(8/100*$width,$height,number_format($totHk,2),1,0,'R',1);
        $pdf->Cell(10/100*$width,$height,number_format($totUpahKerja,0),1,0,'R',1);
        $pdf->Cell(10/100*$width,$height,number_format($totUpahPremi,0),1,0,'R',1);
        $pdf->Cell(13/100*$width,$height,number_format($totSisa,0),1,1,'R',1);
		
		$pdf->Output();
        
	break;
}
?>