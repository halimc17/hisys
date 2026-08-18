<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$kdorg = checkPostGet('kdorg','');
$tgl1=tanggalsystemn(checkPostGet('tgl1',''));
$tgl2=tanggalsystemn(checkPostGet('tgl2',''));
if(($proses=='excel')or($proses=='pdf')){
	
	$tgl1=tanggalsystemn($_GET['tgl1']);
	$tgl2=tanggalsystemn($_GET['tgl2']);
	
}





if(($proses=='preview')or($proses=='excel')or($proses=='pdf'))
{

    if(($tgl1=='')or($tgl2==''))
	{
		echo"Error: Tanggal tidak boleh kosong"; 
		exit;
    }

    else if($tgl1>$tgl2)
	{
        echo"Error: Tanggal pertama tidak boleh lebih besar dari tanggal kedua"; 
		exit;
    }
	
}


$optNmKeg=  makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
$optCust=  makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer');

$kdorgsch = "";
$kdorgsch2 = "";

if($kdorg!='')
{
    $kdorgsch="and kodeorg like '".$kdorg."%' ";
    $kdorgsch2="and b.kodeorg like '%".$kdorg."%' ";
}



###prepare data
if($_SESSION['language']=='EN'){
	$stream="Internal Delivery";
}else{
	$stream="Pengiriman Internal";
}
    $stream="Pengiriman Internal";
    if($proses=='excel')$stream.="<table cellspacing='1' border='1' class='sortable'>";
      else $stream.="<table cellspacing='1' border='0' class='sortable'>";
      $stream.="<thead class=rowheader>
       <tr>
                <th align=center>No</th>
                <th align=center>".$_SESSION['lang']['tanggal']."</th>
                <th align=center>".$_SESSION['lang']['unit']."</th>
                <th align=center>".$_SESSION['lang']['divisi']."</th>
                <th align=center>".$_SESSION['lang']['blok']."</th>
                <th align=center>".$_SESSION['lang']['jumlah']."</th>
        </tr></thead>
      <tbody>";
//kgpotsortasi,kodecustomer,beratbersih as netto,substr(tanggal,1,10) as tanggal,(beratbersih/(jumlahtandan1+jumlahtandan2+jumlahtandan3)) as bjr
$str1=" 	SELECT * FROM ".$dbname.".bibitan_mutasi 
        WHERE tanggal between '".$tgl1."' and '".$tgl2."' ".$kdorgsch." "
        . " and intex in ('0','1','3') and kodetransaksi='PNB' order by tanggal ";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_ASSOC);
$noInt=0;
$totalBbt=0;
while($bar1=$res1->fetch())
{
    //$str1eam.="<tr bgcolor=#FFFFFF>";
    $stream.="<tr class=rowcontent>";
    $noInt+=1;
    $stream.="
    <td align=center>".$noInt."</td>
    <td>".tanggalnormal($bar1['tanggal'])."</td>    
    <td>".substr($bar1['afdeling'],0,4)."</td>
    <td>".substr($bar1['afdeling'],0,6)."</td>
    <td>".$bar1['afdeling']."</td>
    <td align=right>".abs($bar1['jumlah'])."</td> 
    </tr>";	
    
    $totalBbt+=abs($bar1['jumlah']);
}
    $stream.="
        <thead><tr>
                <td align=center colspan=5>Total</td>
               
                <td align=right>".number_format($totalBbt)."</td>
        </tr>
    </tbody></table>";
    
 
##### pengiriman ke external  
if($_SESSION['language']=='EN'){
	$stream.="External Delivery";
}else{
	$stream.="Pengiriman External";
}
if($proses=='excel')$stream.="<table cellspacing='1' border='1' class='sortable'>";
  else $stream.="<table cellspacing='1' border='0' class='sortable'>";
  $stream.="<thead class=rowheader>
   <tr>
            <td align=center>No</td>
            <td align=center>".$_SESSION['lang']['tanggal']."</td>
            <td align=center>".$_SESSION['lang']['customerlist']."</td>
            <td align=center>".$_SESSION['lang']['jumlah']."</td>
    </tr></thead>
  <tbody>";
