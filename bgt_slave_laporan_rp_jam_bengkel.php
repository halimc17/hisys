<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses     = checkPostGet('proses','');
$ev         = checkPostGet('ev','');
$thnbudget  = checkPostGet('thnbudget','');
$kdWs       = checkPostGet('kdWs','');
$kdTrak     = checkPostGet('kdTrak','');
$brsKe      = checkPostGet('brsKe','');
$kdeWs      = checkPostGet('kdeWs','');
$kodeTraksi = checkPostGet('kodeTraksi','');
$thnBudget  = checkPostGet('thnBudget','');

$str="select namakaryawan,karyawanid from ".$dbname.".datakaryawan where karyawanid=".$_SESSION['standard']['userid']. "";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$namakar[$bar->karyawanid]=$bar->namakaryawan;
}



$data="<table border=0 cellspacing=0><tr><td colspan=2 scope=row>Summary ".$_SESSION['lang']['laporanrpjambengkel']."</td></tr><tr><td colspan=5 scope=row>".$_SESSION['lang']['tahun']." : ".$thnbudget." <td></tr><tr><td colspan=2>".$_SESSION['lang']['unit']." : ".getNamaOrg($kdWs)."</td></tr></table>";
$data.="<table class=sortable cellspacing=1 border=0 cellpadding=5>
          <thead>
          <tr class=rowheader>
			<th align=center>".substr($_SESSION['lang']['nomor'],0,2)."</th>		  
		    <th align=center>".$_SESSION['lang']['kodetraksi']."</th>
			<th align=center>".$_SESSION['lang']['workshop']."</th>
			<th align=center>".$_SESSION['lang']['rpperthn']."</th> 
			<th align=center>".$_SESSION['lang']['jamperthn']."</th> 
			<th align=center>".$_SESSION['lang']['rpperjam']."</th>
			<th align=center>".$_SESSION['lang']['alokasi']."</th>
			<th align=center>".$_SESSION['lang']['sisa']."</th>
          </tr>
          </thead>"; 
		  
#teralokasi
$str = "select * from ".$dbname.".bgt_budget where 1=1 and tahunbudget='".$thnbudget."' and tipebudget in ('TRK','MILL') and kodebudget='SERVICE'";
$res = fetchdata($str);
foreach($res as $bar){
	$alokasiws[$bar['kodews']]+=$bar['jumlah'];
}

$no=0;
$sList="select * from ".$dbname.".bgt_biaya_ws_per_jam where tahunbudget='".$thnbudget."' and kodews='".$kdWs."' order by kodews ";
$qList=$owlPDO->query($sList) or die(print " Gagal: ".PDOException::getMessage());
$qList->setFetchMode(PDO::FETCH_OBJ);
while($bar=$qList->fetch()){
    $no+=1;
	$stat=" onclick=getDet(".$no.") style=\"cursor: pointer;\"";
    $data.="<tr class=rowcontent id=row_".$no." ".$stat." >
                <td align=center>".$no."</td>
				<td id=kdTrak_".$no." value='".$bar->kodetraksi."'>".getNamaOrg($bar->kodetraksi)."</td>    
				<td id=kdeWs_".$no." value='".$bar->kodews."'>".getNamaOrg($bar->kodews)."</td>    
				<td align=right>".number_format($bar->rppertahun,0)."</td>    
				<td align=right>".number_format($bar->jampertahun)."</td>    
				<td align=right>".number_format($bar->rpperjam,2)."</td>
				<td align=right>".number_format($alokasiws[$bar->kodews],0)."</td>
				<td align=right>".number_format($bar->jampertahun-$alokasiws[$bar->kodews],0)."</td>
			</tr>
			<tr class=rowcontent>
				<td align=center colspan=8 id=detail_".$no."></td>
			</tr>"; 
}  

$data.="</tbody></table>";



