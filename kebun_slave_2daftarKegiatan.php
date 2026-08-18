<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=$_POST['proses'];

$nmbarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$satuanbarang=makeOption($dbname,'log_5masterbarang','kodebarang,satuan');

//echo $daftargudang.____.$tgl1;
if($proses=='excel') {
    $stream="<table class=sortable cellspacing=1 border=1>";
} else { 
    $stream="<table id=mytable class=sortable cellspacing=1 cellpadding=5 style=width:100%>";
}

$stream .= "<thead class=rowheader>
                <tr bgcolor=#CCCCCC class=rowheader>
                    <th align=center>".$_SESSION['lang']['kodeorganisasi']."</th>
                    <th align=center>".$_SESSION['lang']['kodekegiatan']."</th>
                    <th align=center>".$_SESSION['lang']['namakegiatan']."</th>
                    <th align=center>".$_SESSION['lang']['kelompokkegiatan']."</th>
                    <th align=center>".$_SESSION['lang']['satuan']."</th>
                    <th align=center>".$_SESSION['lang']['noakun']."</th>
                    <th align=center>".$_SESSION['lang']['namaakun']."</th>
                    <th align=center>".$_SESSION['lang']['status']."</th>
                    <th align=center>".$_SESSION['lang']['premi']."</th>
                </tr></thead>";
$spremi=array('0'=>'Premi di BKM tidak dikunci','1'=>'Premi di BKM dikunci');
$status=array('0'=>'Non Aktif','1'=>'Aktif');
$sql = "SELECT * FROM ".$dbname.".setup_kegiatan";				
$res = $owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar = $res->fetch()) {
    $no+=1;
    $stream .= "<tr class=rowcontent>
                <td>".getNamaOrg($bar['kodeorg'])."</td>
                <td>".$bar['kodekegiatan']."</td>";
     
    if($_SESSION['language']=='EN')
        $stream .= "<td>".$bar['namakegiatan1']."</td>";
    else
        $stream .= "<td>".$bar['namakegiatan']."</td>";
    
    $stream .= "<td>".$bar['kelompok']."</td>
                <td>".$bar['satuan']."</td>
                <td>".$bar['noakun']."</td>
                <td>".getNamaAkun($bar['noakun'])."</td>
                <td>".$status[$bar['status']]."</td>
                <td>".$spremi[$bar['premi']]."</td>
                </tr>";
}

$stream .= "<tbody></table>";

switch($proses) {
    case'preview':
        echo $stream;
    break;

    case 'excel':
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg = date("Ymd");
        $nop_ = "Daftar_Kegiatan".$tglSkrg;
        
        if(strlen($stream)>0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                            @unlink('tempExcel/'.$file);
                    }
                }	
                closedir($handle);
            }

            $handle = fopen("tempExcel/".$nop_.".xls",'w');
            if (!fwrite($handle,$stream)) {
                echo "<script language=javascript1.2>
                parent.window.alert('Can't convert to excel format');
                </script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
                window.location='tempExcel/".$nop_.".xls';
                </script>";
            }
            fclose($handle);
        }     
    break;

    case'pdf':

        $table = "setup_kegiatan";
        $query = selectQuery($dbname,$table);
        $result = fetchData($query);
        $header = array();
        foreach($result[0] as $key=>$row) {
            $header[] = $key;
        }

        class masterpdf extends FPDF {
            function Header() {
                global $table;
                global $header;

                # Panjang, Lebar
                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 12;

                $this->SetFont('Arial','B',8);
                $this->Cell(20,$height,$_SESSION['org']['namaorganisasi'],'',1,'L');
                $this->SetFont('Arial','B',12);
                $this->Cell($width,$height,strtoupper($_SESSION['lang']['daftarkegiatan']),'',1,'C');
                $this->SetFont('Arial','B',8);
                $this->Cell(420,$height,' ','',0,'R');
                $this->Cell(38,$height,$_SESSION['lang']['tanggal'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(40,$height,date('d-m-Y H:i'),'',1,'L');
                $this->Cell(420,$height,' ','',0,'R');
                $this->Cell(38,$height,$_SESSION['lang']['page'],'',0,'L');
                $this->Cell(8,$height,':','',0,'L');
                $this->Cell(15,$height,$this->PageNo(),'',1,'L');

                $this->Cell(420,$height,' ','',0,'R');
                $this->Cell(38,$height,'User','',0,'L');
                $this->Cell(8,$height,':','',0,'L');
                $this->Cell(20,$height,$_SESSION['standard']['username'],'',1,'L');
                $this->Ln();

                $this->Cell(60,1.5*$height,$_SESSION['lang']['kodeorganisasi'],'TBLR',0,'C');
                $this->Cell(60,1.5*$height,$_SESSION['lang']['kodekegiatan'],'TBLR',0,'C');
                $this->Cell(260,1.5*$height,$_SESSION['lang']['namakegiatan'],'TBLR',0,'C');
                $this->Cell(40,1.5*$height,$_SESSION['lang']['kelompokkegiatan'],'TBLR',0,'C');
                $this->Cell(40,1.5*$height,$_SESSION['lang']['satuan'],'TBLR',0,'C');
                $this->Cell(40,1.5*$height,$_SESSION['lang']['noakun'],'TBLR',0,'C');        
                $this->Cell(40,1.5*$height,$_SESSION['lang']['status'],'TBLR',0,'C');        
                $this->Cell(40,1.5*$height,$_SESSION['lang']['premi'],'TBLR',0,'C');
                $this->Ln();
                $this->Ln();
            }
        }

        #====================== Prepare PDF Setting
        $pdf = new masterpdf('P','pt','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
        $pdf->SetFont('Arial','',8);
        $pdf->AddPage();

        foreach($result as $data) {
            $pdf->Cell(60,$height,$data['kodeorg'],'',0,'L');
            $pdf->Cell(60,$height,$data['kodekegiatan'],'',0,'L');

            if($_SESSION['language']=='EN') {
                $pdf->Cell(260,$height,$data['namakegiatan1'],'',0,'L');
            } else {
                $pdf->Cell(260,$height,$data['namakegiatan'],'',0,'L');
            }

            $pdf->Cell(40,$height,$data['kelompok'],'',0,'C');
            $pdf->Cell(40,$height,$data['satuan'],'',0,'C');
            $pdf->Cell(40,$height,$data['noakun'],'',0,'C');
            $pdf->Cell(40,$height,$data['status'],'',0,'C');        
            $pdf->Cell(40,$height,$data['premi'],'',0,'C');
            $pdf->Ln();
        }

        # Print Out
        // $pdf->Output();

    break;	

}
?>