$str2=" 	SELECT * FROM ".$dbname.".bibitan_mutasi 
        WHERE tanggal between '".$tgl1."' and '".$tgl2."' ".$kdorgsch." "
        . " and intex=2 and kodetransaksi='PNB' order by tanggal ";
$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
$res2->setFetchMode(PDO::FETCH_ASSOC);
while($bar2=$res2->fetch())
{
    //$str2eam.="<tr bgcolor=#FFFFFF>";
    $stream.="<tr class=rowcontent>";
    $noExt+=1;
    $stream.="
    <td align=center>".$noExt."</td>
    <td>".tanggalnormal($bar2['tanggal'])."</td>    
    <td>".$optCust[$bar2['pelanggan']]."</td>
    <td align=right>".abs($bar2['jumlah'])."</td> 
    </tr>";	
    
    $totalBbt2+=abs($bar2['jumlah']);
}
    $stream.="
        <thead><tr>
                <td align=center colspan=3>Total</td>
               
                <td align=right>".number_format(@$totalBbt2)."</td>
        </tr>
    </tbody></table>";  
    
    
    
    
    
##pemakaian di BKM
    


if($_SESSION['language']=='EN'){
	$stream.="Internal Usage";
}else{
	$stream.="Pemakaian BKM";
}
if($proses=='excel')$stream.="<table cellspacing='1' border='1' class='sortable'>";
  else $stream.="<table cellspacing='1' border='0' class='sortable'>";
  $stream.="<thead class=rowheader>
   <tr>
        <td align=center>No</td>
        <td align=center>".$_SESSION['lang']['tanggal']."</td>
        <td align=center>".$_SESSION['lang']['unit']."</td>
            <td align=center>".$_SESSION['lang']['kodeblok']."</td>
        <td align=center>".$_SESSION['lang']['kodekegiatan']."</td>
        <td align=center>".$_SESSION['lang']['namakegiatan']."</td>
        <td align=center>".$_SESSION['lang']['jumlah']."</td>
    </tr></thead>
  <tbody>";//tanggal	kodeblok	kodekegiatan	namakegiatan	jumlah bibit
    
    
$iBkm="select a.*,b.kodeorg as unit,b.tanggal from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b"
        . " on a.notransaksi=b.notransaksi where a.kodekegiatan in "
        . "(select nilai from ".$dbname.".setup_parameterappl where "
        . " kodeparameter in ('SISIPTB','SISIPTBM','TANAMKS','SISIP','SISIP2')) ".$kdorgsch2." ";
// echo $iBkm;
$nBkm=$owlPDO->query($iBkm) or die(print " Gagal: ".PDOException::getMessage());
$nBkm->setFetchMode(PDO::FETCH_ASSOC);
while($dBkm=$nBkm->fetch())
{
$stream.="<tr class=rowcontent>";
    $noBkm+=1;
    $stream.="
    <td align=center>".$noBkm."</td>
        <td>".tanggalnormal($dBkm['tanggal'])."</td>  
        <td>".$dBkm['unit']."</td>
        <td>".$dBkm['kodeorg']."</td>
        <td>".$dBkm['kodekegiatan']."</td>
        <td>".$optNmKeg[$dBkm['kodekegiatan']]."</td>
    <td align=right>".abs($dBkm['hasilkerja'])."</td> 
    </tr>";	
    
    $totalBbt3+=abs($dBkm['hasilkerja']);
}
    $stream.="
        <thead><tr>
                <td align=center colspan=6>Total</td>
               
                <td align=right>".number_format(@$totalBbt3)."</td>
        </tr>
    </tbody></table>";  


#######################################################################
############PANGGGGGGGGGGGGGGGGGGILLLLLLLLLLLLLLLLLLLLLLLLLL###########   
#######################################################################

