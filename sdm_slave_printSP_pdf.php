<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zMysql.php');
require_once('lib/zLib.php');

$nmbag=makeOption($dbname,'sdm_5departemen','kode,nama');
$nosp = $_GET['nosp'];

//=============
$str = "select a.*,b.keterangan,c.*,d.namajabatan,e.wilayahkota as wilKaryawan from " . $dbname . ".sdm_suratperingatan a 
	left join " . $dbname . ".sdm_5jenissp b 
	on a.jenissp = b.kode 
	left join " . $dbname . ".datakaryawan c
	on a.karyawanid = c.karyawanid 
	left join " . $dbname . ".sdm_5jabatan d
	on c.kodejabatan = d.kodejabatan 
	left join " . $dbname . ".organisasi e
	on c.kodeorganisasi = e.kodeorganisasi
	where nomor='" . $nosp . "'";
$resHead = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$resHead->setFetchMode(PDO::FETCH_OBJ);
$tmpBar = $resHead->fetch();

//create Header
class PDF extends FPDF {

    function Header() {
        global $conn;
        global $dbname;
        global $tmpBar;
        global $owlPDO;

        $strx = "select b.namaorganisasi from " . $dbname . ".datakaryawan a 
				left join " . $dbname . ".organisasi b on a.kodeorganisasi=b.kodeorganisasi
				where a.karyawanid=" . $tmpBar->karyawanid;
        $resOrg = fetchData($strx);
        // $this->SetFillColor(255, 255, 255);
        // $this->SetMargins(15, 10, 0);
        // $path = 'images/logo.jpg';
        // //$this->Image($path, 15, 5, 30);
        // $this->SetFont('Arial', '', 6);
        // $this->SetX(163);
        // $this->Cell(30, 10, 'PRINT TIME : ' . date('d-m-Y H:i:s'), 0, 1, 'L');
        // $this->Ln();
    }
}

