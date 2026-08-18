<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/biReport.php');
include_once('lib/zPdfMaster.php');

$level = $_GET['level'];
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

// echo "<pre>";
// print_r($param);
// echo "</pre>";

foreach($param as $key=>$row) {
    if(substr($key,0,8)=='nojurnal') {
        $nojurnal = $row;
    }
}

$level = checkPostGet('level', '');
$mode = checkPostGet('mode', '');

# Level
switch($level) {
    case '0':
        break;
    case '1':
        # Data
        $query = "SELECT b.noreferensi,a.nojurnal,a.noakun,a.keterangan,a.jumlah,a.nodok 
            FROM ".$dbname.".keu_jurnaldt a  LEFT JOIN ".$dbname.".keu_jurnalht b on a.nojurnal=b.nojurnal
            WHERE a.nojurnal = '".$nojurnal."'";
        $data = fetchData($query);

        $idapv=$owlPDO->query("select notransaksi,karyawanid,tanggal from ".$dbname.".approval where notransaksi='".$data[0]['nojurnal']."' order by tanggal desc");
        $idapv->setFetchMode(PDO::FETCH_ASSOC);
        $ddapv= $idapv->fetch();
        $apv=$ddapv['karyawanid'];
        $tglap=tanggalnormald($ddapv['tanggal']);

        $optnm=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$apv."'");

        if($_SESSION['language']=='EN'){
            $kegiatan="SELECT noakun, namaakun1 as namaakun FROM ".$dbname.".keu_5akun";
        }else{
            $kegiatan="SELECT noakun, namaakun FROM ".$dbname.".keu_5akun";
        }
        $query=$owlPDO->query($kegiatan);
        $query->setFetchMode(PDO::FETCH_ASSOC);
        while($res=$query->fetch())
        {
            $kamusakun[$res['noakun']]=$res['namaakun'];
        }    
        # Total
        $total = array(
            'debet'=>0,
            'kredit'=>0
        );
        $noref = "";

        foreach($data as $row) {
            if($row['jumlah']<0) {
                $total['kredit'] += $row['jumlah']*(-1);
            } else {
                $total['debet'] += $row['jumlah'];
            }
                $noref = $row['noreferensi'];
        }
        break;
    default:
}