switch($proses)
{
######HTML
	case 'preview':
		echo $stream;
    break;

######EXCEL	
	case 'excel':
		$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("Ymd");
		$nop_="LAPORAN_BBT_vs_BKM_".$tglSkrg;
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
	
	
	
###############	
#panggil PDFnya
###############
	
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
							global $kdorg;
							global $kdAfd;
							global $tgl1;
							global $tgl2;
							global $where;
							global $nmOrg;
							global $lok;
							global $notrans;
							

                            //$cols=247.5;
                            $query = selectQuery($dbname,'organisasi','alamat,telepon',
                                "kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
                            $orgData = fetchData($query);

                            $width = $this->w - $this->lMargin - $this->rMargin;
                            $height = 20;
                            $path='images/logo.jpg';
                            //$this->Image($path,$this->lMargin,$this->tMargin,50);	
							$this->Image($path,30,15,55);
                            $this->SetFont('Arial','B',9);
                            $this->SetFillColor(255,255,255);	
                            $this->SetX(90); 
							  
                            $this->Cell($width-80,12,$_SESSION['org']['namaorganisasi'],0,1,'L');	 
                            $this->SetX(90); 		
			                $this->SetFont('Arial','',9);
							$height = 12;
                            $this->Cell($width-80,$height,$orgData[0]['alamat'],0,1,'L');	
                            $this->SetX(90); 			
                            $this->Cell($width-80,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
                            $this->Ln();
                            $this->Line($this->lMargin,$this->tMargin+($height*4),
                            $this->lMargin+$width,$this->tMargin+($height*4));

                            $this->SetFont('Arial','B',12);
                                            $this->Ln();
                            $height = 15;
                                            $this->Cell($width,$height,"Laporan Harga TBS ".$kdorg,'',0,'C');
                                            $this->Ln();
                            $this->SetFont('Arial','',10);
                                            $this->Cell($width,$height,strtoupper($_SESSION['lang']['periode'])." : ". tanggalnormal($tgl1)." S/D ". tanggalnormal($tgl2),'',0,'C');
											//$this->Ln();
                                            $this->Ln(30);
                            $this->SetFont('Arial','B',7);
                            $this->SetFillColor(220,220,220);
                                            $this->Cell(3/100*$width,15,substr($_SESSION['lang']['nomor'],0,2),1,0,'C',1);		
                                            $this->Cell(15/100*$width,15,'Supplier',1,0,'C',1);
											$this->Cell(10/100*$width,15,'Tanggal',1,0,'C',1);
											$this->Cell(10/100*$width,15,'BJR',1,0,'C',1);
											$this->Cell(10/100*$width,15,'Harga Satuan',1,0,'C',1);
											$this->Cell(10/100*$width,15,'Netto',1,0,'C',1);
											$this->Cell(10/100*$width,15,'Sortasi',1,0,'C',1);
											$this->Cell(10/100*$width,15,'Berat Normal',1,0,'C',1);
											$this->Cell(10/100*$width,15,'Total',1,1,'C',1);	
											
											
		
											//$this->Ln();
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
                    $height = 15;
                            $pdf->AddPage();
                            $pdf->SetFillColor(255,255,255);
                            $pdf->SetFont('Arial','',7);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);//tinggal tarik $res karna sudah di declarasi di atas
		$no=0;
		$ttl=0;
		while($bar=$res->fetch())
		{	
			
			$bjr=$bar['bjr'];
	//echo $bjr._;
	if($bjr>=3 && $bjr<5)
	{
		$a="select harga from ".$dbname.".pabrik_5hargatbs where bjr='1' and tanggal='".$bar['tanggal']."' and supplierid='".$bar['kodecustomer']."'";
	}
	else if($bjr>=5 && $bjr<7)
	{
		$a="select harga from ".$dbname.".pabrik_5hargatbs where bjr='2' and tanggal='".$bar['tanggal']."' and supplierid='".$bar['kodecustomer']."'";
		//echo $a;
	}
	else
	{
		$a="select harga from ".$dbname.".pabrik_5hargatbs where bjr='3' and tanggal='".$bar['tanggal']."' and supplierid='".$bar['kodecustomer']."'";
		//echo $a;
	}
	$b=$owlPDO->query($a) or die(print " Gagal: ".PDOException::getMessage());
	$b->setFetchMode(PDO::FETCH_ASSOC);
	$c=$b->fetch();
	
	$la="select disticnt kodetimbangan from ".$dbname.".log_5supplier";
	$li=$owlPDO->query($la) or die(print " Gagal: ".PDOException::getMessage());
	$li->setFetchMode(PDO::FETCH_ASSOC);
	$lu=$li->fetch();
		$supz=$lu['kodetimbangan'];
	
	if($supz=='')
	{
		$sNm="select namasupplier from ".$dbname.".log_5supplier where supplierid='".$bar['kodecustomer']."'  ";
	}
	else
	{
		$sNm="select namasupplier from ".$dbname.".log_5supplier where kodetimbangan='".$bar['kodecustomer']."'  ";
	}	
	$qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
	$qNm->setFetchMode(PDO::FETCH_ASSOC);
	$rNm=$qNm->fetch();
		$nm=$rNm['namasupplier'];
		
				$beratnormal=$bar['netto']-$bar['kgpotsortasi'];//and supplierid='".$bar['kodecustomer']."'
		$total=$c['harga']*$beratnormal;//<td>".$optnamacostumer[$bar['kodecustomer']]."</td>
		
		
		
				//echo $sNm;			
			$no+=1;	
			$pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);	
			$pdf->Cell(15/100*$width,$height,$nm,1,0,'L',1);		
			$pdf->Cell(10/100*$width,$height,tanggalnormal($bar['tanggal']),1,0,'L',1);		
			$pdf->Cell(10/100*$width,$height,number_format($bar['bjr'],2),1,0,'R',1);
			$pdf->Cell(10/100*$width,$height,number_format($c['harga']),1,0,'R',1);
			$pdf->Cell(10/100*$width,$height,$bar['netto'],1,0,'R',1);
			$pdf->Cell(10/100*$width,$height,$bar['kgpotsortasi'],1,0,'R',1);
			$pdf->Cell(10/100*$width,$height,$beratnormal,1,0,'R',1);
			$pdf->Cell(10/100*$width,$height,number_format($total),1,1,'R',1);	
		
		$tonetto+=$bar['netto'];
		$tosortasi+=$bar['kgpotsortasi'];
		$toberatnormal+=$beratnormal;
		$tototal+=$total;
											/*
				<td>".$no."</td>
				<td>".$nm."</td>
				<td>".tanggalnormal($bar['tanggal'])."</td>
				<td align=right>".number_format($bar['bjr'],2)."</td>
				<td align=right>".number_format($c['harga'])."</td>
				<td align=right>".number_format($bar['netto'],2)."</td>
				
				<td align=right>".$bar['kgpotsortasi']."</td>
				<td align=right>".$beratnormal."</td>
				<td align=right>".number_format($total)."</td>
											
											
											*/
		
		
		/*
		$tonetto+=$bar['netto'];
		$tosortasi+=$bar['kgpotsortasi'];
		$toberatnormal+=$beratnormal;
		$tototal+=$total;
					
	}
	$stream.="
				<thead><tr>
					<td align=center colspan=5>Total</td>
					<td align=right>".number_format($tonetto,2)."</td>
					<td align=right>".number_format($tosortasi,2)."</td>
					<td align=right>".number_format($toberatnormal,2)."</td>
					<td align=right>".number_format($tototal)."</td>
		
		*/
		//$totnet+=$bar['netto'];
		
		}
			$pdf->SetFillColor(220,220,220);
			//$pdf->SetFont('arial','B',10);
			$pdf->Cell(48/100*$width,$height,strtoupper('Total'),1,0,'C',1);
					
			$pdf->Cell(10/100*$width,$height,number_format($tonetto,2),1,0,'R',1);	
			$pdf->Cell(10/100*$width,$height,number_format($tosortasi,2),1,0,'R',1);
			$pdf->Cell(10/100*$width,$height,number_format($toberatnormal,2),1,0,'R',1);
			$pdf->Cell(10/100*$width,$height,number_format($tototal),1,1,'R',1);
//		
		
		$pdf->Output();
            
	break;

	
	
	default:
	break;
}

?>