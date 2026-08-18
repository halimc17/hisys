<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$kdUnit = checkPostGet('kdUnitOrg','');
$periodeUnit = checkPostGet('periodeDt','');
$optNmOrg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$tab='';

$thn=explode("-",$periodeUnit);
$arrBln=array("1"=>$_SESSION['lang']['jan'],"2"=>$_SESSION['lang']['feb'],"3"=>$_SESSION['lang']['mar'],"4"=>$_SESSION['lang']['apr'],"5"=>$_SESSION['lang']['mei'],"6"=>$_SESSION['lang']['jun'],"7"=>$_SESSION['lang']['jul'],"8"=>$_SESSION['lang']['agt'],"9"=>$_SESSION['lang']['sep'],"10"=>$_SESSION['lang']['okt'],"11"=>$_SESSION['lang']['nov'],"12"=>$_SESSION['lang']['dec']);
$sInd="select kodeorganisasi from ".$dbname.".organisasi where induk='".$kdUnit."' and tipe='AFDELING'";
$qInd=$owlPDO->query($sInd) or die(print " Gagal: ".PDOException::getMessage());
$qInd->setFetchMode(PDO::FETCH_ASSOC);
while($rInd=$qInd->fetch())
{
    $dtAfd[$rInd['kodeorganisasi']]=$rInd['kodeorganisasi'];
}
$sData="select distinct kodeorg,sum(pagi+sore+malam) as jumlah,substr(tanggal,1,7) as bulan from ".$dbname.".kebun_curahhujan
        where kodeorg like '".$kdUnit."%' and substr(tanggal,1,7)  between '".$thn[0]."-01' and '".$periodeUnit."' 
        group by kodeorg,substr(tanggal,1,7)";
$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
$qData->setFetchMode(PDO::FETCH_ASSOC);
while($rData=$qData->fetch())
{
    $bln=explode("-",$rData['bulan']);
    $bulan=intval($bln[1]);
    
    if($rData['jumlah']!=0)
    {
        $dataCrh[$bulan.$rData['kodeorg']]=$rData['jumlah'];
    }
}
$sJhr="select distinct kodeorg,sum(pagi+sore+malam) as jumlah,tanggal from ".$dbname.".kebun_curahhujan
        where kodeorg like '".$kdUnit."%' and substr(tanggal,1,7)  between '".$thn[0]."-01' and '".$periodeUnit."' group by kodeorg,tanggal";
$qJhr=$owlPDO->query($sJhr) or die(print " Gagal: ".PDOException::getMessage());
$qJhr->setFetchMode(PDO::FETCH_ASSOC);
while($rData=$qJhr->fetch())
{
    $bln=explode("-",$rData['tanggal']);
    $bulan=intval($bln[1]);
    $tgl=intval($bln[2]);
    if($rData['jumlah']!=0)
    {
        $dataDrh[$tgl.$bulan.$rData['kodeorg']]=$rData['jumlah'];
    }
}
for($adr=1;$adr<32;$adr++)
{
	for($adf=1;$adf<=$thn[1];$adf++)
	{
		foreach($dtAfd as $kdListAfd)
		{
			if(!empty($dataDrh[$adr.$adf.$kdListAfd]))
			{
				setIt($sumHr[$adf.$kdListAfd],0);
				$sumHr[$adf.$kdListAfd]+=1;
			}
		}
	}
}


