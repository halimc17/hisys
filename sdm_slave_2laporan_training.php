<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');



$proses=checkPostGet('proses','');
$kdPt=checkPostGet('kdOrg','');
$bagian=checkPostGet('bagId','');
$karyawanId=checkPostGet('karyawanId','');
$jnsTraining=checkPostGet('jenistraining','');
$tanggal1=checkPostGet('tanggal1','');
$tanggal2=checkPostGet('tanggal2','');


if($proses == 'excel'){
	$setHfont = "style='font-family:Arial, Helvetica, sans-serif; font-size:14px; text-align:center'";
	$setfont = "style='font-family:Arial, Helvetica, sans-serif; font-size:12px;'";
	$tab="<table>
			<tr>
				<td colspan=9 style='text-align:center'>Laporan Riwayat Training</td>
			</tr>
		</table>";
}else if($proses == 'pdf'){
	$setHfont = "style='font-family:Arial, Helvetica, sans-serif; font-size:11px; text-align:center'";
	$setfont = "style='font-family:Arial, Helvetica, sans-serif; font-size:9px;'";
}else{
	$setHfont = " ";
	$setfont = " ";
}
   
	$label_pt = 'Seluruhnya';
	$label_bag = 'Seluruhnya';
	$label_jnstrain = 'Seluruhnya';

	$where = '';
   
			  
	if($kdPt !=''){
		$where .= " AND B.kodeorganisasi = '".$kdPt."' ";
		$query = selectQuery($dbname,'organisasi','namaorganisasi',"kodeorganisasi='".$kdPt."'");
		$orgData = fetchData($query);
		$label_pt = $orgData[0]['namaorganisasi'];
	}

	if($bagian !=''){
		$where .= " AND B.bagian = '".$bagian."' ";
		$query = selectQuery($dbname,'sdm_5departemen','nama',"kode='".$bagian."'");
		$bagData = fetchData($query);
		$label_bag = $bagData[0]['nama'];
	}

	if($karyawanId !=''){
		$where .= " AND A.karyawanId = '".$karyawanId."' ";
	}

	if($jnsTraining !=''){
		$where .= " AND A.jenistraining = '".$jnsTraining."' ";
		$label_jnstrain = $jnsTraining;
	}
  
	$stro = "select 
				  B.nik
				 ,B.namakaryawan
				 ,A.judultraining
				 ,A.tanggalmulai
				 ,A.tanggalselesai
				 ,A.penyelenggara
				 ,A.biaya
				 ,C.jenistraining as jnsTraining
			from ".$dbname.".sdm_karyawantraining A
				left join ".$dbname.".datakaryawan B
				on A.karyawanid = B.karyawanid
				left join ".$dbname.".sdm_5jenistraining C 
				on A.jenistraining = C.kodetraining
			Where A.tanggalmulai >= '".tanggalsystem($tanggal1)."' 
			  AND A.tanggalselesai <= '".tanggalsystem($tanggal2)."' ".$where;
    $reso=$owlPDO->query($stro) or die(print " Gagal: ".PDOException::getMessage());
	$reso->setFetchMode(PDO::FETCH_ASSOC);
	$num_rows=owlBaris($reso);
    
	$tab.="<table cellspacing='0' border='1' >
		<thead class=rowheader>
		<tr>
			<td ".$setHfont." >No</td>
			<td ".$setHfont.">NIK</td>
			<td ".$setHfont.">Nama Karyawan</td>
			<td ".$setHfont.">Kategori Training</td>
			<td ".$setHfont.">Nama Training</td>
			<td ".$setHfont.">Tanggal Mulai</td>
			<td ".$setHfont.">Tanggal Selesai</td>
			<td ".$setHfont.">Vendor</td>
			<td ".$setHfont.">Biaya</td>
		</tr>
		</thead><tbody>";
	
	if($num_rows > 0){
		$nourut =1;
		while ($row = $reso->fetch()) {
			$tab.="<tr class=rowcontent>";
			$tab.="<td ".$setfont.">".$nourut."</td>"; 
			$tab.="<td ".$setfont.">".$row['nik']."</td>"; 
			$tab.="<td ".$setfont.">".$row['namakaryawan']."</td>"; 
			$tab.="<td ".$setfont.">".$row['jnsTraining']."</td>"; 
			$tab.="<td ".$setfont.">".$row['judultraining']."</td>"; 
			$tab.="<td ".$setfont." align=center>".$row['tanggalmulai']."</td>"; 
			$tab.="<td ".$setfont." align=center>".$row['tanggalselesai']."</td>"; 
			$tab.="<td ".$setfont." >".$row['penyelenggara']."</td>";
			$tab.="<td ".$setfont." align=right>".number_format($row['biaya'],2)."</td>";
			$tab.="</tr>";
			$nourut++;
		}
	}else{
		$tab.="<tr class=rowcontent>";
		$tab.="<td colspan=9 align=center ".$setfont.">".$_SESSION['lang']['datanotfound']."</td>"; 
		$tab.="</tr>";
	}
	$tab.="</table>";

	// $html = $tabH.$tab;
	// $tab=$proses;

