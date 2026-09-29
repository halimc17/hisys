<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');
include_once('lib/zMysql.php');

$pt=checkPostGet('pt','');
$periode=checkPostGet('periode','');
if($periode==''){ exit('Pilih periode terlebih dahulu'); }
#filter sama dengan list SPB
$txtSearch=isset($_GET['txtSearch']) ? $_GET['txtSearch'] : '';
$referensisearch=isset($_GET['referensisearch']) ? $_GET['referensisearch'] : '';
$txtDiv=isset($_GET['txtDiv']) ? $_GET['txtDiv'] : '';
$txtTglAsli=isset($_GET['txtTgl']) ? $_GET['txtTgl'] : '';
$txtTgl=($txtTglAsli!='') ? tanggalsystem($txtTglAsli) : '';
$status_spb=isset($_GET['status_spb']) ? $_GET['status_spb'] : '';
$postsch=isset($_GET['postsch']) ? $_GET['postsch'] : '';
$wherepilih=" a.kodeorg IN (".getOrgDetail(2).") ";
$infofilter='';
if($pt!=''){
	$wherepilih.=" and a.kodeorg='".$pt."' ";
}
if($txtSearch!=''){
	$wherepilih.=" and a.nospb like '%".$txtSearch."%' ";
	$infofilter.=" | No SPB: ".$txtSearch;
}
if($referensisearch!=''){
	$wherepilih.=" and a.noreferensi='".$referensisearch."' ";
	$infofilter.=" | No Referensi: ".$referensisearch;
}
if($txtDiv!=''){
	$wherepilih.=" and a.nospb in (select nospb from ".$dbname.".kebun_spbdt where blok like '%".$txtDiv."%') ";
	$infofilter.=" | Divisi: ".$txtDiv;
}
if($txtTgl!=''){
	$wherepilih.=" and a.tanggal='".$txtTgl."' ";
	$infofilter.=" | Tanggal: ".$txtTglAsli;
}
if($status_spb!=''){
	$wherepilih.=" and a.tujuan='".$status_spb."' ";
	$arrstatus=array('0'=>'Internal','1'=>'Alfiasi','3'=>'Eksternal','4'=>'TPH Besar');
	$infofilter.=" | Status: ".(isset($arrstatus[$status_spb]) ? $arrstatus[$status_spb] : $status_spb);
}
if($postsch=='1'){
	$wherepilih.=" and a.posting='1' ";
	$infofilter.=" | Status Posting: Posted";
}elseif($postsch=='0'){
	$wherepilih.=" and a.posting<>'1' ";
	$infofilter.=" | Status Posting: Belum Posting";
}

#kop: logo PT unit yang dipilih, nama unit, dan waktu cetak
$ptkode=getindukPT(($pt!='') ? substr($pt,0,4) : $_SESSION['empl']['lokasitugas']);
$hdpt=setheadreport($ptkode,$ptkode);
$qnama=fetchData(selectQuery($dbname,'organisasi','namaorganisasi',"kodeorganisasi='".$pt."'"));
$namaunit=($pt!='') ? $pt.' - '.$qnama[0]['namaorganisasi'] : 'Seluruhnya';
$waktucetak=date('d-m-Y H:i:s');
//$pt=substr($pt,0,4);
//$periode=substr($_GET['periode'],0,7);
//ambil namapt
//=================================================

