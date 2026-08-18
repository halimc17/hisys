<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/biReport.php');
include_once('lib/zPdfMaster.php');
include_once('lib/terbilang.php');


if(isset($_GET['mode'])) {
    $mode = $_GET['mode'];
	$mode  = checkPostGet('proses','');
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
$param = $_POST;
if(count($param)==0){$param = $_GET;}

// Kode Organisasi
if(!isset($param['kodeorg'])) $param['kodeorg']=$_SESSION['empl']['lokasitugas'];

# Validasi Periode
if($param['unit']=='') {
    echo 'Warning : Pilih unit terlebih dahulu';
    exit;
}
if($param['periode']=='') {
    echo 'Warning : Pilih periode terlebih dahulu';
    exit;
}

// Get Data
$qData = "SELECT
	b.nik,b.namakaryawan,a.updatetime,a.data, a.updateby
	FROM ".$dbname.".hist_datakaryawan a LEFT JOIN
	".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid LEFT JOIN 
	".$dbname.".datakaryawan c on a.updateby=c.karyawanid
	where a.updatetime like '%".$param['periode']."%' and b.lokasitugas like '%".$param['unit']."%' order by b.namakaryawan asc";

if(!empty($param['jabatan'])) $qData .= " and a.kodejabatan = '".$param['jabatan']."'";
$data = fetchData($qData);

$optJabatan = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',null,'0',true);

// Rearrange Data
foreach($data as $key=>$row) {
	$data[$key]['data'] = json_decode($row['data'],1);
}


$dataShow = $data;
$dataExcel = $data;


// COnvert dari kode ke nama
$akad=array('0'=>'Non Akad','1'=>'Akad','2'=>'Non Akad');		
$tk=makeOption($dbname,'sdm_5tipekaryawan','id,tipe');
$org=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$jab=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
$bag=makeOption($dbname,'sdm_5departemen','kode,nama');

# Report Gen
$theCols = array(
	'No',
	'Employee code',
	$_SESSION['lang']['namakaryawan'],
	'Tanggal perubahaan<br>Oleh ?',
	$_SESSION['lang']['data'],
);

$theCols_PDF = array(
	'Employee code',
	$_SESSION['lang']['namakaryawan'],
	'Tanggal perubahaan',
	$_SESSION['lang']['data'],
	'Data Sebelum',
	'Data Sesudah',
);


$align = explode(",","L,L,L,L");


switch($mode) {
    case 'pdf':
        /** Report Prep **/
		$title = 'Laporan ' . $_SESSION['lang']['histkaryawan'];
        $length = explode(",","12,15,15,10,25,25");
        
        $pdf = new zPdfMaster('L','pt','A4');
        $pdf->setAttr1($title,$align,$length,$theCols_PDF);
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
				if($head!='data') {
					$pdf->Cell($length[$i]/100*$width,$height*count($row['data']),$cont,'LBR',0,$align[$i],1);
				} else {
					$tmpX = $pdf->GetX();
					foreach($row['data'] as $k=>$r) {
						$pdf->SetX($tmpX);
						$pdf->Cell(10/100*$width,$height,$k,'LBR',0,'L');
						
						if($k=='tipekaryawan'){
							$pdf->Cell(25/100*$width,$height,$tk[$r['old']],'LBR',0,'L');
							$pdf->Cell(25/100*$width,$height,$tk[$r['new']],'LBR',0,'L');
						}else if($k=='subbagian'){
							$pdf->Cell(25/100*$width,$height,$org[$r['old']],'LBR',0,'L');
							$pdf->Cell(25/100*$width,$height,$org[$r['new']],'LBR',0,'L');
						}else if($k=='statusakad'){
							$pdf->Cell(25/100*$width,$height,$akad[$r['old']],'LBR',0,'L');
							$pdf->Cell(25/100*$width,$height,$akad[$r['new']],'LBR',0,'L');
						}else if($k=='kodejabatan'){
							$pdf->Cell(25/100*$width,$height,$jab[$r['old']],'LBR',0,'L');
							$pdf->Cell(25/100*$width,$height,$jab[$r['new']],'LBR',0,'L');
						}else if($k=='bagian'){
							$pdf->Cell(25/100*$width,$height,$bag[$r['old']],'LBR',0,'L');
							$pdf->Cell(25/100*$width,$height,$bag[$r['new']],'LBR',0,'L');
							
						}else{
						
						$pdf->Cell(25/100*$width,$height,$r['old'],'LBR',0,'L');
						$pdf->Cell(25/100*$width,$height,$r['new'],'LBR',0,'L');
						}
						$pdf->Ln();
					}
				}
                $i++;
            }
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
            $tab = strtoupper($_SESSION['lang']['histkaryawan']).
            "<table border='1' cellspacing=1>";
            $tab .= "<thead style=\"background-color:#222222\"><tr class='rowheader'>";
        } else {
            $tab = "<table id='periksabuah' cellspacing=1 cellpadding=5 border=0  class='sortable'>";
            $tab .= "<thead><tr class='rowheader'>";
        }
	
		/** Generate Table **/
        foreach($theCols as $key=>$head) {
			if($key==4) {
				$tab .= "<th style='text-align:center' colspan=3>Riwayat ".$head."</th>";
				$tab .= "<tr class='rowheader'>";
				$tab .= "<th style='text-align:center'>Data</th>";
				$tab .= "<th style='text-align:center'>Sebelum</th>";
				$tab .= "<th style='text-align:center'>Sesudah</th>";
				$tab .= "</tr>";
			} else {
				$tab .= "<th style='text-align:center' rowspan=2>".$head."</th>";
			}
        }
        $tab .= "</tr></thead>";
        $tab .= "<tbody>";
		# Content
        // this is
        $no=0;
		foreach($data as $key=>$row) {
            $no+=1;
            $tab .= "<tr class='rowcontent'>";
			$i=0;
			$tab .= "<td valign=top rowspan='".count($row['data'])."' align='".$alignPrev[1]."'>".$no."</td>";
			$tab .= "<td valign=top rowspan='".count($row['data'])."' align='".$alignPrev[1]."'>".$row['nik']."</td>";
			$tab .= "<td valign=top rowspan='".count($row['data'])."' align='".$alignPrev[2]."'>".$row['namakaryawan']."</td>";
			$tab .= "<td valign=top rowspan='".count($row['data'])."' align='".$alignPrev[0]."'>".$row['updatetime']."<br>".getKary($row['updateby'])."</td>";
			$i=0;
			foreach($row['data'] as $k=>$r) {
				if($i>0) $tab .= "<tr class=rowcontent>";
				$tab .= "<td align='left'>".$k."</td>";
				if($k=='tipekaryawan'){
					$tab .= "<td align='left'>".$tk[$r['old']]."";
					$tab .= "<td align='left'>".$tk[$r['new']]."</td></tr>";
				}else if($k=='subbagian'){
					$tab .= "<td align='left'>".$org[$r['old']]."";
					$tab .= "<td align='left'>".$org[$r['new']]."</td></tr>";
				}else if($k=='statusakad'){
					$tab .= "<td align='left'>".$akad[$r['old']]."";
					$tab .= "<td align='left'>".$akad[$r['new']]."</td></tr>";
				}else if($k=='kodejabatan'){
					$tab .= "<td align='left'>".$jab[$r['old']]."";
					$tab .= "<td align='left'>".$jab[$r['new']]."</td></tr>";
				}else if($k=='bagian'){
					$tab .= "<td align='left'>".$bag[$r['old']]."";
					$tab .= "<td align='left'>".$bag[$r['new']]."</td></tr>";
					
				}else{
					$tab .= "<td align='left'>".$r['old']."";
					$tab .= "<td align='left'>".$r['new']."</td></tr>";
				}
				
				
				$i++;
			}
        }
        if($no==0){
            $tab.="<tr>
                    <td colspan=7 align=center>Data tidak ditemukan.</td>
               </tr>";
            }
        
	    /** Output Type **/
        if($mode=='excel') {
            $stream = $tab;
            $nop_="LaporanRiwayatPerubahaanDataKaryawan";
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