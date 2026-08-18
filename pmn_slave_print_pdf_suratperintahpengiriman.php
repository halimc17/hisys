<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/zLib.php');
include_once('lib/terbilang.php');

$tmp=explode(',',$_GET['column']);
$notran=$tmp[0];
$jabatanTtd=makeOption($dbname, 'pmn_5ttd', 'nama,jabatan');
$nmkepada=makeOption($dbname, 'pmn_5kepada', 'id,kepada');

$expnotran = explode('/',$notran);
$expnotran = explode('-',$expnotran[1]);
$kdpt = $expnotran[0];

$qKepada = selectQuery($dbname, 'pmn_5kepada', 'id,kepada,alamat');
$resKepada = fetchData($qKepada);
$optKepada = array();
foreach($resKepada as $row) {
	$optKepada[$row['id']] = array(
		'kepada' => $row['kepada'],
		'alamat' => $row['alamat']
	);
}

$str="select a.*,b.nokontrakexternal,b.kodept,b.kuantitaskontrak,b.satuan,b.kodebarang,b.ffa,b.mdani,b.grading,
	b.dobi,b.moist,c.namabarang,c.inisial,d.namacustomer,d.alamat as alamatpelanggan,
	d.kota,d.telepon,f.namaorganisasi,f.alamat,f.wilayahkota from ".$dbname.".pmn_suratperintahpengiriman a
	left join ".$dbname.".pmn_kontrakjual b
	on a.nokontrak = b.nokontrak 
	left join ".$dbname.".log_5masterbarang c
	on b.kodebarang = c.kodebarang 
	left join ".$dbname.".pmn_4customer d
	on b.koderekanan = d.kodecustomer 
	left join ".$dbname.".organisasi f
	on b.kodept = f.kodeorganisasi
	where a.nodo='".$notran."'";
	

	
