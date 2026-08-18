<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=$_GET['proses'];

$_POST['kodeorg1']==''?$kodeorg1=$_GET['kodeorg1']:$kodeorg1=$_POST['kodeorg1'];
$_POST['tanggal1']==''?$tanggal1=$_GET['tanggal1']:$tanggal1=$_POST['tanggal1'];
$_POST['tanggal2']==''?$tanggal2=$_GET['tanggal2']:$tanggal2=$_POST['tanggal2'];

if($kodeorg1=='')
{
    // echo"warning: Pabrik Tidak Boleh Kosong"; exit();
}
if($tanggal1=='')
{
    echo"warning: Tanggal mulai Tidak Boleh Kosong"; exit();
}
if($tanggal2=='')
{
    echo"warning: Tanggal sampai Tidak Boleh Kosong"; exit();
}
$tanggal1=tanggalsystemn($tanggal1);
$tanggal2=tanggalsystemn($tanggal2);
$arrtanggal=rangeTanggalarr($tanggal1,$tanggal2);
$u=$n=0;

// kamus induk
$sql="select kodeorganisasi, induk from ".$dbname.".organisasi where 1";
$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while($res=$query->fetch()){
	$kamusinduk[$res['kodeorganisasi']]=$res['induk'];
}


switch($proses)
{
	case'preview':
            
		echo"<table class=sortable cellspacing=1 border=0>
		<thead><tr class=rowheader>
		<td align=center>".$_SESSION['lang']['tanggal']."</td>
		<td align=center>".$_SESSION['lang']['produk']."</td>
		<td align=center>".$_SESSION['lang']['saldoawal']."</td>
		<td align=center>".$_SESSION['lang']['produksi']."</td>
		<td align=center>".$_SESSION['lang']['pengiriman']."</td>
		<td align=center>".$_SESSION['lang']['saldoakhir']."</td>
		</tr></thead><tbody>";

		foreach($arrtanggal as $dttgl){
		


		#= saldo akhir
			$z=$u=$c=$m=$n=$g=0;
		$sql="select sum(kuantitas) as kuantitas from ".$dbname.".pabrik_masukkeluartangki where kodeorg like '".$kodeorg1."%' and tanggal = '".$dttgl."'";
        $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
        $query->setFetchMode(PDO::FETCH_ASSOC);
        while($res=$query->fetch())
		{
				$z=$res['kuantitas'];
		}
		#= produksi
		$sql="select oer from ".$dbname.".pabrik_produksi where kodeorg like '".$kodeorg1."%' and tanggal = '".$dttgl."'";
        $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
        $query->setFetchMode(PDO::FETCH_ASSOC);
        while($res=$query->fetch())
		{
				$u=$res['oer'];
		}
		#= pengiriman
		// $sql="select sum(jumlah) as jumlah from ".$dbname.".pmn_bapengiriman where unit = '".$kodeorg1."' and tanggal like '".$dttgl."%' and kodebarang = '40000001'";
		$sql="select sum(jumlah) as jumlah from ".$dbname.".pmn_bast where kodept like '".$kamusinduk[$kodeorg1]."%' and tanggalbl like '".$dttgl."%' and kodebarang = '40000001'";
        $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
        $query->setFetchMode(PDO::FETCH_ASSOC);
        while($res=$query->fetch())
		{
				$c=$res['jumlah'];
		}
		
	
	
			#= saldo akhir
        $sql="select sum(kernelquantity) as kuantitas from ".$dbname.".pabrik_masukkeluartangki where kodeorg like '".$kodeorg1."%' and tanggal = '".$dttgl."'";
        $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
        $query->setFetchMode(PDO::FETCH_ASSOC);
        while($res=$query->fetch())
		{
				$m=$res['kuantitas'];
		}
		$sql="select oerpk from ".$dbname.".pabrik_produksi where kodeorg like '".$kodeorg1."%' and tanggal = '".$dttgl."'";
        $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
        $query->setFetchMode(PDO::FETCH_ASSOC);
        while($res=$query->fetch())
		{
				$n=$res['oerpk'];
		}
		// $sql="select sum(jumlah) as jumlah from ".$dbname.".pmn_bapengiriman where unit = '".$kodeorg1."' and tanggal like '".$dttgl."%' and kodebarang = '40000002'";
		$sql="select sum(jumlah) as jumlah from ".$dbname.".pmn_bast where kodept like '".$kamusinduk[$kodeorg1]."%' and tanggalbl like '".$dttgl."%' and kodebarang = '40000002'";
        $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
        $query->setFetchMode(PDO::FETCH_ASSOC);
        while($res=$query->fetch())
		{
				$g=$res['jumlah'];
		}
	  
		// $d=$z+$u-$c;
        // $h=$m+$n-$g;
        
        echo"<tr class=rowcontent>";
         echo"<td>".tanggalnormal($dttgl)."</td>";
        echo"<td>CPO</td>";
		/*
        echo"<td align=right>".number_format($z,0)."</td>";
        echo"<td align=right>".number_format($u,0)."</td>";
        echo"<td align=right style=cursor:pointer onclick=viewDetail('".$kodeorg1."','".$dttgl."','40000001',event) title='".$_SESSION['lang']['detailPengiriman']."'>".number_format($c,0)."</td>";
        echo"<td align=right>".number_format($d,0)."</td>";
		*/
		
		// $sawal=$z-$u+$c;
		// $sawalker=$m-$n+$g;

		$kemarin=date('Y-m-d',strtotime($dttgl.' -1 day'));


		$sql="select sum(kuantitas) as kuantitas from ".$dbname.".pabrik_masukkeluartangki where kodeorg like '".$kodeorg1."%' and tanggal = '".$kemarin."'";
        $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
        $query->setFetchMode(PDO::FETCH_ASSOC);
        while($res=$query->fetch())
		{
				$sawal=$res['kuantitas'];
		}
		
        $sql="select sum(kernelquantity) as kuantitas from ".$dbname.".pabrik_masukkeluartangki where kodeorg like '".$kodeorg1."%' and tanggal = '".$kemarin."'";
        $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
        $query->setFetchMode(PDO::FETCH_ASSOC);
        while($res=$query->fetch())
		{
				$sawalker=$res['kuantitas'];
		}

		
		echo"<td align=right>".number_format($sawal,0)."</td>";
        echo"<td align=right>".number_format($u,0)."</td>";
        echo"<td align=right>".number_format($c,0)."</td>";
        echo"<td align=right>".number_format($z,0)."</td>";
		
		
        echo"</tr>";
        echo"<tr class=rowcontent>";
		 echo"<td>".tanggalnormal($dttgl)."</td>";
        echo"<td>Kernel</td>";
        echo"<td align=right>".number_format($sawalker,0)."</td>";
        echo"<td align=right>".number_format($n,0)."</td>";
        echo"<td align=right>".number_format($g,0)."</td>";
        echo"<td align=right>".number_format($m,0)."</td>";
		
		/*
		 echo"<td align=right>".number_format($m,0)."</td>";
        echo"<td align=right>".number_format($n,0)."</td>";
        echo"<td align=right style=cursor:pointer onclick=viewDetail('".$kodeorg1."','".$dttgl."','40000002',event) title='".$_SESSION['lang']['detailPengiriman']."'>".number_format($g,0)."</td>";
        echo"<td align=right>".number_format($h,0)."</td>";
		*/
		
        echo"</tr>";
		}
	echo"</tbody></table>";
	break;
        
	case'getTangki':
		$sGet="select kodetangki,keterangan from ".$dbname.".pabrik_5tangki where kodeorg='".$kdPbrik."'";
        $qGet=$owlPDO->query($sGet) or die(print " Gagal: ".PDOException::getMessage());
        $qGet->setFetchMode(PDO::FETCH_ASSOC);
        while($rGet=$qGet->fetch())    
	{
		$optTangki.="<option value=".$rGet['kodetangki'].">".$rGet['keterangan']."</option>";
	}
	echo $optTangki;
	break;
	default:
	break;        
//	case'pdf':
//	$periode=$_GET['periode'];
//	 class PDF extends FPDF
//        {
//            function Header() {
//                global $conn;
//                global $dbname;
//                global $align;
//                global $length;
//                global $colArr;
//                global $title;
//				global $kdPbrik;
//				global $kdTangki;
//				global $periode;
//				global $tampilperiode;
//				
//				$sql="select nokontrak,kodebarang,tanggalkontrak,koderekanan,tanggalkirim,sdtanggal,kuantitaskontrak,kodept from ".$dbname.".pmn_kontrakjual where tanggalkontrak like '%".$periode."%'";
//				$query=
//				$res=($query);
//				
//                $tkdOperasi=$res['jlhharitdkoperasi'];
//				$jmlhHariOperasi=$res['jlhharioperasi'];
//				$meter=$res['merterperhari'];
//				$kdOrg=$res['orgdata'];
//				
//                
//                # Alamat & No Telp
//                $query = selectQuery($dbname,'organisasi','alamat,telepon',
//                    "kodeorganisasi='".$res['kodept']."'");
//                $orgData = fetchData($query);
//                
//                $width = $this->w - $this->lMargin - $this->rMargin;
//                $height = 15;
//                $path='images/logo.jpg';
//                $this->Image($path,$this->lMargin,$this->tMargin,0,55);
//                $this->SetFont('Arial','B',9);
//                $this->SetFillColor(255,255,255);	
//                $this->SetX(100);   
//                $this->Cell($width-100,$height,$_SESSION['org']['namaorganisasi'],0,1,'L');	 
//                $this->SetX(100); 		
//                $this->Cell($width-100,$height,$orgData[0]['alamat'],0,1,'L');	
//                $this->SetX(100); 			
//                $this->Cell($width-100,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
//                $this->Line($this->lMargin,$this->tMargin+($height*4),
//                    $this->lMargin+$width,$this->tMargin+($height*4));
//                $this->Ln();
//                
//                $this->SetFont('Arial','B',12);
//				$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['laporanstok']." ".$kdPbrik." ".$kdTangki,'',0,'L');
//				$this->Ln();
//				$this->SetFont('Arial','',8);
//				$this->Cell((10/100*$width)-5,$height,$_SESSION['lang']['periode'],'',0,'L');
//				$this->Cell(5,$height,':','',0,'L');
//				$this->Cell(45/100*$width,$height,$tampilperiode,'',0,'L');
//			
//              
//				
//				$this->Ln();
//                $this->SetFont('Arial','U',12);
//                $this->Cell($width,$height, $_SESSION['lang']['laporanstok'],0,1,'C');	
//                $this->Ln();	
//				
//                $this->SetFont('Arial','',8);	
//                $this->SetFillColor(220,220,220);
//				
//				$this->Cell(3/100*$width,$height,"No.",1,0,'C',1);
//				$this->Cell(10/100*$width,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);	
//				$this->Cell(8/100*$width,$height,$_SESSION['lang']['kodetangki'],1,0,'C',1);	
//				$this->Cell(10/100*$width,$height,"CPO - qty (KG)",1,0,'C',1);		
//				$this->Cell(7/100*$width,$height,"CPO - rend",1,0,'C',1);		
//				$this->Cell(7/100*$width,$height,"CPO - FFA",1,0,'C',1);		
//				$this->Cell(7/100*$width,$height,"CPO - kd.Air",1,0,'C',1);		
//				$this->Cell(7/100*$width,$height,"CPO - kd.Kot",1,0,'C',1);		
//				$this->Cell(10/100*$width,$height,"Kernel - qty (KG)",1,0,'C',1);		
//				$this->Cell(7/100*$width,$height,"Kernel - rend",1,0,'C',1);		
//				$this->Cell(7/100*$width,$height,"Kernel - FFA",1,0,'C',1);		
//				$this->Cell(7/100*$width,$height,"Kernel - kd.Air",1,0,'C',1);		
//				$this->Cell(7/100*$width,$height,"Kernel - kd.Kot",1,1,'C',1);		
//            }
//                
//            function Footer()
//            {
//                $this->SetY(-15);
//                $this->SetFont('Arial','I',8);
//                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
//            }
//        }
//        $pdf=new PDF('L','pt','A4');
//        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
//        $height = 10;
//		$pdf->AddPage();
//		$pdf->SetFillColor(255,255,255);
//		$pdf->SetFont('Arial','',8);
//	$sql="select * from ".$dbname.".pabrik_masukkeluartangki where ".$where."";
//	
//	
//	
//		{
//			$no+=1;
//				$pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);
//				$pdf->Cell(10/100*$width,$height,tanggalnormal($res['tanggal']),1,0,'C',1);	
//				$pdf->Cell(8/100*$width,$height,$res['kodetangki'],1,0,'C',1);	
//				$pdf->Cell(10/100*$width,$height,number_format($res['kuantitas'],0),1,0,'R',1);		
//				$pdf->Cell(7/100*$width,$height,$res['cporendemen'],1,0,'R',1);		
//				$pdf->Cell(7/100*$width,$height,$res['cpoffa'],1,0,'R',1);		
//				$pdf->Cell(7/100*$width,$height,$res['cpokdair'],1,0,'R',1);		
//				$pdf->Cell(7/100*$width,$height,$res['cpokdkot'],1,0,'R',1);		
//				$pdf->Cell(10/100*$width,$height,number_format($res['kernelquantity'],0),1,0,'R',1);		
//				$pdf->Cell(7/100*$width,$height,$res['kernelrendemen'],1,0,'R',1);		
//				$pdf->Cell(7/100*$width,$height,$res['kernelffa'],1,0,'R',1);		
//				$pdf->Cell(7/100*$width,$height,$res['kernelkdair'],1,0,'R',1);		
//				$pdf->Cell(7/100*$width,$height,$res['kernelkdkot'],1,1,'R',1);		
//
//			
//		}
//			
//        $pdf->Output();
//	break;
//	case'excel':
//	$periode=$_GET['periode'];
//			$stream.="
//			<table>
//			<tr><td>".$_SESSION['lang']['laporanstok']." ".$kdPbrik." ".$kdTangki."</td></tr>
//			<tr><td>".$_SESSION['lang']['periode']."</td><td>".$tampilperiode."</td></tr>
//			<tr></tr>
//			</table>
//			<table border=1>
//			<tr bgcolor=#DEDEDE>
//			
//		<td>".$_SESSION['lang']['kodeorg']."</td>
//		<td>".$_SESSION['lang']['tanggal']."</td>
//		<td>".$_SESSION['lang']['kodetangki']."</td>
//		<td align=right>".$_SESSION['lang']['cpokuantitas']." (KG)</td>
//		<td align=right>".$_SESSION['lang']['cporendemen']." (%)</td>
//		<td align=right>".$_SESSION['lang']['cpoffa']." (%)</td>
//		<td align=right>".$_SESSION['lang']['cpokdair']." (%)</td>
//		<td align=right>".$_SESSION['lang']['cpokdkot']." (%)</td>
//		<td align=right>".$_SESSION['lang']['kernelquantity']." (KG)</td>
//		<td align=right>".$_SESSION['lang']['kernelrendemen']." (%)</td>
//		<td align=right>".$_SESSION['lang']['kernelffa']." (%)</td>
//		<td align=right>".$_SESSION['lang']['kernelkdair']." (%)</td>
//		<td align=right>".$_SESSION['lang']['kernelkdkot']." (%)</td>
//			
//			
//			
//			</tr>";
//
//	$sql="select * from ".$dbname.".pabrik_masukkeluartangki where ".$where."";
//	$query=
//	$row=
//	if($row<1)
//	{
//		$stream.="<tr class=rowcontent>
//		<td colspan=8 align=center>Not Avaliable</td></tr>
//		";
//	}
//	$query=
//	while($res=
//	{
//		$stream.="<tr class=rowcontent>
//		<td>".$res['kodeorg']."</td>
//		<td>".$res['tanggal']."</td>
//		<td>".$res['kodetangki']."</td>
//		<td align=right>".number_format($res['kuantitas'],0)."</td>
//		<td align=right>".$res['cporendemen']."</td>
//		<td align=right>".$res['cpoffa']."</td>
//		<td align=right>".$res['cpokdair']."</td>
//		<td align=right>".$res['cpokdkot']."</td>
//		<td align=right>".number_format($res['kernelquantity'],0)."</td>
//		<td align=right>".$res['kernelrendemen']."</td>
//		<td align=right>".$res['kernelffa']."</td>
//		<td align=right>".$res['kernelkdair']."</td>
//		<td align=right>".$res['kernelkdkot']."</td>
//		</tr>";
//
//	}
//			//echo "warning:".$strx;
//			//=================================================
//			$stream.="</table>";
//						$stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
//			
//			$nop_="Laporan Stok-".$kdPbrik.$periode.$kdTangki;
//			if(strlen($stream)>0)
//			{
//			if ($handle = opendir('tempExcel')) {
//			while (false !== ($file = readdir($handle))) {
//			if ($file != "." && $file != ".." && $file != "index.html") {
//			@unlink('tempExcel/'.$file);
//			}
//			}	
//			closedir($handle);
//			}
//			$handle=fopen("tempExcel/".$nop_.".xls",'w');
//			if(!fwrite($handle,$stream))
//			{
//			echo "<script language=javascript1.2>
//			parent.window.alert('Can't convert to excel format');
//			</script>";
//			exit;
//			}
//			else
//			{
//			echo "<script language=javascript1.2>
//			window.location='tempExcel/".$nop_.".xls';
//			</script>";
//			}
//			fclose($handle);
//			}
//	break;


}

?>