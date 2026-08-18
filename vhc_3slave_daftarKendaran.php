<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$kdKbn=checkPostGet('kdKbn','');
$klpmkVhc=checkPostGet('klpmkVhc','');
if($_SESSION['language']=='EN'){
    $arrKlmpk=array("KD"=>"Vehicle","MS"=>"Machinery","AB"=>"Heavy Equipment");
}else{
        $arrKlmpk=array("KD"=>"Kendaraan","MS"=>"Mesin","AB"=>"Alat Berat");
}

##sesuai detail akses
$whrdetailakses = " and left(kodetraksi,4) in (".getOrgDetail(2).")";

switch($proses)
{
	case'preview':
	echo"<table class=sortable cellspacing=1 border=0 cellpadding=5>
	<thead>
 		<tr class=rowheader>
		  <th align=center>No</th>
		   <th align=center>".$_SESSION['lang']['kodeorganisasi']."</th>		 
		   <th align=center>".str_replace(" ","<br>",$_SESSION['lang']['jenkendabmes'])."</th>
		   <th align=center>".$_SESSION['lang']['kodevhc']."</th>		
		   <th align=center>".$_SESSION['lang']['nopol']."</th>		
           <th align=center>".$_SESSION['lang']['namabarang']."</th>		
		   <th align=center>".$_SESSION['lang']['tahunperolehan']."</th>
		   <th align=center>".$_SESSION['lang']['noakun']."</th>
		   <th align=center>".$_SESSION['lang']['beratkosong']."</th>
		   <th align=center>".$_SESSION['lang']['nomorrangka']."</th>
		   <th align=center>".$_SESSION['lang']['nomormesin']."</th>
		   <th align=center>".$_SESSION['lang']['detail']."</th>	   
		   <th align=center>".$_SESSION['lang']['kepemilikan']."</th>
		  </tr>
		   </thead><tbody>
	";
	// exit('warning:'.print_r(getOrgDetail(2)));

	if(($kdKbn!='0')&&($klpmkVhc!='0'))
	{
		$sql="select * from ".$dbname.".vhc_5master where kodetraksi='".$kdKbn."' and kelompokvhc='".$klpmkVhc."' order by kodetraksi,kodevhc";
	}
	elseif(($kdKbn!='0')&&($klpmkVhc=='0'))
	{
		$sql="select * from ".$dbname.".vhc_5master where kodetraksi ='".$kdKbn."' order by kodetraksi,kodevhc";
	}
	elseif(($kdKbn=='0')&&($klpmkVhc!='0'))
	{
		$sql="select * from ".$dbname.".vhc_5master where kelompokvhc='".$klpmkVhc."' order by kodetraksi,kodevhc";
	}
	elseif(($kdKbn=='0')&&($klpmkVhc=='0'))
	{
		$sql="select * from ".$dbname.".vhc_5master where 1=1 ".$whrdetailakses." order by kodetraksi,kodevhc";
	}
		// echo $sql;
        $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
        $row=owlBaris($query);
	if($row>0)
	{
            $query->setFetchMode(PDO::FETCH_ASSOC);
            while($res=$query->fetch())
			{
			$sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$res['kodebarang']."'";
            $qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
            $qBrg->setFetchMode(PDO::FETCH_ASSOC);
            $rBrg=$qBrg->fetch();
			$no+=1;		
			$namabarang=$rBrg['namabarang'];
			
			$sJnsvhc="select namajenisvhc from ".$dbname.".vhc_5jenisvhc where jenisvhc='".$res['jenisvhc']."'";
            $qJnsVhc=$owlPDO->query($sJnsvhc) or die(print " Gagal: ".PDOException::getMessage());
            $qJnsVhc->setFetchMode(PDO::FETCH_ASSOC);
            $rJnsVhc=$qJnsVhc->fetch();
			

			
			if($res['kepemilikan']==1)
			{
			  $dptk=$_SESSION['lang']['miliksendiri'];	
			}
			else
			{
				$dptk=$_SESSION['lang']['sewa'];
			}		
			echo"<tr class=rowcontent>
				 <td align=center>".$no."</td>
				 <td align=center>".$res['kodeorg']."</td>				 
				 <td>".$rJnsVhc['namajenisvhc']."</td>			 		
				 <td>".$res['kodevhc']."</td>
				 <td>".$res['nopol']."</td>
				 <td>".$namabarang."</td>
				 <td align=center>".$res['tahunperolehan']."</td>
				 <td>".$res['noakun']."</td>
				 <td>".$res['beratkosong']."</td>		
				 <td>".$res['nomorrangka']."</td>	
				 <td>".$res['nomormesin']."</td> 
				 <td>".$res['detailvhc']."</td> 	
				 <td>".$dptk."</td>		
			</tr>
			";
		}
	}
	else
	{
		echo"<tr class=rowcontent><td colspan=13 align=center>Not Found</td></tr>";
	}
	echo"</tbody></table>";
	break;
	case'pdf':
	$kdKbn=$_GET['kdKbn'];
	if ($kdKbn==0) {
		exit('warning: Kode Traksi Wajib Diisi!');
	}
	$klpmkVhc=$_GET['klpmkVhc'];
	 class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
				global $kdKbn;
				global $klpmkVhc;
				global $sDet;
				global $arrKlmpk;
				
			    # Alamat & No Telp
                $arrHead = setheadreport(substr($kdKbn,0,4));
				
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
                
                $this->SetFont('Arial','B',12);
				$this->SetFont('Arial','',8);
				if(($kdKbn!='0')&&($klpmkVhc!='0'))
				{
					$sDet="select * from ".$dbname.".vhc_5master where kodetraksi ='".$kdKbn."' and kelompokvhc='".$klpmkVhc."' order by kodetraksi,kodevhc";
					$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['unitkerja'],'',0,'L');
					$this->Cell(5,$height,':','',0,'L');
					$this->Cell(45/100*$width,$height,$kdKbn,'',0,'L');
					$this->Ln();
					$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['kodekelompok'],'',0,'L');
					$this->Cell(5,$height,':','',0,'L');
					$this->Cell(45/100*$width,$height,$arrKlmpk[$klpmkVhc],'',0,'L');
					$this->Ln();					
				}
				elseif(($kdKbn!='0')&&($klpmkVhc=='0'))
				{
					$sDet="select * from ".$dbname.".vhc_5master where kodetraksi ='".$kdKbn."' order by kodetraksi,kodevhc";
					$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['unitkerja'],'',0,'L');
					$this->Cell(5,$height,':','',0,'L');
					$this->Cell(45/100*$width,$height,$kdKbn,'',0,'L');
					$this->Ln();
				}
				elseif(($kdKbn=='0')&&($klpmkVhc!='0'))
				{
					##sesuai detail akses
					$whrdetailakses = " and left(kodetraksi,4) in (".getOrgDetail(2).")";

					$sDet="select * from ".$dbname.".vhc_5master where kelompokvhc='".$klpmkVhc."' ".$whrdetailakses." order by kodetraksi,kodevhc";
					$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['kodekelompok'],'',0,'L');
					$this->Cell(5,$height,':','',0,'L');
					$this->Cell(45/100*$width,$height,$arrKlmpk[$klpmkVhc],'',0,'L');
					$this->Ln();
				}
				elseif(($kdKbn=='0')&&($klpmkVhc=='0'))
				{
					##sesuai detail akses
					$whrdetailakses = " and left(kodetraksi,4) in (".getOrgDetail(2).")";
					
					$sDet="select * from ".$dbname.".vhc_5master where 1=1 ".$whrdetailakses." order by kodetraksi,kodevhc";
				}
			
                $this->SetFont('Arial','U',12);
                $this->Cell($width,$height, strtoupper($_SESSION['lang']['datamesinkendaraan']),0,1,'C');	
                $this->Ln();	
				
                $this->SetFont('Arial','B',7);	
                $this->SetFillColor(220,220,220);
	
				
				$this->Cell(3/100*$width,$height,'No',1,0,'C',1);
				if($kdKbn=='0')
				{
					$this->Cell(8/100*$width,$height,$_SESSION['lang']['kodeorganisasi'],1,0,'C',1);	
				}
				if($klpmkVhc=='0')
				{
					$this->Cell(8/100*$width,$height,$_SESSION['lang']['kodekelompok'],1,0,'C',1);	
				}
				$this->Cell(17/100*$width,$height,$_SESSION['lang']['jenkendabmes'],1,0,'C',1);		
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['kodenopol'],1,0,'C',1);		
				$this->Cell(11/100*$width,$height,$_SESSION['lang']['namabarang'],1,0,'C',1);		
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['tahunperolehan'],1,0,'C',1);
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['noakun'],1,0,'C',1);	
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['beratkosong'],1,0,'C',1);	
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['nomorrangka'],1,0,'C',1);	
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['nomormesin'],1,0,'C',1);				
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['kepemilikan'],1,1,'C',1);					
            }
                
            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
        $pdf=new PDF('L','pt','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
		$pdf->AddPage();
		$pdf->SetFillColor(255,255,255);
		$pdf->SetFont('Arial','',7);
	
                $qDet=$owlPDO->query($sDet) or die(print " Gagal: ".PDOException::getMessage());
                $qDet->setFetchMode(PDO::FETCH_ASSOC);
                while($rDet=$qDet->fetch())
		{
			$no+=1;
			$sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$rDet['kodebarang']."'";
                        $qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
                        $qBrg->setFetchMode(PDO::FETCH_ASSOC);
                        $rBrg=$qBrg->fetch();
			
			$sJnsvhc="select namajenisvhc from ".$dbname.".vhc_5jenisvhc where jenisvhc='".$rDet['jenisvhc']."'";
                        $qJnsVhc=$owlPDO->query($sJnsvhc) or die(print " Gagal: ".PDOException::getMessage());
                        $qJnsVhc->setFetchMode(PDO::FETCH_ASSOC);
                        $rJnsVhc=$qJnsVhc->fetch();
			
			$pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);
			if($kdKbn=='0')
			{
				$pdf->Cell(8/100*$width,$height,$rDet['kodeorg'],1,0,'C',1);	
			}
			if($klpmkVhc=='0')
			{
				$pdf->Cell(8/100*$width,$height,$arrKlmpk[$rDet['kelompokvhc']],1,0,'L',1);	
			}
			if($rDet['kepemilikan']==1)
			{
			  $dptk=$_SESSION['lang']['miliksendiri'];	
			}
			else
			{
				$dptk=$_SESSION['lang']['sewa'];
			}		
			$pdf->Cell(17/100*$width,$height,$rJnsVhc['namajenisvhc'],1,0,'L',1);		
			$pdf->Cell(8/100*$width,$height,$rDet['kodevhc'],1,0,'L',1);		
			$pdf->Cell(11/100*$width,$height,$rBrg['namabarang'],1,0,'L',1);		
			$pdf->Cell(8/100*$width,$height,$rDet['tahunperolehan'],1,0,'L',1);
			$pdf->Cell(8/100*$width,$height,$rDet['noakun'],1,0,'L',1);	
			$pdf->Cell(8/100*$width,$height,$rDet['beratkosong'],1,0,'L',1);	
			$pdf->Cell(8/100*$width,$height,$rDet['nomorrangka'],1,0,'L',1);	
			$pdf->Cell(8/100*$width,$height,$rDet['nomormesin'],1,0,'L',1);				
			$pdf->Cell(8/100*$width,$height,$dptk,1,1,'L',1);		
		}
			
        $pdf->Output();
	break;
	case'excel':
	$kdKbn=$_GET['kdKbn'];
	$klpmkVhc=$_GET['klpmkVhc'];
	if(($kdKbn!='0')&&($klpmkVhc!='0'))
	{
		$sql="select * from ".$dbname.".vhc_5master where kodetraksi='".$kdKbn."' and kelompokvhc='".$klpmkVhc."' order by kodetraksi,kodevhc";
		$tbl="<tr><td colspan=3>".$_SESSION['lang']['unitkerja']."</td><td>".$kdKbn."</td></tr>
			<tr><td colspan=3>".$_SESSION['lang']['kodekelompok']."</td><td>".$klpmkVhc."</td></tr>";
	}
	elseif(($kdKbn!='0')&&($klpmkVhc=='0'))
	{
		$sql="select * from ".$dbname.".vhc_5master where kodetraksi='".$kdKbn."' order by kodetraksi,kodevhc";
		$tbl="<tr><td colspan=3>".$_SESSION['lang']['unitkerja']."</td><td>".$kdKbn."</td></tr>";
	}
	elseif(($kdKbn=='0')&&($klpmkVhc!='0'))
	{
		$sql="select * from ".$dbname.".vhc_5master where kelompokvhc='".$klpmkVhc."' order by kodetraksi,kodevhc";
		$tbl="<tr><td colspan=3>".$_SESSION['lang']['kodekelompok']."</td><td>".$klpmkVhc."</td></tr>";
	}
	elseif(($kdKbn=='0')&&($klpmkVhc=='0'))
	{
		$sql="select * from ".$dbname.".vhc_5master where 1=1 ".$whrdetailakses." order by kodetraksi,kodevhc";
		$tbl="";
	}
			
			
			
			$stream="
			<table>
			<tr><td colspan=13 align=center>".strtoupper($_SESSION['lang']['datamesinkendaraan'])."</td></tr>
			".$tbl."
			<tr><td colspan=3></td><td></td></tr>
			</table>
			<table border=1>
			<tr>
				<td bgcolor=#DEDEDE align=center valign=top>No.</td>
				<td bgcolor=#DEDEDE align=center valign=top>".$_SESSION['lang']['kodeorganisasi']."</td>
				<td bgcolor=#DEDEDE align=center valign=top>".str_replace(" ","<br>",$_SESSION['lang']['jenkendabmes'])."</td>
				<td bgcolor=#DEDEDE align=center valign=top>".$_SESSION['lang']['kodevhc']."</td>
				<td bgcolor=#DEDEDE align=center valign=top>".$_SESSION['lang']['nopol']."</td>	
				<td bgcolor=#DEDEDE align=center valign=top>".$_SESSION['lang']['namabarang']."</td>	
				<td bgcolor=#DEDEDE align=center valign=top>".$_SESSION['lang']['tahunperolehan']."</td>	
				<td bgcolor=#DEDEDE align=center valign=top>".$_SESSION['lang']['noakun']."</td>		
				<td bgcolor=#DEDEDE align=center valign=top>".$_SESSION['lang']['beratkosong']."</td>		
				<td bgcolor=#DEDEDE align=center valign=top>".$_SESSION['lang']['nomorrangka']."</td>		
				<td bgcolor=#DEDEDE align=center valign=top>".$_SESSION['lang']['nomormesin']."</td>	
				<td bgcolor=#DEDEDE align=center valign=top>".$_SESSION['lang']['detail']."</td>
				<td bgcolor=#DEDEDE align=center valign=top>".$_SESSION['lang']['kepemilikan']."</td>	
			</tr>";

        $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
        $row=owlBaris($query);
	if($row>0)
	{
            $query->setFetchMode(PDO::FETCH_ASSOC);
            while($res=$query->fetch())
            {
			$sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$res['kodebarang']."'";
            $qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
            $qBrg->setFetchMode(PDO::FETCH_ASSOC);
            $rBrg=$qBrg->fetch();
			$no+=1;
		
			$namabarang=$rBrg['namabarang'];

            $sJnsvhc="select namajenisvhc from ".$dbname.".vhc_5jenisvhc where jenisvhc='".$res['jenisvhc']."'";
            $qJnsVhc=$owlPDO->query($sJnsvhc) or die(print " Gagal: ".PDOException::getMessage());
            $qJnsVhc->setFetchMode(PDO::FETCH_ASSOC);
            $rJnsVhc=$qJnsVhc->fetch();
			
			
			if($res['kepemilikan']==1)
			{
			  $dptk=$_SESSION['lang']['miliksendiri'];	
			}
			else
			{
				$dptk=$_SESSION['lang']['sewa'];
			}		
			// $stream.="<tr class=rowcontent>
			// 	 <td>".$no."</td>
			// 	 <td>".$res['kodeorg']."</td>
			// 	 <td>".$arrKlmpk[$res['kelompokvhc']]."</td>				 
			// 	 <td>".$res['jenisvhc']."</td>			 		
			// 	 <td>".$res['kodevhc']."</td>
			// 	 <td>".$namabarang."</td>
			// 	 <td>".$res['tahunperolehan']."</td>
			// 	 <td>".$res['noakun']."</td>
			// 	 <td>".$res['beratkosong']."</td>		
			// 	 <td>".$res['nomorrangka']."</td>	
			// 	 <td>".$res['nomormesin']."</td> 
			// 	 <td>".$res['detailvhc']."</td> 	
			// 	 <td>".$dptk."</td>		
			// </tr>
			// ";

			$stream.="<tr class=rowcontent>
				 <td>".$no."</td>
				 <td>".$res['kodeorg']."</td>				 
				 <td>".$rJnsVhc['namajenisvhc']."</td>			 		
				 <td>".$res['kodevhc']."</td>
				 <td>".$res['nopol']."</td>
				 <td>".$namabarang."</td>
				 <td>".$res['tahunperolehan']."</td>
				 <td>".$res['noakun']."</td>
				 <td>".$res['beratkosong']."</td>		
				 <td>".$res['nomorrangka']."</td>	
				 <td>".$res['nomormesin']."</td> 
				 <td>".$res['detailvhc']."</td> 	
				 <td>".$dptk."</td>		
			</tr>
			";
		}
	}
	else
	{
		$stream.="<tr class=rowcontent><td colspan=13 align=center>Not Found</td></tr>";
	}
	$stream.="</tbody></table>";
	
			
			//echo "warning:".$strx;
			//=================================================
		$stream.="Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
			
			$nop_="daftarKendaraan";
			if(strlen($stream)>0)
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
			if(!fwrite($handle,$stream))
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
			//closedir($handle);
			}
	break;
	case'getDetail':
	echo"<link rel=stylesheet type=text/css href=style/generic.css>";
	$nokontrak=$_GET['nokontrak'];
	$sHed="select  a.tanggalkontrak,a.koderekanan,a.kodebarang from ".$dbname.".pmn_kontrakjual a where a.nokontrak='".$nokontrak."'";
    $qHead=$owlPDO->query($sHed) or die(print " Gagal: ".PDOException::getMessage());
    $qHead->setFetchMode(PDO::FETCH_ASSOC);
	$rHead=$qHead->fetch();
	$sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$rHead['kodebarang']."'";
	$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
    $qBrg->setFetchMode(PDO::FETCH_ASSOC);
	$rBrg=$qBrg->fetch();
	
	$sCust="select namacustomer  from ".$dbname.".pmn_4customer where kodecustomer='".$rHead['koderekanan']."'";
	$qCust=$owlPDO->query($sCust) or die(print " Gagal: ".PDOException::getMessage());
    $qCust->setFetchMode(PDO::FETCH_ASSOC);
	$rCust=$qCust->fetch();
	echo"<fieldset><legend>".$_SESSION['lang']['detailPengiriman']."</legend>
	<table cellspacing=1 border=0 class=myinputtext>
	<tr>
		<td>".$_SESSION['lang']['NoKontrak']."</td><td>:</td><td>".$nokontrak."</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tglKontrak']."</td><td>:</td><td>".tanggalnormal($rHead['tanggalkontrak'])."</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['komoditi']."</td><td>:</td><td>".$rBrg['namabarang']."</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['Pembeli']."</td><td>:</td><td>".$rCust['namacustomer']."</td>
	</tr>
	</table><br />
	<table cellspacing=1 border=0 class=sortable><thead>
	<tr class=data>
	<td>".$_SESSION['lang']['notransaksi']."</td>
	<td>".$_SESSION['lang']['tanggal']."</td>
	<td>".$_SESSION['lang']['nodo']."</td>
	<td>".$_SESSION['lang']['nosipb']."</td>
	<td>".$_SESSION['lang']['beratBersih']."</td>
	<td>".$_SESSION['lang']['kodenopol']."</td>
	<td>".$_SESSION['lang']['sopir']."</td>
	</tr></thead><tbody>
	";
	$sDet="select notransaksi,tanggal,nodo,nosipb,beratbersih,nokendaraan,supir from ".$dbname.".pabrik_timbangan where nokontrak='".$nokontrak."'";
	$qDet=$owlPDO->query($sDet) or die(print " Gagal: ".PDOException::getMessage());
    $qDet->setFetchMode(PDO::FETCH_ASSOC);	
	$rCek=owlBaris($qDet);
	if($rCek>0)
	{
		while($rDet=$qDet->fetch())
		{
			echo"<tr class=rowcontent>
			<td>".$rDet['notransaksi']."</td>
			<td>".tanggalnormal($rDet['tanggal'])."</td>
			<td>".$rDet['nodo']."</td>
			<td>".$rDet['nosipb']."</td>
			<td align=right>".number_format($rDet['beratbersih'],2)."</td>
			<td>".$rDet['nokendaraan']."</td>
			<td>".ucfirst($rDet['supir'])."</td>
			</tr>";
		}
	}
	else
	{
		echo"<tr><td colspan=7>Not Found</td></tr>";
	}
	echo"</tbody></table></fieldset>";

	break;
	default:
	break;
}
?>