//untuk exel awal
$data4="<table border=0 cellspacing=0><tr><td colspan=5 scope=row>Summary ".$_SESSION['lang']['laporanrpjambengkel']."</td></tr><tr><td colspan=5 scope=row>".$_SESSION['lang']['tahun']." : ".$thnbudget." </td></tr><tr><td colspan=5>".$_SESSION['lang']['unit']." : ".$kdWs."</td></tr></table>";
$data4.="<table class=sortable cellspacing=1 border=1>
          <thead>
          <tr class=rowheader bgcolor=#CCCCCC>
			<td align=center>".substr($_SESSION['lang']['nomor'],0,2)."</td>
		    <td align=center>".$_SESSION['lang']['kodetraksi']."</td>
			<td align=center>".$_SESSION['lang']['workshop']."</td>
			<td align=center>".$_SESSION['lang']['rpperthn']."</td> 
			<td align=center>".$_SESSION['lang']['jamperthn']."</td> 
			<td align=center>".$_SESSION['lang']['rpperjamKend']."</td>
          </tr>
          </thead>"; 
$no=0;
$sList="select * from ".$dbname.".bgt_biaya_ws_per_jam where tahunbudget='".$thnbudget."' and kodews='".$kdWs."' order by kodews ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$qList->fetch())
{
    $no+=1;
    $data4.="<tr class=rowcontent >
                <td align=center>".$no."</td>
				<td align=left>".$bar->kodetraksi."</td>    
				<td align=left>".$bar->kodews."</td>    
				<td align=right>".$bar->rppertahun."</td>    
				<td align=right>".$bar->jampertahun."</td>    
				<td align=right>".number_format($bar->rpperjam,2)."</td></tr>"; 
}  
$data4.="</tbody></table>";
$data4.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];


//untuk exel detail
	$data3=" ".$_SESSION['lang']['laporandetail']." ".$_SESSION['lang']['laporanrpjambengkel']." <br>";
	$data3.=" ".$_SESSION['lang']['tahun']." : ".$thnbudget."   <br>";
	$data3.=" ".$_SESSION['lang']['unit']." : ".getNamaOrg($kdWs)." <br>";
	$data3.="<table cellspacing=1 border=1 class=sortable width=100% cellpadding=5>
		<thead>
			<tr class=rowheader bgcolor=#CCCCCC>
				<th align=center>".substr($_SESSION['lang']['nomor'],0,2)."</th>
				<th align=center>".$_SESSION['lang']['kodetraksi']."</th>
				<th align=center>".$_SESSION['lang']['workshop']."</th>
				<th align=center>".$_SESSION['lang']['kodebudget']."</th>
				<th align=center>".$_SESSION['lang']['noakun']."</th> 
				<th align=center>".$_SESSION['lang']['volume']."</th>
				<th align=center>".$_SESSION['lang']['satuanvolume']."</th>
				<th align=center>".$_SESSION['lang']['jumlah']."</th>
				<th align=center>".$_SESSION['lang']['satuanjumlah']."</th>
				<th align=center>".$_SESSION['lang']['rp']."</th>
			</tr>
			
		</thead><tbody>";
		$no=0;
		$sList="select * from ".$dbname.".bgt_budget b
		 where  kodeorg='".$kdWs."'  and tahunbudget='".$thnbudget."'  and tipebudget='WS' ";
		$qList=$owlPDO->query($sList) or die(print " Gagal: ".PDOException::getMessage());
		$qList->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$qList->fetch())
		{
			$no+=1;
			$data3.="<tr class=rowcontent width=100%>
                <td align=center>".$no."</td>
				<td align=left>".@$bar->kodetraksi."</td>    
				<td align=left>".@$bar->kodews."</td>    
				<td align=left>".@$bar->kodebudget."</td>
				<td align=left>".@$bar->noakun."</td>    
				<td align=right>".@$bar->volume."</td>    
				<td align=right>".@$bar->satuanv."</td>    
				<td align=right>".@$bar->jumlah."</td>    
				<td align=left>".@$bar->satuanj."</td>        
				<td align=right>".@number_format($bar->rupiah,2)."</td></tr>";	
		}
	$data3.="</table>";
	$data3.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];





