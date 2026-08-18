<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses = checkPostGet('proses','');

//$arr="##periode##tipeIntex##unit";
$periode=checkPostGet('periode','');
$tipeIntex=checkPostGet('tipeIntex','');
$unit=checkPostGet('unit','');
$kodeOrg=checkPostGet('kodeOrg','');
$brsKe=checkPostGet('brsKe','');
$tgl=tanggalsystem(checkPostGet('tgl',''));
$tglAfd=checkPostGet('tglAfd','');
$kdBlok=checkPostGet('kdBlok','');
$endKe=checkPostGet('endKe','');
$nospb=checkPostGet('nospb','');
$kodePabrik=checkPostGet('kodePabrik','');
$kodeUnit=checkPostGet('kodeUnit','');
switch($proses)
{
	case'getKdorg':
	//echo "warning:masuk";
	$optorg="<option value=''>".$_SESSION['lang']['all']."</option>";
	if($tipeIntex==1)
	{
		//$sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and induk in(select induk from ".$dbname.".organisasi where tipe='PABRIK') order by namaorganisasi asc";
            $sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and induk ='".$_SESSION['org']['kodeorganisasi']."' order by namaorganisasi asc";
	}
	elseif($tipeIntex==0)
	{
		$sOrg="SELECT namasupplier,`kodetimbangan` FROM ".$dbname.".log_5supplier WHERE trim(kodetimbangan)!='' order by namasupplier asc";//echo "warning:".$sOrg;
	}
	elseif($tipeIntex==2)
	{
		//$sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and induk not in(select induk from ".$dbname.".organisasi where tipe='PABRIK') order by namaorganisasi asc";
            $sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and induk <>'".$_SESSION['org']['kodeorganisasi']."' order by namaorganisasi asc";
	}
	$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
	$qOrg->setFetchMode(PDO::FETCH_ASSOC);
	while($rOrg=$qOrg->fetch())
	{
            if($tipeIntex!=0)
            {
                $optorg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
            }
            else
            {
                $optorg.="<option value=".$rOrg['kodetimbangan'].">".$rOrg['namasupplier']."</option>";
            }
	}
	echo $optorg;
	break;
	case'getAfdeling':
	
	echo"
	<img onclick=\"closeAfd(".$brsKe.");\" title=\"Tutup\" class=\"resicon\" src=\"images/close.gif\">
	<table cellspacing=1 border=0 class=sortable width=100%>
		<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nospb']."</td>
				<td align=center>Kode ".$_SESSION['lang']['afdeling']."</td>
				<td align=center>".$_SESSION['lang']['afdeling']."</td>
			</tr>
		</thead><tbody>";
	//$sGet="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$kodeOrg."' and tipe='AFDELING'";
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$kodeOrg."' and tipe='AFDELING'";
	$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
	$qOrg->setFetchMode(PDO::FETCH_ASSOC);
	while($rData=$qOrg->fetch())
	{
		$rDataOrg[$rData['kodeorganisasi']]=$rData;
	}
	$sGet="select substr(a.nospb,9,6) as kodeorganisasi,a.nospb from ".$dbname.".kebun_spbdt a left join ".$dbname.".kebun_spbht b on a.nospb=b.nospb where a.nospb like '%".$kodeOrg."%' and b.tanggal ='".$tglAfd."' group by nospb";
	$qGet=$owlPDO->query($sGet) or die(print " Gagal: ".PDOException::getMessage());
	$qGet->setFetchMode(PDO::FETCH_ASSOC);
	while($rGet=$qGet->fetch())
	{
		$no+=1;
		
		echo"<tr  align=center class=rowcontent id=detail_".$brsKe."_".$no." onclick=detailBlok(".$brsKe.",".$no.") style=\"cursor: pointer;\" >
				<td id=nospb_".$brsKe."_".$no.">".$rGet['nospb']."</td>
				<td id=kdBlok_".$brsKe."_".$no.">".$rGet['kodeorganisasi']."</td>
				<td>".$rDataOrg[$rGet['kodeorganisasi']]['namaorganisasi']."</td>
			</tr><tr><td colspan=2><div id=detailBlok_".$brsKe."_".$no."></div></td></tr>";
	}
	echo"		</tbody>
		</table>";

	break;
	case'getUnit':
	
	$sOrg="select distinct kodeorg from ".$dbname.".pabrik_timbangan where kodeorg!='' and millcode like '%".$kodePabrik."%' order by kodeorg";
	$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
	$qOrg->setFetchMode(PDO::FETCH_ASSOC);
	echo"<option value=''>".$_SESSION['lang']['all']."</option>";
	while($rData=$qOrg->fetch())
	{
		echo"<option value=".$rData['kodeorg'].">".$rData['kodeorg']."</option>";
	}
	break;
	case'getAfdeling2':
$sOrg2="select distinct kodeorg from ".$dbname.".pabrik_timbangan where kodeorg!='' and millcode like '%".$kodePabrik."%' order by kodeorg";
$qOrg2=$owlPDO->query($sOrg2) or die(print " Gagal: ".PDOException::getMessage());
$qOrg2->setFetchMode(PDO::FETCH_ASSOC);
$unitintimbangan='(';
while($rData2=$qOrg2->fetch())
{
        $unitintimbangan.="'".$rData2['kodeorg']."',";
}
$unitintimbangan=substr($unitintimbangan,0,-1);
$unitintimbangan.=')'; if($unitintimbangan==')')$unitintimbangan="('')";
if($kodeUnit=='')
	$sOrg="select kodeorganisasi from ".$dbname.".organisasi where tipe = 'AFDELING' and induk in ".$unitintimbangan." order by kodeorganisasi"; else
	$sOrg="select kodeorganisasi from ".$dbname.".organisasi where tipe = 'AFDELING' and induk like '%".$kodeUnit."%' order by kodeorganisasi";
	$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
	$qOrg->setFetchMode(PDO::FETCH_ASSOC);
	echo"<option value=''>".$_SESSION['lang']['all']."</option>";
	while($rData=$qOrg->fetch())
	{
		echo"<option value=".$rData['kodeorganisasi'].">".$rData['kodeorganisasi']."</option>";
	}
	break;
	case 'getAfdCmb':
		$unit = checkPostGet('unit','');
		$sAfd="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk like '".$unit."%' and tipe='AFDELING' order by namaorganisasi asc";
		$qAfd=$owlPDO->query($sAfd) or die(print " Gagal: ".PDOException::getMessage());
		$qAfd->setFetchMode(PDO::FETCH_ASSOC);
		$optAfdDt="<option value=''>".$_SESSION['lang']['all']."</option>";
		while($rAfd=$qAfd->fetch()){
			$optAfdDt.="<option value='".$rAfd['kodeorganisasi']."'>".$rAfd['kodeorganisasi']." - ".$rAfd['namaorganisasi']."</option>";
		}
		echo $optAfdDt;
	break;
        case'getPrestasi':
	//setup_kegiatan
	echo"<table cellspacing=1 border=0 class=sortable>
	<thead>
	<tr class=rowheader>
	<td align=center>".$_SESSION['lang']['blok']."</td>
	<td align=center>".$_SESSION['lang']['brondolan']."</td>
	<td align=center>".$_SESSION['lang']['kgbjr']."</td>
	<td align=center>".$_SESSION['lang']['janjang']."</td>
	</tr>
	</thead><tbody>
	";
	/*$sPrestasi="select b.namakegiatan,b.satuan,a.hasilkerja,a.jumlahhk,c.tipetransaksi from ".$dbname.".kebun_prestasi a left join ".$dbname.".setup_kegiatan b on a.kodekegiatan=b.kodekegiatan left join ".$dbname.".kebun_aktifitas c on a.notransaksi=c.notransaksi where a.kodeorg='".$kdBlok."' and c.tanggal='".$tgl."'";*/
	$sPrestasi="select * from ".$dbname.".kebun_spbdt where nospb='".$nospb."'";
	$qPrestasi=$owlPDO->query($sPrestasi) or die(print " Gagal: ".PDOException::getMessage());
	$qPrestasi->setFetchMode(PDO::FETCH_ASSOC);
	$bPrestasi=owlBaris($qPrestasi);
	if($bPrestasi>0)
	{
		while($rPrestasi=$qPrestasi->fetch())
		{
			$no+=1;
			echo"<tr  align=center class=rowcontent onclick=\"closeBlok(".$brsKe.",".$endKe.")\">
			<td>".$rPrestasi['blok']."</td>
			<td>".$rPrestasi['kgbjr']."</td>
			<td>".$rPrestasi['brondolan']."</td>
			<td align=right>".$rPrestasi['jjg']."</td>
			</tr>";
			$total+=$rPrestasi['jjg'];
		}
		echo"<tr class=\"rowcontent\"><td colspan=3>Total</td><td align=right>".number_format($total,2)."</td></tr>";
		
	}
	else
	{
		echo"<tr class=rowcontent onclick=\"closeBlok(".$brsKe.",".$endKe.")\"><td colspan=5>Data Kosong</td></tr>";
	}
	echo"</tbody></table>";
	break;
	case'preview':
            
            
           
            
        $where='';
            
            
            
        if($tipeIntex!='')
        {
            if($tipeIntex==0)
            {
                if($unit=='')
                {
                    $where.=" and intex='0'";
                }
                else
                {
                    $where.=" and kodecustomer='".$unit."'";
                }
            }
            else if ($tipeIntex==1)
            {
                if($unit=='')
                {
                    $where.=" and kodeorg in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' "
                            . " and induk ='".$_SESSION['org']['kodeorganisasi']."') ";
                }
                else
                {
                    $where.=" and kodeorg='".$unit."'";
                }
            }
            else
            {
                if($unit=='')
                {
                    $where.=" and kodeorg in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' "
                            . " and induk <> '".$_SESSION['org']['kodeorganisasi']."') ";
                }
                else
                {
                    $where.=" and kodeorg='".$unit."'";
                }
            }
        }
        else
        {
            echo"warning: Pilih salah satu Sumber TBS";
            exit();
        }
	
            
        
      
        /*if($tipeIntex!='')
	{
            $where.=" and intex='".$tipeIntex."'";
	}
        
        
	else
	{
		echo"warning:Pilih salah satu Sumber TBS";
		exit();
	}
	if($unit!="")
	{
		if($tipeIntex==0)
		{
			$where.=" and kodecustomer='".$unit."'";
		}
		elseif($tipeIntex!=0)
		{
			$where.=" and kodeorg='".$unit."' ";
		}
	}*/
            
	if($periode!='')
	{
		$where.=" and tanggal like '%".$periode."%'";
	}
	

	echo"<table cellspacing=1 border=0 class=sortable>
	<thead class=rowheader>
	<tr>
		<td  align=center>No.</td>
		";
        
        if($unit!='')
        {
            echo"<td align=center>".$_SESSION['lang']['kodeorg']."</td>";
        }echo"
        
		<td align=center>".$_SESSION['lang']['tanggal']."</td>
		<td align=center>".$_SESSION['lang']['janjang']."</td>
		<td align=center>".$_SESSION['lang']['beratBersih']." (Kg)</td>
	</tr>
	</thead>
	<tbody>";
	
	$sData="select kodeorg,sum(jumlahtandan1) as jjg,sum(beratbersih) as netto,kodecustomer,tanggal "
                . "from ".$dbname.".pabrik_timbangan where kodebarang='40000003' "
                . " ".$where." group by substr(tanggal,1,10)";
    $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
	$qData->setFetchMode(PDO::FETCH_ASSOC);
	$brs=owlBaris($qData);
	if($brs>0)
	{
		
		while($rData=$qData->fetch())
		{	
			$no+=1;
			$stat="";
			$isi="";
			if($tipeIntex!=0)
			{
				$sNm="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$rData['kodeorg']."'";
				$qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
				$qNm->setFetchMode(PDO::FETCH_ASSOC);
				$rNm=$qNm->fetch();
				$nm=$rNm['namaorganisasi'];
				$kd=$rData['kodeorg'];
				$isi=" value=".$kd."";
			} else {
				$sNm="select namasupplier from ".$dbname.".log_5supplier where kodetimbangan='".$rData['kodecustomer']."'";
				$qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
				$qNm->setFetchMode(PDO::FETCH_ASSOC);
				$rNm=$qNm->fetch();
				$nm=$rNm['namasupplier'];	
			}
			
			echo"
			<tr class=rowcontent id=row_".$no." ".$stat.">
			<td align=center>".$no."</td>";
                        if($unit!='')
                        {
                            echo"<td id=kdOrg_".$no." ".$isi.">".$nm."</td>";
			}
                        echo"
                        <td id=tanggal_".$no." value=".$rData['tanggal'].">".tanggalnormal($rData['tanggal'])."</td>
			<td  align=right>".number_format($rData['jjg'])."</td>
			<td align=right>".number_format($rData['netto'],2)."</td>
			</tr>";
			setIt($subtota,0);
			$subtota+=$rData['netto'];
			$subtota2+=$rData['jjg'];
		}
		if($unit!='')
                {
                    echo"<tr class=rowcontent ><td colspan=3 align=right>Total (Kg)</td>
					<td align=right>".number_format($subtota2)."</td>
					<td align=right>".number_format($subtota,2)."</td></tr>";
	
                }
                else
                {
                    echo"<tr class=rowcontent ><td colspan=2 align=right>Total (Kg)</td>
					<td align=right>".number_format($subtota2)."</td>
					<td align=right>".number_format($subtota,2)."</td></tr>";
	
                }
                
        }
	else
	{
            
		echo"<tr class=rowcontent><td colspan=5 align=center>Data Kosong</td></tr>";
	}
	break;
	case'pdf':
	$periode=$_GET['periode'];
	$tipeIntex=$_GET['tipeIntex'];
	$unit=$_GET['unit'];
	
	
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
                $this->SetFont('Arial','B',11);
                $this->Cell($width,$height, strtoupper($_SESSION['lang']['rProdKebun']),0,1,'C');	
			 	$this->SetFont('Arial','',8);
			 	$this->Cell($width,$height, "Periode : ".$tanggal,0,1,'C');	
				$this->Ln();$this->Ln();
                $this->SetFont('Arial','B',7);	
                $this->SetFillColor(220,220,220);
				
				$this->Cell(3/100*$width,$height,'No',1,0,'C',1);
                                if($unit!='')
                                {
                                    $this->Cell(18/100*$width,$height,$_SESSION['lang']['kodeorg'],1,0,'C',1);
                                }
						
				$this->Cell(12/100*$width,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);		
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['janjang'],1,0,'C',1);			
				$this->Cell(15/100*$width,$height,$_SESSION['lang']['beratBersih']." (KG)",1,1,'C',1);	            
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
		$pdf->SetFillColor(255,255,255);
		$pdf->SetFont('Arial','',7);
		/*if($tipeIntex!='')
		{
			$where.=" and intex='".$tipeIntex."'";
		}
		else
		{
			echo"warning:Pilih salah satu Sumber TBS";
			exit();
		}
		if($unit!="")
		{
			if($tipeIntex==0)
			{
			$where.=" and kodecustomer='".$unit."'";
			}
			elseif($tipeIntex!=0)
			{
			$where.=" and kodeorg='".$unit."' ";
			}
		}*/
                
                if($tipeIntex!='')
                {
                    if($tipeIntex==0)
                    {
                        if($unit=='')
                        {
                            $where.=" and intex='0'";
                        }
                        else
                        {
                            $where.=" and kodecustomer='".$unit."'";
                        }
                    }
                    else if ($tipeIntex==1)
                    {
                        if($unit=='')
                        {
                            $where.=" and kodeorg in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' "
                                    . " and induk ='".$_SESSION['org']['kodeorganisasi']."') ";
                        }
                        else
                        {
                            $where.=" and kodeorg='".$unit."'";
                        }
                    }
                    else
                    {
                        if($unit=='')
                        {
                            $where.=" and kodeorg in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' "
                                    . " and induk <> '".$_SESSION['org']['kodeorganisasi']."') ";
                        }
                        else
                        {
                            $where.=" and kodeorg='".$unit."'";
                        }
                    }
                }
                else
                {
                    echo"warning:Pilih salah satu Sumber TBS";
                    exit();
                }
                
                
		if($periode!='')
		{
			$where.=" and tanggal like '%".$periode."%'";
		}
		
		$sList="select kodeorg,sum(jumlahtandan1) as jjg,sum(beratbersih) as netto,kodecustomer,tanggal from ".$dbname.".pabrik_timbangan where kodebarang='40000003' ".$where." group by substr(tanggal,1,10)";
		$qList=$owlPDO->query($sList) or die(print " Gagal: ".PDOException::getMessage());
		$qList->setFetchMode(PDO::FETCH_ASSOC);
		while($rData=$qList->fetch())
		{			
			if($tipeIntex!=0)
			{
				$sNm="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$rData['kodeorg']."'";
				$qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
				$qNm->setFetchMode(PDO::FETCH_ASSOC);
				$rNm=$qNm->fetch();
				$nm=$rNm['namaorganisasi'];
				$kd=$rData['kodeorg'];
				$isi=" value=".$kd."";
				
				$stat=" onclick=getAfd(".$no.") style=\"cursor: pointer;\"";
	
			}
			else
			{
				$sNm="select namasupplier from ".$dbname.".log_5supplier where kodetimbangan='".$rData['kodecustomer']."'";
				$qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
				$qNm->setFetchMode(PDO::FETCH_ASSOC);
				$rNm=$qNm->fetch();
				$nm=$rNm['namasupplier'];	
				$stat="";	
				$isi="";		
			}
			$no+=1;
			$pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);
                        if($unit!='')
                        {
                            $pdf->Cell(18/100*$width,$height,$nm,1,0,'C',1);
                        }
					
			$pdf->Cell(12/100*$width,$height,tanggalnormal($rData['tanggal']),1,0,'C',1);		
			$pdf->Cell(10/100*$width,$height,number_format($rData['jjg']),1,0,'R',1);			
			$pdf->Cell(15/100*$width,$height,number_format($rData['netto'],2),1,1,'R',1);
			setIt($subtota,0);
			$subtota+=$rData['netto'];
		}
                if($unit!='')
                {
                    $pdf->Cell(43/100*$width,$height,"Total",1,0,'C',1);
                }
                else
                {
                    $pdf->Cell(25/100*$width,$height,"Total",1,0,'C',1);
                }
		
		$pdf->Cell(15/100*$width,$height,number_format($subtota,2),1,1,'R',1);
		
			
    $pdf->Output();
	break;
	case'excel':
	$periode=$_GET['periode'];
	$tipeIntex=$_GET['tipeIntex'];
	$unit=$_GET['unit'];
	$tglPeriode=explode("-",$periode);
	$tanggal=$tglPeriode[1]."-".$tglPeriode[0];
	$where="";
	/*if($tipeIntex!='')
	{
		$where.=" and intex='".$tipeIntex."'";
	}
	else
	{
		echo"warning:Pilih salah satu Sumber TBS";
		exit();
	}
	if($unit!="")
	{
		if($tipeIntex==0)
		{
			$where.=" and kodecustomer='".$unit."'";
		}
		elseif($tipeIntex!=0)
		{
			$where.=" and kodeorg='".$unit."' ";
		}
	}*/
        
        if($tipeIntex!='')
        {
            if($tipeIntex==0)
            {
                if($unit=='')
                {
                    $where.=" and intex='0'";
                }
                else
                {
                    $where.=" and kodecustomer='".$unit."'";
                }
            }
            else if ($tipeIntex==1)
            {
                if($unit=='')
                {
                    $where.=" and kodeorg in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' "
                            . " and induk ='".$_SESSION['org']['kodeorganisasi']."') ";
                }
                else
                {
                    $where.=" and kodeorg='".$unit."'";
                }
            }
            else
            {
                if($unit=='')
                {
                    $where.=" and kodeorg in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' "
                            . " and induk <> '".$_SESSION['org']['kodeorganisasi']."') ";
                }
                else
                {
                    $where.=" and kodeorg='".$unit."'";
                }
            }
        }
        else
        {
            echo"warning:Pilih salah satu Sumber TBS";
            exit();
        }
        
	if($periode!='')
	{
		$where.=" and tanggal like '%".$periode."%'";
	}
	$tab="<table cellspacing=1 border=0>
	<tr><td colspan=5 align=center>".strtoupper($_SESSION['lang']['rProdKebun'])."</td></tr>
	<tr><td colspan=2  align=left>Periode</td><td colspan=3 align=left>".$tanggal."</td></tr>
	</table>
	";

	$tab.="<table cellspacing=1 border=1 class=sortable>
	<thead >
	<tr class=rowheader>
		<td align=center bgcolor=#DEDEDE>No.</td>";
	if($unit!='')
        {
            $tab.="<td align=center bgcolor=#DEDEDE>".$_SESSION['lang']['kodeorg']."</td>";
        }
	$tab.="<td align=center bgcolor=#DEDEDE>".$_SESSION['lang']['tanggal']."</td>";
	$tab.="<td align=center bgcolor=#DEDEDE>".$_SESSION['lang']['janjang']."</td>";
	$tab.="<td align=center bgcolor=#DEDEDE>".$_SESSION['lang']['beratBersih']." (KG)</td>";
	$tab.="
        </tr>
	</thead>
	<tbody>";
	
	$sData="select kodeorg,sum(jumlahtandan1) as jjg,sum(beratbersih) as netto,kodecustomer,tanggal from ".$dbname.".pabrik_timbangan where kodebarang='40000003' ".$where." group by substr(tanggal,1,10)";
	$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
	$qData->setFetchMode(PDO::FETCH_ASSOC);
	$brs=owlBaris($qData);
	if($brs>0)
	{
		
		while($rData=$qData->fetch())
		{	
			$no+=1;
			
			if($tipeIntex!=0)
			{
				$sNm="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$rData['kodeorg']."'";
				$qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
				$qNm->setFetchMode(PDO::FETCH_ASSOC);
				$rNm=$qNm->fetch();
				$nm=$rNm['namaorganisasi'];
				$kd=$rData['kodeorg'];
				$isi=" value=".$kd."";
				
				$stat=" onclick=getAfd(".$no.") style=\"cursor: pointer;\"";
	
			}
			else
			{
				$sNm="select namasupplier from ".$dbname.".log_5supplier where kodetimbangan='".$rData['kodecustomer']."'";
				$qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
				$qNm->setFetchMode(PDO::FETCH_ASSOC);
				$rNm=$qNm->fetch();
				$nm=$rNm['namasupplier'];	
				$stat="";	
				$isi="";		
			}
			
			$tab.="
			<tr class=rowcontent id=row_".$no." ".$stat.">
			<td align=center>".$no."</td>";
                        
                        if($unit!='')
                        {
                           $tab.="<td id=kdOrg_".$no." ".$isi.">".$nm."</td>";
                        }
                        
                        $tab.="    
			
			<td id=tanggal_".$no.">".tanggalnormal($rData['tanggal'])."</td>
			<td  align=right>".number_format(number_format($rData['jjg']))."</td>
			<td align=right>".number_format($rData['netto'],2)."</td>
			</tr>";
			setIt($subtota,0);
			$subtota+=$rData['netto'];
		}
                if($unit!='')
                {
                    $tab.="<tr class=rowcontent ><td colspan=4 align=right>Total (KG)</td><td align=right>".number_format($subtota,2)."</td></tr>";
	    
                }
                else
                {
                    $tab.="<tr class=rowcontent ><td colspan=3 align=right>Total (KG)</td><td align=right>".number_format($subtota,2)."</td></tr>";
	
                }
        }
	else
	{
		$tab.="<tr class=rowcontent><td colspan=5 align=center>Data Kosong</td></tr>";
	}
	
			
			//echo "warning:".$strx;
			//=================================================

			
			$tab.="</tbody></table>Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
			$tglSkrg=date("Ymd");
			$nop_="LaporanProduksi_".$unit."_".$periode;
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