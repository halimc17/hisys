<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses=$_GET['proses'];
$periode=	isset($_POST['periode'])? $_POST['periode']: '';



switch($proses)
{
	case'preview':
	//echo"masuk";
	echo"<table cellspacing=1 border=0 class=sortable><thead><tr class=rowheader>
	<td>".$_SESSION['lang']['ptpemilikbarang']."</td>
	<td>".$_SESSION['lang']['pt']."</td>
	<td>".$_SESSION['lang']['untukunit']."</td>
	<td>".$_SESSION['lang']['jumlah']."</td>
        <td>".$_SESSION['lang']['detail']."</td>
	</tr></thead><tbody>";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
	{ 
		$newWhere = " ";
	}else{
		$newWhere = " and untukunit like '%".$_SESSION['empl']['lokasitugas']."'";
	}
	$sPemakaian="select kodept,untukpt,untukunit from ".$dbname.".log_transaksi_vw where tanggal like '%".$periode."%' ".$newWhere."  group by untukunit";

	$qPemakaian=$owlPDO->query($sPemakaian) or die(print " Gagal: ".PDOException::getMessage());
	$qPemakaian->setFetchMode(PDO::FETCH_ASSOC);

	$row=owlBaris($qPemakaian);
	if($row>0)
	{
		while($rPemakaian=$qPemakaian->fetch())
		{
			//untukunit='".$kdUnit."' and tanggal like '%".$periode."%' and tipetransaksi='5'
			$sJmlh="select sum(jumlah) as jmlh from ".$dbname.".log_transaksi_vw where untukunit='".$rPemakaian['untukunit']."' and tanggal like '%".$periode."%' and tipetransaksi='5'";
			$qJmlh=$owlPDO->query($sJmlh) or die(print " Gagal: ".PDOException::getMessage());
			$qJmlh->setFetchMode(PDO::FETCH_ASSOC);
			$rJmlh=$qJmlh->fetch();
			
			$sComp="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$rPemakaian['kodept']."'";	
			$qComp=$owlPDO->query($sComp) or die(print " Gagal: ".PDOException::getMessage());
			$qComp->setFetchMode(PDO::FETCH_ASSOC);			
			$rComp=$qComp->fetch();
			
			$sComp="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$rPemakaian['untukpt']."'";	
			$qComp=$owlPDO->query($sComp) or die(print " Gagal: ".PDOException::getMessage());
			$qComp->setFetchMode(PDO::FETCH_ASSOC);
			$rComp2=$qComp->fetch();
			
			$sComp="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$rPemakaian['untukunit']."'";	
			$qComp=$owlPDO->query($sComp) or die(print " Gagal: ".PDOException::getMessage());
			$qComp->setFetchMode(PDO::FETCH_ASSOC);
			$rComp3=$qComp->fetch();

			$test="kdunit"."##".$rPemakaian['untukunit']."##periode"."##".$periode;					
			
			echo"<tr class=rowcontent  title=Click>
			<td onclick=\"zPdfInputan('log_slave_2alokasibiayaPemakaian','".$test."','printContainer2')\">".$rComp['namaorganisasi']."</td>
			<td onclick=\"zPdfInputan('log_slave_2alokasibiayaPemakaian','".$test."','printContainer2')\">".$rComp2['namaorganisasi']."</td>
			<td onclick=\"zPdfInputan('log_slave_2alokasibiayaPemakaian','".$test."','printContainer2')\">".$rComp3['namaorganisasi']."</td>
			<td onclick=\"zPdfInputan('log_slave_2alokasibiayaPemakaian','".$test."','printContainer2')\" align=right>".number_format($rJmlh['jmlh'],2)."</td>
                        <td><button onclick=\"zExceldetail(event,'log_slave_2alokasibiayaPemakaian.php','".$test."','printContainer2')\" class=\"mybutton\" name=\"excel\" id=\"excel\">".$_SESSION['lang']['excel']."</button>  </td>        
                        </tr>";		
		}
	}
	else
	{
		echo"<tr class=rowcontent><td colspan=5 align=center>".$_SESSION['lang']['datanotfound']."</td></tr>";
	}
		echo"</tbody></table>";
	break;
	case'pdf':
	$kdUnit=$_GET['kdunit'];
	$periode=$_GET['periode'];
//	echo "warning:masuk".$noTrans."__".$periode;exit();
	
	class PDF extends FPDF
	{
	
	
	function Header()
	{
 	global $conn;
	global $dbname;
	global $kdUnit;
	global $periode;
	global $owlPDO;
			//ambil nama pt
			   $str1="select distinct induk from ".$dbname.".organisasi where kodeorganisasi='$kdUnit'"; 
      			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
				$res1->setFetchMode(PDO::FETCH_OBJ);
			   $bar1=$res1->fetch();
               $sComp="select namaorganisasi,alamat,wilayahkota,telepon from ".$dbname.".organisasi where kodeorganisasi='".$bar1->induk."'";	
      			$qComp=$owlPDO->query($$sComp) or die(print " Gagal: ".PDOException::getMessage());
				$qComp->setFetchMode(PDO::FETCH_OBJ);				
				$rComp=$qComp->fetch();
			   	 $namapt=$rComp->namaorganisasi;
				 $alamatpt=$rComp->alamat.", ".$rComp->wilayahkota;
				 $telp=$rComp->telepon;				 
			   
                $path='images/logo.jpg';
                $this->Image($path,15,7,17);	
		$this->SetFont('Arial','B',9);
		$this->SetFillColor(255,255,255);	
		$this->SetX(35);   
                $this->Cell(60,5,$namapt,0,1,'L');	 
		$this->SetX(35); 		
                $this->Cell(60,5,$alamatpt,0,1,'L');	
		$this->SetX(35); 			
		$this->Cell(60,5,"Tel: ".$telp,0,1,'L');	
		
		$this->Ln();
		//$this->Cell(40,4,": ".$kodegudang,0,1,'L'); 
		/*$this->Cell(35,4,$_SESSION['lang']['ptpemilikbarang'],0,0,'L'); 
		$this->Cell(40,4,": ".$rComp['namaorganisasi'],0,1,'L'); 				
		$this->Cell(35,4,$_SESSION['lang']['pt'],0,0,'L'); 
		$this->Cell(40,4,": ".$rComp2['namaorganisasi'],0,1,'L'); 	*/	  
		$this->Cell(35,4,$_SESSION['lang']['untukunit'],0,0,'L'); 
		$this->Cell(40,4,": ".$kdUnit,0,1,'L'); 
		$this->Cell(35,4,$_SESSION['lang']['periode'],0,0,'L'); 
		$this->Cell(40,4,": ".$periode,0,1,'L'); 
	
		$this->SetFont('Arial','U',12);
		$this->SetY(40);
		$this->Cell(190,5,strtoupper($_SESSION['lang']['pemakaianBarang']),0,1,'C');		
		//$this->SetFont('Arial','',15);
                //$this->Cell(190,5,strtoupper($_SESSION['lang']['permintaan_harga']),0,1,'C');
		$this->SetFont('Arial','',6); 
		$this->SetY(27);
		$this->SetX(163);
                $this->Cell(30,10,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'L');		
		$this->Line(10,27,200,27);	
		$this->SetY(40);
	}
	
	function Footer()
	{
	    $this->SetY(-15);
	    $this->SetFont('Arial','I',8);
	    $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
	}

}

	$pdf=new PDF('P','mm','A4');
	$pdf->AddPage();
			
//ambil kelengkapan
    
	$pdf->Ln();
   
	$pdf->SetFont('Arial','B',8);	
	$pdf->SetFillColor(220,220,220);
    $pdf->Cell(8,5,'No',1,0,'L',1);
    $pdf->Cell(20,5,$_SESSION['lang']['tanggal'],1,0,'C',1);
    $pdf->Cell(30,5,$_SESSION['lang']['namabarang'],1,0,'C',1);		
    $pdf->Cell(15,5,$_SESSION['lang']['jumlah'],1,0,'C',1);	
  	$pdf->Cell(10,5,$_SESSION['lang']['satuan'],1,0,'C',1);	
	$pdf->Cell(36,5,$_SESSION['lang']['kalkulasihargarata'],1,0,'C',1);
	$pdf->Cell(25,5,$_SESSION['lang']['kodeblok'],1,0,'C',1);
	$pdf->Cell(50,5,$_SESSION['lang']['keterangan'],1,1,'C',1);


		$pdf->SetFillColor(255,255,255);
	    $pdf->SetFont('Arial','',8);
		
		$str="select kodebarang,kodeblok,hargarata,jumlah,satuan,tanggal  from ".$dbname.".log_transaksi_vw where untukunit='".$kdUnit."' and tanggal like '%".$periode."%' and tipetransaksi='5'";// echo $str;exit();
		$re=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$re->setFetchMode(PDO::FETCH_ASSOC);
		$no=0;
		while($bar=$re->fetch())
		{
			$no+=1;
			$sComp="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$bar['kodebarang']."'";	
			$qComp=$owlPDO->query($sComp) or die(print " Gagal: ".PDOException::getMessage());
			$qComp->setFetchMode(PDO::FETCH_ASSOC);

			$rComp3=$qComp->fetch();
			    $pdf->Cell(8,5,$no,1,0,'L',1);
				$pdf->Cell(20,5,tanggalnormal($bar['tanggal']),1,0,'C',1);
				$pdf->Cell(30,5,substr($rComp3['namabarang'],0,30),1,0,'L',1);		
				$pdf->Cell(15,5,number_format($bar['jumlah'],2),1,0,'R',1);	
				$pdf->Cell(10,5,$bar['satuan'],1,0,'C',1);	
				$pdf->Cell(36,5,number_format($bar['hargarata'],2),1,0,'C',1);
				$pdf->Cell(25,5,$bar['kodeblok'],1,0,'L',1);
				$pdf->Cell(50,5,substr($bar['keterangan'],0,50),1,1,'L',1);	
			    	   
		}
		
	$pdf->Output();


	break;
	case'excel':
            
           
            
	$periode=$_GET['periode'];
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
	{ 
		$newWhere = " ";
	}else{
		$newWhere = " and untukunit like '%".$_SESSION['empl']['lokasitugas']."'";
	}
	$strx="select notransaksi,kodebarang,kodeblok,hargarata,jumlah,satuan,tanggal,kodept,untukpt,untukunit  from ".$dbname.".log_transaksi_vw where tanggal like '%".$periode."%' and tipetransaksi='5' ".$newWhere." order by untukunit asc";
						
			$stream="
			<table>
			<tr><td colspan=9 align=center>".$_SESSION['lang']['list']." ".$_SESSION['lang']['pemakaianBarang']."</td></tr>
			<tr><td colspan=5 align=center>Periode : ".$periode."</td></tr>
			</table>
			<table border=1>
			<tr>
				<td bgcolor=#DEDEDE align=center >No.</td>
				<td bgcolor=#DEDEDE align=center >".$_SESSION['lang']['ptpemilikbarang']."</td>
				<td bgcolor=#DEDEDE align=center >".$_SESSION['lang']['pt']."</td>
				<td bgcolor=#DEDEDE align=center >".$_SESSION['lang']['untukunit']."</td>
				<td bgcolor=#DEDEDE align=center >".$_SESSION['lang']['tanggal']."</td>
				<td bgcolor=#DEDEDE align=center >".$_SESSION['lang']['namabarang']."</td>
				<td bgcolor=#DEDEDE align=center >".$_SESSION['lang']['satuan']."</td>
				<td bgcolor=#DEDEDE align=center >".$_SESSION['lang']['kalkulasihargarata']."</td>
				<td bgcolor=#DEDEDE align=center >".$_SESSION['lang']['jumlah']."</td>";
				$stream.="</tr>";

	$query=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
	$query->setFetchMode(PDO::FETCH_ASSOC);

	$row=owlbaris($query);
	if($row>0)
	{
		$no=0;
		while($rPemakaian=$query->fetch())
		{
			$no+=1;
			
			
			$sComp="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$rPemakaian['kodept']."'";	
			$qComp=$owlPDO->query($sComp) or die(print " Gagal: ".PDOException::getMessage());
			$qComp->setFetchMode(PDO::FETCH_ASSOC);			
			$rComp=$qComp->fetch();
			
			$sComp="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$rPemakaian['untukpt']."'";	
			$qComp=$owlPDO->query($sComp) or die(print " Gagal: ".PDOException::getMessage());
			$qComp->setFetchMode(PDO::FETCH_ASSOC);			
			$rComp2=$qComp->fetch();
			
			$sComp="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$rPemakaian['untukunit']."'";	
			$qComp=$owlPDO->query($sComp) or die(print " Gagal: ".PDOException::getMessage());
			$qComp->setFetchMode(PDO::FETCH_ASSOC);			
			$rComp3=$qComp->fetch();
			
			$sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$rPemakaian['kodebarang']."'";	
			$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
			$qBrg->setFetchMode(PDO::FETCH_ASSOC);

			$rBrg=$qBrg->fetch();
			
			$stream.="<tr class=rowcontent onclick=\"masterPDF('log_transaksiht','".$rPemakaian['notransaksi']."','','log_slave_2alokasibiayaPemakaian',event);\">
			<td>".$no."</td>
			<td>".$rComp['namaorganisasi']."</td>
			<td>".$rComp2['namaorganisasi']."</td>
			<td>".$rComp3['namaorganisasi']."</td>
			<td>".tanggalnormal($rPemakaian['tanggal'])."</td>
			<td>".$rBrg['namabarang']."</td>
			<td>".$rPemakaian['satuan']."</td>
			<td>".$rPemakaian['hargarata']."</td>
			<td align=right>".number_format($rPemakaian['jumlah'],2)."</td>
			</tr>";		
                        
		}
	}
	else
	{
		$stream.="<tr><td colpsan=9>".$_SESSION['lang']['datanotfound']."</td></tr>";			
	}
			

			//echo "warning:".$strx;
			//=================================================

			
			$stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
			//exit("Error:$stream");
			$nop_="PemakaianBarang";
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
	case'exceldetail':
        $kdUnit=$_GET['kdunit'];
	$periode=$_GET['periode'];
	$str="select notransaksi,kodebarang,kodeblok,hargarata,jumlah,satuan,tanggal,keterangan  from ".$dbname.".log_transaksi_vw where untukunit='".$kdUnit."' and tanggal like '%".$periode."%' and tipetransaksi='5'";// echo $str;exit();
	//exit("error".$str);
        
			$stream="
			<table>
			<tr><td colspan=10 align=center>".$_SESSION['lang']['list']." ".$_SESSION['lang']['pemakaianBarang']."</td></tr>
			<tr><td colspan=10 align=center>Periode : ".$periode."</td></tr>
			</table>
			<table border=1>
			<tr>
				<td bgcolor=#DEDEDE align=center >No.</td>
				<td bgcolor=#DEDEDE align=center >".$_SESSION['lang']['tanggal']."</td>
				<td bgcolor=#DEDEDE align=center >".$_SESSION['lang']['kodebarang']."</td>
				<td bgcolor=#DEDEDE align=center >".$_SESSION['lang']['namabarang']."</td>
				<td bgcolor=#DEDEDE align=center >".$_SESSION['lang']['jumlah']."</td>
				<td bgcolor=#DEDEDE align=center >".$_SESSION['lang']['satuan']."</td>
				<td bgcolor=#DEDEDE align=center >".$_SESSION['lang']['kalkulasihargarata']."</td>
                                <td bgcolor=#DEDEDE align=center >".$_SESSION['lang']['afdeling']."</td>   
				<td bgcolor=#DEDEDE align=center >".$_SESSION['lang']['kodeblok']."</td>
				<td bgcolor=#DEDEDE align=center >".$_SESSION['lang']['keterangan']."</td>";
				$stream.="</tr>";
				
	$query=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
	$query->setFetchMode(PDO::FETCH_ASSOC);

	$row=owlbaris($query);
	if($row>0)
	{
		$no=0;
		while($rPemakaian=$query->fetch())
		{
			$no+=1;
			$sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where kodebarang='".$rPemakaian['kodebarang']."'";	
			$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
			$qBrg->setFetchMode(PDO::FETCH_ASSOC);

			$rBrg=$qBrg->fetch();
			
			$stream.="<tr class=rowcontent onclick=\"masterPDF('log_transaksiht','".$rPemakaian['notransaksi']."','','log_slave_2alokasibiayaPemakaian',event);\">
			<td>".$no."</td>
			<td>".$rPemakaian['tanggal']."</td>
			<td>".$rBrg['kodebarang']."</td>
			<td>".$rBrg['namabarang']."</td>
			<td align=right>".$rPemakaian['jumlah']."</td>
			<td>".$rPemakaian['satuan']."</td>
			<td align=right>".number_format($rPemakaian['hargarata'],2)."</td>
                        <td>".substr($rPemakaian['kodeblok'],0,6)."</td>    
			<td>".$rPemakaian['kodeblok']."</td>
			<td>".$rPemakaian['keterangan']."</td>
			</tr>";		
                        
              	}
	}
	else
	{
		$stream.="<tr><td colpsan=10>".$_SESSION['lang']['datanotfound']."</td></tr>";			
	}
			

			//echo "warning:".$strx;
			//=================================================

			
			$stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
			// exit("Error:$stream");
			$nop_="PemakaianBarangDetail";
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
	default:
	break;
}
?>