//
switch($proses)
{
	case'getDetail':
	
	
	$data2="<center>
	<button class=mybutton onclick=\"dataKeExcel(event,'".$kdTrak."','".$kdeWs."','".$thnbudget."');\">Excel</button>
	<button class=mybutton onclick=\"dataKePdf('".$thnbudget."','".$kdTrak."','".$kdeWs."',event);\">PDF</button>
	<button class=mybutton onclick=\"closeDet(".$brsKe.");\">Hide</button>
	</center>
	<table cellspacing=1 border=0 class=sortable width=100% cellpadding=5>
		<thead>
			<tr class=rowheader >
				<th align=center>".substr($_SESSION['lang']['nomor'],0,2)."</th>
				<th align=center>".$_SESSION['lang']['kodebudget']."</th>
				<th align=center>".$_SESSION['lang']['barang']."</th>
				<th align=center>".$_SESSION['lang']['namabarang']."</th>
				<th align=center>".$_SESSION['lang']['volume']."</th>
				<th align=center width=50px>".$_SESSION['lang']['satuanvolume']."</th>
				<th align=center>".$_SESSION['lang']['jumlah']."</th>
				<th align=center width=50px>".$_SESSION['lang']['satuanjumlah']."</th>
				<th align=center>".$_SESSION['lang']['rp']."</th>
			</tr>
		</thead><tbody>";
		$no=0;
		$sList="select * from ".$dbname.".bgt_budget b where  kodeorg='".$kdeWs."'  and tahunbudget='".$thnbudget."'  and tipebudget='WS' ";
		$qList=$owlPDO->query($sList) or die(print " Gagal: ".PDOException::getMessage());
		$qList->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$qList->fetch()){
			$no+=1;
			$data2.="<tr class=rowcontent width=100%>
                <td align=center>".@$no."</td>
				<td align=left>".@getBgtKode($bar->kodebudget)."</td>
				<td align=left>".@$bar->kodebarang."</td>    
				<td align=left>".@getNamaBrg($bar->kodebarang)."</td>    
				<td align=right>".@$bar->volume."</td>    
				<td align=right>".@$bar->satuanv."</td>    
				<td align=right>".@$bar->jumlah."</td>    
				<td align=left>".@$bar->satuanj."</td>        
				<td align=right>".@number_format($bar->rupiah,2)."</td></tr>";
		}
	$data2.="</tbody></table>";	
	
	$data2.="<br><label><b>ALOKASI JAM BENGKEL</b></label><table cellspacing=1 border=0 class=sortable width=100% cellpadding=5>
		<thead>
			<tr class=rowheader >
				<th align=center>".substr($_SESSION['lang']['nomor'],0,2)."</th>
				<th align=center>".$_SESSION['lang']['kodevhc']." / ".$_SESSION['lang']['station']."</th>
				<th align=center>".$_SESSION['lang']['jumlah']."</th>
			</tr>
		</thead><tbody>";
	$str = "select * from ".$dbname.".bgt_budget where 1=1 and tahunbudget='".$thnbudget."' and kodews='".$kdeWs."' and tipebudget in ('TRK','MILL') and kodebudget='SERVICE'";
	$no='';
	$res = fetchdata($str);
	foreach($res as $bar){
		$no++;
		$data2.="<tr class=rowcontent>
			<td align=center>".$no."</td>";
			if($bar['kodevhc']==''){
				$data2.="<td align=left>".$bar['kodeorg']." - ".getNamaOrg($bar['kodeorg'])."</td>";
			}else{
				$data2.="<td align=left>".$bar['kodevhc']." - ".getNopol($bar['kodevhc'])."</td>";
			}
		$data2.="<td align=right>".@number_format($bar['jumlah'],2)."</td></tr>";
		
		$ttljamalk+=$bar['jumlah'];
	}
	$data2.="<tr class=rowcontent>
			<td align=center colspan=2>Total</td>
			<td align=right>".@number_format($ttljamalk,2)."</td></tr>
			";
	$data2.="</tbody></table>";			
			
	echo $data2;
	break;
	
	
	case'preview':
	
		if($thnbudget=='')
		{
			echo "warning : Tahun Budget Masih Kosong";
			exit();	
		}
		else if($kdWs=='')
		{
			echo "warning : Kode Bengkel Masih Kosong";
			exit();	
		}
		else 
		{
			echo $data;
		}
	break;



	case 'excel':
		
		if($thnbudget=='')
		{
			echo "warning : Tahun Budget Masih Kosong";
			exit();	
		}
		else if($kdWs=='')
		{
			echo "warning : Kode Bengkel Masih Kosong";
			exit();	
		}
		
		$nop_="Laporan";
		//$nop_"Laporan Daftar Asset ".$nmOrg."_".$nmAst;
		//$nop_="Daftar Asset : ".$nmOrg." ".$nmAst;
		if(strlen($data4)>0)
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
			if(!fwrite($handle,$data4))
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




	case 'ExcelAlokasi':
	
	
		if($thnbudget=='')
		{
			echo "warning : Tahun Budget Masih Kosong";
			exit();	
		}
		else if($kdWs=='')
		{
			echo "warning : Kode Bengkel Masih Kosong";
			exit();	
		}
		
		$nop_="Laporan";
		//$nop_"Laporan Daftar Asset ".$nmOrg."_".$nmAst;
		//$nop_="Daftar Asset : ".$nmOrg." ".$nmAst;
		if(strlen($data3)>0)
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
			if(!fwrite($handle,$data3))
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
		
		
		
	case'pdf':
	
		if($thnbudget=='')
		{
			echo "warning : Tahun Budget Masih Kosong";
			exit();	
		}
		else if($kdWs=='')
		{
			echo "warning : Kode Bengkel Masih Kosong";
			exit();	
		}
