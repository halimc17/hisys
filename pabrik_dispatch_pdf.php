<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/zLib.php');
include_once('lib/terbilang.php');

$tmp = explode(',', $_GET['column']);
$noba = $tmp[0];

$nmbrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkomoditi=array('40000001'=>'CPO','40000002'=>'PALM KERNEL');
$nmtangki=makeOption($dbname,'pabrik_5tangki','kodetangki,keterangan');
	 

//create Header
class PDF extends FPDF {

    function Header() {

		global $conn;
		global $dbname;
		global $noba;
		global $owlPDO;
		
		$str = "select * from " . $dbname . ".pabrik_blk_dispatchht where noba_pengapalan='" . $noba . "' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
			$kodept=$bar['kodept'];
 
		$str="select namaorganisasi,alamat,wilayahkota,telepon from ".$dbname.".organisasi where kodeorganisasi='".$kodept."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			 $namapt=$bar['namaorganisasi'];
			 $alamatpt=$bar['alamat'];
			 $telp=$bar['telepon'];				 

		$this->SetFont('Arial', '', 20);
		$height=10;
		$this->Cell(200,$height,$namapt, 0, 1, 'C');
		$this->Cell(200,$height,'BULKING STATION', 0, 1, 'C');
		$this->Ln(5);
		$height=4;
		$this->SetFont('Arial', 'B', 8);
		$this->Cell(20,$height,'Kantor Pusat', 0, 0, 'L');
		$this->Cell(5,$height,':', 0, 0, 'C');
		$this->Cell(20,$height,$alamatpt, 0, 1, 'L');
		$this->Cell(20,$height,'', 0, 0, 'L');
		$this->Cell(5,$height,'', 0, 0, 'C');
		$this->Cell(20,$height,$telp, 0, 1, 'L');
		$this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(10, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
	
}



$pdf = new PDF('P', 'mm', 'A4');
$pdf->AddPage();

$str = "select * from " . $dbname . ".pabrik_blk_dispatchht where noba_pengapalan='" . $noba . "' ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
	$kodept=$bar['kodept'];
	$tanggal=$bar['tanggal'];
	$nokontrak=$bar['nokontrak'];
	$komoditi=$bar['komoditi'];
	$tanggalmulai=$bar['tanggalmulai'];
		$jammulaiht=substr($bar['tanggalmulai'],11,5);
	$tanggalselesai=$bar['tanggalselesai'];
		$jamselesaiht=substr($bar['tanggalselesai'],11,5);
	$asalkirim=$bar['asalkirim'];
	$tujuan=$bar['tujuan'];
	$surveyor=$bar['surveyor'];
	$cheif=$bar['cheif'];
	$head_bulking=$bar['head_bulking'];
	$namakapal=$bar['namakapal'];
	$kgawal=$bar['kgawal'];
	$tinggiawal=$bar['tinggiawal'];
	$suhuawal=$bar['suhuawal'];
	$kgakhir=$bar['kgakhir'];
	$tinggiakhir=$bar['tinggiakhir'];
	$suhuakhir=$bar['suhuakhir'];
	$totalmuat=$bar['totalmuat'];
	$ptsurveyor=$bar['ptsurveyor'];
	

$height=5;
$pdf->SetFont('Arial','BU',10);
$pdf->Cell(200,$height,'BERITA ACARA PEMUATAN '.$nmkomoditi[$komoditi], 0, 1, 'C');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(200,$height,$noba, 0, 1, 'C');

$pdf->Ln(10);
$nmhari=hari($tanggal,'ID');
$expltgl=explode('-',$tanggal);
$nmtbthn=terbilang($expltgl[0],'');
$nmtbtbln=terbilang($expltgl[1],'');
$nmtbhari=terbilang($expltgl[2],'');
$nmbln=numToMonth($expltgl[1],'I','long');


$tglgaring=str_replace('-','/',tanggalnormal($tanggal));
$dtkopatas="pada hari ".$nmhari." tanggal ".$nmtbhari." bulan ".$nmbln." tahun ".$nmtbthn." (".$tglgaring.") telah dilaksanakan pemuatan ".$nmkomoditi[$komoditi]." atas kapal / ".$namakapal;
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(180,$height,ucwords($dtkopatas),0,'L',0);



$str="select * from ".$dbname.".pabrik_blk_daftar where nokontrak='".$nokontrak."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
	 $nmcust=$bar['namacustomer'];

	 
#bentuk format tanggal mulai

$nmharimulai=hari($tanggalmulai,'ID');	
$tanggalmulai=substr($tanggalmulai,0,10);
$expltglmulai=explode('-',$tanggalmulai);
$tglmulai=$expltgl[2].' '.numToMonth($expltglmulai[1],'I','long').' '.$expltgl[0];	 
$tglmulaitampil=$nmharimulai.' / '.$tglmulai;

$nmhariselesai=hari($tanggalselesai,'ID');	
$tanggalselesai=substr($tanggalselesai,0,10);
$expltglselesai=explode('-',$tanggalselesai);
$tglselesai=$expltgl[2].' '.numToMonth($expltglselesai[1],'I','long').' '.$expltgl[0];	 
$tglselesaitampil=$nmhariselesai.' / '.$tglselesai;





$pdf->Ln(5);
$pdf->Cell(50,$height,'Lokasi Pemuatan', 0, 0, 'L');
$pdf->Cell(5,$height,':', 0, 0, 'C');
$pdf->Cell(50,$height,'Dermaga Maloy - Sangkulirang', 0, 1, 'L');
$pdf->Cell(50,$height,'Mulai Pemuatan', 0, 0, 'L');
$pdf->Cell(5,$height,':', 0, 0, 'C');
$pdf->Cell(50,$height,$tglmulaitampil, 0, 1, 'L');
$pdf->Cell(50,$height,'Selesai Pemuatan', 0, 0, 'L');
$pdf->Cell(5,$height,':', 0, 0, 'C');
$pdf->Cell(50,$height,$tglselesaitampil, 0, 1, 'L');
$pdf->Cell(50,$height,'Jenis Bahan Baku', 0, 0, 'L');
$pdf->Cell(5,$height,':', 0, 0, 'C');
$pdf->Cell(50,$height,$nmkomoditi[$komoditi], 0, 1, 'L');
$pdf->Cell(50,$height,'Nomor Kontrak', 0, 0, 'L');
$pdf->Cell(5,$height,':', 0, 0, 'C');
$pdf->Cell(50,$height,$nokontrak, 0, 1, 'L');
$pdf->Cell(50,$height,'Asal Pengiriman', 0, 0, 'L');
$pdf->Cell(5,$height,':', 0, 0, 'C');
$pdf->Cell(50,$height,$asalkirim, 0, 1, 'L');
$pdf->Cell(50,$height,'Pembeli', 0, 0, 'L');
$pdf->Cell(5,$height,':', 0, 0, 'C');
$pdf->Cell(50,$height,$nmcust, 0, 1, 'L');
$pdf->Cell(50,$height,'Tujuan', 0, 0, 'L');
$pdf->Cell(5,$height,':', 0, 0, 'C');
$pdf->Cell(50,$height,$tujuan, 0, 1, 'L');
$pdf->Cell(50,$height,'Tonase Pengapalan [B/L)', 0, 0, 'L');
$pdf->Cell(5,$height,':', 0, 0, 'C');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(50,$height,number_format($totalmuat).' KG', 0, 1, 'L');


$str="select * from ".$dbname.".pabrik_blk_dispatchlab where noba_pengapalan='".$noba."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
	 $kodetangki=$bar['kodetangki'];
	 $ffa=$bar['ffa'];
	 $moisture=$bar['moisture'];
	 $dirt=$bar['dirt'];	
	 $tanggalanalisa=$bar['tanggalbaa'];
	 $jammulaianalisa=$bar['jammulai'];
	 $jamselesaianalisa=$bar['jamselesai'];
	 
$pdf->Ln();
$pdf->SetFont('Arial','B',10);
$pdf->SetX(50);
$pdf->Cell(35,$height,'Quality', 1, 0, 'C');
$pdf->Cell(50,$height,$nmtangki[$kodetangki], 1, 1, 'C');
$pdf->SetFont('Arial','',10);
$pdf->SetX(50);
$pdf->Cell(35,$height,'FFA (Average)', 1, 0, 'L');
$pdf->Cell(50,$height,$ffa.' %', 1, 1, 'C');
$pdf->SetX(50);
$pdf->Cell(35,$height,'Moisture (Average)', 1, 0, 'L');
$pdf->Cell(50,$height,$moisture.' %', 1, 1, 'C');
$pdf->SetX(50);
$pdf->Cell(35,$height,'Dirt', 1, 0, 'L');
$pdf->Cell(50,$height,$dirt.' %', 1, 1, 'C');

$pdf->Ln(10);

$pdf->Cell(35,$height,'Sebagai Pelengkap Berita Acara ini Kami Lampirkan :', 0, 1, 'L');
$pdf->SetX(35);
$pdf->Cell(35,$height,'1 Berita Acara Sounding Kapal Terlampir', 0, 1, 'L');
$pdf->SetX(35);
$pdf->Cell(35,$height,'2 Berita Acara PRE-SHIPMENT', 0, 1, 'L');

$pdf->Ln(10);

$pdf->MultiCell(160,$height,'Demikian Berita Acara Pengapalan ini kami buat dengan sebenarnya serta ditandatangani bersama oleh yang bersangkutan untuk dapat dipergunakan sebagaimana mestinya.',0,'L',0);

$pdf->Ln(10);
$tglttd=$expltgl[2].' '.numToMonth($expltgl[1],'I','long').' '.$expltgl[0];
$pdf->Cell(180,$height,'Pel. Maloy/Maluwi, '.$tglttd, 0, 1, 'R');

$pdf->Ln(10);
$pdf->Cell(50,$height,'Pihak Kedua', 0, 0, 'C');
$pdf->Cell(50,$height,'', 0, 0, 'C');
$pdf->Cell(100,$height,'Pihak Pertama', 0, 1, 'C');


$pdf->SetFont('Arial','B',10);
$pdf->Cell(50,$height,'CHIEF', 0, 0, 'C');
$pdf->Ln(20);
$pdf->SetFont('Arial','BU',10);
$pdf->Cell(50,$height,$cheif, 0, 0, 'C');
$pdf->Cell(50,$height,'', 0, 0, 'C');
$pdf->Cell(50,$height,$surveyor, 0, 0, 'C');
$pdf->Cell(50,$height,$head_bulking, 0, 1, 'C');


$pdf->SetFont('Arial','B',10);
$pdf->Cell(50,$height,$namakapal, 0, 0, 'C');
$pdf->Cell(50,$height,'', 0, 0, 'C');
$pdf->Cell(50,$height,$ptsurveyor, 0, 0, 'C');
$pdf->Cell(50,$height,'HEAD BULKING', 0, 1, 'C');


/**************************************************************************************************************************************************/
/* PAGE 2 */
/**************************************************************************************************************************************************/

$height=5;
$pdf->Ln(100);
$pdf->SetFont('Arial','BU',10);
$pdf->Cell(200,$height,'BERITA ACARA PRE-SHIPMENT', 0, 1, 'C');
$pdf->SetFont('Arial','',10);

$pdf->Ln(10);


 $tanggalanalisa=$bar['tanggalbaa'];
	 $jammulaianalisa=$bar['jammulai'];
	 $jamselesaianalisa=$bar['jamselesai'];
	 
$nmharianalisa=hari($tanggalanalisa,'ID');
$expltglanalisa=explode('-',$tanggalanalisa);
$nmtbthnanalisa=terbilang($expltglanalisa[0],'');
$nmtbtblnanalisa=terbilang($expltglanalisa[1],'');
$nmtbharianalisa=terbilang($expltglanalisa[2],'');
$nmblnanalisa=numToMonth($expltglanalisa[1],'I','long');
$tglanalisagaring=str_replace('-','/',tanggalnormal($tanggalanalisa));


$dtkopatas="       Pada hari ini ".$nmharianalisa." Tanggal ".$nmtbthnanalisa." Bulan ".$nmblnanalisa." Tahun ".$nmtbthnanalisa." (".$tglanalisagaring.") pukul ".$jammulaianalisa." Wita sampai dengan ".$jamselesaianalisa." Wita, telah dilakukan pengambilan Sampel PRE-SHIPMENT, di ".$nmtangki[$kodetangki]." ".$nmorg[$kodept]." - Maloy Bulking Station. Dimana Sounding Dan Analisa Dilakukan Bersama - sama dari Pihak Shiper, Chief kapal, dan ".$ptsurveyor.". Analisa Sample dilakukan Dilaboratorium ".$nmorg[$kodept]." - Maloy Bulking Station, didapatkan Data Sebagai Berikut :";
$pdf->MultiCell(180,$height,ucwords($dtkopatas),0,'L',0);
$pdf->Ln(10);


$pdf->SetX(25);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(60,$height,'Sample '.$nmtangki[$kodetangki], 1,0, 'C');
$pdf->Cell(35,$height,'Average', 1, 1, 'C');
$pdf->SetFont('Arial','',10);
$pdf->SetX(25);
$pdf->Cell(60,$height,'FFA', 1, 0, 'L');
$pdf->Cell(35,$height,$ffa.' %', 1, 1, 'C');
$pdf->SetX(25);
$pdf->Cell(60,$height,'Moisture', 1, 0, 'L');
$pdf->Cell(35,$height,$moisture.' %', 1, 1, 'C');
$pdf->SetX(25);
$pdf->Cell(60,$height,'Dirt', 1, 0, 'L');
$pdf->Cell(35,$height,$dirt.' %', 1, 1, 'C');

$pdf->Ln();

//SoundingAwal [Cm) Temperature 'c Volume [Kg)

$pdf->SetX(25);
$pdf->SetFont('Arial','',10);
$pdf->Cell(60,$height,'Sounding Awal (Cm)', 1,0, 'C');
$pdf->Cell(35,$height,'Temperature C', 1, 0, 'C');
$pdf->Cell(60,$height,'Volume (Kg)', 1, 1, 'C');
$pdf->SetFont('Arial','B',10);
$pdf->SetX(25);
$pdf->Cell(60,$height,$tinggiawal, 1, 0, 'L');
$pdf->Cell(35,$height,$suhuawal, 1, 0, 'C');
$pdf->Cell(60,$height,number_format($kgawal), 1, 1, 'R');

$pdf->SetX(25);
$pdf->SetFont('Arial','',10);
$pdf->Cell(60,$height,'Sounding Akhir (Cm)', 1,0, 'C');
$pdf->Cell(35,$height,'Temperature C', 1, 0, 'C');
$pdf->Cell(60,$height,'Volume (Kg)', 1, 1, 'C');
$pdf->SetFont('Arial','B',10);
$pdf->SetX(25);
$pdf->Cell(60,$height,$tinggiakhir, 1, 0, 'L');
$pdf->Cell(35,$height,$suhuakhir, 1, 0, 'C');
$pdf->Cell(60,$height,number_format($kgakhir), 1, 1, 'R');

$pdf->SetX(25);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(95,$height,'Total Pemuatan', 1,0, 'C');
$pdf->Cell(60,$height,number_format($totalmuat), 1, 1, 'R');
$pdf->SetFont('Arial','',10);

$pdf->Ln(10);
$tglttdanalisa=$expltglanalisa[2].' '.numToMonth($expltglanalisa[1],'I','long').' '.$expltglanalisa[0];
$pdf->Cell(180,$height,'Demikian Berita Acara lni Dibuat Dengan Sebenarnya, Dan Dipergunakan Dengan Semestinya', 0,1, 'L');
$pdf->Ln(5);
$pdf->Cell(180,$height,'Maloy / Maluwi, '.$tglttdanalisa, 0,0, 'L');


$pdf->Ln(20);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(60,$height,$nmorg[$kodept], 0, 0, 'C');
$pdf->Cell(60,$height,'CHIEF', 0, 0, 'C');
$pdf->Cell(60,$height,'SURVEYOR', 0, 1, 'C');
$pdf->Cell(60,$height,'SHIPER', 0, 0, 'C');;
$pdf->Ln(30);
$pdf->SetFont('Arial','BU',10);
$pdf->Cell(60,$height,$head_bulking, 0, 0, 'C');
$pdf->Cell(60,$height,$cheif, 0, 0, 'C');
$pdf->Cell(60,$height,$surveyor, 0, 1, 'C');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(60,$height,'HEAD BULKING', 0, 0, 'C');
$pdf->Cell(60,$height,$namakapal, 0, 0, 'C');
$pdf->Cell(60,$height,$ptsurveyor, 0, 1, 'C');



/**************************************************************************************************************************************************/
/* PAGE 3 */
/**************************************************************************************************************************************************/


$pdf->AddPage();
$height=5;
$pdf->SetFont('Arial','BU',10);
$pdf->Cell(200,$height,'BERITA ACARA PEMUATAN SOUNDING KAPAL', 0, 1, 'C');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(200,$height,$noba, 0, 1, 'C');
$pdf->Ln(10);
/*
$nmhari=hari($tanggal,'ID');
$expltgl=explode('-',$tanggal);
$nmtbthn=terbilang($expltgl[0]);
$nmtbtbln=terbilang($expltgl[1]);
$nmtbhari=terbilang($expltgl[2]);
$nmbln=numToMonth($expltgl[1],'I','long');
$tglgaring=str_replace('-','/',tanggalnormal($tanggal));
*/
$dtkopatas="          pada hari ini ".$nmhari." tanggal ".$nmtbhari." bulan ".$nmbln." tahun ".$nmtbthn." (".$tglgaring.") telah dilaksanakan pemuatan ".$nmkomoditi[$komoditi]." atas kapal / ".$namakapal;
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(180,$height,ucwords($dtkopatas),0,'L',0);

$pdf->Ln(5);

$pdf->Cell(70,$height,'1. Lokasi Sounding', 0, 0, 'L');
$pdf->Cell(5,$height,':', 0, 0, 'C');
$pdf->Cell(50,$height,'Dermaga Maluwi/ Maloy, Sangkulirang', 0, 1, 'L');
$pdf->Cell(70,$height,'2. Jam Mulai Sounding', 0, 0, 'L');
$pdf->Cell(5,$height,':', 0, 0, 'C');
$pdf->Cell(50,$height,$jammulaiht.' WITA', 0, 1, 'L');
$pdf->Cell(70,$height,'3. fam Selesai Sounding', 0, 0, 'L');
$pdf->Cell(5,$height,':', 0, 0, 'C');
$pdf->Cell(50,$height,$jamselesaiht.' WITA', 0, 1, 'L');
$pdf->Cell(70,$height,'4. Tonase Pengapalan [B/L]', 0, 0, 'L');
$pdf->Cell(5,$height,':', 0, 0, 'C');
$pdf->Cell(50,$height,number_format($totalmuat), 0, 1, 'L');

$pdf->Ln(5);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(30,$height,'No. PALKA', 1, 0, 'C');
$pdf->Cell(30,$height,'TINGGI (CM)', 1, 0, 'C');
$pdf->Cell(30,$height,'VOLUME', 1, 0, 'C');
$pdf->Cell(30,$height,'SUHU', 1, 0, 'C');
$pdf->Cell(30,$height,'BERAT JENIS', 1, 0, 'C');
$pdf->Cell(30,$height,'TONASE (KG)', 1, 1, 'C');
$pdf->SetFont('Arial','',10);

$str=" select * from ".$dbname.".pabrik_blk_dispatchdtsound where noba_pengapalan='".$noba."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	if(substr($bar['nopalka'],1,1)=='S'){
		$bar['nopalka']='S';
		@$tsoundings+=$bar['tonase'];
	}else{
		$bar['nopalka']=$bar['nopalka'];
		@$tsoundingp+=$bar['tonase'];
	}
	$pdf->Cell(30,$height,$bar['nopalka'], 1, 0, 'C');
	$pdf->Cell(30,$height,$bar['tinggi'], 1, 0, 'C');
	$pdf->Cell(30,$height,number_format($bar['volume']), 1, 0, 'R');
	$pdf->Cell(30,$height,$bar['suhu'], 1, 0, 'C');
	$pdf->Cell(30,$height,number_format($bar['beratjenis'],4), 1, 0, 'C');
	$pdf->Cell(30,$height,number_format($bar['tonase']), 1, 1, 'R');	
}
$pdf->Cell(150,$height,'TOTAL', 1, 0, 'C');
$pdf->Cell(30,$height,number_format($tsoundingp), 1, 1, 'R');
$pdf->Cell(150,$height,'B/L', 1, 0, 'C');
$pdf->Cell(30,$height,number_format($tsoundings), 1, 1, 'R');
$pdf->Cell(150,$height,'Selisih', 1, 0, 'C');
$pdf->Cell(30,$height,number_format($tsoundingp-$tsoundings), 1, 1, 'R');
$pdf->Cell(150,$height,'PERSENTASE SELISIH', 1, 0, 'C');
$pdf->Cell(30,$height,number_format(($tsoundingp-$tsoundings)/$tsoundings*100,2).' %', 1, 1, 'R');

