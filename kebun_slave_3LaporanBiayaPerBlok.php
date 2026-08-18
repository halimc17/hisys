<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses = checkPostGet('proses','');
$periode=checkPostGet('periode','');
$unit=checkPostGet('unit','');
$detailunit=checkPostGet('detailunit','');
$detailperiode=checkPostGet('detailperiode','');
$namaOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

### BEGIN PROSES DATA ###
$whereJ = "t1.periode = '".$periode."' and t1.kodeorg = '".$unit."' and (t1.noakun LIKE '126%' or t1.noakun LIKE '128%' or t1.noakun LIKE '611%' or t1.noakun LIKE '621%')";
$str="select t1.kodeblok, t2.tahuntanam, t2.luasareaproduktif, t2.statusblok, SUM(t1.debet-t1.kredit) AS totalbiaya from ".$dbname.".keu_jurnaldt_vw t1
	  left join ".$dbname.".setup_blok t2
	  on t1.kodeblok = t2.kodeorg
      where ".$whereJ." 
	  and t2.tahuntanam != '0'
	  and char_length(t1.kodeblok) > 6
      group by t1.kodeblok
	  order by t2.tahuntanam DESC";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$num_rows=owlBaris($res);

$border = 0;
if($proses=='excel')
{
	$border = 1;
}

$stream="Periode : ".substr($periode,5,2)."-".substr($periode,0,4)."<br>
		Unit : ".$unit;
$stream.="<table cellspacing='1' cellpadding='5' border='".$border."' class='sortable'>
			<thead class=rowheader>
			<tr>
			<td style='width:30px; text-align:center;'>No</td>
			<td style='text-align:center; width:100px;'>".$_SESSION['lang']['kodeblok']."</td>
			<td style='text-align:center;'>".$_SESSION['lang']['tahuntanam']."</td>    
			<td style='text-align:center; width:80px;'>".$_SESSION['lang']['luas']."</td>
			<td style='text-align:center;'>".$_SESSION['lang']['statusblok']."</td> 
			<td style='text-align:center; width:100px;'>".$_SESSION['lang']['totalbiaya']."</td>
			</tr></thead>
			<tbody>";
$no=1;
$total_biaya_akhir=0;
while($bar=$res->fetch())
{
    $stream.="<tr class=rowcontent style='cursor:pointer;' title='Click untuk melihat detail' onclick=\"lihatDetail('".$bar->kodeblok."','".$periode."',event);\">
                <td style='text-align:center;'>".$no."</td>
                <td>".$namaOrg[$bar->kodeblok]."</td>    
                <td style='text-align:center'>".$bar->tahuntanam."</td>  
                <td style='text-align:right'>".$bar->luasareaproduktif."</td>
                <td>".$bar->statusblok."</td>
                <td style='text-align:right'>".number_format($bar->totalbiaya,2)."</td>
              </tr>";
	$no+=1;
	$total_biaya_akhir+=$bar->totalbiaya;
}
$stream.="<tr class=rowcontent>
			<td colspan='5' style='text-align:center'><b>TOTAL</b></td>
			<td style='text-align:right'><b>".number_format($total_biaya_akhir,2)."</b></td>
		  </tr>";
### END PROSES DATA ###

### BEGIN DETAIL DATA ###
$stream2="<fieldset>
		<legend><b>Blok ".$detailunit."</b></legend>		
			<table class='sortable' border='0' cellspacing='1' cellpadding='5' width='100%'>
				<thead class=rowheader>
				<tr>
					<td style='text-align:center'>".$_SESSION['lang']['nojurnal']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['noakun']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['namaakun']."</td>    
					<td style='text-align:center; width:100px;'>".$_SESSION['lang']['debet']."</td>
					<td style='text-align:center; width:100px;'>".$_SESSION['lang']['kredit']."</td> 
					<td style='text-align:center; width:100px;'>".$_SESSION['lang']['biaya']."</td> 
				</tr>
				</thead>
				<tbody>";

$whereJ2 = "t1.periode = '".$detailperiode."' and t1.kodeblok = '".$detailunit."' and (t1.noakun LIKE '126%' or t1.noakun LIKE '128%' or t1.noakun LIKE '611%' or t1.noakun LIKE '621%')";
$str2="select t1.nojurnal, t1.noakun, t2.namaakun, t1.debet, t1.kredit from ".$dbname.".keu_jurnaldt_vw t1
	  left join ".$dbname.".keu_5akun t2
	  on t1.noakun = t2.noakun
	  where ".$whereJ2."
	  ORDER BY t1.noakun asc, t1.nojurnal ASC";
$resTab = fetchData($str2);

$total_debet_akhir=0;
$total_kredit_akhir=0;
foreach($resTab as $key=>$row) {
	$stream2.="<tr class='rowcontent'>
		<td>".$row['nojurnal']."</td>
		<td>".$row['noakun']."</td>
		<td>".$row['namaakun']."</td>  
		<td style='text-align:right'>".number_format($row['debet'])."</td>
		<td style='text-align:right'>".number_format($row['kredit'])."</td>
		<td style='text-align:right'>".number_format($row['debet']-$row['kredit'])."</td>
	  </tr>";
	$total_debet_akhir+=isset($row['debet']) ? $row['debet'] : 0;
	$total_kredit_akhir+=isset($row['kredit']) ? $row['kredit'] : 0;
}
$stream2.="<tr class=rowcontent>
			<td colspan='3' style='text-align:center'><b>TOTAL</b></td>
			<td style='text-align:right'><b>".number_format($total_debet_akhir)."</b></td>
			<td style='text-align:right'><b>".number_format($total_kredit_akhir)."</b></td>
			<td style='text-align:right'><b>".number_format($total_debet_akhir-$total_kredit_akhir)."</b></td>
		  </tr>";
