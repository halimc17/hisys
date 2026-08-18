<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
//require_once('lib/zFunction.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
include_once('lib/zMysql.php');

$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];
//=============

//create Header
class PDF extends FPDF
{
	
	function Header()
	{
 	global $owlPDO;
	global $dbname;
    global $userid;
	global $posting;
	global $notransaksi;
	global $kodePt;
	global $kdBrg;
	global $tgl;
	global $kdCust;
	global $nmBrg;
	global $wilKota;
	global $nama;
			
			$notransaksi=$_GET['column'];
			//$nospb=substr($noSpb,0,4);
			
			$str="select * from ".$dbname.".".$_GET['table']."  where notransaksi='".$notransaksi."' ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$kodePt=$bar['millcode'];
			$kdBrg=$bar['kodebarang'];
			$tgl=tanggalnormal($bar['tanggal']);
			$kdCust=$bar['kodecustomer'];
			
			//echo $posting; exit();	
			//ambil nama pt
			   $kodeorg=substr($notransaksi,0,4);
			   //print_r($kodeorg);
			   $str1="select * from ".$dbname.".organisasi where kodeorganisasi='PMO'"; 
			   $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			   $res1->setFetchMode(PDO::FETCH_OBJ);
			   while($bar1=$res1->fetch())
			   {
			   	 $nama=$bar1->namaorganisasi;
				 $alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
				 $telp=$bar1->telepon;	
				 $wilKota=$bar1->wilayahkota;			 
			   }    
			
			$sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where kodebarang='".$kdBrg."'";
			$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
			$qBrg->setFetchMode(PDO::FETCH_ASSOC);
			$rBrg=$qBrg->fetch();
			$nmBrg=$rBrg['namabarang'];
		
		$arrHead = setheadreport($kodeorg);
		$path=$arrHead['logo'];
		// $path='images/logo.jpg';
	    $this->Image($path,15,5,22);	
		$this->SetFont('Arial','B',10);
		$this->SetFillColor(255,255,255);	
		$this->SetX(45);   
	    $this->Cell(60,5,$arrHead['nama'],0,1,'L');	 
		$this->SetX(45); 		
	    $this->Cell(60,5,$arrHead['alamat'],0,1,'L');	
		$this->SetX(45); 			
		$this->Cell(60,5,"Tel: ".$arrHead['telepon'],0,1,'L');	
		$this->Line(10,30,200,30);
		$this->Ln();
		$this->Ln();
		$this->SetX(85);
		$this->SetFont('Arial','B',10);
		$this->Cell(35,5,strtoupper($_SESSION['lang']['pabrikTimbangan']." (EXTERNAL)"),0,1,'L');
		$this->Ln();
			$this->SetFont('Arial','',8);
			$this->Cell(35,5,$_SESSION['lang']['notransaksi'],'',0,'L');
			$this->Cell(2,5,':','',0,'L');
			$this->Cell(100,5,$notransaksi,'',0,'L');		
			$this->Cell(15,5,$_SESSION['lang']['tanggal'],'',0,'L');
			$this->Cell(2,5,':','',0,'L');
			$this->Cell(35,5,$tgl,0,1,'L');
//		$this->Cell(140,5,' ','',0,'R');
		$this->Cell(35,5,$_SESSION['lang']['kdpabrik'],'',0,'L');
		$this->Cell(2,5,':','',0,'L');
		$this->Cell(100,5,$kodePt,'',0,'L');		
		$this->Cell(15,5,'User','',0,'L');
		$this->Cell(2,5,':','',0,'L');
		$this->Cell(35,5,$_SESSION['standard']['username'],'',1,'L');
		$this->Ln();
	/*		$this->Cell(35,5,$_SESSION['lang']['nospb'],'',0,'L');
			$this->Cell(2,5,':','',0,'L');
			$this->Cell(100,5,$noSpb,'',0,'L');		
			$this->Cell(15,5,$_SESSION['lang']['page'],'',0,'L');
			$this->Cell(2,5,':','',0,'L');
			$this->Cell(35,5,$this->PageNo(),0,1,'L');
//		$this->Cell(140,5,' ','',0,'R');
		$this->Cell(35,5,$_SESSION['lang']['tanggal'],'',0,'L');
		$this->Cell(2,5,':','',0,'L');
		$this->Cell(100,5,tanggalnormal($res2['tanggal']),'',0,'L');		
		$this->Cell(15,5,'User','',0,'L');
		$this->Cell(2,5,':','',0,'L');
		$this->Cell(35,5,$_SESSION['standard']['username'],'',1,'L');*/

//			$this->Cell(140,5,' ','',0,'R');	
     $this->Ln();
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
	if(($kdBrg=='40000001')||($kdBrg=='40000005')||($kdBrg=='40000002'))
	{
		$arrStat=array("On","Off");
		$sTrans="select * from ".$dbname.".".$_GET['table']."  where notransaksi='".$notransaksi."' ";
		$qTrans=$owlPDO->query($sTrans) or die(print " Gagal: ".PDOException::getMessage());
		$qTrans->setFetchMode(PDO::FETCH_ASSOC);
		$rTrans=$qTrans->fetch();
		
		$sCust="select namacustomer  from ".$dbname.".pmn_4customer where kodecustomer='".$rTrans['kodecustomer']."'"; 
		$qCust=$owlPDO->query($sCust) or die(print " Gagal: ".PDOException::getMessage());
		$qCust->setFetchMode(PDO::FETCH_ASSOC);
		$rCust=$qCust->fetch(0);
		$pdf->SetFont('Arial','U',10);
		$pdf->Cell(35,5,"List Data",'',0,'L');
		$pdf->Ln();
		$pdf->SetFont('Arial','','10');
		$pdf->Cell(35,5,$_SESSION['lang']['namabarang'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$nmBrg,'',0,'L');
		$pdf->Ln();
		
		$pdf->Cell(35,5,$_SESSION['lang']['NoKontrak'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['nokontrak'],'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['nmcust'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rCust['namacustomer'],'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['nodo'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['nodo'],'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['statTimbangan'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$arrStat[$rTrans['timbangonoff']],'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['nosipb'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['nosipb'],'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['kodenopol'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['nokendaraan'],'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['sopir'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,ucfirst($rTrans['supir']),'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['beratkosong'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['beratmasuk']." KG",'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['beratBersih'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['beratbersih']." KG",'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['beratKeluar'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['beratkeluar']." KG",'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['jammasuk'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['jammasuk'],'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['jamkeluar'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['jamkeluar'],'',0,'L');	
		$pdf->Ln();
	}
	elseif($kdBrg=='40000004')
	{
		$arrStat=array("On","Off");
		$sTrans="select * from ".$dbname.".".$_GET['table']."  where notransaksi='".$notransaksi."' ";
		$qTrans=$owlPDO->query($sTrans) or die(print " Gagal: ".PDOException::getMessage());
		$qTrans->setFetchMode(PDO::FETCH_ASSOC);
		$rTrans=$qTrans->fetch();
		
		$sCust="select namacustomer  from ".$dbname.".pmn_4customer where kodecustomer='".$rTrans['kodecustomer']."'"; 
		$qCust=$owlPDO->query($sCust) or die(print " Gagal: ".PDOException::getMessage());
		$qCust->setFetchMode(PDO::FETCH_ASSOC);
		$rCust=$qCust->fetch();
		$pdf->SetFont('Arial','U',10);
		$pdf->Cell(35,5,"List Data",'',0,'L');
		$pdf->Ln();
		$pdf->SetFont('Arial','','10');
		$pdf->Cell(35,5,$_SESSION['lang']['namabarang'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$nmBrg,'',0,'L');
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['nmcust'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rCust['namacustomer'],'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['statTimbangan'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$arrStat[$rTrans['timbangonoff']],'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['kodenopol'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['nokendaraan'],'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['sopir'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,ucfirst($rTrans['supir']),'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['beratkosong'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['beratmasuk']." KG",'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['beratBersih'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['beratbersih']." KG",'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['beratKeluar'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['beratkeluar']." KG",'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['jammasuk'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['jammasuk'],'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['jamkeluar'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['jamkeluar'],'',0,'L');	
		$pdf->Ln();
	}
	elseif($kdBrg=='40000003')
	{
		$arrStat=array("On","Off");
		$arrOptIntex=array("External","Internal","Afiliasi");
		$sTrans="select * from ".$dbname.".".$_GET['table']."  where notransaksi='".$notransaksi."' ";
		$qTrans=$owlPDO->query($sTrans) or die(print " Gagal: ".PDOException::getMessage());
		$qTrans->setFetchMode(PDO::FETCH_ASSOC);
		$rTrans=$qTrans->fetch();
		
		
		$pdf->SetFont('Arial','U',10);
		$pdf->Cell(35,5,"List Data",'',0,'L');
		$pdf->Ln();
		$pdf->SetFont('Arial','','10');
		$pdf->Cell(35,5,$_SESSION['lang']['namabarang'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$nmBrg,'',0,'L');
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['statTimbangan'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$arrStat[$rTrans['timbangonoff']],'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['nospb'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['nospb'],'',0,'L');	
		$pdf->Ln();
		
		$pdf->Cell(35,5,$_SESSION['lang']['ticket'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['norefrensi'],'',0,'L');	
		$pdf->Ln();
		
		$pdf->Cell(35,5,$_SESSION['lang']['statusBuah'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$arrOptIntex[$rTrans['intex']],'',0,'L');	
		$pdf->Ln();
		if($rTrans['intex']=='0')
		{
			$sSupp="select namasupplier  from ".$dbname.".log_5supplier where supplierid='".$rTrans['kodecustomer']."'"; 
			$qSupp=$owlPDO->query($sSupp) or die(print " Gagal: ".PDOException::getMessage());
			$qSupp->setFetchMode(PDO::FETCH_ASSOC);
			$rSupp=$qSupp->fetch();
			$pdf->Cell(35,5,$_SESSION['lang']['namasupplier '],'',0,'L');
			$pdf->Cell(2,5,':','',0,'L');
			$pdf->Cell(100,5,$rSupp['namasupplier'],'',0,'L');	
			$pdf->Ln();		
		}
		elseif(($rTrans['intex']=='1')||($rTrans['intex']=='2'))
		{
			$sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$rTrans['kodeorg']."'";
			$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
			$qOrg->setFetchMode(PDO::FETCH_ASSOC);
			$rOrg=$qOrg->fetch();
			$pdf->Cell(35,5,$_SESSION['lang']['kebun'],'',0,'L');
			$pdf->Cell(2,5,':','',0,'L');
			$pdf->Cell(100,5,$rOrg['namaorganisasi'],'',0,'L');	
			$pdf->Ln();			
		}
		$pdf->Cell(35,5,$_SESSION['lang']['thntanam'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['thntm1'],'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['jmlhTandan'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['jumlahtandan1'],'',0,'L');	
		$pdf->Ln();
		// $pdf->Cell(35,5,$_SESSION['lang']['thntanam']." 2",'',0,'L');
		// $pdf->Cell(2,5,':','',0,'L');
		// $pdf->Cell(100,5,$rTrans['thntm2'],'',0,'L');	
		// $pdf->Ln();
		// $pdf->Cell(35,5,$_SESSION['lang']['jmlhTandan']." 2",'',0,'L');
		// $pdf->Cell(2,5,':','',0,'L');
		// $pdf->Cell(100,5,$rTrans['jumlahtandan2']." KG",'',0,'L');	
		// $pdf->Ln();
		// $pdf->Cell(35,5,$_SESSION['lang']['thntanam']." 3",'',0,'L');
		// $pdf->Cell(2,5,':','',0,'L');
		// $pdf->Cell(100,5,$rTrans['thntm3'],'',0,'L');	
		// $pdf->Ln();
		// $pdf->Cell(35,5,$_SESSION['lang']['jmlhTandan']." 3",'',0,'L');
		// $pdf->Cell(2,5,':','',0,'L');
		// $pdf->Cell(100,5,$rTrans['jumlahtandan3']." KG",'',0,'L');	
		// $pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['brondolan'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['brondolan']." Kg",'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['kodenopol'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['nokendaraan'],'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['sopir'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,ucfirst($rTrans['supir']),'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['beratkosong'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['beratmasuk']." KG",'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['beratBersih'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['beratbersih']." KG",'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['beratKeluar'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['beratkeluar']." KG",'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['statusSortasi'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['statussortasi'],'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['petugasSortasi'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,ucfirst($rTrans['petugassortasi']),'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['jammasuk'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['jammasuk'],'',0,'L');	
		$pdf->Ln();
		$pdf->Cell(35,5,$_SESSION['lang']['jamkeluar'],'',0,'L');
		$pdf->Cell(2,5,':','',0,'L');
		$pdf->Cell(100,5,$rTrans['jamkeluar'],'',0,'L');	
		$pdf->Ln();
	}

	
	
	$pdf->Output();
?>
