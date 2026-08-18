<?php
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
//require_once('lib/zFunction.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
//=============
$tmp = explode(',', $_GET['column']);
$kdOrg = $tmp[0];
$tgl = $tmp[1];

//create Header
class PDF extends FPDF {

    function Header() {
        global $conn;
        global $dbname;
        global $userid;
        global $kdOrg;
        global $tgl;
        global $akhirY;
        global $akhirYline;
        global $owlPDO;
        global $optakun;

		$str="select * from ".$dbname.".keu_5akun";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optakun[$bar['noakun']]=$bar['namaakun'];
		}
		$str="select * from ".$dbname.".setup_kegiatan";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optakun[$bar['kodekegiatan']]=$bar['namakegiatan'];
		}

        $sInduk = "select induk from " . $dbname . ".organisasi where kodeorganisasi='" . $kdOrg . "'";
		$qInduk=$owlPDO->query($sInduk) or die(print " Gagal: ".PDOException::getMessage());
		$qInduk->setFetchMode(PDO::FETCH_ASSOC);
        $rInduk = $qInduk->fetch();

        // $str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$rInduk['induk']."'"; 
        $str1 = "select * from " . $dbname . ".organisasi where kodeorganisasi='" . $_SESSION['org']['kodeorganisasi'] . "'";
        $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_OBJ);
		while ($bar1 = $res1->fetch()) {
            $nama = $bar1->namaorganisasi;
            $alamatpt = $bar1->alamat . ", " . $bar1->wilayahkota;
            $telp = $bar1->telepon;
        }

        $sIsi = "select * from " . $dbname . ".sdm_absensiht where kodeorg='" . $kdOrg . "' and tanggal='" . tanggalsystem($tgl) . "'";
		$qIsi=$owlPDO->query($sIsi) or die(print " Gagal: ".PDOException::getMessage());
		$qIsi->setFetchMode(PDO::FETCH_ASSOC);
        $rIsi = $qIsi->fetch();

        $sOrg = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $rIsi['kodeorg'] . "'";
        $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
		$qOrg->setFetchMode(PDO::FETCH_ASSOC);
		$rOrg = $qOrg->fetch();

        
        $path = 'images/logodepan.png';
        $this->Image($path, 12, 6, 25);
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(255, 255, 255);
        $this->SetX(40);
        $this->Cell(60, 5, $nama, 0, 1, 'L');
        $this->SetX(40);

        $this->MultiCell(150, 5, $alamatpt, 0);

        //$this->Cell(60,5,$alamatpt,0,1,'L');	
        $this->SetX(40);
        $this->Cell(60, 5, "Tel: " . $telp, 0, 1, 'L');
        $this->Ln(5);
        $this->SetFont('Arial', 'B', 10);
        // $this->Cell(20, 7, $nama, '', 1, 'L');
        $this->SetY(40);
        $this->Ln(5);
        $this->SetFont('Arial', '', 8);
        
        
        $akhirY = $this->GetY() - 5;
        $this->Line(10, $akhirY, 285, $akhirY);
        
        $akhirYline = $this->GetY();
        
        $this->SetY($akhirYline);
        $this->SetFontSize(10);

        
        $this->Cell(35, 5, $_SESSION['lang']['kodeorg'], '', 0, 'L');
        $this->Cell(2, 5, ':', '', 0, 'L');
        $this->Cell(75, 5, $rIsi['kodeorg'], '', 0, 'L');

        $this->SetX(202);
        $this->Cell(35, 5, 'Nama Organisasi', '', 0, 'L');
        $this->Cell(2, 5, ':', '', 0, 'L');
        $this->Cell(35, 5, $rOrg['namaorganisasi'], 0, 1, 'L');
        $this->Cell(35, 5, $_SESSION['lang']['tanggal'], '', 0, 'L');
        $this->Cell(2, 5, ':', '', 0, 'L');
        $this->Cell(75, 5, $tgl, '', 0, 'L');
        $this->SetX(202);
        $this->Cell(35, 5, $_SESSION['lang']['periode'], '', 0, 'L');
        $this->Cell(2, 5, ':', '', 0, 'L');
        $this->Cell(35, 5, substr(tanggalnormal($rIsi['periode']), 1, 7), 0, 1, 'L');
        $this->Ln();
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(10, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

}

