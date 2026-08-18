<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

if(isset($_POST['proses'])){
	$proses=$_POST['proses'];
}else{
	$proses=$_GET['proses'];
}


$namaOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

//$arr2="##periodeId##unitId";
$periode = checkPostGet('periodeId','');
$unit = checkPostGet('unitId','');
$intiplasma = checkPostGet('intiplasma',''); 


$jenisbibit=makeOption($dbname,'setup_blok','kodeorg,jenisbibit');
$optThnTanam=makeOption($dbname, 'setup_blok', 'kodeorg,tahuntanam');

if($periode=='')    {
    exit("Error : Periode tidak boleh kosong");
}
$where="";
if($unit!=''){
    $where=" and kodeorg='".$unit."'";
}


if($intiplasma!=''){
    $inplas=" and intiplasma='".$intiplasma."'";
}

$brd='0';
$bgdt='';
$tab='';
if($proses=='excel'){
    $brd=1;
    $bgdt="bgcolor=#DEDEDE align=center";
    $tab.="<table cellspacing=1 cellpadding=1 border=0>";
    $tab.="<tr><td colspan=11 >".strtoupper($_SESSION['lang']['rProdKebundetail'])."</td></tr>";
    $tab.="<tr><td colspan=11>".$_SESSION['lang']['unit']." : ".$unit."</td></tr>";
    $tab.="<tr><td colspan=11>".$_SESSION['lang']['periode']." : ".$periode."</td></tr></table>";
}


