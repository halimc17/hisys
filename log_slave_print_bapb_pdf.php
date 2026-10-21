<?
require_once('config/connection.php');
$mobileValid = false;
if(isset($_POST['par']) || isset($_GET['par'])){
	$validasiPostMobile = explode(" ", $_POST['par']);
	$validasiGetMobile = explode(" ", $_GET['par']);
	if($validasiGetMobile[0] == "owlApp" or $validasiPostMobile[0] == "owlApp"){
		$mobileValid = true;
		$strlang=$owlPDO->query("select legend,ID from ".$dbname.".bahasa order by legend");
		$strlang->setFetchMode(PDO::FETCH_NUM);
		while($barlang=$strlang->fetch()) {
			$_SESSION['lang'][$barlang[0]]=$barlang[1];
		}
	};
}

if($mobileValid == false){//untuk redirec dari mobile
	require_once('master_validation.php');
}
require_once('lib/nangkoelib.php');
require_once('lib/zFunction.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
$nodok = checkPostGet('notransaksi','0');
$optOrganisasi = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

$str="select * from ".$dbname.".log_transaksiht where notransaksi='".$_GET['notransaksi']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	$kodept=$bar->kodept;
	$kodegudang=$bar->kodegudang;
	$userid=$bar->user;
	$posted=$bar->postedby;
	$aproved1=$bar->persetujuan1;
	$aproved2=$bar->persetujuan2;
	$stt1=$bar->hasilpersetujuan1;
	$stt2=$bar->hasilpersetujuan2;
	$status=$bar->post;
	$tanggal=$bar->tanggal;
	$lastupdate=$bar->lastupdate;
	$idsupplier=$bar->idsupplier;
	$nosj=$bar->nosj;
	$nofaktur=$bar->nofaktur;	
	$nopo=$bar->nopo;		
	if($status==0)
	 $status='Not Confirm';
	else
	 $status='Confirmed'; 
	//ambil nama pt
}