$pdf = new PDF('L', 'mm', 'A4');
$pdf->AddPage();

$pdf->Ln();

$pdf->SetFont('Arial', 'U', 10);
$pdf->SetY(60);
$pdf->SetX(50);
$pdf->Cell(190, 5, strtoupper($_SESSION['lang']['list'] . " " . $_SESSION['lang']['absensi']), 0, 1, 'C');
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetFillColor(220, 220, 220);
$pdf->Cell(5, 5, 'No', 1, 0, 'C', 1);
$pdf->Cell(55, 5, $_SESSION['lang']['namakaryawan'], 1, 0, 'C', 1);
//$pdf->Cell(20,5,$_SESSION['lang']['shift'],1,0,'C',1);
$pdf->Cell(40, 5, $_SESSION['lang']['absensi'], 1, 0, 'C', 1);
$pdf->Cell(25, 5, $_SESSION['lang']['jenis']." ".$_SESSION['lang']['kegiatan'], 1, 0, 'C', 1);
$pdf->Cell(30, 5, $_SESSION['lang']['alokasi'], 1, 0, 'C', 1);
$pdf->Cell(20, 5, $_SESSION['lang']['hk'], 1, 0, 'C', 1);
// $pdf->Cell(20, 5,'Upah', 1, 0, 'C', 1);
//$pdf->Cell(20, 5, $_SESSION['lang']['jam'], 1, 0, 'C', 1);
//$pdf->Cell(20, 5, $_SESSION['lang']['keluar'], 1, 0, 'C', 1);
$pdf->Cell(20, 5, $_SESSION['lang']['premi'], 1, 0, 'C', 1);
// $pdf->Cell(25, 5,"Extra Fooding", 1, 0, 'C', 1);
// $pdf->Cell(30, 5, $_SESSION['lang']['penaltykehadiran'], 1, 0, 'C', 1);
// $pdf->Cell(30, 5, "Premi Kehadiran", 1, 0, 'C', 1);
$pdf->Cell(80, 5, $_SESSION['lang']['keterangan'], 1, 1, 'C', 1);


