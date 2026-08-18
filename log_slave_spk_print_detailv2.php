<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');

$urlefil=checkPostGet('urlefil','0');
$proses = $_GET['proses'];
$param = $_GET;

$str="select * from ".$dbname.".log_spkht where notransaksi='".$param['notransaksi']."'";
$res=fetchData($str);
$param['kodeorg'] = $res[0]['kodeorg'];
$param['koderekanan'] = $res[0]['koderekanan'];

/** Report Prep **/
$cols = array();

# Detail
$col1 = "kodeblok,kodekegiatan,hk,hasilkerjajumlah,satuan,jumlahrp";
$cols = explode(',',$col1);
$cols[0] = 'subunit';
$where = "notransaksi='".$param['notransaksi']."' and (left(kodeblok,4)='".$param['kodeorg']."' or length(kodeblok)>8)";
$query = selectQuery($dbname,'log_spkdt',$col1,$where);
$data = fetchData($query);
$align = explode(",","L,L,L,R,L,R");
$length = explode(",","20,20,10,20,10,20");
if(empty($data)) {
    echo "Data Kosong";
    exit;
}

# Options
$whereOrg = "kodeorganisasi in (";
$whereKeg = "kodekegiatan in (";
foreach($data as $key=>$row) {
    if($key==0) {
        $whereOrg .= "'".$row['kodeblok']."'";
        $whereKeg .= "'".$row['kodekegiatan']."'";
    } else {
        $whereOrg .= ",'".$row['kodeblok']."'";
        $whereKeg .= ",'".$row['kodekegiatan']."'";
    }
}
$whereOrg .= ",'".$param['kodeorg']."')";
$whereKeg .= ")";
$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
    $whereOrg,'0',true);
$optKeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',
    $whereKeg,'0',true);
$optSupp = makeOption($dbname,'log_5supplier','supplierid,namasupplier',
    "supplierid='".$param['koderekanan']."'");

// $optProject = makeOption($dbname,'project','kode,nama');
// $optProjectDt = makeOption($dbname,'project_dt','kegiatan,namakegiatan');

$optDivisi = makeOption($dbname,'log_spkht','notransaksi,divisi',"notransaksi='".$param['notransaksi']."'");

//Perumahan
$optRegOrg = makeOption($dbname,'bgt_regional_assignment','kodeunit,regional',"kodeunit='".$param['kodeorg']."'");
$optPerumahan = makeOption($dbname,'sdm_perumahanht','norumah,keterangan',"kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional = '".$optRegOrg[$param['kodeorg']]."')");
$optKegPerumahan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kelompok = 'SPL'");

# Data Show
$dataShow = $data;
foreach($dataShow as $key=>$row) {
	if($optDivisi[$param['notransaksi']] == 'PROJECT'){
		$optCapex = makeOption($dbname,'project','kode,kodecapex',"kode='".$row['kodeblok']."'");
		$optProject = makeOption($dbname,'project','kode,nama',"kode='".$row['kodeblok']."'");
		$kodecapex = $optCapex[$row['kodeblok']];
		
		if($kodecapex==''){
			$optKegCapex = makeOption($dbname,'project_dt','kegiatan,namakegiatan',"kegiatan='".$row['kodekegiatan']."'");
		}
		else{
			$optKegCapex = makeOption($dbname,'spl_capexbangunandt','kegiatan,namakegiatan',"kegiatan='".$row['kodekegiatan']."'");
		}
		@$dataShow[$key]['kodeblok'] = $optProject[$row['kodeblok']];
		@$dataShow[$key]['kodekegiatan'] = $optKegCapex[$row['kodekegiatan']];
	}else if($optDivisi[$param['notransaksi']] == 'S'){
		@$dataShow[$key]['kodeblok'] = $optPerumahan[$row['kodeblok']];
		@$dataShow[$key]['kodekegiatan'] = $optKegPerumahan[$row['kodekegiatan']];
	}else{
		@$dataShow[$key]['kodeblok'] = $optOrg[$row['kodeblok']];
		@$dataShow[$key]['kodekegiatan'] = $optKeg[$row['kodekegiatan']];
	}
}

$title = $_SESSION['lang']['spk'];
$titleDetail = array('Detail');

/** Output Format **/
switch($proses) {
    case 'pdf':
		ob_start();
        $pdf=new zPdfMaster('L','pt','A4');
        $pdf->_noThead=true;
        $pdf->setAttr1($title,$align,$length,array());
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
	$pdf->AddPage();
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell($width,$height,$_SESSION['lang']['notransaksi']." : ".
            $param['notransaksi'],0,1,'L',1);
        $pdf->Cell($width,$height,$_SESSION['lang']['kodeorg']." : ".
            $optOrg[$param['kodeorg']],0,1,'L',1);
	$pdf->Cell($width,$height,$_SESSION['lang']['koderekanan']." : ".
            $optSupp[$param['koderekanan']],0,1,'L',1);
        $pdf->Ln();
        
        # Header
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell($width,$height,$titleDetail[0],0,1,'L',1);
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
        }
        $pdf->Ln();
        
		# Print Out
        if($urlefil=='0'){
			$pdf->Output();
		}else{
			$pdf->Output($urlefil);
		}
        break;
		ob_end_flush();
    case 'excel':
        break;
    default:
    break;
}
?>