$tab.="<table cellspacing=1 cellpadding=5 border='".$brd."' class=sortable>";
$tab.="<thead><tr class=rowheader>";
$tab.="<th align=center ".$bgdt.">".$_SESSION['lang']['nomor']."</th>";
$tab.="<th align=center ".$bgdt.">".$_SESSION['lang']['tanggal']."</th>";
$tab.="<th align=center ".$bgdt.">".$_SESSION['lang']['nospb']."</th>";
$tab.="<th align=center ".$bgdt.">".$_SESSION['lang']['nopol']."</th>";
$tab.="<th align=center ".$bgdt.">".$_SESSION['lang']['noTiket']."</th>";
$tab.="<th align=center ".$bgdt.">".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['panen']."</th>";
$tab.="<th align=center ".$bgdt.">Induk ".$_SESSION['lang']['blok']."</th>";
$tab.="<th align=center ".$bgdt.">".$_SESSION['lang']['kodeblok']."</th>";
$tab.="<th align=center ".$bgdt.">".$_SESSION['lang']['blok']."</th>";
$tab.="<th align=center ".$bgdt.">".$_SESSION['lang']['jenisbibit']."</th>";
$tab.="<th align=center style=width:50px ".$bgdt.">".$_SESSION['lang']['tahuntanam']."</th>";
// $tab.="<th align=center ".$bgdt.">".$_SESSION['lang']['luas']."</th>";
// $tab.="<th align=center ".$bgdt.">".$_SESSION['lang']['pokok']."</th>";
$tab.="<th align=center ".$bgdt.">".$_SESSION['lang']['brondolan']."</th>";
$tab.="<th align=center ".$bgdt.">".$_SESSION['lang']['jjg']."</th>";
$tab.="<th align=center ".$bgdt.">".$_SESSION['lang']['kgwb']." Netto</th>";
$tab.="<th align=center ".$bgdt.">".$_SESSION['lang']['kgwb']." Brutto</th>";
$tab.="<th align=center ".$bgdt.">".$_SESSION['lang']['bjr']." WB Netto</th>";
$tab.="<th align=center ".$bgdt.">".$_SESSION['lang']['bjr']." WB Brutto</th>";
$tab.="</tr><tbody>";
$tglTemp='';
$sData="select distinct * from ".$dbname.".kebun_spb_vw4 where tanggal like '%".$periode."%' ".$where." ".$inplas." order by tanggal,nospb,blok,tanggalpanen asc";
// echo $sData;
$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
$qData->setFetchMode(PDO::FETCH_ASSOC);
$rowDta=owlBaris($qData);
if($rowDta>0)
{
    $totJjg=$totKgwbBruto=$totKgwbNetto=$totLuasprd=$totJmlh=0;
    $afdC=false;$blankC=false;
    $tglTemp='';
	$dtNo=0;
    while($rData=$qData->fetch())
    {
        // ambil row tiap dt
        if($nospbatas!=$rData['nospb']){
            $jumlahBarisdt=0;
            $sEspebe="select count(*) as jumlahbarisdt from ".$dbname.".kebun_spbdt_detail where nospb='".$rData['nospb']."'";
            $qEspebe=$owlPDO->query($sEspebe) or die(print " Gagal: ".PDOException::getMessage());
            $qEspebe->setFetchMode(PDO::FETCH_ASSOC);
            $rEspebe=$qEspebe->fetch();
            $jumlahBarisdt=$rEspebe['jumlahbarisdt'];
            $dtNo++;
        }

        $totJjg+=$rData['jjg'];
        $totBrd+=$rData['brondolan'];
        $totKgwbNetto+=$rData['kgwbnetto'];
        $totKgwbBrutto+=$rData['kgwb'];
          
        if($rData['tanggal']!=$tglTemp){
            $afdC=false;
            $tglTemp=$rData['tanggal'];
        }
            // $dtNo++;
            $tab.="<tr class=rowcontent>";
            
        if($nospbatas!=$rData['nospb']){    
           $tab.="<td align=center rowspan='".$jumlahBarisdt."' valign='top'>".$dtNo."</td>";
            if($proses=='excel'){
                $tab.="<td rowspan='".$jumlahBarisdt."' valign='top'>".$rData['tanggal']."</td>";
            }else{
                $tab.="<td rowspan='".$jumlahBarisdt."' valign='top'>".tanggalnormal($rData['tanggal'])."</td>";
            }
            
            $tab.="<td rowspan='".$jumlahBarisdt."' valign='top'>".$rData['nospb']."</td>";
            $tab.="<td rowspan='".$jumlahBarisdt."' valign='top'>".$rData['nokendaraan']."</td>";
            $tab.="<td rowspan='".$jumlahBarisdt."' valign='top'>".$rData['notiket']."</td>";
        }    
            if($proses=='excel')
            {
                $tab.="<td>".$rData['tanggalpanen']."</td>";
            }
            else
            {
                $tab.="<td>".tanggalnormal($rData['tanggalpanen'])."</td>";
            }
            @$bjrwbNetto=fixnan(@$rData['kgwbnetto']/@$rData['jjg']);
            @$bjrwbBruto=fixnan(@$rData['kgwb']/@$rData['jjg']);
            $tab.="<td>".$rData['indukblok']."</td>";
            $tab.="<td>".$rData['blok']."</td>";
            $tab.="<td>".$namaOrg[$rData['blok']]."</td>";
            $tab.="<td>".$jenisbibit[$rData['blok']]."</td>";
            $tab.="<td align=center>".$optThnTanam[$rData['blok']]."</td>";
            // $tab.="<td align=right>".$rDtBlok['luasareaproduktif']."</td>";
            // $tab.="<td align=right>".number_format($rDtBlok['jumlahpokok'],0)."</td>";
			$tab.="<td align=right>".$rData['brondolan']."</td>";
			$tab.="<td align=right>".$rData['jjg']."</td>";
            $tab.="<td align=right>".number_format($rData['kgwbnetto'],2)."</td>";
            $tab.="<td align=right>".number_format($rData['kgwb'],2)."</td>";
            $tab.="<td align=right>".number_format($bjrwbNetto,2)."</td>";
            $tab.="<td align=right>".number_format($bjrwbBruto,2)."</td>";
            
            $tab.="</tr>";
        $nospbatas=$rData['nospb'];   
    }
}
else
{
    $tab.="<tr class=rowcontent><td colspan=11>".$_SESSION['lang']['dataempty']."</td>";
}
    $tab.="<tr class=rowcontent><td colspan=11>".$_SESSION['lang']['total']."</td>";
    $tab.="<td align=right>".number_format($totBrd,0)."</td>";
    $tab.="<td align=right>".number_format($totJjg,0)."</td>";
    $tab.="<td align=right>".number_format($totKgwbNetto,2)."</td>";
    $tab.="<td align=right>".number_format($totKgwbBrutto,2)."</td>";
    // $tab.="<td align=right>".number_format($totLuasprd,2)."</td>";
    // $tab.="<td align=right></td>";
    // $tab.="<td align=right>".number_format($totJmlh,0)."</td>";
    // $tab.="<td align=right></td>";
    $tab.="<td align=right></td>";
    $tab.="<td align=right></td>";
    $tab.="</tr>";
    $tab.="</tbody></table>";


