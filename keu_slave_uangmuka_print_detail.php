<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');
include_once('lib/terbilang.php');

$proses = $_GET['proses'];
$param = $_GET;
$urlefil=checkPostGet('urlefil','0');

/** Report Prep **/
$cols = array();

#=============================== Header =======================================
$where = "notransaksi='".$param['notransaksi']."'";
//$queryH = selectQuery($dbname,'keu_uangmuka','*',$where);

$sql = "select a.*,b.induk from ".$dbname.".keu_uangmuka a inner join ".$dbname.".organisasi b on (a.unit=b.kodeorganisasi) where ".$where." ";
//exit("Error ".$sql);
$resH = fetchData($sql);
$dataH = $resH[0];

#=============================== Detail =======================================
# Data
$query = selectQuery($dbname,'keu_tagihandt','*',$where);
$res = fetchData($query);

# Options
$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun like '116%' and detail=1");
$optPt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
                                        "kodeorganisasi='".$dataH['induk']."'");
$tab = ($dataH['tipeinvoice']=='p')? 'log_poht': 'log_spkht';
$cond = ($dataH['tipeinvoice']=='p')? 'nopo': 'notransaksi';

if($tab=='log_poht')
{
    $qSupp = "select b.namasupplier".(($dataH['tipeinvoice']=='p')? ',a.matauang':'')."
        from ".$dbname.".".$tab." a
        left join ".$dbname.".log_5supplier b on a.kodesupplier=b.supplierid
        where ".$cond."='".$dataH['nopo']."'";    
}
else
{
    $qSupp = "select b.namasupplier".(($dataH['tipeinvoice']=='p')? ',a.matauang':'')."
        from ".$dbname.".".$tab." a
        left join ".$dbname.".log_5supplier b on a.koderekanan=b.supplierid
        where ".$cond."='".$dataH['nopo']."'";
}    

//exit("Error:$qSupp");