if($proses!='getPeriode')
{
    if(($periodeUnit=='')||($kdUnit==''))
    {
        exit("Error : Semua Field Tidak Boleh Kosong");
    }
}
$rowAfd=count($dtAfd);
	switch($proses)
        {
            case'preview':
             $tab.="<table cellpadding=1 cellspacing=1 border=0>";
             ;
             $tab.="<tr><td>".$_SESSION['lang']['kebun']."</td>";
             $tab.="<td>:</td><td>".$optNmOrg[$kdUnit]."</td></tr>";
             $tab.="<tr><td>".$_SESSION['lang']['tahun']."</td>";
             $tab.="<td>:</td><td>".$thn[0]."</td></tr></table><br />";
             $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
             $tab.="<tr class=rowheader>";
             $tab.="<td rowspan=2 align=center>".$_SESSION['lang']['bulan']."</td>";
             foreach($dtAfd as $listAfd)
             {
                 $tab.="<td colspan=2  align=center>".$listAfd."</td>";
             }
             
             $tab.="<td colspan=2>".$_SESSION['lang']['rtEstate']."</td></tr><tr>";
             foreach($dtAfd as $listAfd)
             {
                 $tab.="<td align=center>CH</td><td  align=center>HH</td>";
             }
             $tab.="<td  align=center>CH</td><td  align=center>HH</td>";
             $tab.="</tr></thead><tbody>";
             foreach($arrBln as $dtBln=>$nmbulan)
             {
				$tab.="<tr class=rowcontent><td>".$nmbulan."</td>";
				$jumCH=0;
				$jumHH=0;
				foreach($dtAfd as $listAfd)
				{
					setIt($dataCrh[$dtBln.$listAfd],0);
					setIt($sumHr[$dtBln.$listAfd],0);
					setIt($sumAll[$dtBln],0);
					setIt($sumAllHari[$dtBln],0);
					$tab.="<td align=right>".number_format($dataCrh[$dtBln.$listAfd],2)."</td><td align=right>".number_format($sumHr[$dtBln.$listAfd],2)."</td>";
					$sumAll[$dtBln]+=$dataCrh[$dtBln.$listAfd];
					$sumAllHari[$dtBln]+=$sumHr[$dtBln.$listAfd];
					if($dataCrh[$dtBln.$listAfd]>0)$jumCH+=1;
					if($sumHr[$dtBln.$listAfd])$jumHH+=1;
				}
				@$rt2CC[$dtBln]=$sumAll[$dtBln]/$jumCH;
				@$rt2Hr[$dtBln]=$sumAllHari[$dtBln]/$jumHH;
				
				if($rt2CC[$dtBln]!=0)
				{
				   $tab.="<td align=right>".number_format($rt2CC[$dtBln],2)."</td>";
				}
				else
				{
					$tab.="<td align=right>&nbsp</td>";
				}
				
				if($rt2Hr[$dtBln]!=0)
				{
				   $tab.="<td align=right>".number_format($rt2Hr[$dtBln],2)."</td>";
				}
				else
				{
					$tab.="<td align=right>&nbsp</td>";
				}
				$tab.="</tr>";
            }
            $tab.="</tbody></table>";
            echo $tab;
            break;//
            case'excel':
			 $bgclr="bgcolor=#DEDEDE align=center";
             $tab.="<table cellpadding=1 cellspacing=1 border=1>";
             ;
             $tab.="<tr><td>".$_SESSION['lang']['kebun']."</td>";
             $tab.="<td>:</td><td>".$optNmOrg[$kdUnit]."</td></tr>";
             $tab.="<tr><td>".$_SESSION['lang']['tahun']."</td>";
             $tab.="<td>:</td><td>".$thn[0]."</td></tr></table><br />";
             $tab.="<table cellpadding=1 cellspacing=1 border=1 class=sortable><thead>";
             $tab.="<tr class=rowheader>";
             $tab.="<td rowspan=2 ".$bgclr.">".$_SESSION['lang']['bulan']."</td>";
             foreach($dtAfd as $listAfd)
             {
                 $tab.="<td colspan=2 ".$bgclr.">".$listAfd."</td>";
             }
             
             $tab.="<td colspan=2 ".$bgclr.">".$_SESSION['lang']['rtEstate']."</td></tr><tr>";
             foreach($dtAfd as $listAfd)
             {
                 $tab.="<td ".$bgclr.">CH</td><td ".$bgclr.">HH</td>";
             }
             $tab.="<td ".$bgclr.">CH</td><td ".$bgclr.">HH</td>";
             $tab.="</tr></thead><tbody>";
             foreach($arrBln as $dtBln=>$nmbulan)
             {
				$tab.="<tr class=rowcontent><td>".$nmbulan."</td>";
				$jumCH=0;
				$jumHH=0;
				foreach($dtAfd as $listAfd)
				{
					setIt($dataCrh[$dtBln.$listAfd],0);
					setIt($sumHr[$dtBln.$listAfd],0);
					setIt($sumAll[$dtBln],0);
					setIt($sumAllHari[$dtBln],0);
					$tab.="<td align=right>".$dataCrh[$dtBln.$listAfd]."</td><td align=right>".$sumHr[$dtBln.$listAfd]."</td>";
					$sumAll[$dtBln]+=$dataCrh[$dtBln.$listAfd];
					$sumAllHari[$dtBln]+=$sumHr[$dtBln.$listAfd];
					if($dataCrh[$dtBln.$listAfd]>0)$jumCH+=1;
					if($sumHr[$dtBln.$listAfd])$jumHH+=1;
				}
				@$rt2CC[$dtBln]=$sumAll[$dtBln]/$jumCH;
				@$rt2Hr[$dtBln]=$sumAllHari[$dtBln]/$jumHH;
				
				if($rt2CC[$dtBln]!=0)
				{
				   $tab.="<td align=right>".number_format($rt2CC[$dtBln],2)."</td>";
				}
				else
				{
					$tab.="<td align=right>&nbsp</td>";
				}
				
				if($rt2Hr[$dtBln]!=0)
				{
				   $tab.="<td align=right>".number_format($rt2Hr[$dtBln],2)."</td>";
				}
				else
				{
					$tab.="<td align=right>&nbsp</td>";
				}
				$tab.="</tr>";
            }
            $tab.="</tbody></table>";
						
            $tab.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
            $dte=date("His");
            $nop_="curahHujanBlnan_".$dte;
            $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                     gzwrite($gztralala, $tab);
                     gzclose($gztralala);
                     echo "<script language=javascript1.2>
                        window.location='tempExcel/".$nop_.".xls.gz';
                        </script>";

						
						
						
						
						
            break;
            case'getPeriode':
            $optper="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
            $sTgl="select distinct substr(tanggal,1,7) as periode from ".$dbname.".kebun_curahhujan where 
                   kodeorg like '".$kdUnit."%' order by tanggal desc";
			$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
			$qTgl->setFetchMode(PDO::FETCH_ASSOC);
            while($rTgl=$qTgl->fetch())
            {
               $optper.="<option value='".$rTgl['periode']."'>".substr($rTgl['periode'],5,2)."-".substr($rTgl['periode'],0,4)."</option>";
            }
            echo $optper;
            break;
            case'pdf':
        
      
           class PDF extends FPDF {
            function Header() {
            global $optNmOrg;
            global $dbname;
            global $kdUnit;
            global $periodeUnit;
            global $rowAfd;
            global $arrBln;
            global $thn;
            global $dtAfd;
            global $owlPDO;
            
			$arrHead = setheadreport(substr($kdUnit,0,4));
				
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
			
                $kebun=substr($kdUnit,0,4);
               
                $this->SetFont('Arial','B',11);
                $this->Cell($width,$height,strtoupper($_SESSION['lang']['curahbulanan']." ".$_SESSION['lang']['tahun']." ".$thn[0]),0,1,'C');
                $this->Ln();	
                //$this->Cell(275,5,strtoupper($_SESSION['lang']['rprodksiPabrik']),0,1,'C');
                
                $this->SetFont('Arial','',8);
                $this->Cell(50,$height,$_SESSION['lang']['kebun'],0,0,'L');
                $this->Cell(10,$height,':','',0,0,'L');
                $this->Cell(70,$height,$optNmOrg[$kdUnit],0,1,'L');
                $this->Cell(50,$height,$_SESSION['lang']['tahun'],0,0,'L');
                $this->Cell(10,$height,':','',0,0,'L');
                $this->Cell(70,$height,$thn[0],0,1,'L');
                
                $this->Cell(50,$height,$_SESSION['lang']['page'],0,0,'L');
                $this->Cell(10,$height,':','',0,0,'R');
                $this->Cell(70,$height,$this->PageNo(),0,1,'L');
                $this->Cell(50,$height,'User',0,0,'L');
                $this->Cell(10,$height,':','',0,0,'L');
                $this->Cell(70,$height,$_SESSION['standard']['username'],0,1,'L');
                $this->Ln(10);
                
                $height = 12;
                $this->SetFillColor(220,220,220);
                $this->SetFont('Arial','B',7);
                $this->Cell(60,$height,$_SESSION['lang']['bulan'],'TLR',0,'C',1);
				$no=0;
                foreach($dtAfd as $listAfd)
                {
                    $no+=1;
                   
                        $this->Cell(60,$height,$listAfd,1,0,'C',1);
                }
                $this->Cell(60,$height,$_SESSION['lang']['rtEstate'],1,1,'C',1);
                $this->Cell(60,$height,$_SESSION['lang']['bulan'],'BLR',0,'C',1);
                foreach($dtAfd as $listAfd)
                {
                    $no+=1;
                   
                        $this->Cell(30,$height,"CC",1,0,'C',1);
                        $this->Cell(30,$height,"HH",1,0,'C',1);
                }
                $this->Cell(30,$height,"CC",1,0,'C',1);
                $this->Cell(30,$height,"HH",1,1,'C',1);
                
                
          }
              function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
            }
            //================================

            $pdf=new PDF('L','pt','A4');
            $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
            $height = 10;
            $pdf->AddPage();
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','',7);
            foreach($arrBln as $dtBln=>$nmbulan)
             {
                 $pdf->Cell(60,$height,$nmbulan,1,0,'L',1);
                 foreach($dtAfd as $listAfd)
                 {
					setIt($dataCrh[$dtBln.$listAfd],0);
					setIt($sumHr[$dtBln.$listAfd],0);
					setIt($sumAll[$dtBln],0);
					setIt($sumAllHari[$dtBln],0);
                     $pdf->Cell(30,$height,$dataCrh[$dtBln.$listAfd],1,0,'R',1);
                     $pdf->Cell(30,$height,$sumHr[$dtBln.$listAfd],1,0,'R',1);
                     $sumAll[$dtBln]+=$dataCrh[$dtBln.$listAfd];
                     $sumAllHari[$dtBln]+=$sumHr[$dtBln.$listAfd];
					 if($dataCrh[$dtBln.$listAfd]>0)$jumCH+=1;
					 if($sumHr[$dtBln.$listAfd])$jumHH+=1;
                 }
                 @$rt2CC[$dtBln]=$sumAll[$dtBln]/$jumCH;
                 @$rt2Hr[$dtBln]=$sumAllHari[$dtBln]/$jumHH;
                 
                 if($rt2CC[$dtBln]!=0)
                 {
                    $pdf->Cell(30,$height,number_format($rt2CC[$dtBln],2),1,0,'R',1);
                 }
                 else
                 {
                     $pdf->Cell(30,$height," ",1,0,'R',1);
                 }
                 
                 if($rt2Hr[$dtBln]!=0)
                 {
                    $pdf->Cell(30,$height,number_format($rt2Hr[$dtBln],2),1,1,'R',1);
                 }
                 else
                 {
                    $pdf->Cell(30,$height," ",1,1,'R',1);
                 }
             }
            $pdf->Output();	
                
                
            break;
                
            default:
            break;
        }
	
?>