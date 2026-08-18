<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');

$proses = $_GET['proses'];
$param = $_GET;
$urlefil=checkPostGet('urlefil','0');

/** Report Prep **/
$cols = array();

#====================== Rencana =========================
$col1 = "kodeblok,kodekegiatan,hk,hasilkerjajumlah,satuan,jumlahrp";
$cols = explode(',',$col1);
$cols[0] = 'subunit';
$where = "notransaksi='".$param['notransaksi']."'";
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
$whereKeg2 = "kegiatan in (";
foreach($data as $key=>$row) {
    if($key==0) {
        $whereOrg .= "'".$row['kodeblok']."'";
        $whereKeg .= "'".$row['kodekegiatan']."'";
		$whereKeg2 .= "'".$row['kodekegiatan']."'";
    } else {
        $whereOrg .= ",'".$row['kodeblok']."'";
        $whereKeg .= ",'".$row['kodekegiatan']."'";
		$whereKeg2 .= ",'".$row['kodekegiatan']."'";
    }
}
$whereOrg .= ",'".$param['kodeorg']."')";
$whereKeg .= ")";
$whereKeg2 .= ")";

$optKebun = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
					   "kodeorganisasi='".$param['kodeorg']."'",'0',true);
if(empty($param['divisi']) or $param['divisi']=='PROJECT') {
	$str="select * from ".$dbname.".log_spkht where notransaksi='".$param['notransaksi']."'";
	$res=fetchData($str);
	$nopengajuan = $res[0]['nopengajuan'];
	
	$str="select * from ".$dbname.".lgl_pengajuanspkht where notransaksi='".$nopengajuan."'";
	$res=fetchData($str);
	$pengajuandivisi = $res[0]['divisi'];
	
	$optCapex = makeOption($dbname,'project','kode,kodecapex',"kode='".$pengajuandivisi."'");
	// $optProject = makeOption($dbname,'project','kode,nama',"kode='".$row['kodeblok']."'");
	$kodecapex = $optCapex[$pengajuandivisi];
	
	if($kodecapex==''){
		$optKeg = makeOption($dbname,'project_dt','kegiatan,namakegiatan',"kodeproject='".$pengajuandivisi."'");
	}
	else{
		$optKeg = makeOption($dbname,'spl_capexbangunandt','kegiatan,namakegiatan',"kodeproject='".$kodecapex."'");
	}

	$optOrg = makeOption($dbname,'project','kode,nama',"kodeorg='".
						 $param['kodeorg']."' and posting=0");
	// $optKeg = makeOption($dbname,'project_dt','kegiatan,namakegiatan',
		// $whereKeg2,'0',true);
} else if($param['divisi'] == 'S'){
	$optRegOrg = makeOption($dbname,'bgt_regional_assignment','kodeunit,regional',"kodeunit='".$param['kodeorg']."'");
	$optOrg = makeOption($dbname,'sdm_perumahanht','norumah,keterangan',"kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional = '".$optRegOrg[$param['kodeorg']]."')");
	$optKeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kelompok = 'SPL'");
}else {
	$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
		$whereOrg,'0',true);
	$optKeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',
		$whereKeg,'0',true);
}
$optSupp = makeOption($dbname,'log_5supplier','supplierid,namasupplier',
    "supplierid='".$param['koderekanan']."'");

# Data Show
$dataShow = $data;
foreach($dataShow as $key=>$row) {
    $dataShow[$key]['kodeblok'] = $optOrg[$row['kodeblok']];
    $dataShow[$key]['kodekegiatan'] = $optKeg[$row['kodekegiatan']];
}

#====================== Realisasi =========================
$col2 = "tanggal,kodeblok,kodekegiatan,hkrealisasi,hasilkerjarealisasi,jumlahrealisasi";
$cols2 = explode(',',$col2);
$cols[1] = 'kegiatan';
$where2 = "notransaksi='".$param['notransaksi']."' and kodekegiatan in (";
foreach($data as $key=>$row) {
    if($key==0) {
        $where2 .= "'".$row['kodekegiatan']."'";
    } else {
        $where2 .= ",'".$row['kodekegiatan']."'";
    }
}
$where2 .= ")";
$query2 = selectQuery($dbname,'log_baspk',$col2,$where2);
$data2 = fetchData($query2);
$align2 = explode(",","L,L,L,R,R,R");
$length2 = explode(",","10,20,25,10,15,20");

if(empty($data2)) {
    echo "Data Realisasi belum ada";
    exit;
}

# Options
$whereOrg2 = "kodeorganisasi in (";
foreach($data2 as $key=>$row) {
    if($key==0) {
        $whereOrg2 .= "'".$row['kodeblok']."'";
    } else {
        $whereOrg2 .= ",'".$row['kodeblok']."'";
    }
}
$whereOrg2 .= ",'".$param['kodeorg']."')";
$optOrg2 = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
    $whereOrg2,'0',true);

# Data Show
$dataShow2 = $data2;
foreach($dataShow2 as $key=>$row) {
	if(isset($optOrg2[$row['kodeblok']])) {
		$dataShow2[$key]['kodeblok'] = $optOrg2[$row['kodeblok']];
	} else {
		$dataShow2[$key]['kodeblok'] = $optOrg[$row['kodeblok']];
	}
	$dataShow2[$key]['kodekegiatan'] = $optKeg[$row['kodekegiatan']];
}

$title = $_SESSION['lang']['spk'];
$titleDetail = array('');

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
        $pdf->Cell($width,$height,$_SESSION['lang']['notransaksi']." : ".
            $param['notransaksi'],0,1,'L',1);
        $pdf->Cell($width,$height,$_SESSION['lang']['kodeorg']." : ".
            $optKebun[$param['kodeorg']],0,1,'L',1);
        $pdf->Cell($width,$height,$_SESSION['lang']['koderekanan']." : ".
            $optSupp[$param['koderekanan']],0,1,'L',1);
        $pdf->Ln();
        
        #====================== Rencana =========================
        # Header
        $pdf->Cell($width,$height,'Rencana',0,0,'L',1);
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
            foreach($row as $attr=>$cont) {
				if($attr=='jumlahrp') $cont = number_format($cont,2);
                $pdf->Cell($length[$i]/100*$width,$height,$cont,1,0,$align[$i],1);
                $i++;
            }
            $pdf->Ln();
        }
        $pdf->Ln();
        
        #====================== Realisasi =========================
        # Header
        $pdf->Cell($width,$height,'Realisasi',0,0,'L');
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell($width,$height,$titleDetail[0],0,1,'L',1);
        $pdf->SetFillColor(220,220,220);
        $i=0;
        foreach($cols2 as $column) {
            $pdf->Cell($length2[$i]/100*$width,$height,$_SESSION['lang'][$column],1,0,'C',1);
            $i++;
        }
        $pdf->Ln();
        
        # Content
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',9);
        foreach($dataShow2 as $key=>$row) {    
            $i=0;
            foreach($row as $attr=>$cont) {
				if($attr=='jumlahrealisasi') $cont = number_format($cont,2);
                $pdf->Cell($length2[$i]/100*$width,$height,$cont,1,0,$align2[$i],1);
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
    case 'excel':
        break;
    default:
    break;
}
?>