$resHead1 = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$resHead1->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $resHead1->fetch()) {

    //===================smbil nama karyawan
    $namakaryawan = '';
    $strx = "select a.namakaryawan,b.namajabatan,tipekaryawan,a.alamataktif,a.bagian,a.tanggalmasuk from " . $dbname . ".datakaryawan a 
          left join " . $dbname . ".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
          where karyawanid=" . $bar->karyawanid;
	$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
	$resx->setFetchMode(PDO::FETCH_OBJ);
    while ($barx = $resx->fetch()) {
        $namakaryawan = $barx->namakaryawan;
        $jabatanybs = $barx->namajabatan;
        $tipex = $barx->tipekaryawan;
        $alamataktif = $barx->alamataktif;
		$bagianybs=$barx->bagian;
		$tmk=$barx->tanggalmasuk;
    }

    $tanggal = tanggalnormal($bar->tanggal);
    $sampai = tanggalnormal($bar->sampai);
    $tipesp = $bar->jenissp;
    //====================ambil tipe untuk hal
    $ketHal = '';
    $str = "select keterangan from " . $dbname . ".sdm_5jenissp where kode='" . $tipesp . "'";
    $rekx=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$rekx->setFetchMode(PDO::FETCH_OBJ);
    while ($barkx = $rekx->fetch()) {
        $ketHal = trim($barkx->keterangan);
    }
    //===============================

    $paragraf1 = $bar->paragraf1;
    $pelanggaran = $bar->pelanggaran;
    $paragraf3 = $bar->paragraf3;
    $paragraf4 = $bar->paragraf4;
    $karyawanid = $bar->karyawanid;

    $penandatangan = $bar->penandatangan;
    $jabatan = $bar->jabatan;
    $tembusan1 = $bar->tembusan1;
    $tembusan2 = $bar->tembusan2;
    $tembusan3 = $bar->tembusan3;
    $tembusan4 = $bar->tembusan4;
    $verifikasi = $bar->verifikasi;
    $dibuat = $bar->dibuat;
    $jabatandibuat = $bar->jabatandibuat;
    $jabatanverifikasi = $bar->jabatanverifikasi;
	
	$menimbang = $bar->menimbang;
	$mengingat = $bar->mengingat;
	$mendengar = $bar->mendengar;
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->SetFont('Arial', 'B', 14);
$pdf->AddPage();
//$pdf->SetY(40);
$pdf->SetFillColor(255, 255, 255);



	

	
$potongtgl=explode('-',$tmpBar->tanggal);
$thn=$potongtgl[0];
$bln=$potongtgl[1];
	$nmbln=numToMonth($bln,'I','long');
$tgl=$potongtgl[2];


if ($tmpBar->jenissp == 'PHK') {
    $pdf->SetX(20);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(170, 5, $_SESSION['lang']['suratkeputusan'], 0, 0, 'C');
    $pdf->Ln();
    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(170, 5, 'No. ' . strtoupper($tmpBar->nomor), 0, 0, 'C');

    // $pdf->SetX(20);
    // $pdf->SetFont('Arial', '', 12);
    // $pdf->Cell(170, 5, $_SESSION['lang']['tentang'], 0, 0, 'C');
    // $pdf->Ln();
    // $pdf->Ln();
    // $pdf->SetX(20);
    // $pdf->SetFont('Arial', '', 12);
    // $pdf->Cell(170, 5, strtoupper($tmpBar->keterangan), 0, 0, 'C');
    // $pdf->Ln();
    // $pdf->SetX(20);
    // $pdf->SetFont('Arial', '', 12);
    // $pdf->Cell(170, 5, "(" . strtoupper($tmpBar->jenissp) . ")", 0, 0, 'C');
} else {
    $pdf->SetX(20);
    $pdf->SetFont('Arial', 'BU', 12);
    $pdf->Cell(170, 5, strtoupper($tmpBar->keterangan), 0, 0, 'C');
    $pdf->Ln();
    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(170, 5, $tmpBar->nomor, 0, 0, 'C');
}
$pdf->Ln();
$pdf->Ln();
/*
if ($tipesp == 'BAPP') {
    
} else if ($tipesp == 'PHK') {
    
} else if ($tipesp == 'SKR') {
    
} else {
    // echo $tmpBar->jenissp;
    $pdf->SetX(20);
    $pdf->Cell(20, 5, $_SESSION['lang']['kepada'], 0, 0, 'L');
    $pdf->Ln();
    // $pdf->Cell(5,5,':',0,0,'L');	
    // $pdf->Cell(100,5,$namakaryawan,0,1,'L');
    $pdf->SetX(20);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(10, 5, 'Sdr. ' . $namakaryawan, 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(10, 5, 'Di  -', 0, 1, 'L');
    $pdf->SetX(28);
    $pdf->SetFont('Arial', 'U', 10);
    $pdf->Cell(105, 5, 'TEMPAT', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Ln();
    $pdf->Ln();
}
*/

//Content Letter
if ($tipesp == 'BAPP') {
    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(170, 5, $pelanggaran, 0, 'J');

    $pdf->Ln();

    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(170, 5, $paragraf1, 0, 'J');

    $pdf->Ln();

    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(20, 5, '', 0, 0, 'L');
    $pdf->Cell(20, 5, $_SESSION['lang']['nama'], 0, 0, 'L');
    $pdf->Cell(2, 5, ':', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(110, 5, $namakaryawan, 0, 'J');
    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(20, 5, '', 0, 0, 'L');
    $pdf->Cell(20, 5, $_SESSION['lang']['jabatan'], 0, 0, 'L');
    $pdf->Cell(2, 5, ':', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(110, 5, $jabatan, 0, 'J');

    $pdf->Ln();

    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(170, 5, $paragraf3, 0, 'J');

    $pdf->Ln();

    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(170, 5, $paragraf4, 0, 'J');
} else if ($tipesp == 'SKR') {
    $pdf->SetX(20);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 5, $_SESSION['lang']['menimbang'], 0, 0, 'L');
    $pdf->Cell(10, 5, ':', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(10, 5, '1.', 0, 0, 'L');
    $pdf->MultiCell(110, 5, 'Perlu adanya tindakan disiplin bagi pegawai yang telah melakukan pelanggaran terhadap peraturan perusahaan yang berlaku.', 0, 'J');
    $pdf->SetX(20);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 5, '', 0, 0, 'L');
    $pdf->Cell(10, 5, '', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(10, 5, '2.', 0, 0, 'L');
    $pdf->MultiCell(110, 5, 'Perlu adanya peringatan keras bagi pegawai yang telah melakukan pelanggaran serius terhadap peraturan perusahaan yang berlaku.', 0, 'J');

    $pdf->Ln();

    $pdf->SetX(20);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 5, $_SESSION['lang']['memperhatikan'], 0, 0, 'L');
    $pdf->Cell(10, 5, ':', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(125, 5, $pelanggaran, 0, 'J');

    $pdf->Ln();

    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(170, 5, $paragraf1, 0, 'J');

    $pdf->Ln();

    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(20, 5, '', 0, 0, 'L');
    $pdf->Cell(20, 5, $_SESSION['lang']['nama'], 0, 0, 'L');
    $pdf->Cell(2, 5, ':', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(110, 5, $namakaryawan, 0, 'J');
    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(20, 5, '', 0, 0, 'L');
    $pdf->Cell(20, 5, $_SESSION['lang']['jabatan'], 0, 0, 'L');
    $pdf->Cell(2, 5, ':', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(110, 5, $jabatan, 0, 'J');
    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(20, 5, '', 0, 0, 'L');
    $pdf->Cell(20, 5, $_SESSION['lang']['alamat'], 0, 0, 'L');
    $pdf->Cell(2, 5, ':', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(110, 5, $alamataktif, 0, 'J');

    $pdf->Ln();

    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(170, 5, $paragraf3, 0, 'J');

    $pdf->Ln();

    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(170, 5, $paragraf4, 0, 'J');
} 


else if ($tipesp == 'PHK') {
    $height=5;
   
    $pdf->SetFont('Arial', '', 10);
	
	$pdf->Cell(30, $height,'Dasar', 0, 0, 'L');
	$pdf->Cell(5, $height,':', 0, 0, 'R');
    $pdf->MultiCell(150, $height, $mendengar, 0, 'L');

	
	$pdf->Cell(30, $height, $_SESSION['lang']['menimbang'], 0, 0, 'L');
	$pdf->Cell(5, $height,':', 0, 0, 'R');
    $pdf->MultiCell(150, $height, $menimbang, 0, 'L');



    $pdf->Cell(30, $height, $_SESSION['lang']['mengingat'], 0, 0, 'L');
	$pdf->Cell(5, $height,':', 0, 0, 'R');
    $pdf->MultiCell(150, $height, $mengingat, 0, 'L');

	$pdf->Ln();
	$pdf->SetFont('Arial', 'B', 10);
	$pdf->Cell(190, $height,'Memutuskan', 0, 1, 'C');
	
	$pdf->SetFont('Arial', '', 10);
   $pdf->Ln();
   
    $pdf->Cell(30, $height,'Pertama', 0, 0, 'L');
	$pdf->Cell(5, $height,':', 0, 0, 'R');
	$pdf->MultiCell(150, $height, $pelanggaran, 0, 'L');
	$pdf->Ln();
	$pdf->SetX(45);
	$pdf->Cell(30, $height,'Nama', 0, 0, 'L');
	$pdf->Cell(5, $height,':', 0, 0, 'R');
	$pdf->Cell(50, $height,$namakaryawan, 0, 1, 'L');
	$pdf->SetX(45);
	$pdf->Cell(30, $height,'Jabatan', 0, 0, 'L');
	$pdf->Cell(5, $height,':', 0, 0, 'R');
	$pdf->Cell(50, $height,$jabatanybs, 0, 1, 'L');
	$pdf->SetX(45);
	$pdf->Cell(30, $height,'Tgl Masuk Kerja', 0, 0, 'L');
	$pdf->Cell(5, $height,':', 0, 0, 'R');
	$pdf->Cell(50, $height,tanggalnormal($tmk), 0, 1, 'L');
	$pdf->Ln();
	
	
	
	
	
	$pdf->Cell(30, $height,'Kedua', 0, 0, 'L');
	$pdf->Cell(5, $height,':', 0, 0, 'R');
	$pdf->MultiCell(150, $height, $paragraf1, 0, 'L');
	
	$pdf->Cell(30, $height,'Ketiga', 0, 0, 'L');
	$pdf->Cell(5, $height,':', 0, 0, 'R');
	$pdf->MultiCell(150, $height, $paragraf3, 0, 'L');
	$pdf->Ln();

	$pdf->MultiCell(190, $height, $paragraf4, 0, 'L');
    $pdf->Ln();
	
	
	
	$pdf->Cell(30, $height,'Dikeluarkan di', 0, 0, 'L');
	$pdf->Cell(5, $height,':', 0, 0, 'R');
	$pdf->Cell(50, $height,$tmpBar->wilKaryawan, 0, 1, 'L');

	
	$pdf->Cell(30, $height,'Pada tanggal', 0, 0, 'L');
	$pdf->Cell(5, $height,':', 0, 0, 'R');
	$pdf->Cell(50, $height,$tgl.' '.$nmbln.' '.$thn, 0, 1, 'L');
	$pdf->Ln();

	$pdf->Cell(60, $height,'Diberikan oleh,', 0, 0, 'C');
	
	$pdf->Ln(20);
	
	
	$pdf->SetFont('Arial', 'B', 10);
	$pdf->Cell(60, 5, "" . $dibuat . " ", 'B', 0, 'C');
	$pdf->SetX(120);
	$pdf->Cell(60, 5, "" . $verifikasi . " ", 'B', 0, 'C');
	$pdf->Ln();
	$pdf->Cell(60, 5, "" . $jabatandibuat . " ", '', 0, 'C');
	$pdf->SetX(120);
	$pdf->Cell(60, 5, "" . $jabatanverifikasi . " ", '', 1, 'C');
	
	
	
	
} else {
	
	$height=5;
   
    $pdf->SetFont('Arial', '', 10);
	
	$pdf->Cell(30, $height, $_SESSION['lang']['menimbang'], 0, 0, 'L');
	$pdf->Cell(5, $height,':', 0, 0, 'R');
    $pdf->MultiCell(150, $height, $menimbang, 0, 'L');

    $pdf->Ln();

    $pdf->Cell(30, $height, $_SESSION['lang']['mengingat'], 0, 0, 'L');
	$pdf->Cell(5, $height,':', 0, 0, 'R');
    $pdf->MultiCell(150, $height, $mengingat, 0, 'L');

    $pdf->Ln();

   
    $pdf->Cell(30, $height,'Mendengar', 0, 0, 'L');
	$pdf->Cell(5, $height,':', 0, 0, 'R');
    $pdf->MultiCell(150, $height, $mendengar, 0, 'L');

    $pdf->Ln();
	
	
	
	
	$pdf->SetFont('Arial', 'B', 10);
	$pdf->Cell(190, $height,'Memutuskan', 0, 1, 'C');
	$pdf->SetFont('Arial', '', 10);
	$pdf->MultiCell(190, $height, $pelanggaran, 0, 'C');
	
	$pdf->Ln();
	$pdf->SetFont('Arial', 'B', 10);
	$pdf->Cell(190, $height,$ketHal, 0, 1, 'C');
	
	$pdf->SetFont('Arial', '', 10);
	$pdf->Cell(20, $height,'Kepada', 0, 1, 'L');
	
	$pdf->Cell(30, $height,'Nama', 0, 0, 'L');
	$pdf->Cell(5, $height,':', 0, 0, 'R');
	$pdf->Cell(50, $height,$namakaryawan, 0, 1, 'L');
	
	$pdf->Cell(30, $height,'Jabatan', 0, 0, 'L');
	$pdf->Cell(5, $height,':', 0, 0, 'R');
	$pdf->Cell(50, $height,$jabatanybs, 0, 1, 'L');
	
	
	$pdf->Cell(30, $height,'Departemen', 0, 0, 'L');
	$pdf->Cell(5, $height,':', 0, 0, 'R');
	$pdf->Cell(50, $height,$nmbag[$bagianybs], 0, 1, 'L');
	
	$pdf->Ln();
	$pdf->MultiCell(190, $height, $paragraf1, 0, 'L');
	
	$pdf->Ln();
	$pdf->MultiCell(190, $height, $paragraf3, 0, 'L');
  
    $pdf->Ln();
	$pdf->MultiCell(190, $height, $paragraf4, 0, 'L');
	
	$pdf->SetX(120);
	$pdf->Cell(30, $height,'Dikeluarkan di', 0, 0, 'L');
	$pdf->Cell(5, $height,':', 0, 0, 'R');
	$pdf->Cell(50, $height,$tmpBar->wilKaryawan, 0, 1, 'L');
	
	$pdf->SetX(120);
	$pdf->Cell(30, $height,'Pada tanggal', 0, 0, 'L');
	$pdf->Cell(5, $height,':', 0, 0, 'R');
	$pdf->Cell(50, $height,$tgl.' '.$nmbln.' '.$thn, 0, 1, 'L');
	$pdf->Ln();
	$pdf->SetX(120);
	$pdf->Cell(60, $height,'Diberikan oleh,', 0, 0, 'C');
	
	$pdf->Ln(20);
	
	
$pdf->SetFont('Arial', 'B', 10);
	$pdf->SetX(120);
	$pdf->Cell(60, 5, "" . $dibuat . " ", 'B', 0, 'C');
	$pdf->Ln();
	$pdf->SetX(120);
	$pdf->Cell(60, 5, "" . $jabatandibuat . " ", '', 1, 'C');
  
}


$pdf->SetFont('Arial', '', 10);

$pdf->Ln();

/*
$pdf->SetX(20);
$pdf->MultiCell(170, 5, $tmpBar->wilKaryawan . ', ' . tanggalnormal($tmpBar->tanggal), 0, 'J');
$pdf->Ln();


//signature
if ($tmpBar->jenissp == 'BAPP') {
    $pdf->SetX(20);
    $pdf->Cell(40, 5, $_SESSION['lang']['yangmemeriksa'], 0, 0, 'C');
    $pdf->Cell(15, 5, '', 0, 0, 'C');
    $pdf->Cell(40, 5, $_SESSION['lang']['yangdiperiksa'], 0, 0, 'C');
    $pdf->Cell(15, 5, '', 0, 0, 'C');
    $pdf->Cell(40, 5, $_SESSION['lang']['mengetahui'], 0, 1, 'C');
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->SetX(20);
    $pdf->Cell(40, 5, "" . $penandatangan . " ", 'B', 0, 'C');
    $pdf->Cell(15, 5, '', 0, 0, 'C');
    $pdf->Cell(40, 5, "" . $namakaryawan . " ", 'B', 0, 'C');
    $pdf->Cell(15, 5, '', 0, 0, 'C');
    $pdf->Cell(40, 5, "" . $penandatangan . " ", 'B', 1, 'C');
    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(40, 5, "" . $jabatan . " ", 0, 0, 'C');
    $pdf->Cell(15, 5, '', 0, 0, 'C');
    $pdf->Cell(40, 5, "" . $jabatanybs . " ", 0, 0, 'C');
    $pdf->Cell(15, 5, '', 0, 0, 'C');
    $pdf->Cell(40, 5, "" . $jabatanverifikasi . " ", 0, 1, 'C');
} else if ($tmpBar->jenissp == 'PHK') {
    $pdf->SetX(20);
    $pdf->Cell(40, 5, $_SESSION['lang']['disetujui'], 0, 0, 'C');
    $pdf->Cell(15, 5, '', 0, 0, 'C');
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->SetX(20);
    $pdf->Cell(40, 5, "" . $penandatangan . " ", 'B', 0, 'C');
    $pdf->Ln();
    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(40, 5, "" . $jabatan . " ", 0, 0, 'C');
} else if ($tmpBar->jenissp == 'SKR') {
    $pdf->SetX(20);
    $pdf->Cell(40, 5, $_SESSION['lang']['disetujui'], 0, 0, 'C');
    $pdf->Cell(15, 5, '', 0, 0, 'C');
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->SetX(20);
    $pdf->Cell(40, 5, "" . $penandatangan . " ", 'B', 0, 'C');
    $pdf->Ln();
    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(40, 5, "" . $jabatan . " ", 0, 0, 'C');
} else if ($tmpBar->jenissp == 'ST1') {
    $pdf->SetX(20);
    $pdf->Cell(40, 5, $_SESSION['lang']['disetujui'], 0, 0, 'C');
    $pdf->Cell(70, 5, '', 0, 0, 'C');
    $pdf->Cell(40, 5, $_SESSION['lang']['pegawaiyangbersangkutan'], 0, 1, 'C');


    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->SetX(20);
    $pdf->Cell(40, 5, "" . $penandatangan . " ", 'B', 0, 'C');
    $pdf->Cell(70, 5, '', 0, 0, 'C');
    $pdf->Cell(40, 5, "" . $namakaryawan . " ", 'B', 1, 'C');

    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(40, 5, "" . $jabatan . " ", 0, 0, 'C');
    $pdf->Cell(70, 5, '', 0, 0, 'C');
    $pdf->Cell(40, 5, "" . $jabatanybs . " ", 0, 1, 'C');
} else {
    $pdf->SetX(20);
    $pdf->Cell(40, 5, $_SESSION['lang']['disetujui'], 0, 0, 'C');
    $pdf->Cell(15, 5, '', 0, 0, 'C');
    $pdf->Cell(40, 5, $_SESSION['lang']['diketahuioleh'], 0, 0, 'C');
    $pdf->Cell(15, 5, '', 0, 0, 'C');
    if ($dibuat == '') {
        $pdf->Cell(40, 5, $_SESSION['lang']['pegawaiyangbersangkutan'], 0, 1, 'C');
    } else {
        $pdf->Cell(40, 5, $_SESSION['lang']['dibuat'], 0, 1, 'C');
    }

    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->SetX(20);
    $pdf->Cell(40, 5, "" . $penandatangan . " ", 'B', 0, 'C');
    $pdf->Cell(15, 5, '', 0, 0, 'C');
    $pdf->Cell(40, 5, "" . $verifikasi . " ", 'B', 0, 'C');
    $pdf->Cell(15, 5, '', 0, 0, 'C');
    if ($dibuat == '') {
        $pdf->Cell(40, 5, "" . $namakaryawan . " ", 'B', 1, 'C');
    } else {
        $pdf->Cell(40, 5, "" . $dibuat . " ", 'B', 1, 'C');
    }

    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(40, 5, "" . $jabatan . " ", 0, 0, 'C');
    $pdf->Cell(15, 5, '', 0, 0, 'C');
    $pdf->Cell(40, 5, "" . $jabatanverifikasi . " ", 0, 0, 'C');
    $pdf->Cell(15, 5, '', 0, 0, 'C');
    if ($dibuat == '') {
        $pdf->Cell(40, 5, "" . $jabatanybs . " ", 0, 1, 'C');
    } else {
        $pdf->Cell(40, 5, "" . $jabatandibuat . " ", 0, 1, 'C');
    }
}
*/

// if($tipex=='0'){
// //=========penandatangan
// $pdf->SetX(20);
// $pdf->Cell(40,5,'KPP GROUP',0,1,'L');
// $pdf->Ln();
// $pdf->Ln();			
// $pdf->Ln();	
// $pdf->SetX(20);
// $pdf->Cell(40,5,"".$penandatangan." ",'B',1,'L');
// $pdf->SetX(20);
// $pdf->SetFont('Arial','',10);	
// $pdf->Cell(40,5,"".$jabatan." ",0,1,'L');	
// }else{
// }
//=====================tembusan	
$pdf->Ln();
$pdf->Ln();
$pdf->SetX(20);
$pdf->Cell(40, 5, 'Tembusan:', 0, 1, 'L');
if ($tembusan1 != '') {
    $pdf->SetX(25);
    $pdf->Cell(40, 5, '1. ' . $tembusan1, 0, 1, 'L');
}
if ($tembusan2 != '') {
    $pdf->SetX(25);
    $pdf->Cell(40, 5, '2. ' . $tembusan2, 0, 1, 'L');
}
if ($tembusan3 != '') {
    $pdf->SetX(25);
    $pdf->Cell(40, 5, '3 .' . $tembusan3, 0, 1, 'L');
}
if ($tembusan4 != '') {
    $pdf->SetX(25);
    $pdf->Cell(40, 5, '4 .' . $tembusan4, 0, 1, 'L');
}


//footer================================
$pdf->Ln();
$pdf->Output();
?>
