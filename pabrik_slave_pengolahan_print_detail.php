<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');

$proses = $_GET['proses'];
$param = $_GET;


/** Report Prep **/
$cols = array();

# Detail
$col1 = "kodeorg as station,tahuntanam,jammulai,jamselesai,jamstagnasi,".
    "keterangan";
$cols = explode(',',$col1);
$cols[0] = 'station';
$cols[1] = 'mesin';
$query = selectQuery($dbname,'pabrik_pengolahanmesin',$col1,
    "nopengolahan='".$param['nopengolahan']."'");
$data = fetchData($query);
$align = explode(",","L,L,L,L,L,L");
$length = explode(",","20,15,15,15,15,20");
if(empty($data)) {
    //echo "Data Kosong";
    //exit;
}
else
{
    # Material
    $queryMat = selectQuery($dbname,'pabrik_pengolahan_barang','kodeorg,tahuntanam,kodebarang,jumlah',
        "nopengolahan='".$param['nopengolahan']."'");
    $resMat = fetchData($queryMat);

    # Options
    $whereOrg = "kodeorganisasi in (";
    foreach($data as $key=>$row) {
        if($key==0) {
            $whereOrg .= "'".$row['station']."','".$row['tahuntanam']."'";
        } else {
            $whereOrg .= ",'".$row['station']."','".$row['tahuntanam']."'";
        }
    }
    $whereOrg .= ")";
    if(!empty($resMat)) {
        $whereBarang = "kodebarang in (";
        foreach($resMat as $key=>$row) {
            if($key==0) {
                $whereBarang .= "'".$row['kodebarang']."'";
            } else {
                $whereBarang .= ",'".$row['kodebarang']."'";
            }
        }
        $whereBarang .= ")";
    } else {
        $whereBarang = null;
    }
}
$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
    $whereOrg,'0',true);
$optBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',
    $whereBarang,'0',true);
$optBarang[0] = "";

# Data Show
$dataShow = $data;
foreach($dataShow as $key=>$row) {
    $dataShow[$key]['station'] = $optOrg[$row['station']];
    $dataShow[$key]['tahuntanam'] = $optOrg[$row['tahuntanam']];
}

$dataMat = array();
if(is_array($resMat)){
	foreach($resMat as $key=>$row) {
		$dataMat[$row['kodeorg']][$row['tahuntanam']][] = array(
		'nama'=>$optBarang[$row['kodebarang']],
		'jumlah'=>$row['jumlah']
		);
	}
}
$title = $_SESSION['lang']['operasipabrik'];
$titleDetail = 'Detail';

/** Output Format **/
switch($proses) {
    case 'pdf':
        $pdf=new zPdfMaster('L','pt','A4');
        $pdf->_noThead=true;
        $pdf->setAttr1($title,$align,$length,array());
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
	$pdf->AddPage();
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell($width,$height,$_SESSION['lang']['nopengolahan']." : ".
            $param['nopengolahan'],0,1,'L',1);
        
        $iData="select * from ".$dbname.".pabrik_pengolahan where nopengolahan='".$param['nopengolahan']."' ";
        $nData=$owlPDO->query($iData) or die(print " Gagal: ".PDOException::getMessage());
        $nData->setFetchMode(PDO::FETCH_ASSOC);
        $dData=$nData->fetch();
        
        $pdf->Cell($width,$height,$_SESSION['lang']['jammulai']." : ".
            $dData['jammulai'],0,1,'L',1);
        
        $pdf->Cell($width,$height,$_SESSION['lang']['jamselesai']." : ".
            $dData['jamselesai'],0,1,'L',1);
        
        $pdf->Ln();
        
        # Header
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell($width,$height,$titleDetail,0,1,'L',1);
        $pdf->SetFillColor(220,220,220);
        $i=0;
        foreach($cols as $column) {
            $pdf->Cell($length[$i]/100*$width,$height,$_SESSION['lang'][$column],1,0,'C',1);
            $i++;
        }
        $pdf->Ln();
        
        # Content
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',9);
        foreach($dataShow as $key=>$row) {    
            $i=0;
            foreach($row as $cont) {
                $pdf->Cell($length[$i]/100*$width,$height,$cont,1,0,$align[$i],1);
		$i++;
            }
            $pdf->Ln();
	    
	    # Show Material
	    if(isset($dataMat[$data[$key]['station']][$data[$key]['tahuntanam']])) {
		$j=1;
		foreach($dataMat[$data[$key]['station']][$data[$key]['tahuntanam']] as $rowMat) {
		    $pdf->Cell(20/100*$width,$height,$j,1,0,'R',1);
		    $pdf->Cell(30/100*$width,$height,$rowMat['nama'],1,0,'L',1);
		    $pdf->Cell(50/100*$width,$height,$rowMat['jumlah'],1,0,'R',1);
		    $pdf->Ln();
		    $j++;
		}
	    }
        }
        $pdf->Ln();
        
        $pdf->Output();
        break;
    case 'excel':
        break;
    default:
    break;
}
?>