//$pdf->Cell(25,5,'Total',1,1,'C',1);
$pdf->SetFillColor(255, 255, 255);
$pdf->SetFont('Arial', '', 7);
$str = "select *  from " . $dbname . ".sdm_absensidt  where tanggal='" . tanggalsystem($tgl) . "' and kodeorg='" . $kdOrg . "' order by tanggal asc"; 
$re=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$re->setFetchMode(PDO::FETCH_ASSOC);
$no = 0;
while ($res = $re->fetch()) {
    $sKry = "select namakaryawan from " . $dbname . ".datakaryawan where karyawanid='" . $res['karyawanid'] . "'";
	$qKry=$owlPDO->query($sKry) or die(print " Gagal: ".PDOException::getMessage());
	$qKry->setFetchMode(PDO::FETCH_ASSOC);
    $rKry = $qKry->fetch();

    $sShift = "select keterangan from " . $dbname . ".sdm_5absensi where kodeabsen='" . $res['absensi'] . "'";
	$qShif=$owlPDO->query($sShift) or die(print " Gagal: ".PDOException::getMessage());
	$qShif->setFetchMode(PDO::FETCH_ASSOC);
    $rShift = $qShif->fetch();

    
    // $sAlk = "select alokasi from " . $dbname . ".setup_blok where kodeorg='".$res['kodeorg']."' ";
    // $qAlk = $owlPDO->query($sAlk) or die(print " Gagal: " . PDOException::getMessage());
    // $qAlk->setFetchMode(PDO::FETCH_ASSOC);
    // $rAlk = $qAlk->fetch();
    
    #wordwrap
	$cellWidth=25; //lebar sel
	$cellHeight=5; //tinggi sel satu baris normal
	
	//periksa apakah teksnya melibihi kolom?
	if($pdf->GetStringWidth($optakun[$res['noakun']]) < $cellWidth){
		//jika tidak, maka tidak melakukan apa-apa
		$line=1;
	}else{
		//jika ya, maka hitung ketinggian yang dibutuhkan untuk sel akan dirapikan
		//dengan memisahkan teks agar sesuai dengan lebar sel
		//lalu hitung berapa banyak baris yang dibutuhkan agar teks pas dengan sel
		
		$textLength=strlen($optakun[$res['noakun']]);	//total panjang teks
        // exit('error'.$textLength);
		$errMargin=5;		//margin kesalahan lebar sel, untuk jaga-jaga
		$startChar=0;		//posisi awal karakter untuk setiap baris
		$maxChar=0;			//karakter maksimum dalam satu baris, yang akan ditambahkan nanti
		$textArray=array();	//untuk menampung data untuk setiap baris
		$tmpString="";		//untuk menampung teks untuk setiap baris (sementara)
		
		while($startChar < $textLength){ //perulangan sampai akhir teks
			//perulangan sampai karakter maksimum tercapai
			while( 
			$pdf->GetStringWidth( $tmpString ) < ($cellWidth-$errMargin) &&
			($startChar+$maxChar) < $textLength ) {
				$maxChar++;
				$tmpString=substr($optakun[$res['noakun']],$startChar,$maxChar);
			}
			//pindahkan ke baris berikutnya
			$startChar=$startChar+$maxChar;
			//kemudian tambahkan ke dalam array sehingga kita tahu berapa banyak baris yang dibutuhkan
			array_push($textArray,$tmpString);
			//reset variabel penampung
			$maxChar=0;
			$tmpString='';
			
		}
		//dapatkan jumlah baris
		$line=count($textArray);
	}
    $no+=1;
    $pdf->Cell(5,($cellHeight * $line), $no, 1,'C', 1);
    $pdf->Cell(55,($cellHeight * $line), $rKry['namakaryawan'], 1, 0, 'L', 1);
    $pdf->Cell(40,($cellHeight * $line), strtoupper($rShift['keterangan']), 1, 0, 'C', 1);
	$xPos=$pdf->GetX();
	$yPos=$pdf->GetY();
	$pdf->MultiCell($cellWidth,$cellHeight,$optakun[$res['noakun']],1);
	
	//kembalikan posisi untuk sel berikutnya di samping Cell 
    //dan offset x dengan lebar Cell
	$pdf->SetXY($xPos + 25 , $yPos);
	$pdf->Cell(30,($cellHeight * $line), $res['alokasi'], 1, 0, 'L', 1);
	$pdf->Cell(20,($cellHeight * $line), $res['hk'], 1, 0, 'R', 1);
	// $pdf->Cell(20,($cellHeight * $line),number_format($res['umr']), 1, 0, 'R', 1);
    //$pdf->Cell(20,($cellHeight * $line), $res['jam'], 1, 0, 'C', 1);
    //$pdf->Cell(20,($cellHeight * $line), $res['jamPlg'], 1, 0, 'C', 1);
    $pdf->Cell(20,($cellHeight * $line), number_format($res['premi'] + $res['insentif']), 1, 0, 'R', 1);
    // $pdf->Cell(25,($cellHeight * $line), number_format($res['insentiflibur']), 1, 0, 'R', 1);
    // $pdf->Cell(30,($cellHeight * $line), number_format($res['penaltykehadiran']), 1, 0, 'R', 1);
    // $pdf->Cell(30,($cellHeight * $line), number_format($res['premikehadiran']), 1, 0, 'R', 1);
    $pdf->Cell(80,($cellHeight * $line),strtoupper($res['penjelasan']), 1, 1, 'L', 1);
}
$pdf->Output();