$nPasar=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$nPasar->setFetchMode(PDO::FETCH_ASSOC);
$data=$nPasar->fetch();
	
		$kdpt=$data['kodept'];

	
	
	if($data['kodebarang']!='40000003')
	{
		if (!class_exists('PDF')){
			class PDF extends FPDF
			{
				
				function Header(){	
					global $kdpt;
					global $height;
				
					$arrHead = setheadreport("",$kdpt);
					$path=$arrHead['logo'];
					$this->SetFont('Arial','B',9);
					$this->SetFillColor(255,255,255);	
					$this->SetX(30);
					$this->Cell(180,$height,$arrHead['nama'],0,1,'L');
					$this->SetX(30);
					$this->Cell(180,$height,$arrHead['alamat'],0,1,'L');	
					$this->SetX(30);
					$this->Cell(180,$height,"Tel: ".$arrHead['telepon'],0,1,'L');	
					$akhiry=$this->GetY();
					
					$this->Line(0,$akhiry,300,$akhiry);
					$this->Line(0,$akhiry+1,300,$akhiry+1);
					$this->Ln();
				}
				
				function Footer()
				{
					$this->SetY(-15);
					$this->SetFont('Arial','I',10);
				}

			}
		}
			
		$pdf=new PDF('P','mm','A4');
		$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
		$height = 5;
		$pdf->AddPage();
		
		
		$pdf->ln(-3);
	
		$pdf->SetX(30);
		$pdf->SetFont('Arial','',10);
		$pdf->SetFillColor(255,255,255);	
		$pdf->Cell(20,$height,$_SESSION['lang']['kepada']." Yth.",0,1,'L');
		
		//GET NAMA PT
		$arrHead = setheadreport("",$kdpt);
		
		$pdf->SetFont('Arial','B',10);
		$pdf->SetX(35);
		// $pdf->Cell(20,$height,"MILL MANAGER PMKS ".$arrHead['nama'],0,1,'L');
		$pdf->Cell(20,$height,$nmkepada[$data['kepada']],0,1,'L');
		$pdf->Ln(5);
		
		
		
		$pdf->SetFont('Arial','B',16);
		$pdf->SetFillColor(255,255,255);
		$pdf->Cell(190,5,strtoupper('DELIVERY ORDER'),0,1,'C');
		$pdf->SetFont('Arial','UB',10);
		$pdf->SetFillColor(255,255,255);
		$pdf->Cell(190,5,"No : ".$notran,0,1,'C');
		$pdf->SetFont('Arial','',10);
		$pdf->SetFillColor(255,255,255);
		$x=$pdf->GetX();
		
		if($data['nokontrakexternal']!=''){
			$pdf->Cell(190,5,"Kontrak No. ".$data['nokontrakexternal'],0,0,'C');
		}else{
			$pdf->Cell(190,5,"Kontrak No. ".$data['nokontrak'],0,0,'C');
		}
		
		
		$pdf->SetX($x);
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(180,5,"ASLI",0,1,'R');
		
		$akhiry=$pdf->GetY();
		
		$pdf->Line(0,$akhiry,300,$akhiry);
		$pdf->ln(2);
		
		$pdf->SetX(30);
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(180,5,"Harap diserahkan kepada :",0,1,'L');
		
		$pdf->SetX(35);
		$pdf->Cell(45,5,"Nama Konsumen",0,0,'L');
		$pdf->Cell(3,5,":",0,0,'L');
		$pdf->Cell(105,5,$data['namacustomer'],0,1,'L');
		
		$pdf->SetX(35);
		$pdf->Cell(45,5,"Jenis & Spesifikasi Produk",0,0,'L');
		$pdf->Cell(3,5,":",0,0,'L');
		$pdf->Cell(105,5,$data['namabarang'],0,1,'L');
		
		$pdf->SetX(35);
		$pdf->Cell(45,5,"Jumlah Berat",0,0,'L');
		$pdf->Cell(3,5,":",0,0,'L');
		$pdf->Cell(105,5,number_format($data['qty'])." Kg (".ucfirst(terbilang($data['qty'],2))." Kilogram)",0,1,'L');
		
		$pdf->SetX(35);
		$pdf->Cell(45,5,"Diambil",0,0,'L');
		$pdf->Cell(3,5,":",0,0,'L');
		$pdf->Cell(105,5,$data['waktupenyerahan'],0,1,'L');
		
		if($data['transportir']=='')
		{
			$transportir = $data['namacustomer'];
		}
		else
		{
			$optTrans=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$data['transportir']."'");
			$transportir = $optTrans[$data['transportir']];
		}
		
		$pdf->SetX(35);
		$pdf->Cell(45,5,"Angkutan",0,0,'L');
		$pdf->Cell(3,5,":",0,0,'L');
		$pdf->Cell(105,5,$transportir,0,1,'L');
		
		if($data['kodebarang']=='40000001')
		{
			$pdf->SetX(30);
			$pdf->Cell(45,5,"Mutu tidak boleh melewati untuk :",0,1,'L');
			
			$pdf->SetX(35);
			$pdf->Cell(60,5,$data['inisial'],1,1,'C');
			
			$pdf->SetX(35);
			$pdf->Cell(30,5,"FFA",'LRB',0,'L');
			$pdf->Cell(30,5,number_format($data['ffa'],2)." %",'RB',1,'R');
		
			$pdf->SetX(35);
			$pdf->Cell(30,5,"M & I",'LRB',0,'L');
			$pdf->Cell(30,5,number_format($data['mdani'],2)." %",'RB',1,'R');
		
			if($data['dobi']!=0){
			$pdf->SetX(35);
			$pdf->Cell(30,5,"Dobi",'LRB',0,'L');
			$pdf->Cell(30,5,number_format($data['dobi'],2)." %",'RB',1,'R');
			}
		}
		if($data['kodebarang']=='40000002')
		{
			$pdf->SetX(30);
			$pdf->Cell(45,5,"Mutu tidak boleh melewati untuk :",0,1,'L');
			
			$pdf->SetX(35);
			$pdf->Cell(60,5,$data['inisial'],1,1,'C');
			
			if($data['ffa'] > 0){
				$pdf->SetX(35);
				$pdf->Cell(30,5,"FFA",'LRB',0,'L');
				$pdf->Cell(30,5,number_format($data['ffa'],2)." %",'RB',1,'R');
			}
			
			$pdf->SetX(35);
			$pdf->Cell(30,5,"KOTORAN",'LRB',0,'L');
			$pdf->Cell(30,5,number_format($data['grading'],2)." %",'RB',1,'R');
		
			$pdf->SetX(35);
			$pdf->Cell(30,5,"AIR",'LRB',0,'L');
			$pdf->Cell(30,5,number_format($data['moist'],2)." %",'RB',1,'R');
		}
		
		$pdf->Ln(10);
		
		$optLokasi = makeOption($dbname,'pmn_5lokasikontrak','inisial,lokasi',"inisial='".$data['lokasi']."'");
		$tgl = explode('-',$data['tanggaldo']);
		$bln = numToMonth($tgl[1],'I','long');
		$tgl = $tgl[2]." ".$bln." ".$tgl[0];
		$pdf->SetX(30);
		$pdf->Cell(50,5,"",0,0,'C');
		$pdf->Cell(60,5,"",0,0,'C');
		$pdf->Cell(50,5,ucfirst($optLokasi[$data['lokasi']]).", ".$tgl,0,1,'C');

		$pdf->SetX(30);
		$pdf->Cell(50,5,"",0,0,'C');
		$pdf->Cell(60,5,"",0,0,'C');
		$pdf->Cell(50,5,"Disetujui Oleh,",0,1,'C');
		
		$pdf->Ln(15);
		
		$pdf->SetFont('Arial','B',10);
		$pdf->SetX(30);
		$pdf->Cell(50,5,"",0,0,'C');
		$pdf->Cell(60,5,"",0,0,'C');
		$pdf->Cell(50,5,"WIRYADINATA",0,1,'C');
	}
	else
	{
		if (!class_exists('PDF')){
			class PDF extends FPDF
			{
				
				function Header(){	
					$this->ln(15);
					global $kdpt;
					$arrHead = setheadreport("",$kdpt);
					$path=$arrHead['logo'];
					$this->SetFont('Arial','B',12);
					$this->SetFillColor(255,255,255);
					$this->SetX(30);
					$this->SetFont('Arial','',10);
					$this->Cell(180,$height,$arrHead['nama'],0,1,'L');
					$this->ln(5);	
					$this->SetX(30);
					$this->Cell(180,$height,$arrHead['alamat'],0,1,'L');	
					$this->ln(5);	
					$this->SetX(30);
					$this->Cell(180,$height,"Tel: ".$arrHead['telepon'],0,1,'L');	
					$this->Ln();
				}
				
				function Footer()
				{
					$this->SetY(-15);
					$this->SetFont('Arial','I',10);
				}

			}
		}
			
		$pdf=new PDF('P','mm','A4');
		$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
		$height = 5;
		$pdf->AddPage();
		$pdf->ln(15);
	
		$pdf->SetX(30);
		$pdf->SetFont('Arial','',10);
		$pdf->SetFillColor(255,255,255);	
		$pdf->Cell(20,$height,$_SESSION['lang']['kepada']." Yth.",0,1,'L');
		
		//GET NAMA PT
		$arrHead = setheadreport("",$kdpt);
		
		$pdf->SetX(30);
		$pdf->Cell(20,$height,"EM ".$arrHead['nama'],0,1,'L');
		
		$pdf->Ln(15);
		
		$pdf->SetFont('Arial','B',16);
		$pdf->SetFillColor(255,255,255);
		$pdf->Cell(190,5,strtoupper('DELIVERY ORDER'),0,1,'C');
		$pdf->SetFont('Arial','UB',10);
		$pdf->SetFillColor(255,255,255);
		$pdf->Cell(190,5,"No : ".$notran,0,1,'C');
		$pdf->SetFont('Arial','',10);
		$pdf->SetFillColor(255,255,255);
		$x=$pdf->GetX();
		$pdf->Cell(190,5,"Kontrak No. ".$data['nokontrak'],0,1,'C');
		$pdf->SetX($x);
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(180,5,"ASLI",0,1,'R');
		
		$pdf->Line(30,95,190,95);
		
		$pdf->ln(10);
		
		$pdf->SetX(30);
		$pdf->SetFont('Arial','',10);
		$pdf->SetX(35);
		$pdf->Cell(45,10,"Harap diserahkan kepada",0,0,'L');
		$pdf->Cell(3,10,":",0,0,'L');
		$pdf->Cell(105,10,$data['namacustomer'],0,1,'L');
		
		$pdf->SetX(35);
		$pdf->Cell(45,10,"Nama Konsumen",0,0,'L');
		$pdf->Cell(3,10,":",0,0,'L');
		$pdf->Cell(105,10,$data['namacustomer'],0,1,'L');
		
		$pdf->SetX(35);
		$pdf->Cell(45,10,"Jenis & Spesifikasi Produk",0,0,'L');
		$pdf->Cell(3,10,":",0,0,'L');
		$pdf->Cell(105,10,$data['namabarang'],0,1,'L');
		
		$pdf->SetX(35);
		$pdf->Cell(45,10,"Kwantitas",0,0,'L');
		$pdf->Cell(3,10,":",0,0,'L');
		$pdf->Cell(105,10,number_format($data['qty'])." Kg (".ucfirst(terbilang($data['qty'],2))." Kilogram)",0,1,'L');
		$pdf->ln(2.5);
		$pdf->SetX(35);
		$pdf->Cell(45,5,"Masa berlaku DO",0,0,'L');
		$pdf->Cell(3,5,":",0,0,'L');
		$pdf->Cell(105,5,"Pengambilan TBS berlaku dari tanggal",0,1,'L');
		$pdf->SetX(35);
		$pdf->Cell(45,5,"",0,0,'L');
		$pdf->Cell(3,5,"",0,0,'L');
		$pdf->Cell(105,5,$data['waktupenyerahan'],0,1,'L');
		
		$pdf->Ln(10);
		
		$optLokasi = makeOption($dbname,'pmn_5lokasikontrak','inisial,lokasi',"inisial='".$data['lokasi']."'");
		$tgl = explode('-',$data['tanggaldo']);
		$bln = numToMonth($tgl[1],'I','long');
		$tgl = $tgl[2]." ".$bln." ".$tgl[0];
		$pdf->SetX(30);
		$pdf->Cell(50,5,"",0,0,'C');
		$pdf->Cell(60,5,"",0,0,'C');
		$pdf->Cell(50,5,ucfirst($optLokasi[$data['lokasi']]).", ".$tgl,0,1,'C');

		$pdf->SetX(30);
		$pdf->Cell(50,5,"",0,0,'C');
		$pdf->Cell(60,5,"",0,0,'C');
		$pdf->Cell(50,5,$arrHead['nama'],0,1,'C');
		
		$pdf->Ln(15);
		
		$pdf->SetFont('Arial','U',10);
		$pdf->SetX(30);
		$pdf->Cell(50,5,"",0,0,'C');
		$pdf->Cell(60,5,"",0,0,'C');
		$pdf->Cell(50,5,$data['ttd'],0,1,'C');
		$pdf->SetFont('Arial','',10);
		$pdf->SetX(30);
		$pdf->Cell(50,5,"",0,0,'C');
		$pdf->Cell(60,5,"",0,0,'C');
		$pdf->Cell(50,5,$jabatanTtd[$data['ttd']],0,1,'C');
	}
	
	$urlefil=checkPostGet('urlefil','0');
	if($urlefil=='0'){
		$pdf->Output();
	}else{
		$pdf->Output($urlefil);
	}
?>