//=============
if (!class_exists('PDFGRN')){
	class PDFGRN extends FPDF
	{
		# lebar kolom tabel barang: No, Kode, Nama, Satuan, Kuantitas (total 190 mm)
		var $kolom = array(10, 28, 102, 20, 30);

		function Header()
		{
			global $dbname;
			global $owlPDO;
			global $optOrganisasi;

			$str="select * from ".$dbname.".log_transaksiht where notransaksi='".$_GET['notransaksi']."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while($bar=$res->fetch())
			{
				$kodept=$bar->kodept;
				$kodegudang=$bar->kodegudang;
				$tanggal=$bar->tanggal;
				$nosj=$bar->nosj;
				$nofaktur=$bar->nofaktur;
				$nopo=$bar->nopo;
				$status=($bar->post==0)?'Not Confirm':'Confirmed';
			}
			$namapt='';
			$alamatpt='';
			$telp='';
			$str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$kodept."'";
			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_OBJ);
			while($bar1=$res1->fetch())
			{
				$namapt=$bar1->namaorganisasi;
				$alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
				$telp=$bar1->telepon;
			}

			$arrHead = setheadreport($kodept,$kodept);
			$path=$arrHead['logo'];

			#= kop: logo di kiri, judul di tengah, garis di bawahnya (sama dengan Bukti Penerimaan Barang)
			if($path!='' && file_exists($path)){
				$this->Image($path,10,8,0,13);
			}
			$this->SetFont('Arial','B',13);
			$this->SetXY(10,11);
			$this->Cell(190,7,strtoupper($_SESSION['lang']['bapb']),0,0,'C');
			$this->SetLineWidth(0.4);
			$this->Line(10,24,200,24);
			$this->SetLineWidth(0.2);

			#= nama PT di kiri
			$this->SetXY(10,27);
			$this->SetFont('Arial','B',10);
			$this->Cell(105,5,$namapt,0,2,'L');
			$this->SetFont('Arial','',8);
			$this->MultiCell(105,4,$alamatpt,0,'L');
			$this->Cell(105,4,"Tel: ".$telp,0,1,'L');
			$yKiri=$this->GetY();

			#= keterangan dokumen di kanan
			$this->SetFont('Arial','',8.5);
			$y=27;
			$baris=array(
				array($_SESSION['lang']['sloc'],$optOrganisasi[$kodegudang]." - ".$kodegudang),
				array($_SESSION['lang']['docnum'],$_GET['notransaksi']),
				array($_SESSION['lang']['tanggal'],tanggalnormal($tanggal)),
				array($_SESSION['lang']['nopo'],$nopo)
			);
			if($nosj!=''){
				$baris[]=array($_SESSION['lang']['suratjalan'],$nosj);
			}
			if($nofaktur!=''){
				$baris[]=array($_SESSION['lang']['faktur'],$nofaktur);
			}
			$baris[]=array($_SESSION['lang']['docstatus'],$status);
			foreach($baris as $b){
				$this->SetXY(120,$y);
				$this->Cell(27,4.5,$b[0],0,0,'L');
				$this->Cell(53,4.5,': '.$b[1],0,0,'L');
				$y+=4.5;
			}
			$this->SetY(max($yKiri,$y)+4);
		}

		function Footer()
		{
			$this->SetY(-14);
			$this->SetFont('Arial','I',7);
			$this->Cell(95,5,'Print time : '.date('d-m-Y H:i:s'),0,0,'L');
			$this->Cell(95,5,'Halaman '.$this->PageNo().' dari {nb}',0,0,'R');
		}

		# jumlah baris yang dibutuhkan teks pada lebar w (dipakai agar baris tabel menyesuaikan tinggi)
		function NbLines($w,$txt)
		{
			$cw=$this->CurrentFont['cw'];
			$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
			$s=str_replace("\r",'',$txt);
			$nb=strlen($s);
			if($nb>0 && $s[$nb-1]=="\n"){
				$nb--;
			}
			$sep=-1;
			$i=0;
			$j=0;
			$l=0;
			$nl=1;
			while($i<$nb){
				$c=$s[$i];
				if($c=="\n"){
					$i++;
					$sep=-1;
					$j=$i;
					$l=0;
					$nl++;
					continue;
				}
				if($c==' '){
					$sep=$i;
				}
				$l+=$cw[$c];
				if($l>$wmax){
					if($sep==-1){
						if($i==$j){
							$i++;
						}
					}else{
						$i=$sep+1;
					}
					$sep=-1;
					$j=$i;
					$l=0;
					$nl++;
				}else{
					$i++;
				}
			}
			return $nl;
		}

		function KepalaTabel()
		{
			$w=$this->kolom;
			$this->SetFont('Arial','B',9);
			$this->SetFillColor(225,225,225);
			$this->Cell($w[0],6,'No',1,0,'C',1);
			$this->Cell($w[1],6,$_SESSION['lang']['kodebarang'],1,0,'C',1);
			$this->Cell($w[2],6,$_SESSION['lang']['namabarang'],1,0,'C',1);
			$this->Cell($w[3],6,$_SESSION['lang']['satuan'],1,0,'C',1);
			$this->Cell($w[4],6,$_SESSION['lang']['kuantitas'],1,1,'C',1);
		}

		function BarisBarang($no,$kode,$nama,$satuan,$jumlah,$catatan)
		{
			$w=$this->kolom;
			$this->SetFont('Arial','',9);
			$hNama=5*$this->NbLines($w[2],$nama);
			$hCatatan=0;
			if($catatan!=''){
				$this->SetFont('Arial','I',8);
				$hCatatan=4*$this->NbLines($w[2],'Catatan : '.$catatan);
			}
			$tinggi=$hNama+$hCatatan+2;
			if($this->GetY()+$tinggi>262){
				$this->AddPage();
				$this->KepalaTabel();
			}
			$x0=$this->GetX();
			$y0=$this->GetY();
			$x=$x0;
			for($i=0;$i<count($w);$i++){
				$this->Rect($x,$y0,$w[$i],$tinggi);
				$x+=$w[$i];
			}
			$this->SetFont('Arial','',9);
			$this->SetXY($x0,$y0+1);
			$this->Cell($w[0],5,$no,0,0,'C');
			$this->Cell($w[1],5,$kode,0,0,'L');
			$this->SetXY($x0+$w[0]+$w[1],$y0+1);
			$this->MultiCell($w[2],5,$nama,0,'L');
			if($catatan!=''){
				$this->SetFont('Arial','I',8);
				$this->SetX($x0+$w[0]+$w[1]);
				$this->MultiCell($w[2],4,'Catatan : '.$catatan,0,'L');
			}
			$this->SetFont('Arial','',9);
			$this->SetXY($x0+$w[0]+$w[1]+$w[2],$y0+1);
			$this->Cell($w[3],5,$satuan,0,0,'C');
			$this->Cell($w[4],5,number_format($jumlah,2,'.',','),0,0,'R');
			$this->SetXY($x0,$y0+$tinggi);
		}

		# gambar tanda tangan, diletakkan di tengah kolom
		function Ttd($file,$xkol,$wkol,$y)
		{
			if($file=='' || !file_exists($file)){
				return;
			}
			$tinggi=15;
			$sz=@getimagesize($file);
			$lebar=($sz && $sz[1]>0)?($tinggi*$sz[0]/$sz[1]):20;
			if($lebar>$wkol-4){
				$lebar=$wkol-4;
			}
			$this->Image($file,$xkol+($wkol-$lebar)/2,$y,$lebar,$tinggi);
		}

	}
}

	$pdf=new PDFGRN('P','mm','A4');
	$pdf->SetMargins(10,10,10);
	$pdf->SetAutoPageBreak(true,18);
	$pdf->AliasNbPages();
	$pdf->AddPage();

