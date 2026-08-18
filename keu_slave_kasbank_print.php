<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');

$proses = $_GET['proses'];
$param = $_POST;


/** Report Prep **/
$str=$owlPDO->query("select periode, tanggalmulai, tanggalsampai from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_SESSION['empl']['lokasitugas']."' and tutupbuku = '0' order by periode desc");
$str->setFetchMode(PDO::FETCH_ASSOC);
while($res=$str->fetch()){
    #$periodeaktif=$res['periode'];
    $periodemulai=$res['tanggalmulai'];
    #$periodesampai=$res['tanggalsampai'];
}

$str=$owlPDO->query("select periode, tanggalmulai, tanggalsampai from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_SESSION['empl']['lokasitugas']."' and tutupbuku = '0'  order by periode asc");
$str->setFetchMode(PDO::FETCH_ASSOC);
while($res=$str->fetch()){
    $periodeaktif=$res['periode'];
    #$periodemulai=$res['tanggalmulai'];
    $periodesampai=$res['tanggalsampai'];
}

$where = "kodeorg='".$_SESSION['empl']['lokasitugas']."' and tanggal >= '".$periodemulai."' and tanggal <= '".$periodesampai."'";
$cols = 'notransaksi,tanggal,noakun,tipetransaksi,jumlah,posting,keterangan';

$colArr = explode(',',$cols);
$query = selectQuery($dbname,'keu_kasbankht',$cols,$where,'tanggal desc, notransaksi desc');
$data = fetchData($query);
// echo $query;
$title = "Cash / Bank Tansaction";
$align = explode(",","L,L,C,C,R,C,L");
$length = explode(",","22,10,11,13,10,7,30");

/** Output Format **/
switch($proses) {
    case 'pdf':
        $pdf=new zPdfMaster('P','pt','A4');
        $pdf->setAttr1($title,$align,$length,$colArr);
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
        $pdf->AddPage();

        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',9);
        foreach($data as $key=>$row) {    
            $i=0;
            foreach($row as $cont) {
				if($i==0){
					$awalX = $pdf->GetX();
					$awalY = $pdf->GetY();
					$pdf->SetX(1000);
					$pdf->MultiCell($length[6]/100*$width,$height,$row['keterangan'],1,$align[6],1);
					$akhirY = $pdf->GetY();
					$resultY = $akhirY - $awalY;
				}
				
				$pdf->SetY($awalY);
				if($i == 6){
					$pdf->SetX($awalX2);
					$pdf->MultiCell($length[$i]/100*$width,$height,$cont,1,$align[$i],1);					
				}else if($i==0){
					$pdf->SetX($awalX);
					$pdf->Cell($length[$i]/100*$width,$resultY,$cont,1,0,$align[$i]);
					$awalX2=$pdf->GetX();
				}else{
					$pdf->SetX($awalX2);
					$pdf->Cell($length[$i]/100*$width,$resultY,$cont,1,0,$align[$i]);
					$awalX2=$pdf->GetX();
				}
                $i++;
            }
			if(($awalY+$resultY) > 700){
				$pdf->AddPage();
			}
        }

        $pdf->Output();
        break;
    case 'excel':
                $tab = strtoupper($_SESSION['lang']['kasbank'])."<br>".
                        strtoupper($_SESSION['lang']['tanggal'])." : ".$periodemulai." s/d ".$periodesampai.
                        "<table border='1'>";
                $tab .= "<thead style=\"background-color:#EEE\">";
                $tab .= "<tr class=rowheader>";
                $tab .= "<td>".$_SESSION['lang']['notransaksi']."</td>";
                $tab .= "<td>".$_SESSION['lang']['tanggal']."</td>";
                $tab .= "<td>".$_SESSION['lang']['noakun']."</td>";
                $tab .= "<td>".$_SESSION['lang']['tipetransaksi']."</td>";
                $tab .= "<td>".$_SESSION['lang']['jumlah']."</td>";
                $tab .= "<td>".$_SESSION['lang']['posting']."</td>";
                $tab .= "<td>".$_SESSION['lang']['keterangan']."</td>";
                $tab .= "</tr></thead><tbody>";
                foreach($data as $key=>$row) {    
            $tab .= "<tr>";
            foreach($row as $cont) {
                $tab .= "<td>".$cont."</td>";
            }
            $tab .= "</tr>";
        }
                $tab .= "</tbody></table>";

                header("Cache-control: must-revalidate");
                header("Pragma: must-revalidate");
                header("Content-type: application/vnd.ms-excel");
                header("Content-disposition: attachment; filename=KasBank_".$_SESSION['empl']['lokasitugas']."_".$periodeaktif.".xls");
                echo $tab;
                break;
    default:
    break;
}
?>