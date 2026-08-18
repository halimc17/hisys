<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=$_GET['proses'];
$periode=checkPostGet('periode','');
$idKebun=checkPostGet('idKebun','');


$namaOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

switch($proses)
{
	case'preview':
	
	
		if($periode=='')
		{
			echo "warning : Periode masih kosong";
			exit();	
		}

		$where = "";
		if ($idKebun != '') {
			$where .= "and a.kodeorg='".$idKebun."'";
		}

		echo" <table class=sortable cellspacing=1 border=0>
		<thead>
		<tr class=rowheader>
		<th align=center >".substr($_SESSION['lang']['nomor'],0,2).".</th>
		<th align=center >".$_SESSION['lang']['nospb']."</th>
		<th align=center >".$_SESSION['lang']['tanggal']."</th>
		<th align=center >".$_SESSION['lang']['status']." Posting</th>
		<th align=center >".$_SESSION['lang']['status']." Pabrik</th>
		<th align=center >".$_SESSION['lang']['nopol']."</th>
		<th align=center >".$_SESSION['lang']['kontrak']."</th>
		<th align=center >".$_SESSION['lang']['bjr']."</th>
		<th align=center >".$_SESSION['lang']['janjang']."</th>
		<th align=center >".$_SESSION['lang']['brondolan']."</th>
		<th align=center >".$_SESSION['lang']['mentah']."</th>
		<th align=center >".$_SESSION['lang']['busuk']."</th>
		<th align=center >".$_SESSION['lang']['matang']."</th>
		<th align=center >".$_SESSION['lang']['lewatmatang']."</th>
		<th align=center >".$_SESSION['lang']['kg']." ".$_SESSION['lang']['kebun']."</th>
		<th align=center >".$_SESSION['lang']['kgwb']."</th>
		<th hidden >".$_SESSION['lang']['totalkg']."</th>
		</tr>
		</thead><tbody>";
		

		/*$sql="select a.nospb,a.tanggal,a.posting,b.* from ".$dbname.".kebun_spbht a inner join ".$dbname.".kebun_spbdt b on a.nospb=b.nospb where a.tanggal like '%".$periode."%' and a.kodeorg='".$idKebun."'  order by a.tanggal asc";*/
		$sql="select a.nospb,a.tanggal,a.posting,a.tujuan from ".$dbname.".kebun_spbht a where tanggal like '%".$periode."%' ".$where." order by a.tanggal asc";
		$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
		$row=owlBaris($query);
		if($row>0)
		{
			$no=0;
			while($res=$query->fetch())
			{
				$no+=1;
				$sSpbDet="select sum(bjr) as Bjr,sum(jjg) as Janjang,sum(brondolan) as Brondolan,sum(mentah) as Mentah,sum(busuk) as Busuk,sum(matang) as Matang,sum(lewatmatang) as Lewatmatang,sum(kgbjr) as kgBjr,sum(kgwb) as kGwb,sum(totalkg) as totaLkg from ".$dbname.".kebun_spbdt_detail where nospb='".$res['nospb']."'";
				$qSpbDet=$owlPDO->query($sSpbDet) or die(print " Gagal: ".PDOException::getMessage());
				$qSpbDet->setFetchMode(PDO::FETCH_ASSOC);
				$rSpbDet=$qSpbDet->fetch();
							$srow="select blok from ".$dbname.".kebun_spbdt_detail where nospb='".$res['nospb']."'";
							$qrow=$owlPDO->query($srow) or die(print " Gagal: ".PDOException::getMessage());
							$rRow=owlBaris($qrow);
							@$bjrR=$rSpbDet['Bjr']/$rRow;

				$sSpbExt="select nokendaraan,nokontrak from ".$dbname.".pabrik_timbangan where nospb='".$res['nospb']."'";
				$qSpbDetExt=$owlPDO->query($sSpbExt) or die(print " Gagal: ".PDOException::getMessage());
				$qSpbDetExt->setFetchMode(PDO::FETCH_ASSOC);
				$rSpbDetExt=$qSpbDetExt->fetch();

				$arrPost=array($_SESSION['lang']['belumposting'],$_SESSION['lang']['posting']);
				$arrPB=array('Internal','Afiliasi','','External');
				$arr="nospb"."##".$res['nospb'];
				echo"<tr class=rowcontent onclick=\"zDetail(event,'kebun_slave_2pengangkutan.php','".$arr."')\" style='cursor:pointer;'>
				<td align=center>".$no."</td>
				<td>".$res['nospb']."</td>
				<td>".tanggalnormal($res['tanggal'])."</td>
				<td>".$arrPost[$res['posting']]."</td>	
				<td>".$arrPB[$res['tujuan']]."</td>		
				<td>".$rSpbDetExt['nokendaraan']."</td>	
				<td>".$rSpbDetExt['nokontrak']."</td>		
				<td align=\"right\">".number_format(($rSpbDet['kgBjr']/$rSpbDet['Janjang']),2)."</td>
				<td align=\"right\">".number_format($rSpbDet['Janjang'],2)."</td>
				<td align=\"right\">".number_format($rSpbDet['Brondolan'],2)."</td>
				<td align=\"right\">".number_format($rSpbDet['Mentah'],2)."</td>
				<td align=\"right\">".number_format($rSpbDet['Busuk'],2)."</td>
				<td align=\"right\">".number_format($rSpbDet['Matang'],2)."</td>
				<td align=\"right\">".number_format($rSpbDet['Lewatmatang'],2)."</td>
				<td align=\"right\">".number_format($rSpbDet['kgBjr'],2)."</td>
				<td align=\"right\">".number_format($rSpbDet['kGwb'],2)."</td>
				<td align=\"right\" hidden>".number_format($rSpbDet['totaLkg'],2)."</td>
				</tr>
				";
				setIt($grandTotal['bjrR'],0);
				setIt($grandTotal['Janjang'],0);
				setIt($grandTotal['Brondolan'],0);
				setIt($grandTotal['Mentah'],0);
				setIt($grandTotal['Busuk'],0);
				setIt($grandTotal['Matang'],0);
				setIt($grandTotal['Lewatmatang'],0);
				setIt($grandTotal['kgBjr'],0);
				setIt($grandTotal['kGwb'],0);
				setIt($grandTotal['totaLkg'],0);
				$grandTotal['bjrR'] += $bjrR;
				$grandTotal['Janjang'] += $rSpbDet['Janjang'];
				$grandTotal['Brondolan'] += $rSpbDet['Brondolan'];
				$grandTotal['Mentah'] += $rSpbDet['Mentah'];
				$grandTotal['Busuk'] += $rSpbDet['Busuk'];
				$grandTotal['Matang'] += $rSpbDet['Matang'];
				$grandTotal['Lewatmatang'] += $rSpbDet['Lewatmatang'];
				$grandTotal['kgBjr'] += $rSpbDet['kgBjr'];
				$grandTotal['kGwb'] += $rSpbDet['kGwb'];
				$grandTotal['totaLkg'] += $rSpbDet['totaLkg'];
			}
			echo"<tr class=rowcontent style='font-weight:bold'>
					<td style='text-align:center' colspan=7>TOTAL</td>
					<td style='text-align:right'>".number_format(($grandTotal['kgBjr']/$grandTotal['Janjang']),2)."</td>
					<td style='text-align:right'>".number_format($grandTotal['Janjang'],2)."</td>
					<td style='text-align:right'>".number_format($grandTotal['Brondolan'],2)."</td>
					<td style='text-align:right'>".number_format($grandTotal['Mentah'],2)."</td>
					<td style='text-align:right'>".number_format($grandTotal['Busuk'],2)."</td>
					<td style='text-align:right'>".number_format($grandTotal['Matang'],2)."</td>
					<td style='text-align:right'>".number_format($grandTotal['Lewatmatang'],2)."</td>
					<td style='text-align:right'>".number_format($grandTotal['kgBjr'],2)."</td>
					<td style='text-align:right'>".number_format($grandTotal['kGwb'],2)."</td>
					<td style='text-align:right' hidden>".number_format($grandTotal['totaLkg'],2)."</td>
				</tr>";
		}
		else
		{
			echo"<tr class=rowcontent align=center><td colspan=14>Not Found</td></tr>";
		}
		echo"</tbody></table>";
	break;
	case'pdf':
	


	
	$periode=$_GET['periode'];
	$idKebun=$_GET['idKebun'];
			if($periode=='')
		{
			echo "warning : Periode masih kosong";
			exit();	
		}

		$where = "";
		if ($idKebun != '') {
			$where .= "and a.kodeorg='".$idKebun."'";
		}
	 class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
				global $periode;
				global $kdBrg;
				global $idKebun;
				global $owlPDO;
		
				if ($idKebun != '') {
					$sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$idKebun."'";
					$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
					$qOrg->setFetchMode(PDO::FETCH_ASSOC);
					$rOrg=$qOrg->fetch();
				}
				 
                # Alamat & No Telp
                $arrHead = setheadreport(substr($idKebun,0,4));
				
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
                
/*                $this->SetFont('Arial','B',12);
				$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['laporanPengangkutan'],'',0,'L');
				$this->Ln();
*/				$this->SetFont('Arial','',7);
				$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['periode'],'',0,'L');
				$this->Cell(5,$height,':','',0,'L');
				$this->Cell(45/100*$width,$height,$periode,'',0,'L');
				$this->Ln();
				$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['kodeorg'],'',0,'L');
				$this->Cell(5,$height,':','',0,'L');
				if ($idKebun != '') {
					$this->Cell(45/100*$width,$height,$idKebun."-".$rOrg['namaorganisasi'],'',0,'L');
				} else {
					$this->Cell(45/100*$width,$height,'Seluruhnya','',0,'L');
				}
				
			
              
				
                $this->Ln();
				$this->Ln();
                $this->SetFont('Arial','U',9);
                $this->Cell($width,$height, strtoupper($_SESSION['lang']['laporanPengangkutan']),0,1,'C');	
                $this->Ln();	
				
                $this->SetFont('Arial','B',6);	
                $this->SetFillColor(220,220,220);
				
				$this->Cell(3/100*$width,$height,'No',1,0,'C',1);
				$this->Cell(15/100*$width,$height,$_SESSION['lang']['nospb'],1,0,'C',1);		
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);		
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['status'],1,0,'C',1);			
				$this->Cell(6/100*$width,$height,$_SESSION['lang']['bjr'],1,0,'C',1);		
				$this->Cell(6/100*$width,$height,$_SESSION['lang']['janjang'],1,0,'C',1);
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['brondolan'],1,0,'C',1);		
				$this->Cell(7/100*$width,$height,$_SESSION['lang']['mentah'],1,0,'C',1);		
				$this->Cell(6/100*$width,$height,$_SESSION['lang']['busuk'],1,0,'C',1);		
				$this->Cell(6/100*$width,$height,$_SESSION['lang']['matang'],1,0,'C',1);		
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['lewatmatang'],1,0,'C',1);					
				$this->Cell(6/100*$width,$height,$_SESSION['lang']['kg']." ".$_SESSION['lang']['kebun'],1,0,'C',1);					
				$this->Cell(6/100*$width,$height,$_SESSION['lang']['kgwb'],1,0,'C',1);					
				$this->Cell(6/100*$width,$height,$_SESSION['lang']['totalkg'],1,1,'C',1);					
            
            }
                
            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
        $pdf=new PDF('P','pt','A4');
		$pdf->lMargin=10;
		$pdf->rMargin=10;
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 10;
		$pdf->AddPage();
		$pdf->SetFillColor(255,255,255);
		$pdf->SetFont('Arial','',6);
		/*$sDet="select a.nospb,a.tanggal,a.posting,b.* from ".$dbname.".kebun_spbht a inner join ".$dbname.".kebun_spbdt b on a.nospb=b.nospb where a.tanggal like '%".$periode."%' and a.kodeorg='".$idKebun."' order by a.tanggal asc ";*/
		$sDet="select a.nospb,a.tanggal,a.posting from ".$dbname.".kebun_spbht a where tanggal like '%".$periode."%' ".$where." order by a.tanggal asc ";
		$qDet=$owlPDO->query($sDet) or die(print " Gagal: ".PDOException::getMessage());
		$qDet->setFetchMode(PDO::FETCH_ASSOC);
		$row=owlBaris($qDet);
		
		if($row>0)
		{
			$no=0;
			while($rDet=$qDet->fetch())
			{
				$no+=1;
				$sSpbDet="select sum(bjr) as Bjr,sum(jjg) as Janjang,sum(brondolan) as Brondolan,sum(mentah) as Mentah,sum(busuk) as Busuk,sum(matang) as Matang,sum(lewatmatang) as Lewatmatang,sum(kgbjr) as kgBjr,sum(kgwb) as kGwb,sum(totalkg) as totaLkg from ".$dbname.".kebun_spbdt_detail where nospb='".$rDet['nospb']."'";
				$qSpbDet=$owlPDO->query($sSpbDet) or die(print " Gagal: ".PDOException::getMessage());
				$qSpbDet->setFetchMode(PDO::FETCH_ASSOC);
				$rSpbDet=$qSpbDet->fetch();
							$srow="select blok from ".$dbname.".kebun_spbdt_detail where nospb='".$rDet['nospb']."'";
							$qrow=$owlPDO->query($srow) or die(print " Gagal: ".PDOException::getMessage());
							$rRow=owlBaris($qrow);
							@$bjrR=$rSpbDet['Bjr']/$rRow;
				$arrPost=array($_SESSION['lang']['belumposting'],$_SESSION['lang']['posting']);
				$arr="nospb"."##".$rDet['nospb'];
				
				$pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);
				$pdf->Cell(15/100*$width,$height,$rDet['nospb'],1,0,'L',1);		
				$pdf->Cell(8/100*$width,$height,tanggalnormal($rDet['tanggal']),1,0,'C',1);		
				$pdf->Cell(8/100*$width,$height,$arrPost[$rDet['posting']],1,0,'L',1);			
				$pdf->Cell(6/100*$width,$height,number_format(($rSpbDet['kgBjr']/$rSpbDet['Janjang']),2),1,0,'R',1);		
				$pdf->Cell(6/100*$width,$height,number_format($rSpbDet['Janjang'],2),1,0,'R',1);
				$pdf->Cell(8/100*$width,$height,number_format($rSpbDet['Brondolan'],2),1,0,'R',1);		
				$pdf->Cell(7/100*$width,$height,number_format($rSpbDet['Mentah'],2),1,0,'R',1);		
				$pdf->Cell(6/100*$width,$height,number_format($rSpbDet['Busuk'],2),1,0,'R',1);		
				$pdf->Cell(6/100*$width,$height,number_format($rSpbDet['Matang'],2),1,0,'R',1);		
				$pdf->Cell(8/100*$width,$height,number_format($rSpbDet['Lewatmatang'],2),1,0,'R',1);	
				$pdf->Cell(6/100*$width,$height,number_format($rSpbDet['kgBjr'],2),1,0,'R',1);					
				$pdf->Cell(6/100*$width,$height,number_format($rSpbDet['kGwb'],2),1,0,'R',1);					
				$pdf->Cell(6/100*$width,$height,number_format($rSpbDet['totaLkg'],2),1,1,'R',1);
				
				setIt($grandTotal['bjrR'],0);
				setIt($grandTotal['Janjang'],0);
				setIt($grandTotal['Brondolan'],0);
				setIt($grandTotal['Mentah'],0);
				setIt($grandTotal['Busuk'],0);
				setIt($grandTotal['Matang'],0);
				setIt($grandTotal['Lewatmatang'],0);
				setIt($grandTotal['kgBjr'],0);
				setIt($grandTotal['kGwb'],0);
				setIt($grandTotal['totaLkg'],0);
				$grandTotal['bjrR'] += $bjrR;
				$grandTotal['Janjang'] += $rSpbDet['Janjang'];
				$grandTotal['Brondolan'] += $rSpbDet['Brondolan'];
				$grandTotal['Mentah'] += $rSpbDet['Mentah'];
				$grandTotal['Busuk'] += $rSpbDet['Busuk'];
				$grandTotal['Matang'] += $rSpbDet['Matang'];
				$grandTotal['Lewatmatang'] += $rSpbDet['Lewatmatang'];
				$grandTotal['kgBjr'] += $rSpbDet['kgBjr'];
				$grandTotal['kGwb'] += $rSpbDet['kGwb'];
				$grandTotal['totaLkg'] += $rSpbDet['totaLkg'];
			}
			
			$pdf->Cell(34/100*$width,$height,"TOTAL",1,0,'C',1);
			$pdf->Cell(6/100*$width,$height,number_format(($grandTotal['kgBjr']/$grandTotal['Janjang']),2),1,0,'R',1);		
			$pdf->Cell(6/100*$width,$height,number_format($grandTotal['Janjang'],2),1,0,'R',1);
			$pdf->Cell(8/100*$width,$height,number_format($grandTotal['Brondolan'],2),1,0,'R',1);		
			$pdf->Cell(7/100*$width,$height,number_format($grandTotal['Mentah'],2),1,0,'R',1);		
			$pdf->Cell(6/100*$width,$height,number_format($grandTotal['Busuk'],2),1,0,'R',1);		
			$pdf->Cell(6/100*$width,$height,number_format($grandTotal['Matang'],2),1,0,'R',1);		
			$pdf->Cell(8/100*$width,$height,number_format($grandTotal['Lewatmatang'],2),1,0,'R',1);	
			$pdf->Cell(6/100*$width,$height,number_format($grandTotal['kgBjr'],2),1,0,'R',1);					
			$pdf->Cell(6/100*$width,$height,number_format($grandTotal['kGwb'],2),1,0,'R',1);					
			$pdf->Cell(6/100*$width,$height,number_format($grandTotal['totaLkg'],2),1,1,'R',1);
		}
		else
		{
			$pdf->Cell(96/100*$width,$height,"Not Found",1,1,'C',1);
		}
				
        $pdf->Output();
	break;
	case'excel':
	$periode=$_GET['periode'];
	$idKebun=$_GET['idKebun'];
	
	if($periode=='')
		{
			echo "warning : Periode masih kosong";
			exit();	
		}

	$where = "";
	if ($idKebun != '') {
		$where .= "and a.kodeorg='".$idKebun."'";
	}

	$sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$idKebun."'";
	$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
	$qOrg->setFetchMode(PDO::FETCH_ASSOC);
	$rOrg=$qOrg->fetch();
			$stream="
			<table>
			<tr><td colspan=14 align=center><b>".strtoupper($_SESSION['lang']['laporanPengangkutan'])."</b></td></tr>
			<tr><td colspan=3>".$_SESSION['lang']['periode']."</td><td>".$periode."</td></tr>";
			if ($idKebun != '') {
				$stream.="<tr><td colspan=3>".$_SESSION['lang']['kodeorg']."</td><td>".$idKebun."-".$rOrg['namaorganisasi']."</td></tr>";
			} else {
				$stream.="<tr><td colspan=3>".$_SESSION['lang']['kodeorg']."</td><td>Seluruhnya</td></tr>";
			}
			
			$stream.="<tr><td colspan=3></td><td></td></tr>
			</table>
			<table border=1>
			<tr>
				<td bgcolor=#DEDEDE align=center>No.</td>
				<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nospb']."</td>
				<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tanggal']."</td>
				<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['status']."</td>		
				<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['bjr']."</td>
				<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['janjang']."</td>
				<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['brondolan']."</td>
				<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['mentah']."</td>
				<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['busuk']."</td>
				<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['matang']."</td>
				<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['lewatmatang']."</td>
				<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kg']." ".$_SESSION['lang']['kebun']."</td>
				<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kgwb']."</td>
				<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['totalkg']."</td>			
			</tr>";
			
			$strx="select a.nospb,a.tanggal,a.posting from ".$dbname.".kebun_spbht a where tanggal like '%".$periode."%' ".$where." order by a.tanggal asc";
			$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
			$resx->setFetchMode(PDO::FETCH_ASSOC);
			$row=owlBaris($resx);
			if($row<1)
			{
			$stream.="	<tr class=rowcontent>
			<td colspan=14 align=center>Not Found</td></tr>
			";
			}
			else
			{
			$no=0;
				while($barx=$resx->fetch())
				{
				$no+=1;
				
				$sSpbDet="select sum(bjr) as Bjr,sum(jjg) as Janjang,sum(brondolan) as Brondolan,sum(mentah) as Mentah,sum(busuk) as Busuk,sum(matang) as Matang,sum(lewatmatang) as Lewatmatang,sum(kgbjr) as kgBjr,sum(kgwb) as kGwb,sum(totalkg) as totaLkg from ".$dbname.".kebun_spbdt_detail where nospb='".$barx['nospb']."'";
				$qSpbDet=$owlPDO->query($sSpbDet) or die(print " Gagal: ".PDOException::getMessage());
				$qSpbDet->setFetchMode(PDO::FETCH_ASSOC);
				$rSpbDet=$qSpbDet->fetch();
							$srow="select blok from ".$dbname.".kebun_spbdt_detail where nospb='".$barx['nospb']."'";
							$qrow=$owlPDO->query($srow) or die(print " Gagal: ".PDOException::getMessage());
							$qrow->setFetchMode(PDO::FETCH_ASSOC);
							$rRow=owlBaris($qrow);
							@$bjrR=$rSpbDet['Bjr']/$rRow;
				$arrPost=array($_SESSION['lang']['belumposting'],$_SESSION['lang']['posting']);
				$arr="nospb"."##".$barx['nospb'];
				
				$stream.="<tr class=rowcontent>";
			$qSpbDet=$owlPDO->query($sSpbDet) or die(print " Gagal: ".PDOException::getMessage());
			$qSpbDet->setFetchMode(PDO::FETCH_ASSOC);
			$rSpbDet=$qSpbDet->fetch();
			$arrPost=array($_SESSION['lang']['belumposting'],$_SESSION['lang']['posting']);
			$arr="nospb"."##".$barx['nospb'];
			$stream.="
			<td>".$no."</td>
			<td>".$barx['nospb']."</td>
			<td>".$barx['tanggal']."</td>
			<td>".$arrPost[$barx['posting']]."</td>		
			<td align=\"right\">".number_format(($rSpbDet['kgBjr']/$rSpbDet['Janjang']),2)."</td>
			<td align=\"right\">".number_format($rSpbDet['Janjang'],2)."</td>
			<td align=\"right\">".number_format($rSpbDet['Brondolan'],2)."</td>
			<td align=\"right\">".number_format($rSpbDet['Mentah'],2)."</td>
			<td align=\"right\">".number_format($rSpbDet['Busuk'],2)."</td>
			<td  align=\"right\">".number_format($rSpbDet['Matang'],2)."</td>
			<td  align=\"right\">".number_format($rSpbDet['Lewatmatang'],2)."</td>
			<td  align=\"right\">".number_format($rSpbDet['kgBjr'],2)."</td>
			<td  align=\"right\">".number_format($rSpbDet['kGwb'],2)."</td>
			<td  align=\"right\">".number_format($rSpbDet['totaLkg'],2)."</td>
			</tr>
			";
			
				setIt($grandTotal['bjrR'],0);
				setIt($grandTotal['Janjang'],0);
				setIt($grandTotal['Brondolan'],0);
				setIt($grandTotal['Mentah'],0);
				setIt($grandTotal['Busuk'],0);
				setIt($grandTotal['Matang'],0);
				setIt($grandTotal['Lewatmatang'],0);
				setIt($grandTotal['kgBjr'],0);
				setIt($grandTotal['kGwb'],0);
				setIt($grandTotal['totaLkg'],0);
				$grandTotal['bjrR'] += $bjrR;
				$grandTotal['Janjang'] += $rSpbDet['Janjang'];
				$grandTotal['Brondolan'] += $rSpbDet['Brondolan'];
				$grandTotal['Mentah'] += $rSpbDet['Mentah'];
				$grandTotal['Busuk'] += $rSpbDet['Busuk'];
				$grandTotal['Matang'] += $rSpbDet['Matang'];
				$grandTotal['Lewatmatang'] += $rSpbDet['Lewatmatang'];
				$grandTotal['kgBjr'] += $rSpbDet['kgBjr'];
				$grandTotal['kGwb'] += $rSpbDet['kGwb'];
				$grandTotal['totaLkg'] += $rSpbDet['totaLkg'];
				}
				$stream.="<tr style='font-weight:bold'>
					<td colspan='4' style='text-align:center'>TOTAL</td>
					<td align=\"right\">".number_format(($grandTotal['kgBjr']/$grandTotal['Janjang']),2)."</td>
					<td align=\"right\">".number_format($grandTotal['Janjang'],2)."</td>
					<td align=\"right\">".number_format($grandTotal['Brondolan'],2)."</td>
					<td align=\"right\">".number_format($grandTotal['Mentah'],2)."</td>
					<td align=\"right\">".number_format($grandTotal['Busuk'],2)."</td>
					<td  align=\"right\">".number_format($grandTotal['Matang'],2)."</td>
					<td  align=\"right\">".number_format($grandTotal['Lewatmatang'],2)."</td>
					<td  align=\"right\">".number_format($grandTotal['kgBjr'],2)."</td>
					<td  align=\"right\">".number_format($grandTotal['kGwb'],2)."</td>
					<td  align=\"right\">".number_format($grandTotal['totaLkg'],2)."</td>
					</tr>
					";
			}
			
			//echo "warning:".$strx;
			//=================================================
			$stream.="</table>";
						$stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
			
			$nop_="laporanPengangkutanPanen";
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
			fclose($handle);
			}
	break;
	case'getDetail':
	

	
	$nospb=$_GET['nospb'];
	
	
	 class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
				global $nospb;
				global $owlPDO;
		
				$sHed="select  a.nospb,a.kodeorg,a.tanggal,a.posting from ".$dbname.".kebun_spbht a where a.nospb='".$nospb."'";
				$qHead=$owlPDO->query($sHed) or die(print " Gagal: ".PDOException::getMessage());
				$qHead->setFetchMode(PDO::FETCH_ASSOC);
				$rHead=$qHead->fetch();
				
				$sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$rHead['kodeorg']."'";
				$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
				$qOrg->setFetchMode(PDO::FETCH_ASSOC);
				$rOrg=$qOrg->fetch();
				 
                # Alamat & No Telp
                $query = selectQuery($dbname,'organisasi','alamat,telepon',
                    "kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
                $orgData = fetchData($query);
                
                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 15;
				$this->SetFont('Arial','',8);
				$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['nospb'],'',0,'L');
				$this->Cell(5,$height,':','',0,'L');
				$this->Cell(45/100*$width,$height,$rHead['nospb'],'',0,'L');
				$this->Ln();
				$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['tanggal'],'',0,'L');
				$this->Cell(5,$height,':','',0,'L');
				$this->Cell(45/100*$width,$height,tanggalnormal($rHead['tanggal']),'',0,'L');
				$this->Ln();
				$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['kodeorg'],'',0,'L');
				$this->Cell(5,$height,':','',0,'L');
				$this->Cell(45/100*$width,$height,$rOrg['namaorganisasi'],'',0,'L');
				
         
                $this->Line($this->lMargin,$this->tMargin+($height*4),
                    $this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();
                
               	$this->Ln();
				$this->Ln();
                $this->SetFont('Arial','U',9);
                $this->Cell($width,$height, strtoupper($_SESSION['lang']['laporanPengangkutan']),0,1,'C');	
                $this->Ln();	
				
                $this->SetFont('Arial','B',8);	
                $this->SetFillColor(220,220,220);
				
				$this->Cell(3/100*$width,$height,'No',1,0,'C',1);			
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['blok'],1,0,'C',1);	
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['bjr'],1,0,'C',1);		
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['janjang'],1,0,'C',1);
				$this->Cell(9/100*$width,$height,$_SESSION['lang']['brondolan'],1,0,'C',1);		
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['mentah'],1,0,'C',1);		
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['busuk'],1,0,'C',1);		
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['matang'],1,0,'C',1);		
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['lewatmatang'],1,0,'C',1);					
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['kg']." ".$_SESSION['lang']['kebun'],1,0,'C',1);					
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['kgwb'],1,0,'C',1);					
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['totalkg'],1,1,'C',1);					
            
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
        $height = 10;
		$pdf->AddPage();
		$pdf->SetFillColor(255,255,255);
		$pdf->SetFont('Arial','',6);
		/*$sDet="select a.nospb,a.tanggal,a.posting,b.* from ".$dbname.".kebun_spbht a inner join ".$dbname.".kebun_spbdt b on a.nospb=b.nospb where a.tanggal like '%".$periode."%' and a.kodeorg='".$idKebun."' order by a.tanggal asc ";*/
		$sDet="select * from ".$dbname.".kebun_spbdt_detail where nospb='".$nospb."'";
		$qDet=$owlPDO->query($sDet) or die(print " Gagal: ".PDOException::getMessage());
		$qDet->setFetchMode(PDO::FETCH_ASSOC);
		$row=owlBaris($qDet);
		
		if($row>0)
		{
			while($rSpbDet=$qDet->fetch())
			{
				$no+=1;
				$arrPost=array($_SESSION['lang']['belumposting'],$_SESSION['lang']['posting']);
		
				$pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);			
				$pdf->Cell(10/100*$width,$height,$namaOrg[$rSpbDet['blok']],1,0,'L',1);	
				$pdf->Cell(8/100*$width,$height,number_format(($rSpbDet['kgbjr']/$rSpbDet['jjg']),2),1,0,'R',1);		
				$pdf->Cell(8/100*$width,$height,number_format($rSpbDet['jjg'],2),1,0,'R',1);
				$pdf->Cell(9/100*$width,$height,number_format($rSpbDet['brondolan'],2),1,0,'R',1);		
				$pdf->Cell(8/100*$width,$height,number_format($rSpbDet['mentah'],2),1,0,'R',1);		
				$pdf->Cell(8/100*$width,$height,number_format($rSpbDet['busuk'],2),1,0,'R',1);		
				$pdf->Cell(8/100*$width,$height,number_format($rSpbDet['matang'],2),1,0,'R',1);		
				$pdf->Cell(10/100*$width,$height,number_format($rSpbDet['lewatmatang'],2),1,0,'R',1);	
				$pdf->Cell(8/100*$width,$height,number_format($rSpbDet['kgbjr'],2),1,0,'C',1);					
				$pdf->Cell(8/100*$width,$height,number_format($rSpbDet['kgwb'],2),1,0,'C',1);					
				$pdf->Cell(8/100*$width,$height,number_format($rSpbDet['totalkg'],2),1,1,'C',1);
				
				setIt($grandTotal['jjg'],0);
				setIt($grandTotal['brondolan'],0);
				setIt($grandTotal['mentah'],0);
				setIt($grandTotal['busuk'],0);
				setIt($grandTotal['matang'],0);
				setIt($grandTotal['lewatmatang'],0);
				setIt($grandTotal['kgbjr'],0);
				setIt($grandTotal['kgwb'],0);
				setIt($grandTotal['totalkg'],0);
				$grandTotal['jjg'] += $rSpbDet['jjg'];
				$grandTotal['brondolan'] += $rSpbDet['brondolan'];
				$grandTotal['mentah'] += $rSpbDet['mentah'];
				$grandTotal['busuk'] += $rSpbDet['busuk'];
				$grandTotal['matang'] += $rSpbDet['matang'];
				$grandTotal['lewatmatang'] += $rSpbDet['lewatmatang'];
				$grandTotal['kgbjr'] += $rSpbDet['kgbjr'];
				$grandTotal['kgwb'] += $rSpbDet['kgwb'];
				$grandTotal['totalkg'] += $rSpbDet['totalkg'];
			}
				$pdf->Cell(13/100*$width,$height,"TOTAL",1,'','C',1);			
				$pdf->Cell(8/100*$width,$height,number_format(($grandTotal['kgbjr']/$grandTotal['jjg']),2),1,0,'R',1);		
				$pdf->Cell(8/100*$width,$height,number_format($grandTotal['jjg'],2),1,0,'R',1);
				$pdf->Cell(9/100*$width,$height,number_format($grandTotal['brondolan'],2),1,0,'R',1);		
				$pdf->Cell(8/100*$width,$height,number_format($grandTotal['mentah'],2),1,0,'R',1);		
				$pdf->Cell(8/100*$width,$height,number_format($grandTotal['busuk'],2),1,0,'R',1);		
				$pdf->Cell(8/100*$width,$height,number_format($grandTotal['matang'],2),1,0,'R',1);		
				$pdf->Cell(10/100*$width,$height,number_format($grandTotal['lewatmatang'],2),1,0,'R',1);	
				$pdf->Cell(8/100*$width,$height,number_format($grandTotal['kgbjr'],2),1,0,'C',1);					
				$pdf->Cell(8/100*$width,$height,number_format($grandTotal['kgwb'],2),1,0,'C',1);					
				$pdf->Cell(8/100*$width,$height,number_format($grandTotal['totalkg'],2),1,1,'C',1);
		}
		else
		{
			$pdf->Cell(68/100*$width,$height,"Not Found",1,1,'C',1);
		}
        $pdf->Output();

	break;
	default:
	break;
}
?>