$resSupp = fetchData($qSupp);
if(!isset($resSupp[0]['namasupplier']) || $resSupp[0]['namasupplier']==''){
    $str=$owlPDO->query("select   b.namasupplier,a.kodesupplier from ".$dbname.".keu_tagihanht a left join log_5supplier b
              on a.kodesupplier=b.supplierid where a.noinvoice='".$param['noinvoice']."'");
    $str->setFetchMode(PDO::FETCH_OBJ);
    $res=$str->fetch();
	if($res->namasupplier=='')
	{
		$optNmsp = makeOption($dbname,'log_5klsupplier','kode,kelompok',"kode='".$res->kodesupplier."'");
		$resSupp[0]['namasupplier'] = $optNmsp[$res->kodesupplier];
	}
	else
	{
		$resSupp[0]['namasupplier']=$res->namasupplier;
	}
}
#=============================== Detail =======================================
# Data
$optJenis=makeOption($dbname,"keu_5jenistagihan","kode,source","kode='".$dataH['tipeinvoice']."'");
if(substr($optJenis[$dataH['tipeinvoice']],0,3)=='htg'){
	$col1 = 'noinvoice,noakun,sum(nilai) as nilai,keterangan,kodevhc,kodeasset';
	$where.=" group by noakun";
}else{
	$col1 = '*';
}
$query_detail = selectQuery($dbname,'keu_tagihandt',$col1,$where);
$red 		  = fetchData($query_detail);

$col2 = 'noakun,namaakun';
$queryakun = selectQuery($dbname,'keu_5akun',$col2,'');
$rea 		  = fetchData($queryakun);

$col3 = 'kode,jurnal';
$queryjenis = selectQuery($dbname,'keu_5jenistagihan',$col3,'');
$rej 		  = fetchData($queryjenis);


function find_dom($array,$code,$compare,$findout){
	$result = "";
	foreach($array as $r){
		if($code == $r[$compare]){
			$result = $r[$findout];
			break;
		}
	}
	return $result;
}


# Data Empty
//if(empty($red)) {
//    echo 'Data Empty';
//    exit;
//}

#================================ Prep Data ===================================

	$title = "NOTA UANG MUKA";	


/** Output Format **/
switch($proses) {
    case 'pdf':
        
		if(!class_exists('PDFINV')){
			class PDFINV extends FPDF
			{
				function Header()
				{
					global $conn;
					global $dbname;
					global $userid;
					global $notransaksi;
					global $kodevhc;
					global $posting;
					global $kodept;
					global $param;
					global $owlPDO;
					global $dataH;
					global $title;

					
					
					
					//ambil nama pt
					$arrHead = setheadreport('',$dataH['induk']);
				
					
					$width = $this->w - $this->lMargin - $this->rMargin;
					$height = 5;
					$path=$arrHead['logo'];
					$this->Image($path,$this->lMargin,($this->tMargin-8),0,20);
					$this->SetFont('Arial','B',9);
					$this->SetFillColor(255,255,255);	
					$this->Ln(-5);
					$this->SetX(35);   
					$this->Cell($width-100,$height,$arrHead['nama'],0,1,'L');	 
					$this->SetX(35); 		
					$this->Cell($width-100,$height,$arrHead['alamat'],0,1,'L');	
					$this->SetX(35); 			
					$this->Cell($width-100,$height,"Tel: ".$arrHead['telepon'],0,1,'L');	
					$this->Line($this->lMargin,$this->tMargin+($height*3),
					$this->lMargin+$width,$this->tMargin+($height*3));
					$this->Ln();
					$this->Ln();
					$this->SetFont('Arial','',10);
					$this->Cell($width,$height,$title,0,1,'C');	 
					
					$this->Ln();
									
				}

				function Footer()
				{
					$width = $this->w - $this->lMargin - $this->rMargin;
					$height = 12;
					$this->SetY(-20);
					$this->SetFont('Arial','I',7);
					$this->Cell(1,$height,'Page '.$this->PageNo(),'T',0,'L');
					$str = "Printed by ".$_SESSION['standard']['username']."[".$_SESSION['empl']['lokasitugas']."]".
							":".@$rPeriode['periode']." at ".date('d-m-Y H:i:s');
					$this->Cell($width-1,$height,$str,'T',0,'R');
				}
			}
		}
		
		
		$pdf=new PDFINV('P','mm','A4');
        // $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 4;
		
        // $pdf->_noThead=true;
        $pdf->_title = $title;
        // $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;

        $pdf->AddPage();
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',9);

        switch($dataH['tipeinvoice']) {
            case 'p':
                    $tipe = 'PO';
                    break;
            case 'k':
                    $tipe = 'SPK';
                    break;
        }

        $pdf->Ln();
        // Header
        $startY = $pdf->GetY();
		$width1=30;
        $pdf->Cell($width1,$height,$_SESSION['lang']['notransaksi'],0,0,'L');
		$pdf->Cell(5,$height,':',0,0,'L');
	    $pdf->Cell(25,$height,$dataH['notransaksi'],0,1,'L');
	    
	    $pdf->Cell($width1,$height,$_SESSION['lang']['pt'],0,0,'L');
		$pdf->Cell(5,$height,':',0,0,'L');
        $pdf->Cell(25,$height,$optPt[$dataH['induk']],0,1,'L');
        
        $pdf->Cell($width1,$height,$_SESSION['lang']['tanggal'],0,0,'L');
		$pdf->Cell(5,$height,':',0,0,'L');
        $pdf->Cell(25,$height,tanggalnormal($dataH['tanggal']),0,1,'L');
        
        $pdf->Cell($width1,$height,$_SESSION['lang']['keterangan'],0,0,'L');
		$pdf->Cell(5,$height,':',0,0,'L');
        $pdf->MultiCell(70,$height,$dataH['keterangan'],0,'J');

        
        $pdf->Cell($width1,$height,$_SESSION['lang']['nilai'],0,0,'L');
        $pdf->Cell(5,$height,':',0,0,'L');
        $pdf->Cell(25,$height,number_format($dataH['nilaiuangmuka'],2),0,1,'L');
		
		$pdf->Cell($width1,$height,$_SESSION['lang']['unit'],0,0,'L');
        $pdf->Cell(5,$height,':',0,0,'L');
        $pdf->Cell(25,$height,$dataH['unit'],0,1,'L');
		

        $optNmPosting=makeOption($dbname,"datakaryawan","karyawanid,namakaryawan","karyawanid='".$dataH['postingby']."'");
        $pdf->Cell($width1,$height,$_SESSION['lang']['dipostingoleh'],0,0,'L');
        $pdf->Cell(5,$height,':',0,0,'L');
        $pdf->Cell(25,$height,$optNmPosting[$dataH['postingby']],0,1,'L');
				   
		$startYhdetail = $pdf->GetY() + 10;  
		
		
		$optNmuser=makeOption($dbname,"datakaryawan","karyawanid,namakaryawan","karyawanid='".$dataH['updateby']."'");

		$pdf->Ln(15);
		$pdf->SetX(40);
		$pdf->Cell(50,$height,'Pembuat',0,0,'L');
		$pdf->Cell(50,$height,'',0,0,'L');
		$pdf->Cell(50,$height,$_SESSION['lang']['menyetujui'],0,1,'L');
		$pdf->SetX(40);
		$pdf->Cell(50,20,'',0,0,'L');
		$pdf->Cell(50,20,'',0,0,'L');
		$pdf->Cell(50,20,'',0,1,'L');
		$pdf->SetX(40);
        $pdf->Cell(50,$height,$optNmuser[$dataH['updateby']],0,0,'L');  
        $pdf->Cell(50,$height,'',0,'L');  
        $pdf->Cell(50,$height,$optNmPosting[$dataH['postingby']],0,1,'L');

        $pdf->SetX(40);
        $pdf->Cell(50,$height,'',0,'L');  
        $pdf->Cell(50,$height,'',0,0,'L');  
        $pdf->Cell(50,$height,$dataH['postingdate'],0,1,'L');  

        
	    # Print Out
		if($urlefil=='0'){
			$pdf->Output();
		}else{
			$pdf->Output($urlefil);
		}
    break;
		
	case 'file':
		if($dataH['uploadinvoice']!=""){
			$doc = $dataH['uploadinvoice'];
			$potong=explode('.',$doc);
			if($potong[1]=='pdf'){
				echo"<embed src=\"filegis/".$doc."\" width=780px height=370px>";
			} else {
				echo"<img src=\"filegis/".$doc."\">";
			}
		}else{
			echo $_SESSION['lang']['tidakditemukan'];
		}
		
		break;
		
    default:
    break;
}
?>