$pdf->Ln(5);
$pdf->SetX(20);
$pdf->Cell(25,$height,'Remark :', 0, 0, 'C');
$pdf->MultiCell(100,$height,'.......................................................................
.......................................................................
.......................................................................
.......................................................................',0,'L',0);

$pdf->Ln(5);
$tglttd=$expltgl[2].' '.numToMonth($expltgl[1],'I','long').' '.$expltgl[0];
$pdf->Cell(180,$height,'Pel. Maloy/Maluwi, '.$tglttd, 0, 1, 'R');
$pdf->Ln(10);
$pdf->SetFont('Arial','',10);
$pdf->Cell(60,$height,'Surveyor', 0, 0, 'C');
$pdf->Cell(60,$height,'Chief', 0, 0, 'C');
$pdf->Cell(60,$height,'Loading Master', 0, 1, 'C');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(60,$height,$ptsurveyor, 0, 0, 'C');
$pdf->Cell(60,$height,$namakapal, 0, 0, 'C');
$pdf->Cell(60,$height,$nmorg[$kodept], 0, 1, 'C');
$pdf->Ln(20);
$pdf->Cell(60,$height,$surveyor, 0, 0, 'C');
$pdf->Cell(60,$height,$cheif, 0, 0, 'C');
$pdf->Cell(60,$height,$head_bulking, 0, 1, 'C');



/**************************************************************************************************************************************************/
/* PAGE 4 */
/**************************************************************************************************************************************************/
$pdf->AddPage();
$height=5;
$pdf->SetFont('Arial','BU',10);
$pdf->Cell(200,$height,'BERITA ACARA PENYEGELAN', 0, 1, 'C');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(200,$height,$noba, 0, 1, 'C');
$pdf->Ln(10);
$pdf->SetFont('Arial','',10);
$dtkopatas="          pada hari ini ".$nmhari." tanggal ".$nmtbhari." bulan ".$nmbln." tahun ".$nmtbthn." (".$tglgaring.") telah dilakukan penyegelan di manifold, Sounding Pipa, Mainhold dan valve Oleh ".$nmorg[$kodept].", dan disaksikan pihak kapal ".$namakapal.".";
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(180,$height,ucwords($dtkopatas),0,'L',0);
$pdf->Ln(10);

$pdf->SetX(30);
$pdf->Cell(30,$height+2,'Posisi Segel', 1, 0, 'C');
$pdf->Cell(60,$height+2,'Nomor Segel', 1, 0, 'C');
$pdf->Cell(30,$height+2,'Total Segel', 1, 0, 'C');
$pdf->Cell(30,$height+2,'Warna Segel', 1, 1, 'C');
$str=" select * from ".$dbname.".pabrik_blk_dispatchdtsegel where noba_pengapalan='".$noba."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$pdf->SetX(30);
	$pdf->Cell(30,$height,$bar['posisi_segel'], 1, 0, 'L');
	$pdf->Cell(60,$height,$bar['nosegel_view'], 1, 0, 'C');
	$pdf->Cell(30,$height,number_format($bar['total_segel']), 1, 0, 'C');
	$pdf->Cell(30,$height,$bar['warna_segel'], 1, 1, 'C');
	@$tsegel+=$bar['total_segel'];
	$warnasegel=$bar['warna_segel'];
}
$pdf->SetX(30);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(90,$height,'Total Plastic Segel', 1, 0, 'C');
$pdf->Cell(30,$height,number_format($tsegel), 1, 0, 'C');
$pdf->Cell(30,$height,$warnasegel, 1, 0, 'C');