switch($proses)
{
        case'getKaryawan':
			//exit("Error:masuk");
			$optKary="<option value=''>".$_SESSION['lang']['all']."</option>";
            $sKary="select B.karyawanid,B.namakaryawan from ".$dbname.".datakaryawan B where 1=1
                    ".$where." order by B.namakaryawan asc";
            $qKary=$owlPDO->query($sKary) or die(print " Gagal: ".PDOException::getMessage());
			$qKary->setFetchMode(PDO::FETCH_ASSOC);
            while($rKary=$qKary->fetch())
            {
                $optKary.="<option value=".$rKary['karyawanid'].">".$rKary['namakaryawan']."</option>";
            }
            echo $optKary;
			
        break;
        case'preview':
          
		  echo $tab;
        break;
        case'pdf':
            
			class PDF extends FPDF
			{
				function Header() {
					global $conn;
					global $dbname;
					global $label_pt;
					global $label_bag;
					global $label_jnstrain;
					global $kdPt;
					
					
					$arrHead = setheadreport(substr($kdPt,0,4),$kdPt);
				
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
					
					$width = $this->w - $this->lMargin - $this->rMargin;
					$height = 15;
					$this->SetFont('Arial','B',6);
					$this->SetFillColor(220,220,220);
					$this->Cell(3/100*$width,$height,'No',1,0,'C',1);
					$this->Cell(7/100*$width,$height,'NIK',1,0,'C',1);		
					$this->Cell(15/100*$width,$height,'Nama Karyawan',1,0,'C',1);
					$this->Cell(10/100*$width,$height,'Kategori Training',1,0,'C',1);
					$this->Cell(15/100*$width,$height,'Nama Training',1,0,'C',1);
					$this->Cell(7/100*$width,$height,'Tgl Mulai',1,0,'C',1);
					$this->Cell(7/100*$width,$height,'Tgl Selesai',1,0,'C',1);
					$this->Cell(15/100*$width,$height,'Vendor',1,0,'C',1);
					$this->Cell(10/100*$width,$height,'Biaya',1,0,'C',1);
					$this->Ln();
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
        $height = 12;
         $pdf->AddPage();
         $pdf->SetFillColor(255,255,255);
         $pdf->SetFont('Arial','',6);
			
			$strp = $stro;
			$resp=$owlPDO->query($strp) or die(print " Gagal: ".PDOException::getMessage());
			$resp->setFetchMode(PDO::FETCH_ASSOC);
			$num_rowp=owlBaris($resp);
			if($num_rowp > 0){
			    $nourutp =1;
			    while ($rowp = $resp->fetch()) {
				   
					$pdf->Cell(3/100*$width,$height,$nourutp,1,0,'C',1);
					$pdf->Cell(7/100*$width,$height,$rowp['nik'],1,0,'L',1);		
					$pdf->Cell(15/100*$width,$height,$rowp['namakaryawan'],1,0,'L',1);
					$pdf->Cell(10/100*$width,$height,$rowp['jnsTraining'],1,0,'L',1);
					$pdf->Cell(15/100*$width,$height,$rowp['judultraining'],1,0,'L',1);
					$pdf->Cell(7/100*$width,$height,$rowp['tanggalmulai'],1,0,'C',1);
					$pdf->Cell(7/100*$width,$height,$rowp['tanggalselesai'],1,0,'C',1);
					$pdf->Cell(15/100*$width,$height,$rowp['penyelenggara'],1,0,'L',1);
					$pdf->Cell(10/100*$width,$height,number_format($rowp['biaya']),1,0,'R',1);
					$pdf->Ln();
					
				  
				  $nourutp++;
				}
			}	
			
            			
	        $pdf->Output();
        break;
		
        case'excel':
		   
			$tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];
            $nop_="LaporanRiwayatTraining";

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