//create Header

		class PDF extends FPDF
        {
            function Header() {
                
				global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
				
				global $thnbudget;
				global $kdWs;
				global $kdTrak;
				global $namakar;
 
				
                //alamat PT

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
                $this->Line($this->lMargin,$this->tMargin+($height*4),
                $this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();
                
				//untuk sub judul
                $this->SetFont('Arial','B',7);
				$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['laporanrpjambengkel'],'',0,'L');
				$this->Ln();
				$this->SetFont('Arial','',7);
				$this->Cell((100/100*$width)-5,$height,"Printed By : ".$namakar[$_SESSION['standard']['userid']],'',0,'R');
				$this->Ln();
				$this->Cell((100/100*$width)-5,$height,"Tanggal By : ".date('d-m-Y'),'',0,'R');
				$this->Ln();
				$this->Cell((100/100*$width)-5,$height,"Time By : ".date('h:i:s'),'',0,'R');
		
				
				
				
				$this->Ln();
				$this->Ln();
				//judul tengah
				$this->SetFont('Arial','B',10);
				$this->Cell($width,$height,strtoupper($_SESSION['lang']['laporanrpjambengkel']." "."$kdWs"),'',0,'C');
				$this->Ln();
				$this->Cell($width,$height,strtoupper($_SESSION['lang']['tahun']." "."$thnbudget"),'',0,'C');
				$this->Ln();
				$this->Ln();
				
				//isi atas tabel
              	$this->SetFont('Arial','B',8);
                $this->SetFillColor(220,220,220);
				$this->Cell(2/100*$width,$height,substr($_SESSION['lang']['nomor'],0,2),1,0,'C',1);
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['kodetraksi'],1,0,'C',1);
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['workshop'],1,0,'C',1);
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['rpperthn'],1,0,'C',1);
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['jamperthn'],1,0,'C',1);
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['rpperjam'],1,1,'C',1);	
            }
            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
		//untuk kertas L=len p=potraid
        $pdf=new PDF('P','pt','Legal');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 20;
		$pdf->AddPage();
		$pdf->SetFillColor(255,255,255);
		$pdf->SetFont('Arial','',7);


		//isi tabel dan tabelnya
		$no=0;
		$sql="select * from ".$dbname.".bgt_biaya_ws_per_jam where tahunbudget='".$thnbudget."' and kodews='".$kdWs."' order by kodews ";
		$qDet=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$qDet->setFetchMode(PDO::FETCH_ASSOC);
		while($res=$qDet->fetch())
		{
			$no+=1;
			$pdf->Cell(2/100*$width,$height,$no,1,0,'C',1);
			$pdf->Cell(10/100*$width,$height,$res['kodetraksi'],1,0,'L',1);	
			$pdf->Cell(10/100*$width,$height,$res['kodews'],1,0,'L',1);	
			$pdf->Cell(10/100*$width,$height,$res['rppertahun'],1,0,'R',1);	
			$pdf->Cell(10/100*$width,$height,$res['jampertahun'],1,0,'R',1);	 
			$pdf->Cell(10/100*$width,$height,number_format($res['rpperjam'],2),1,0,'R',1);	 			                   
			$pdf->Ln();	
			 
		}
	$pdf->Output();
			break;
					
	
	
case'pdfAlokasi':

		if($thnbudget=='')
		{
			echo "warning : Tahun Budget Masih Kosong";
			exit();	
		}
		else if($kdWs=='')
		{
			echo "warning : Kode Bengkel Masih Kosong";
			exit();	
		}
