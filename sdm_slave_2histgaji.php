<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/biReport.php');
include_once('lib/zPdfMaster.php');
include_once('lib/terbilang.php');

if(isset($_GET['mode'])) {
    $mode = $_GET['mode'];
} else {
    $mode = 'preview';
}
if($mode=='pdf') {
    $param = $_GET;
    unset($param['mode']);
    unset($param['level']);
} else {
    $param = $_POST;
}

// Kode Organisasi
if(!isset($param['kodeorg'])) $param['kodeorg']=$_SESSION['empl']['lokasitugas'];
/*
# Validasi Periode
$periode1 = $param['periode_from'];
$periode2 = $param['periode_until'];
#1. Empty
if($periode1=='' or $periode2=='') {
    echo 'Warning : Transaction period required';
    exit;
}
#2. Range Terbalik
if(tanggalsystem($periode1)>tanggalsystem($periode2)) {
    $tmp = $periode1;
    $periode1 = $periode2;
    $periode2 = $tmp;
}
*/
// Get Data
$qData = "SELECT
	a.updatetime,c.namakaryawan as updatedby,b.namakaryawan,
	a.tahun,a.idkomponen,a.jumlahlalu,a.jumlah
	FROM ".$dbname.".hist_gajikaryawan a LEFT JOIN
	".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid LEFT JOIN 
	".$dbname.".datakaryawan c on a.updateby=c.karyawanid
	where  b.namakaryawan like '%".$param['karyawan'].
	"%' and tahun='".$param['tahun']."'";
// echo $qData;
if(!empty($param['komponen'])) $qData .= " and idkomponen = '".$param['komponen']."'";
$data = fetchData($qData);

$optCmp = makeOption($dbname,'sdm_ho_component','id,name',"type='basic'",'0',true);

// Rearrange Data
$dataShow = $data;
foreach($data as $key=>$row) {
	$dataShow[$key]['idkomponen'] = $optCmp[$row['idkomponen']];
	$dataShow[$key]['jumlahlalu'] = number_format($row['jumlahlalu'],0);
	$dataShow[$key]['jumlah'] = number_format($row['jumlah'],0);
}
$dataExcel = $data;

# Report Gen
$theCols = array(
	$_SESSION['lang']['tanggalupdate'],
	$_SESSION['lang']['updateby'],
	$_SESSION['lang']['namakaryawan'],
	$_SESSION['lang']['tahun'],
	$_SESSION['lang']['idkomponen'],
	$_SESSION['lang']['jumlahlalu'],
	$_SESSION['lang']['jumlah'],
);
$align = explode(",","C,C,L,C,C,R,R");

switch($mode) {
    case 'pdf':
        /** Report Prep **/
		$title = $_SESSION['lang']['histgaji'];
        $length = explode(",","17,17,17,10,17,12,10");
        
        $pdf = new zPdfMaster('P','pt','A4');
        $pdf->setAttr1($title,$align,$length,$theCols);
		$pdf->_finReport = true;
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
		$pdf->AddPage();
        
        $pdf->SetFillColor(255,255,255);
		
		# Content
		$pdf->SetFont('Arial','',9);
        foreach($dataShow as $key=>$row) {
			$i=0;
            foreach($row as $head=>$cont) {
				if($head=='tanggal') $cont = tanggalnormal($cont);
				$pdf->Cell($length[$i]/100*$width,$height,$cont,'LBR',0,$align[$i],1);
                $i++;
            }
			$pdf->Ln();
        }
        $pdf->Output();
        break;
	
    default:
		# Redefine Align
		$alignPrev = array();
		foreach($align as $key=>$row) {
			switch($row) {
			case 'L':
				$alignPrev[$key] = 'left';
				break;
			case 'R':
				$alignPrev[$key] = 'right';
				break;
			case 'C':
				$alignPrev[$key] = 'center';
				break;
			default:
			}
		}
		
		/** Mode Header **/
        if($mode=='excel') {
            // $tab = strtoupper($_SESSION['lang']['histgaji'])."<br>".
				 // strtoupper($_SESSION['lang']['periode'])." : ".$periode1." s/d ".$periode2.
			$tab = "<table border='1'>";
            $tab .= "<thead style=\"background-color:#222222\"><tr class='rowheader'>";
        } else {
            $tab = "<table id='periksabuah' class='sortable' border=0 cellspacing=1>";
            $tab .= "<thead><tr class='rowheader'>";
        }
	
		/** Generate Table **/
        foreach($theCols as $head) {
            $tab .= "<td>".$head."</td>";
        }
        $tab .= "</tr></thead>";
        $tab .= "<tbody>";
        
		# Content
		foreach($data as $key=>$row) {
            $tab .= "<tr class='rowcontent'>";
			$i=0;
			foreach($row as $head=>$cont) {
			if($mode=='excel') {
				$tab .= "<td align='".$alignPrev[$i]."'>".$dataExcel[$key][$head]."</td>";
			} else {
				$tab .= "<td align='".$alignPrev[$i]."'>".$dataShow[$key][$head]."</td>";
			}
			$i++;
			}
			$tab .= "</tr>";
        }
        
	    /** Output Type **/
        if($mode=='excel') {
            $stream = $tab;
            $nop_="HistGaji_";
            if(strlen($stream)>0) {
                # Delete if exist
                if ($handle = opendir('tempExcel')) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && $file != "index.html") {
                            @unlink('tempExcel/'.$file);
                        }
                    }	
                    closedir($handle);
                }
                
                # Write to File
                $handle=fopen("tempExcel/".$nop_.".xls",'w');
                if(!fwrite($handle,$stream)) {
                    echo "Error : Tidak bisa menulis ke format excel";
                    exit;
                } else {
                    echo $nop_;
                }
                fclose($handle);
            }
        } else {
            echo $tab;
        }
        break;
}