# Mode
switch($mode) {
    case 'pdf':
        // echo "<pre>";
        // print_r($mode);
        // echo "</pre>";
        /** Report Prep **/
        $colsNew = 'noakun,namaakun,keterangan,debet,kredit,nodok';
        $colPdf = explode(',',$colsNew);
        $title = $_SESSION['lang']['nojurnal'].": ".$nojurnal;
        $title .= " ".$_SESSION['lang']['noreferensi'].": ".$noref;
        $align = explode(",","L,L,L,R,R,L");
        $length = explode(",","7,25,38,8,8,13");

        $pdf = new zPdfMaster('L','pt','A4');
         $pdf->SetFont('Arial','',8);
        $pdf->setAttr1($title,$align,$length,$colPdf);
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
        $pdf->AddPage();

        $pdf->SetFillColor(255,255,255);

        foreach($data as $row) {
            $i=0;

                        $awalY=$pdf->GetY();
                        $awalX=$pdf->GetX();
                        $currentX=$pdf->GetX();
                        $pdf->SetY($awalY);
                        $pdf->SetX($currentX);
                        $pdf->MultiCell($length[0]/100*$width, $height, $row['noakun'], '0', $align[0]);

                        $pdf->SetY($awalY);
                        $pdf->SetX($currentX+($length[0]/100*$width));
                        $currentX=$pdf->GetX();

                        $pdf->MultiCell($length[1]/100*$width, $height, $kamusakun[$row['noakun']], '0', $align[1]);
                        $akhirYNoAkun=$pdf->GetY();

                        $pdf->SetY($awalY);
                        $pdf->SetX($currentX+($length[1]/100*$width));
                        $currentX=$pdf->GetX();			

                        $pdf->MultiCell($length[2]/100*$width, $height, $row['keterangan'], '0', $align[2]);
                        $akhirYKeterangan=$pdf->GetY();

                        $pdf->SetY($awalY);
                        $pdf->SetX($currentX+($length[2]/100*$width));
                        $currentX=$pdf->GetX();

                        if($row['jumlah']<0){
                                $pdf->MultiCell($length[3]/100*$width, $height, 0, '0', $align[3]);

                                $pdf->SetY($awalY);
                                $pdf->SetX($currentX+($length[3]/100*$width));
                                $currentX=$pdf->GetX();

                                $pdf->MultiCell($length[4]/100*$width, $height, number_format($row['jumlah']*(-1)), '0', $align[4]);

                                $pdf->SetY($awalY);
                                $pdf->SetX($currentX+($length[4]/100*$width));
                                $currentX=$pdf->GetX();
                        }else{
                                $pdf->MultiCell($length[3]/100*$width, $height, number_format($row['jumlah']), '0', $align[3]);

                                $pdf->SetY($awalY);
                                $pdf->SetX($currentX+($length[3]/100*$width));
                                $currentX=$pdf->GetX();

                                $pdf->MultiCell($length[4]/100*$width, $height, 0, '0', $align[4]);

                                $pdf->SetY($awalY);
                                $pdf->SetX($currentX+($length[4]/100*$width));
                                $currentX=$pdf->GetX();
                        }
                        $pdf->MultiCell($length[3]/100*$width, $height, substr($row['nodok'],0,32), '0', $align[5]);
                        $akhirYNoDokumen=$pdf->GetY();

                        $akhirY = max($akhirYNoAkun,$akhirYKeterangan,$akhirYNoDokumen);
                        $height2=$akhirY-$awalY;
                        // $pdf->SetY($awalY);

                                        $pdf->SetY($awalY);
                    $pdf->Cell($length[0]/100*$width,$height2,"",1,0,$align[0]);
                    $pdf->Cell($length[1]/100*$width,$height2,"",1,0,$align[1]);
                    $pdf->Cell($length[2]/100*$width,$height2,"",1,0,$align[2]);
                    if($row['jumlah']<0){
                        $pdf->Cell($length[3]/100*$width,$height2,"",1,0,$align[3]);
                        $pdf->Cell($length[4]/100*$width,$height2,"",1,0,$align[4]);                
                    }else{
                        $pdf->Cell($length[3]/100*$width,$height2,"",1,0,$align[3]);                                
                        $pdf->Cell($length[4]/100*$width,$height2,"",1,0,$align[4]);
                    }
                    $pdf->Cell($length[5]/100*$width,$height2,"",1,0,$align[5]);

            $pdf->Ln();
        }
        # Total
        $lenTotal = $length[0]+$length[1]+$length[2];
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell($lenTotal/100*$width,$height,'TOTAL',1,0,'C');
        $pdf->Cell($length[3]/100*$width,$height,number_format($total['debet']),1,0,'R');
        $pdf->Cell($length[4]/100*$width,$height,number_format($total['kredit']),1,0,'R');
        $pdf->Cell($length[5]/100*$width,$height,'',1,0,'R');
        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
        $pdf->Ln();

        #Region Tanda Tangan
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetFont('Arial','U','10');
        $pdf->Cell(12,5,$_SESSION['lang']['dibuat'],'',0,'L');
        $pdf->Cell(2,5,'','',0,'L');
        $pdf->Cell(120,5,'','',0,'L');	
        $pdf->Cell(18,5,$_SESSION['lang']['diverifikasi'],'',0,'L');
        $pdf->Cell(2,5,'','',0,'L');
        $pdf->Cell(35,5,'','',1,'L');
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetFont('Arial','','9');
        $pdf->Cell(12,10,'(                        )','',0,'L');
        $pdf->Cell(2,10,'','',0,'L');
        $pdf->Cell(135,10,'','',0,'L');  
        $pdf->Cell(18,10,$optnm[$apv],'',0,'C');
        $pdf->Cell(2,10,'','',0,'L');
        $pdf->Cell(35,10,'','',1,'L');
        $pdf->SetFont('Arial','','9');
        $pdf->Cell(12,10,'','',0,'L');
        $pdf->Cell(2,10,'','',0,'L');
        $pdf->Cell(135,10,'','',0,'L');	
        $pdf->Cell(18,10,$tglap,'',0,'C');
        $pdf->Cell(2,10,'','',0,'L');
        $pdf->Cell(35,10,'','',1,'L');
        $pdf->Ln();


        $apv=$ddapv['karyawanid'];
        $tglap=tanggalnormald($ddapv['tanggal']);

        $pdf->Output();
        break;
    default:
}
?>