//create Header

		class PDF extends FPDF
        {
            function Header() {
                
				global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
				
				global $thnbudget;
				global $kdWs;
				global $kdTrak;
				global $namakar;
				global $brsKe;
				global $kdeWs;
				global $kodeTraksi;
				global $thnBudget;
				

				
                //alamat PT

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
                $this->Line($this->lMargin,$this->tMargin+($height*4),
                $this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();
                
				//untuk sub judul
                $this->SetFont('Arial','B',7);
				$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['laporanrpjambengkel'],'',0,'L');
				$this->Ln();
				$this->SetFont('Arial','',7);
				$this->Cell((100/100*$width)-5,$height,"Printed By : ".$namakar[$_SESSION['standard']['userid']],'',0,'R');
				$this->Ln();
				$this->Cell((100/100*$width)-5,$height,"Date : ".date('d-m-Y'),'',0,'R');
				$this->Ln();
				$this->Cell((100/100*$width)-5,$height,"Time : ".date('h:i:s'),'',0,'R');
				$this->Ln();
				$this->Ln();
				
				//judul tengah
				$this->SetFont('Arial','',10);
				$this->Cell($width,$height,strtoupper($_SESSION['lang']['laporandetail']. $_SESSION['lang']['laporanrpjambengkel']." "."$kdWs"),'',0,'C');
				$this->Ln();
				$this->Cell($width,$height,strtoupper($_SESSION['lang']['tahun']." "."$thnbudget"),'',0,'C');
				$this->Ln();
				$this->Ln();
				

				
				
				//isi atas tabel
              	$this->SetFont('Arial','B',8);
                $this->SetFillColor(220,220,220);
				$this->Cell(2/100*$width,$height,"No",1,0,'C',1);
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['kodetraksi'],1,0,'C',1);
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['workshop'],1,0,'C',1);
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['kodebudget'],1,0,'C',1);
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['noakun'],1,0,'C',1);
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['volume'],1,0,'C',1);	
				$this->Cell(13/100*$width,$height,$_SESSION['lang']['satuanvolume'],1,0,'C',1);	
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['jumlah'],1,0,'C',1);	
				$this->Cell(13/100*$width,$height,$_SESSION['lang']['satuanjumlah'],1,0,'C',1);	
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['rp'],1,1,'C',1);	
				
            }
            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
		//untuk kertas L=len p=potraid
        $pdf=new PDF('P','pt','Legal');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 20;
		$pdf->AddPage();
		$pdf->SetFillColor(255,255,255);
		$pdf->SetFont('Arial','',7);


		//isi tabel dan tabelnya
		$no=0;
		$sql="select * from ".$dbname.".bgt_budget b where  kodeorg='".$kdWs."' and tahunbudget='".$thnbudget."'  and tipebudget='WS'";
		//$sql="select * from ".$dbname.".bgt_biaya_ws_per_jam a, ".$dbname.".bgt_budget b where a.tahunbudget = b.tahunbudget and kodetraksi='".$kodeTraksi."' and kodews='".$kdWs."' and a.tahunbudget='".$thnbudget."' ";
		//echo $sql;
		$qDet=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$qDet->setFetchMode(PDO::FETCH_ASSOC);
		while($res=$qDet->fetch())
		{
			$no+=1;
			$pdf->Cell(2/100*$width,$height,$no,1,0,'C',1);
			$pdf->Cell(10/100*$width,$height,@$res['kodetraksi'],1,0,'L',1);	
			$pdf->Cell(10/100*$width,$height,@$res['kodews'],1,0,'L',1);	
			$pdf->Cell(10/100*$width,$height,@$res['kodebudget'],1,0,'L',1);	
			$pdf->Cell(10/100*$width,$height,@$res['noakun'],1,0,'R',1);	
			$pdf->Cell(10/100*$width,$height,@$res['volume'],1,0,'R',1);	
			$pdf->Cell(13/100*$width,$height,@$res['satuanv'],1,0,'R',1);	
			$pdf->Cell(10/100*$width,$height,@$res['jumlah'],1,0,'R',1);	
			$pdf->Cell(13/100*$width,$height,@$res['satuanj'],1,0,'R',1);	 
			$pdf->Cell(10/100*$width,$height,@number_format($res['rupiah'],2),1,0,'R',1);	 						                   
			$pdf->Ln();	
			 
		}
	$pdf->Output();
	
	break;
	default;
	
}



?>

