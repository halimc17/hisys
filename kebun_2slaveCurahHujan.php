<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$kdUnit = checkPostGet('kdUnit','');
$periodeUnit = checkPostGet('periodeUnit','');
$tab = '';
$optNmOrg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$kebun=substr($kdUnit,0,4);
$thn=explode("-",$periodeUnit);
$arrBln=array("1"=>"Jan","2"=>"Feb","3"=>"Mar","4"=>"Apr","5"=>"Mei","6"=>"Jun","7"=>"Jul","8"=>"Agt","9"=>"Sep","10"=>"Okt","11"=>"Nov","12"=>"Des");
$sInd="select induk from ".$dbname.".organisasi where kodeorganisasi='".$kebun."'";
$qInd=$owlPDO->query($sInd) or die(print " Gagal: ".PDOException::getMessage());
$qInd->setFetchMode(PDO::FETCH_ASSOC);
$rInd=$qInd->fetch();
//$sData="select distinct sum(pagi+sore) as jumlah,tanggal from ".$dbname.".kebun_curahhujan
//        where kodeorg='".$kdUnit."' and substr(tanggal,1,7)  between '".$thn[0]."-01' and '".$periodeUnit."' group by tanggal";
$sData="select distinct pagi,sore,malam,tanggal from ".$dbname.".kebun_curahhujan
        where kodeorg='".$kdUnit."' and substr(tanggal,1,7)  between '".$thn[0]."-01' and '".$periodeUnit."' group by tanggal";