//ambil kelengkapan
//ambil supplier
	$stry="select * from ".$dbname.".log_5supplier where supplierid='".$idsupplier."'";
	$namasupplier=$idsupplier;
	$resy=$owlPDO->query($stry) or die(print " Gagal: ".PDOException::getMessage());
	$resy->setFetchMode(PDO::FETCH_OBJ);
	while($bary=$resy->fetch())
	{
		$namasupplier=$bary->namasupplier;
	}
	$hari=hari($tanggal,$_SESSION['language']);
	$tanggal=tanggalnormal($tanggal);
	$lastupdate=tanggalnormal($lastupdate);
	$resc=str_replace("#DATE_REPARAM#",$hari.", ".$tanggal,$_SESSION['lang']['prebapb']);
	$resc=str_replace("#SLOC_PARAM#",$optOrganisasi[$kodegudang]." - ".$kodegudang,$resc);
	$resc=str_replace("#VENDOR_PARAM#",$namasupplier,$resc);
	$pdf->SetFont('Arial','',9);
	$pdf->MultiCell(190,5,$resc,0,'L');
	$pdf->Ln(3);

	#= tabel barang
	$pdf->KepalaTabel();
	$str="select * from ".$dbname.".log_transaksidt where notransaksi='".$_GET['notransaksi']."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$no=0;
	while($bar=$res->fetch())
	{
		$no+=1;
		$namabarang='';
		$strv="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$bar->kodebarang."'";
		$resv=$owlPDO->query($strv) or die(print " Gagal: ".PDOException::getMessage());
		$resv->setFetchMode(PDO::FETCH_OBJ);
		while($barv=$resv->fetch())
		{
			$namabarang=$barv->namabarang;
		}
		$pdf->BarisBarang($no,$bar->kodebarang,printSpecialChar($namabarang),$bar->satuan,$bar->jumlah,$bar->catatan);
	}

//footer================================
	$namakaryawan=namakaryawan($dbname,$conn,$userid);

	$countApp = getCountApproval('GR',substr($kodegudang,0,4));
	$kolomTtd = $countApp+2;
	$widthApp = 190/$kolomTtd;

	if($posted!=''){
		$ttdposting = makeOption($dbname,'setup_ttd','karyawanid,file',"karyawanid='".$posted."'");
		$ttdPosted = $ttdposting[$posted];
		$posted=namakaryawan($dbname,$conn,$posted);
	}else{
		$ttdPosted = '';
		$posted='';
	}
	$ttdpembuat = makeOption($dbname,'setup_ttd','karyawanid,file',"karyawanid='".$userid."'");
	$ttdPembuat = $ttdpembuat[$userid];

	#= blok tanda tangan: pindah halaman bila sisa ruang tidak cukup
	$pdf->Ln(6);
	if($pdf->GetY()>235){
		$pdf->AddPage();
	}
	$y=$pdf->GetY();
	$pdf->SetFont('Arial','B',8.5);
	$pdf->SetXY(10,$y);
	$pdf->Cell($widthApp,5,$_SESSION['lang']['dbuat_oleh'],0,0,'C');
	for($i=1;$i<=$countApp;$i++)
	{
		$pdf->Cell($widthApp,5,$_SESSION['lang']['persetujuan']." ".$i,0,0,'C');
	}
	$pdf->Cell($widthApp,5,$_SESSION['lang']['posted'],0,0,'C');

	#= gambar tanda tangan
	$yTtd=$y+6;
	$pdf->Ttd($ttdPembuat,10,$widthApp,$yTtd);
	$arrApp=array();
	for($i=1;$i<=$countApp;$i++)
	{
		$arrApp[$i]=detailApprove($i,$nodok,'GR');
		$disetujui=!($arrApp[$i]['status']==0 || $arrApp[$i]['status']==2);
		if($disetujui){
			$ttdApp = makeOption($dbname,'setup_ttd','karyawanid,file',"karyawanid='".$arrApp[$i]['karyawanid']."'");
			$pdf->Ttd($ttdApp[$arrApp[$i]['karyawanid']],10+$i*$widthApp,$widthApp,$yTtd);
		}
	}
	$pdf->Ttd($ttdPosted,10+($countApp+1)*$widthApp,$widthApp,$yTtd);

	#= nama
	$pdf->SetXY(10,$y+22);
	$pdf->SetFont('Arial','BU',8.5);
	$pdf->Cell($widthApp,4,$namakaryawan,0,0,'C');
	for($i=1;$i<=$countApp;$i++)
	{
		$disetujui=!($arrApp[$i]['status']==0 || $arrApp[$i]['status']==2);
		$pdf->Cell($widthApp,4,$disetujui?$arrApp[$i]['nama']:'',0,0,'C');
	}
	$pdf->Cell($widthApp,4,$posted,0,0,'C');

	#= tanggal
	$pdf->SetXY(10,$y+27);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell($widthApp,4,$tanggal,0,0,'C');
	for($i=1;$i<=$countApp;$i++)
	{
		$disetujui=!($arrApp[$i]['status']==0 || $arrApp[$i]['status']==2);
		$pdf->Cell($widthApp,4,$disetujui?tanggalnormal($arrApp[$i]['tanggal']):'',0,0,'C');
	}
	$pdf->Cell($widthApp,4,($posted!='')?$lastupdate:'',0,0,'C');

	$urlefil=checkPostGet('urlefil','0');
	if($urlefil=='0'){
		$pdf->Output();
	}else{
		$pdf->Output($urlefil);
	}
?>