### END DETAIL DATA ###


switch($proses)
{
	case 'preview':
		if($num_rows > 0){
			echo $stream;
		}else{
			echo "Data not Found";
		}
	break;
	case 'detail':
        $theme=$_SESSION['theme'];
        if($theme=='skyblue' || $theme==''){
          $gen='generic.css';
        }else if($theme=='red'){
          $gen='genericRed.css';  
        }else{
          $gen='genericGray.css';  
        }   		
		echo"
		    <link rel=stylesheet type='text/css' href='style/".$gen."'>
		";
		echo $stream2;
	break;
	case 'excel':
		//$qwe=date("YmdHms");
		$nop_="Laporan Biaya Per Blok ".$unit."_".substr($periode,5,2)."-".substr($periode,0,4);
		if(strlen($stream)>0)
		{
			$gzdowmload = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
			gzwrite($gzdowmload, $stream);
			gzclose($gzdowmload);
			echo "<script language=javascript1.2>
			window.location='tempExcel/".$nop_.".xls.gz';
			</script>";
		}
	break;
	case 'pdf':
		if($num_rows > 0){
			//echo "Format Belum tersedia";
			class PDF extends FPDF{
                function Header() {
					global $owlPDO;
					global $dbname;
					global $periode;
					
					$cols=247.5;
					$query = selectQuery($dbname,'organisasi','alamat,telepon',
						"kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
					$orgData = fetchData($query);

					$width = $this->w - $this->lMargin - $this->rMargin;
					$height = 20;
					$path='images/logo.jpg';
					$this->Image($path,$this->lMargin,$this->tMargin,0,55);
					$this->SetFont('Arial','B',9);
					$this->SetFillColor(255,255,255);	
					$this->SetX(100);   
					$this->Cell($width-100,$height,$_SESSION['org']['namaorganisasi'],0,1,'L');	 
					$this->SetX(100); 		
					$this->Cell($width-100,$height,$orgData[0]['alamat'],0,1,'L');	
					$this->SetX(100); 			
					$this->Cell($width-100,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
					$this->Line($this->lMargin,$this->tMargin+($height*3),
					$this->lMargin+$width,$this->tMargin+($height*3));

					$this->SetFont('Arial','B',10);
					$this->Cell($width,$height,"Laporan Biaya per Blok ",'',0,'C');
					$this->Ln();
					$this->Cell($width,$height,strtoupper($_SESSION['lang']['periode'])." : ".substr($periode,5,2)."-".substr($periode,0,4),'',0,'C');
					$this->Ln();
					$this->SetFont('Arial','B',10);
					$this->SetFillColor(220,220,220);
					$this->SetX(80); 
					$this->Cell(7/100*$width,$height,"No.",1,0,'C',1);		
					$this->Cell(15/100*$width,$height,$_SESSION['lang']['kodeblok'],1,0,'C',1);		
					$this->Cell(14/100*$width,$height,$_SESSION['lang']['tahuntanam'],1,0,'C',1);		
					$this->Cell(15/100*$width,$height,$_SESSION['lang']['luas'],1,0,'C',1);		
					$this->Cell(15/100*$width,$height,$_SESSION['lang']['statusblok'],1,0,'C',1);		
					$this->Cell(15/100*$width,$height,$_SESSION['lang']['totalbiaya'],1,0,'C',1);	
				}

				function Footer()
				{
					$this->SetY(-15);
					$this->SetFont('Arial','I',8);
					$this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
				}
			}
			$pdf=new PDF('P','pt','Legal');
			$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
			$height = 13;

			$pdf->AddPage();
			$pdf->SetFillColor(255,255,255);
			$pdf->SetFont('Arial','',10);
			$pdf->Ln();
			
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			$no=0;
			$ttl=0;
			while($bar=$res->fetch())
			{
				$no+=1;
				$pdf->SetX(80);
				$pdf->Cell(7/100*$width,$height,$no,1,0,'R',1);		
				$pdf->Cell(15/100*$width,$height,$bar->kodeblok,1,0,'L',1);		
				$pdf->Cell(14/100*$width,$height,$bar->tahuntanam,1,0,'L',1);		
				$pdf->Cell(15/100*$width,$height,$bar->luasareaproduktif,1,0,'R',1);		
				$pdf->Cell(15/100*$width,$height,$bar->statusblok,1,0,'L',1);		
				$pdf->Cell(15/100*$width,$height,number_format($bar->totalbiaya,2),1,1,'R',1);		
				$ttl+=$bar->totalbiaya;
			}
			$pdf->SetX(80); 
			$pdf->Cell(66/100*$width,$height,'Total',1,0,'C',1);		
			$pdf->Cell(15/100*$width,$height,number_format($ttl,2),1,1,'R',1);
			
			$pdf->Ln();
			
			$pdf->Output();
			
		}else{
			echo "Data not Found";
		}
	break;
	default:
	break;
}
?>