$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
$qData->setFetchMode(PDO::FETCH_ASSOC);
while($rData=$qData->fetch())
{
    $bln=explode("-",$rData['tanggal']);
    $bulan=intval($bln[1]);
    $tgl=intval($bln[2]);
    if(($rData['pagi']!=0)||($rData['sore']!=0)||($rData['malam']!=0))
    {
        $dataCrh[$tgl][$bulan]['p']=$rData['pagi'];
        $dataCrh[$tgl][$bulan]['s']=$rData['sore'];
        $dataCrh[$tgl][$bulan]['m']=$rData['malam'];
    }
}
if($proses!='getPeriode')
{
    if(($periodeUnit=='')||($kdUnit==''))
    {
        exit("Error: Semua Field Tidak Boleh Kosong");
    }
}


	switch($proses)
        {
            case'preview':
             $tab.="<table cellpadding=1 cellspacing=1 border=0>";
             $tab.="<tr><td>".$_SESSION['lang']['afdeling']."</td>";
             $tab.="<td>:</td><td>".$optNmOrg[$kdUnit]."</td></tr>";
             $tab.="<tr><td>".$_SESSION['lang']['kebun']."</td>";
             $tab.="<td>:</td><td>".$optNmOrg[$kebun]."</td></tr>";
             $tab.="<tr><td>".$_SESSION['lang']['pt']."</td>";
             $tab.="<td>:</td><td>".$optNmOrg[$rInd['induk']]."</td></tr></table><br />";
             $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
             $tab.="<tr class=rowheader>";
             $tab.="<td rowspan=2 align=center>".$_SESSION['lang']['tanggal']."</td>";
             for($adf=1;$adf<=$thn[1];$adf++)
             {
                 $tab.="<th colspan=3 style='text-align:center;'>".$arrBln[$adf]."</th>";
             }
             $tab.="</tr>";
             $tab.="<tr class=rowheader>";
             for($adf=1;$adf<=$thn[1];$adf++)
             {
                 $tab.="<th>".$_SESSION['lang']['pagi']."</th>";
                 $tab.="<th>".$_SESSION['lang']['sore']."</th>";
                 $tab.="<th>".$_SESSION['lang']['malam']."</th>";
             }
             $tab.="</tr>";
             $tab.="</thead><tbody>";
             for($adr=1;$adr<32;$adr++)
             {
                 $tab.="<tr class=rowcontent><td align=center>".$adr."</td>";
                 for($adf=1;$adf<=$thn[1];$adf++)
                 {
                    setIt($sumMM[$adf]['p'],0);
					setIt($sumMM[$adf]['s'],0);
					setIt($sumMM[$adf]['m'],0);
					setIt($sumHr[$adf],0);
					// setIt($dataCrh[$adr][$adf]['p'],0);
					// setIt($dataCrh[$adr][$adf]['s'],0);
					// setIt($dataCrh[$adr][$adf]['m'],0);
					$tab.="<td align=right>".$dataCrh[$adr][$adf]['p']."</td>";
                    $tab.="<td align=right>".$dataCrh[$adr][$adf]['s']."</td>";
                    $tab.="<td align=right>".$dataCrh[$adr][$adf]['m']."</td>";
                    $sumMM[$adf]['p']+=$dataCrh[$adr][$adf]['p'];
                    $sumMM[$adf]['s']+=$dataCrh[$adr][$adf]['s'];
                    $sumMM[$adf]['m']+=$dataCrh[$adr][$adf]['m'];
                    if(!empty($dataCrh[$adr][$adf]['p'])||!empty($dataCrh[$adr][$adf]['s'])||!empty($dataCrh[$adr][$adf]['m']))
                    {
                        $sumHr[$adf]+=1;
                    }
                 }
                 $tab.="</tr>";
             }
             
                 $tab.="<tr class=rowcontent><td>".$_SESSION['lang']['jumlah']." (mm)</td>";
                 for($adf=1;$adf<=$thn[1];$adf++)
                 {
                    $tab.="<td align=right>".$sumMM[$adf]['p']."</td>";
                    $tab.="<td align=right>".$sumMM[$adf]['s']."</td>";
                    $tab.="<td align=right>".$sumMM[$adf]['m']."</td>";
                 }
                 $tab.="</tr>";
                 $tab.="<tr class=rowcontent><td width=60px>".$_SESSION['lang']['jumlah']."   (hari hujan (rainy days))</td>";
                 for($adf=1;$adf<=$thn[1];$adf++)
                 {
                    $tab.="<td align=right colspan=3>".$sumHr[$adf]."</td>";
                 }
                 $tab.="</tr>";
             $tab.="</tbody></table>";
            echo $tab;
            break;//
            case'excel':
             $tab="<table cellpadding=1 cellspacing=1 border=0>";
             $tab.="<tr><td colspan=5>".$_SESSION['lang']['curahharian']." ".$_SESSION['lang']['tahun']." ".$thn[0]."</td></tr>";
             $tab.="<tr><td colspan=3>".$_SESSION['lang']['afdeling']."</td>";
             $tab.="<td>:</td><td>".$optNmOrg[$kdUnit]."</td></tr>";
             $tab.="<tr><td colspan=3>".$_SESSION['lang']['kebun']."</td>";
             $tab.="<td>:</td><td>".$optNmOrg[$kebun]."</td></tr>";
             $tab.="<tr><td colspan=3>".$_SESSION['lang']['pt']."</td>";
             $tab.="<td>:</td><td>".$optNmOrg[$rInd['induk']]."</td></tr></table><br />";
             $tab.="<table cellpadding=1 cellspacing=1 border=1 class=sortable><thead>";
             $tab.="<tr class=rowheader>";
             $tab.="<td rowspan=2>".$_SESSION['lang']['tanggal']."</td>";
             for($adf=1;$adf<=$thn[1];$adf++)
             {
                 $tab.="<td colspan=3 style='text-align:center'>".$arrBln[$adf]."</td>";
             }
             $tab.="</tr>";
             $tab.="<tr class=rowheader>";
             for($adf=1;$adf<=$thn[1];$adf++)
             {
                 $tab.="<td style='text-align:center'>".$_SESSION['lang']['pagi']."</td>";
                 $tab.="<td style='text-align:center'>".$_SESSION['lang']['sore']."</td>";
                 $tab.="<td style='text-align:center'>".$_SESSION['lang']['malam']."</td>";
             }
             $tab.="</tr>";
             $tab.="</thead><tbody>";
             for($adr=1;$adr<32;$adr++)
             {
                 $tab.="<tr class=rowcontent><td>".$adr."</td>";
                 for($adf=1;$adf<=$thn[1];$adf++)
                 {
					setIt($sumMM[$adf]['p'],0);
					setIt($sumMM[$adf]['s'],0);
					setIt($sumMM[$adf]['m'],0);
					setIt($sumHr[$adf],0);
					setIt($dataCrh[$adr][$adf]['p'],0);
					setIt($dataCrh[$adr][$adf]['s'],0);
					setIt($dataCrh[$adr][$adf]['m'],0);
                    $tab.="<td align=right>".$dataCrh[$adr][$adf]['p']."</td>";
                    $tab.="<td align=right>".$dataCrh[$adr][$adf]['s']."</td>";
                    $tab.="<td align=right>".$dataCrh[$adr][$adf]['m']."</td>";
                    $sumMM[$adf]['p']+=$dataCrh[$adr][$adf]['p'];
                    $sumMM[$adf]['s']+=$dataCrh[$adr][$adf]['s'];
                    $sumMM[$adf]['m']+=$dataCrh[$adr][$adf]['m'];
                    if(($dataCrh[$adr][$adf]['p']!=0)||($dataCrh[$adr][$adf]['s']!=0)||($dataCrh[$adr][$adf]['m']!=0))
                    {
                         $sumHr[$adf]+=1;
                    }
                 }
                 $tab.="</tr>";
             }
             
                 $tab.="<tr class=rowcontent><td>".$_SESSION['lang']['jumlah']." (mm)</td>";
                 for($adf=1;$adf<=$thn[1];$adf++)
                 {
                    $tab.="<td align=right>".$sumMM[$adf]['p']."</td>";
                    $tab.="<td align=right>".$sumMM[$adf]['s']."</td>";
                    $tab.="<td align=right>".$sumMM[$adf]['m']."</td>";
                 }
                 $tab.="</tr>";
                 $tab.="<tr class=rowcontent><td>".$_SESSION['lang']['jumlah']."   (hari hujan/rainy days)</td>";
                 for($adf=1;$adf<=$thn[1];$adf++)
                 {
                    $tab.="<td align=right colspan=3>".$sumHr[$adf]."</td>";
                 }
                 $tab.="</tr>";
             $tab.="</tbody></table>";
            $tab.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
            $dte=date("His");
            $nop_="curahHujanHarian_".$dte;
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
            global $rInd;
            global $arrBln;
            global $thn;
            global $dataCrh;
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
                $this->Cell($width,$height,strtoupper($_SESSION['lang']['curahharian']." ".$_SESSION['lang']['tahun']." ".$thn[0]),0,1,'C');
                $this->Ln();	
                //$this->Cell(275,5,strtoupper($_SESSION['lang']['rprodksiPabrik']),0,1,'C');
                
                $this->SetFont('Arial','',8);
                $this->Cell(50,$height,$_SESSION['lang']['afdeling'],0,0,'L');
                $this->Cell(10,$height,':','',0,0,'L');
                $this->Cell(70,$height,$optNmOrg[$kdUnit],0,1,'L');
                $this->Cell(50,$height,$_SESSION['lang']['kebun'],0,0,'L');
                $this->Cell(10,$height,':','',0,0,'L');
                $this->Cell(70,$height,$optNmOrg[$kebun],0,1,'L');
                $this->Cell(50,$height,$_SESSION['lang']['pt'],0,0,'L');
                $this->Cell(10,$height,':','',0,0,'R');
                $this->Cell(70,$height,$optNmOrg[$rInd['induk']],0,1,'L');
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
                $this->Cell(60,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);           
                for($adf=1;$adf<=$thn[1];$adf++)
                {
                    if($adf!=$thn[1])
                    {
                        $this->Cell(75,$height,$arrBln[$adf],1,0,'C',1);
                    }
                    else
                    {
                        $this->Cell(75,$height,$arrBln[$adf],1,1,'C',1);
                    }
                }
                $this->Cell(60,$height,'',1,0,'C',1);           
                for($adf=1;$adf<=$thn[1];$adf++)
                {
                    if($adf!=$thn[1])
                    {
                        $this->Cell(25,$height,$_SESSION['lang']['pagi'],1,0,'C',1);
                        $this->Cell(25,$height,$_SESSION['lang']['sore'],1,0,'C',1);
                        $this->Cell(25,$height,$_SESSION['lang']['malam'],1,0,'C',1);
                    }
                    else
                    {
                        $this->Cell(25,$height,$_SESSION['lang']['pagi'],1,0,'C',1);
                        $this->Cell(25,$height,$_SESSION['lang']['sore'],1,0,'C',1);
                        $this->Cell(25,$height,$_SESSION['lang']['malam'],1,1,'C',1);
                    }
                }
                
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
            for($adr=1;$adr<32;$adr++)
             {
                $pdf->Cell(60,$height,$adr,1,0,'C',1);
                 for($adf=1;$adf<=$thn[1];$adf++)
                 {
					setIt($sumMM[$adf]['p'],0);
					setIt($sumMM[$adf]['s'],0);
					setIt($sumMM[$adf]['m'],0);
					setIt($sumHr[$adf],0);
					setIt($dataCrh[$adr][$adf]['p'],0);
					setIt($dataCrh[$adr][$adf]['s'],0);
					setIt($dataCrh[$adr][$adf]['m'],0);
                    if($adf!=$thn[1])
                    {
                        $pdf->Cell(25,$height,$dataCrh[$adr][$adf]['p'],1,0,'R',1);
                        $pdf->Cell(25,$height,$dataCrh[$adr][$adf]['s'],1,0,'R',1);
                        $pdf->Cell(25,$height,$dataCrh[$adr][$adf]['m'],1,0,'R',1);
                    }
                    else
                    {
                        $pdf->Cell(25,$height,$dataCrh[$adr][$adf]['p'],1,0,'R',1);
                        $pdf->Cell(25,$height,$dataCrh[$adr][$adf]['s'],1,0,'R',1);
                        $pdf->Cell(25,$height,$dataCrh[$adr][$adf]['m'],1,1,'R',1);
                    }
                    $sumMM[$adf]['p']+=$dataCrh[$adr][$adf]['p'];
                    $sumMM[$adf]['s']+=$dataCrh[$adr][$adf]['s'];
                    $sumMM[$adf]['m']+=$dataCrh[$adr][$adf]['m'];
                    if(($dataCrh[$adr][$adf]['p']!=0)||($dataCrh[$adr][$adf]['s']!=0)||($dataCrh[$adr][$adf]['m']!=0))
                    {
                         $sumHr[$adf]+=1;
                    }
                 }
                 
             }
             $pdf->SetFont('Arial','',6);
             $pdf->Cell(60,$height,$_SESSION['lang']['jumlah']."(mm)",1,0,'L',1);
             $pdf->SetFont('Arial','',7);
             for($adf=1;$adf<=$thn[1];$adf++)
             {
                 if($adf!=$thn[1])
                {
                    $pdf->Cell(25,$height,$sumMM[$adf]['p'],1,0,'R',1);
                    $pdf->Cell(25,$height,$sumMM[$adf]['s'],1,0,'R',1);
                    $pdf->Cell(25,$height,$sumMM[$adf]['m'],1,0,'R',1);
                }
                else
                {
                    $pdf->Cell(25,$height,$sumMM[$adf]['p'],1,0,'R',1);
                    $pdf->Cell(25,$height,$sumMM[$adf]['s'],1,0,'R',1);
                    $pdf->Cell(25,$height,$sumMM[$adf]['m'],1,1,'R',1);
                }
             }
             $pdf->SetFont('Arial','',6);
             $pdf->Cell(60,$height,$_SESSION['lang']['jumlah']." (hari hujan)",1,0,'L',1);
             $pdf->SetFont('Arial','',7);
             for($adf=1;$adf<=$thn[1];$adf++)
             {
                 if($adf!=$thn[1])
                {
                    $pdf->Cell(75,$height,$sumHr[$adf],1,0,'R',1);
                }
                else
                {
                    $pdf->Cell(75,$height,$sumHr[$adf],1,1,'R',1);
                }
             }
             
            $pdf->Output();	
                
                
            break;
                
            default:
            break;
        }
	
?>