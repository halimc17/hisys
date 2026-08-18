<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');

$proses = $_GET['proses'];
$param = $_POST;


/** Report Prep **/
//cari nama orang
$str="select karyawanid, namakaryawan from ".$dbname.".datakaryawan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
   $nama[$bar->karyawanid]=$bar->namakaryawan;
}    

$where = "kodeorg='".$_SESSION['empl']['lokasitugas']."' and updateby='".$_SESSION['standard']['userid']."'";
if($_SESSION['empl']['kodejabatan']==5)$where = "kodeorg like '%' and updateby like '%'";
$cols = 'notransaksi,kodeorg,tanggal,mandor,mandor1,assisten,
	krani';
$colArr = explode(',',$cols);
$order="tanggal desc";

$query = selectQuery($dbname,'vhc_splht',$cols,$where,$order);
$data = fetchData($query);
	foreach($data as $key=>$row) {
	    $data[$key]['tanggal'] = tanggalnormal($row['tanggal']);
	    $data[$key]['mandor'] = isset($nama[$row['mandor']])? $nama[$row['mandor']]: '';
		$data[$key]['mandor1'] = isset($nama[$row['mandor1']])? $nama[$row['mandor1']]: '';
		$data[$key]['assisten'] = isset($nama[$row['assisten']])? $nama[$row['assisten']]: '';
		$data[$key]['krani'] = isset($nama[$row['krani']])? $nama[$row['krani']]: '';
	}


$title = "SIPIL";
$align = explode(",","L,L,C,L,L,L,L");
$length = explode(",","15,10,8,16,16,16,16");

/** Output Format **/
switch($proses) {
    case 'pdf':
        $pdf=new zPdfMaster('L','pt','A4');
        $pdf->setAttr1($title,$align,$length,$colArr);
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
	$pdf->AddPage();
        
        $pdf->SetFillColor(255,255,255);
	$pdf->SetFont('Arial','',9);
        foreach($data as $key=>$row) {    
            $i=0;
            foreach($row as $cont) {
                $pdf->Cell($length[$i]/100*$width,$height,$cont,1,0,$align[$i],1);
                $i++;
            }
            $pdf->Ln();
        }
	
        $pdf->Output();
        break;
    case 'excel':
        break;
    default:
    break;
}
?>