switch($proses)
{
	case'preview':
	echo $tab;
	break;
	case'pdf':

	 class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
                global $tipeIntex;
                global $periode;
                global $unit;
                global $where;
                global $intiplasma;
                global $owlPDO;
				
				
				$tglPeriode=explode("-",$periode);
				$tanggal=$tglPeriode[1]."-".$tglPeriode[0];
                
				# Alamat & No Telp
				$arrHead = setheadreport(substr($unit,0,4));
				
                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 15;
                $path=$arrHead['logo'];
                $this->Image($path,$this->lMargin,($this->tMargin-12),0,55);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(110);   
                $this->Cell($width-100,$height,$arrHead['nama'],0,1,'L');	 
                $this->SetX(110); 		
                $this->Cell($width-100,$height,$arrHead['alamat'],0,1,'L');	
                $this->SetX(110); 			
                $this->Cell($width-100,$height,"Tel: ".$arrHead['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
				$this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();
				
                $this->Ln();
				$this->Ln();
                $this->SetFont('Arial','B',9);
                $this->Cell($width,$height, strtoupper($_SESSION['lang']['rProdKebundetail']),0,1,'C');	
			 	$this->SetFont('Arial','',8);
			 	$this->Cell($width,$height, "Periode : ".$tanggal,0,1,'C');	
				$this->Ln();$this->Ln();
                $this->SetFont('Arial','B',5);	
                $this->SetFillColor(220,220,220);
				$this->Cell(3/100*$width,$height,'No',1,0,'C',1);
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);		
				$this->Cell(15/100*$width,$height,$_SESSION['lang']['nospb'],1,0,'C',1);		
                $this->Cell(8/100*$width,$height,$_SESSION['lang']['tanggal'].' '.$_SESSION['lang']['panen'],1,0,'C',1);       
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['kodeblok'],1,0,'C',1);
                                $this->Cell(8/100*$width,$height,$_SESSION['lang']['tahuntanam'],1,0,'C',1);
                                $this->Cell(8/100*$width,$height,$_SESSION['lang']['nopol'],1,0,'C',1);
                                $this->Cell(8/100*$width,$height,$_SESSION['lang']['noTiket'],1,0,'C',1);
                                $this->Cell(5/100*$width,$height,$_SESSION['lang']['luas'],1,0,'L',1);
                $this->Cell(7/100*$width,$height,$_SESSION['lang']['jumlahpokok'],1,0,'C',1);              
                                $this->Cell(7/100*$width,$height,$_SESSION['lang']['jjg'],1,0,'C',1);
                                $this->Cell(7/100*$width,$height,$_SESSION['lang']['kgwb'],1,0,'C',1);
                                $this->Cell(7/100*$width,$height,$_SESSION['lang']['bjr'].' WB',1,1,'C',1);
            }
                
            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
        $pdf=new PDF('P','pt','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 9;
		$pdf->AddPage();
                $pdf->SetFont('Arial','',6);
		$pdf->SetFillColor(255,255,255);
		// select distinct * from ".$dbname.".kebun_spb_vw where tanggal like '%".$periode."%' ".$where." ".$inplas." order by tanggal,blok asc
        $sData="select distinct * from ".$dbname.".kebun_spb_vw where tanggal like '%".$periode."%' ".$where." ".$inplas." order by tanggal,blok asc";
		$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
		$rowDta=owlBaris($qData);
        $dtr=0;
        if($rowDta>0)
        {
			$totJjg=$totKgwb=$totLuasprd=$totJmlh=0;
            while($rData=$qData->fetch())
            {	
                $bjrwb=$rData['kgwb']/$rData['jjg'];
                $dtr++;
                $sDtBlok="select distinct tahuntanam,luasareaproduktif ,jumlahpokok from ".$dbname.".setup_blok where kodeorg='".$rData['blok']."'";
				$qDtBlok=$owlPDO->query($sDtBlok) or die(print " Gagal: ".PDOException::getMessage());
				$qDtBlok->setFetchMode(PDO::FETCH_ASSOC);
                $rDtBlok=$qDtBlok->fetch();
                $pdf->Cell(3/100*$width,$height,$dtr,1,0,'C',1);
                $pdf->Cell(8/100*$width,$height,tanggalnormal($rData['tanggal']),1,0,'C',1);		
                $pdf->Cell(15/100*$width,$height,$rData['nospb'],1,0,'L',1);		
                $pdf->Cell(8/100*$width,$height,tanggalnormal($rData['tanggalpanen']),1,0,'C',1);        
                $pdf->Cell(10/100*$width,$height,$rData['blok'],1,0,'L',1);
                $pdf->Cell(8/100*$width,$height,$rDtBlok['tahuntanam'],1,0,'C',1);
                $pdf->Cell(8/100*$width,$height,$rData['nokendaraan'],1,0,'L',1);
                $pdf->Cell(8/100*$width,$height,$rData['notiket'],1,0,'L',1);
                $pdf->Cell(5/100*$width,$height,number_format($rDtBlok['luasareaproduktif'],0),1,0,'R',1);
                $pdf->Cell(7/100*$width,$height,number_format($rDtBlok['jumlahpokok'],0),1,0,'R',1);
                $pdf->Cell(7/100*$width,$height,$rData['jjg'],1,0,'C',1);
                $pdf->Cell(7/100*$width,$height,number_format($rData['kgwb'],0),1,0,'R',1);
                $pdf->Cell(7/100*$width,$height,number_format($bjrwb,2),1,1,'R',1);
                $totJjg+=$rData['jjg'];
                $totKgwb+=$rData['kgwb'];
                $totLuasprd+=$rDtBlok['luasareaproduktif'];
                $totJmlh+=$rDtBlok['jumlahpokok'];
            }
            $pdf->SetFont('Arial','',5);
            $pdf->Cell(68/100*$width,$height,$_SESSION['lang']['total'],1,0,'C',1);
            $pdf->Cell(5/100*$width,$height,"",1,0,'R',1);
            $pdf->Cell(7/100*$width,$height,"",1,0,'R',1);
            $pdf->Cell(7/100*$width,$height,number_format($totJjg,2),1,0,'C',1);
            $pdf->Cell(7/100*$width,$height,number_format($totKgwb,2),1,0,'R',1);
            $pdf->Cell(7/100*$width,$height,"",1,1,'R',1);
        }
        else
        {
            $pdf->Cell(99/100*$width,$height,$_SESSION['lang']['dataempty'],1,1,'L',1);
        }
			
    $pdf->Output();
	break;
	case'excel':
             $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
            $tglSkrg=date("Ymd");
            $nop_="LaporanProduksiDetail__".$unit."_".$periode;
            if(strlen($tab)>0)
            {
            if ($handle = opendir('tempExcel')) {
            while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && $file != "index.html") {
            @unlink('tempExcel/'.$file);
            }
            }	
            closedir($handle);
            }
            $handle=fopen("tempExcel/".$nop_.".xls",'w');
            if(!fwrite($handle,$tab))
            {
            echo "<script language=javascript1.2>
            parent.window.alert('Can't convert to excel format');
            </script>";
            exit;
            }
            else
            {
            echo "<script language=javascript1.2>
            window.location='tempExcel/".$nop_.".xls';
            </script>";
            }
            fclose($handle);
            }
	break;
	default:
	break;
}
?>