$pdf->Ln(20);
$pdf->SetFont('Arial','BU',10);

$pdf->Cell(25,$height,'Remark :', 0, 1, 'C');
$pdf->Ln(10);
$pdf->SetX(25);
$pdf->SetFont('Arial','',10);
$pdf->Cell(25,$height,'Penyegelan dilakukan oleh '.$ptsurveyor, 0, 1, 'L');
$pdf->SetX(25);
$pdf->MultiCell(100,$height,'...................................................................................
...................................................................................
...................................................................................
...................................................................................',0,'L',0);

$height=6;
$pdf->Ln(10);
$tglttd=$expltgl[2].' '.numToMonth($expltgl[1],'I','long').' '.$expltgl[0];
$pdf->Cell(180,$height,'Pel. Maloy/Maluwi, '.$tglttd, 0, 1, 'R');
$pdf->Ln(10);
$pdf->SetFont('Arial','',10);
$pdf->Cell(60,$height,'Pihak Shiper', 0, 0, 'C');
$pdf->Cell(60,$height,'Pihak Kapal', 0, 0, 'C');
$pdf->Cell(60,$height,'Surveyor', 0, 1, 'C');
$pdf->Ln(30);
$pdf->SetFont('Arial','BU',10);
$pdf->Cell(60,$height,$head_bulking, 0, 0, 'C');
$pdf->Cell(60,$height,$cheif, 0, 0, 'C');
$pdf->Cell(60,$height,$surveyor, 0, 1, 'C');
$pdf->SetFont('Arial','',10);
$pdf->Cell(60,$height,'LOADING MASTER', 0, 0, 'C');
$pdf->Cell(60,$height,'CHIEF', 0, 0, 'C');
$pdf->Cell(60,$height,'SURVEYOR', 0, 1, 'C');
$pdf->Cell(60,$height,$nmorg[$kodept], 0, 0, 'C');
$pdf->Cell(60,$height,$namakapal, 0, 0, 'C');
$pdf->Cell(60,$height,$ptsurveyor, 0, 1, 'C');







/**************************************************************************************************************************************************/
/* PAGE 4 */
/**************************************************************************************************************************************************/


$pdf->AddPage();
$height=5;
$pdf->SetFont('Arial','BU',10);
$pdf->Cell(200,$height,'BERITA ACARA ANALISA', 0, 1, 'C');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(200,$height,$noba, 0, 1, 'C');
$pdf->Ln(10);
$pdf->SetFont('Arial','',10);
$dtkopatas="          pada hari ini ".$nmhari." tanggal ".$nmtbhari." bulan ".$nmbln." tahun ".$nmtbthn." (".$tglgaring.") pengambilan sampel di ".$nmtangki[$kodetangki].". dengan hasil dibawah ini :";
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(180,$height,ucwords($dtkopatas),0,'L',0);
$pdf->Ln(10);


$pdf->SetX(50);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(60,$height,'Quality '.$nmtangki[$kodetangki], 1,1, 'C');
$pdf->SetFont('Arial','',10);
$pdf->SetX(50);
$pdf->Cell(40,$height,'FFA', 1, 0, 'L');
$pdf->Cell(20,$height,$ffa.' %', 1, 1, 'C');
$pdf->SetX(50);
$pdf->Cell(40,$height,'Moisture', 1, 0, 'L');
$pdf->Cell(20,$height,$moisture.' %', 1, 1, 'C');
$pdf->SetX(50);
$pdf->Cell(40,$height,'Dirt', 1, 0, 'L');
$pdf->Cell(20,$height,$dirt.' %', 1, 1, 'C');

$pdf->Ln();
$pdf->SetFont('Arial','',10);
$dttengah="Hasil analisa diatas merupakan hasil analisa bersama yang disaksikan oleh pihak surveyor ".$ptsurveyor." dan pihak ".$nmorg[$kodept]." - bulking station, sedangkan untuk final hasil analisa kapal ".$namakapal." dilakukan dilaboratorium ".$ptsurveyor." ";
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(180,$height,ucwords($dttengah),0,'L',0);

$pdf->Ln();
$pdf->SetFont('Arial','',10);
$dttengah="Demikian Berita Acara pengambilan sampel dan analisa ini kami buat dengan sebenarnya serta ditandatangani bersama oleh yang bersangkutan untuk dapat dipergunakan sebagaimana mestinya.";
$pdf->MultiCell(180,$height,ucwords($dttengah),0,'L',0);
$pdf->Ln();
$pdf->Cell(180,$height,'Demikian Berita Acara lni Dibuat Dengan Sebenarnya, Dan Dipergunakan Dengan Semestinya', 0,1, 'L');
$pdf->Ln(5);
$pdf->Cell(180,$height,'Maloy / Maluwi, '.$tglttdanalisa, 0,0, 'R');

$dttengah="Surveyor ".$ptsurveyor." yang ditunjuk oleh ".$nmorg[$kodept]." sebatas mengetahui analisa sampel ".$nmkomoditi[$komoditi]." di bulking station ".$nmorg[$kodept].".";
$pdf->Cell(180,$height,'Not', 0,1, 'R');
$pdf->MultiCell(180,$height,ucwords($dttengah),0,'L',0);

$pdf->Cell(180,$height,'Diketahui Kedua Belah Pihak,', 0,1, 'R');
$pdf->Ln(30);
$pdf->SetFont('Arial','BU',10);
$pdf->SetX(60);
$pdf->Cell(60,$height,$surveyor, 0, 0, 'C');
$pdf->Cell(60,$height,$head_bulking, 0, 1, 'C');
$pdf->SetFont('Arial','',10);
$pdf->SetX(60);
$pdf->Cell(60,$height,'SURVEYOR', 0, 0, 'C');
$pdf->Cell(60,$height,'LOADING MASTER', 0, 1, 'C');

$pdf->Output();
?>