class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
				global $pt;
				global $periode;
				global $hdpt;
				global $namaunit;
				global $infofilter;
				
                
				$noSpb=checkPostGet('column','');
				$nospb=substr($noSpb,8,6);
                
                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 12;
                if(file_exists($hdpt['logo'])){
                    $this->Image($hdpt['logo'],$this->lMargin,$this->tMargin,42);
                }
                $this->SetFont('Arial','B',10);
                $this->SetXY($this->lMargin+50,$this->tMargin+6);
                $this->Cell(400,12,$hdpt['nama'],0,1,'L');
                $this->SetY($this->tMargin+40);
                $this->SetFont('Arial','B',11);
                $this->Cell($width,$height,strtoupper($_SESSION['lang']['listSpb']),0,1,'C');
                $this->SetFont('Arial','',8);
                $this->Cell($width,$height,$_SESSION['lang']['unit'].': '.$namaunit.' | '.$_SESSION['lang']['periode'].': '.$periode.$infofilter,0,1,'C');
                $this->Ln(2);

                $this->SetFont('Arial','B',9);	
                $this->SetFillColor(220,220,220);
			   // $this->Cell(10/100*$width,$height,'No',1,0,'C',1);
                /*foreach($colArr as $key=>$head) {
                    $this->Cell($length[$key]/100*$width,$height,$_SESSION['lang'][$head],1,0,'C',1);
                }*/
				$this->Cell(3/100*$width,$height,'No',1,0,'C',1);
				$this->Cell(15/100*$width,$height,$_SESSION['lang']['nospb'],1,0,'C',1);	
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);	
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['blok'],1,0,'C',1);	
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['janjang'],1,0,'C',1);
                                $this->Cell(10/100*$width,$height,$_SESSION['lang']['kgwb'],1,0,'C',1);
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['bjr'],1,0,'C',1);
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['brondolan'],1,0,'C',1);		
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['mentah'],1,0,'C',1);
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['busuk'],1,0,'C',1);
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['matang'],1,0,'C',1);
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['lewatmatang'],1,1,'C',1);
            
            }
                
            function Footer()
            {
                global $waktucetak;
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(0,10,'Dicetak: '.$waktucetak.' oleh '.$_SESSION['empl']['name'].'     Halaman '.$this->PageNo().' / {nb}',0,0,'L');
            }
        }
        $pdf=new PDF('L','pt','A4');
        $pdf->AliasNbPages();
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
		$pdf->AddPage();
		$pdf->SetFillColor(255,255,255);
		$pdf->SetFont('Arial','',9);
		
		$str="select a.tanggal,b.* from ".$dbname.".kebun_spbht a inner join ".$dbname.".kebun_spbdt b on a.nospb=b.nospb 
		where a.tanggal like '".$periode."-%' and ".$wherepilih." order by a.tanggal asc "; 
		$re=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$re->setFetchMode(PDO::FETCH_ASSOC);
		if($re)
		{
			$no=0;
			while($res=$re->fetch())
			{
				$no+=1;
				
				$pdf->Cell(3/100*$width,$height,$no,1,0,'L',1);
				$pdf->Cell(15/100*$width,$height,$res['nospb'],1,0,'L',1);	
				$pdf->Cell(8/100*$width,$height,tanggalnormal($res['tanggal']),1,0,'C',1);	
				$pdf->Cell(8/100*$width,$height,$res['blok'],1,0,'L',1);	
				$pdf->Cell(8/100*$width,$height,number_format($res['jjg'],2),1,0,'L',1);
                                $pdf->Cell(10/100*$width,$height,number_format($res['kgwb'],2),1,0,'L',1);
				$pdf->Cell(8/100*$width,$height,number_format($res['bjr'],2),1,0,'L',1);
				$pdf->Cell(10/100*$width,$height,number_format($res['brondolan'],2),1,0,'L',1);		
				$pdf->Cell(8/100*$width,$height,number_format($res['mentah'],2),1,0,'L',1);
				$pdf->Cell(8/100*$width,$height,number_format($res['busuk'],2),1,0,'L',1);
				$pdf->Cell(8/100*$width,$height,number_format($res['matang'],2),1,0,'L',1);
				$pdf->Cell(8/100*$width,$height,number_format($res['lewatmatang'],2),1,1,'L',1);	
			   
			}
		}
		else
		{
			$pdf->Cell(102/100*$width,$height,$_SESSION['lang']['datanotfound'],1,1,'C',1);	
		}
	
        $pdf->Output();
?>