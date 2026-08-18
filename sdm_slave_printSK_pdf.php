<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
$nosk = $_GET['nosk'];
$tipe = strtoupper(substr($nosk, 4, 2));

//=============
//create Header
$optPtx = makeOption($dbname,'organisasi','kodeorganisasi,induk');
$namakota = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$str = "select * from " . $dbname . ".sdm_riwayatjabatan where nomorsk='" . $nosk . "'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
        $ke=$optPtx[$bar->kekodeorg];
    }


class PDF extends FPDF {

    function Header() {
        global $conn;
        global $dbname;
        global $tipe;
        global $owlPDO;
        global $nosk;
		global $ke;
        $this->SetFillColor(255, 255, 255);

        // $this->SetMargins(15, 5, 0);
		// $arrHead = setheadreport("",substr($nosk,0,3));
        // if ($tipe=='MU') {
        //     $arrHead = setheadreport("",substr($ke,0,3));
        // }
		 
		
		// $path = 'images/logo.jpg';
        // $this->Image($path, 15, 5, 0, 30);
        // $this->SetFont('Arial', 'B', 18);
        // $this->SetFillColor(255, 255, 255);
        // $this->SetX(45);
        // $this->SetTextColor(0, 150, 0);
        // $this->Cell(60, 15, $_SESSION['org']['namaorganisasi'], 0, 1, 'L');
        // $this->SetTextColor(0, 0, 0);
        // $this->Line(15, 35, 205, 35);
	  
	  
        
			// $height = 5;
			// $path=$arrHead['logo'];
			// $this->Image($path,15, 0, 0, 25);
			// $this->SetFont('Arial','B',9);
			// $this->SetFillColor(255,255,255);	
			// $this->SetX(50);   
			// $this->Cell($width-100,$height,$arrHead['nama'],0,1,'L');	 
			// $this->SetX(50); 		
			// $this->Cell($width-100,$height,$arrHead['alamat'],0,1,'L');	
			// $this->SetX(50); 			
			// $this->Cell($width-100,$height,"Tel: ".$arrHead['telepon'],0,1,'L');	
			// $this->Line(15, 27, 200, 27);

        // //$this->SetY(27);
        // $this->SetX(163);
        // $this->SetFont('Arial', '', 10);
        // if ($_SESSION['language'] == 'EN') {
            // $this->Cell(30, 5, 'CONFIDENTIAL', 0, 1, 'R');
        // } else {
            // $this->Cell(30, 5, 'PRIBADI DAN RAHASIA', 0, 1, 'R');
        // }
        // $this->SetFont('Arial', '', 6);
        // $this->SetX(163);
        // $this->Cell(30, 5, 'PRINT TIME : ' . date('d-m-Y H:i:s'), 0, 1, 'R');
    }

    function Footer() {
        // global $conn;
        // global $dbname;
        // global $owlPDO;
        // $str1 = "select namaorganisasi,alamat,wilayahkota,telepon from " . $dbname . ".organisasi where kodeorganisasi='PMO'";
        // $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
		// $res1->setFetchMode(PDO::FETCH_OBJ);
		// while ($bar1 = $res1->fetch()) {
        //     $namapt = $bar1->namaorganisasi;
        //     $alamatpt = $bar1->alamat . ", " . $bar1->wilayahkota;
        //     $telp = $bar1->telepon;
        // }
        // $this->SetY(-15);
        // $this->Line(15, 275, 205, 275);
        // $this->SetFont('Arial', 'I', 8);
        // $this->Cell(160, 5, (isset($alamatpt) ? $alamatpt : "") . ", Tel:" . (isset($telp) ? $telp : ""), 0, 1, 'L');
        // $this->Cell(10,5,'Page '.$this->PageNo(),0,0,'C');
    }

}

function ambilkomponengaji($idkomponen, $owlPDO, $dbname) {
    $d = '';
    $strc = "select * from " . $dbname . ".sdm_ho_component where id=" . $idkomponen;
    $resc=$owlPDO->query($strc) or die(print " Gagal: ".PDOException::getMessage());
    $resc->setFetchMode(PDO::FETCH_OBJ);
    while ($barc = $resc->fetch()) {
        $d = $barc->name;
    }
    return $d;
}

function ambilkomponengajix($idkomponen, $owlPDO, $dbname) {
    $d = '';
    $strc = "select * from " . $dbname . ".sdm_ho_component where id=" . $idkomponen;
    $resc=$owlPDO->query($strc) or die(print " Gagal: ".PDOException::getMessage());
    $resc->setFetchMode(PDO::FETCH_OBJ);
    while ($barc = $resc->fetch()) {
        $d = $barc->name.'###'.$barc->plus;
    }
    return $d;
}

function ambiljabatan($kodejabatan, $owlPDO, $dbname) {
    $d = '';
    $strc = "select * from " . $dbname . ".sdm_5jabatan where kodejabatan=" . $kodejabatan;
    $resc=$owlPDO->query($strc) or die(print " Gagal: ".PDOException::getMessage());
	$resc->setFetchMode(PDO::FETCH_OBJ);
	while ($barc = $resc->fetch()) {
        $d = $barc->namajabatan;
    }
    return $d;
}

function ambiltipekaryawan($idtipe, $owlPDO, $dbname) {
    $opt = '';
    $str = "select * from " . $dbname . ".sdm_5tipekaryawan where id=" . $idtipe;
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while ($bar = $res->fetch()) {
        $opt = $bar->tipe;
    }
    return $opt;
}

function ambilpangkat($kodepangkat, $owlPDO, $dbname) {
    $d = '';
    $strc = "select * from ".$dbname.".sdm_5golongan where kodegolongan='".$kodepangkat."'";
    $resc=$owlPDO->query($strc) or die(print " Gagal: ".PDOException::getMessage());
	$resc->setFetchMode(PDO::FETCH_OBJ);
	while ($barc = $resc->fetch()) {
        $d = $barc->namagolongan;
    }
    return $d;
}

function ambildepartmen($kodedepartmen, $owlPDO, $dbname) {
    $d = '';
    $strc = "select * from ".$dbname.".sdm_5departemen where kode='".$kodedepartmen."'";
    $resc=$owlPDO->query($strc) or die(print " Gagal: ".PDOException::getMessage());
	$resc->setFetchMode(PDO::FETCH_OBJ);
	while ($barc = $resc->fetch()) {
        $d = $barc->nama;
    }
    return $d;
}

$str = "select * from " . $dbname . ".sdm_riwayatjabatan where nomorsk='" . $nosk . "'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {

    //===================smbil nama 
    $namakaryawan = '';
    $strx = "select a.namakaryawan,a.tanggalmasuk, a.lokasipenerimaan,b.nama,a.nik from " . $dbname . ".datakaryawan a 
              left join " . $dbname . ".sdm_5departemen b on a.bagian=b.kode
              where karyawanid=" . $bar->karyawanid;
	$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
	$resx->setFetchMode(PDO::FETCH_OBJ);
    while ($barx = $resx->fetch()) {
        $namakaryawan = $barx->namakaryawan;
        $tanggalmasuk = $barx->tanggalmasuk;
        $lokasipenerimaan = $barx->lokasipenerimaan;
        $nikkaryawan = $barx->nik;
    }

    $strx = "select a.namakaryawan,b.namajabatan from " . $dbname . ".datakaryawan a
                 left join  " . $dbname . ".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
                 where  karyawanid=" . $bar->atasanbaru;
    $atasanbaru = '';
    $jabatanatasan = '';
	$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
	$resx->setFetchMode(PDO::FETCH_OBJ);
    while ($barx = $resx->fetch()) {
        $atasanbaru = $barx->namakaryawan;
        $jabatanatasan = $barx->namajabatan;
    }

    $tanggal = tanggalnormal($bar->tanggalsk);
    $mulaiberlaku = tanggalnormal($bar->mulaiberlaku);
    $tipesk = $bar->tipesk;
    //====================ambil tipe untuk hal
    $ketHal = '';
    $str = "select keterangan from " . $dbname . ".sdm_5tipesk where kode='" . $tipesk . "'";
	$rekx=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$rekx->setFetchMode(PDO::FETCH_OBJ);
	while ($barkx = $rekx->fetch()) {
        $ketHal = trim($barkx->keterangan);
    }
    //===============================
    $oldjabatan = ambiljabatan($bar->darikodejabatan, $owlPDO, $dbname);
    $newjabatan = ambiljabatan($bar->kekodejabatan, $owlPDO, $dbname);
    $oldtipe = ambiltipekaryawan($bar->daritipe, $owlPDO, $dbname);
    $newtipe = ambiltipekaryawan($bar->ketipekaryawan, $owlPDO, $dbname);
    $oldlokasitugas = $bar->darikodeorg;
    $newlokasitugas = $bar->kekodeorg;
    $nomorinduk = $bar->karyawanid;
    // $oldkodegolongan = $bar->darikodegolongan;
    // $newkodegolongan = $bar->kekodegolongan;
    $oldkodegolongan = ambilpangkat($bar->darikodegolongan, $owlPDO, $dbname);
    $newkodegolongan = ambilpangkat($bar->kekodegolongan, $owlPDO, $dbname);
	$oldkodedepartmen = ambildepartmen($bar->bagian, $owlPDO, $dbname);
    $newkodedepartmen = ambildepartmen($bar->kebagian, $owlPDO, $dbname);
    $oldsubbagian = $bar->darisubbagian;
    $newsubbagian = $bar->kesubbagian;
    $direksi = $bar->namadireksi;
    $tembusan1 = $bar->tembusan1;
    $tembusan2 = $bar->tembusan2;
    $tembusan3 = $bar->tembusan3;
    $tembusan4 = $bar->tembusan4;
    $tembusan5 = $bar->tembusan5;
    
    $namajabatan = $bar->namajabatan;
    $pg1 = trim($bar->pg1);
    $pg2 = trim($bar->pg2);
    $pg3 = trim($bar->pg3);
    $pg4 = trim($bar->pg4);
    $pg5 = trim($bar->pg5);
    $pg6 = trim($bar->pg6);
	
    $menimbang = trim($bar->menimbang);
    $mengingat = trim($bar->mengingat);
    
	$bagian = $bar->bagian;
    $kebagian = $bar->kebagian;
}

//===============ambil PT tempat baru
$strf = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi in(select induk from " . $dbname . ".organisasi where kodeorganisasi='" . $newlokasitugas . "')";
$resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
$resf->setFetchMode(PDO::FETCH_OBJ);
while ($barf = $resf->fetch()) {
    $namaptx = $barf->namaorganisasi;
}

//===============ambil PT
$strpt = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi in(select induk from " . $dbname . ".organisasi where kodeorganisasi='" . $oldlokasitugas . "')";
$respt=$owlPDO->query($strpt) or die(print " Gagal: ".PDOException::getMessage());
$respt->setFetchMode(PDO::FETCH_OBJ);
while ($barpt = $respt->fetch()) {
    $namapt = $barpt->namaorganisasi;
}

//  
$paragraf1 = "Dalam rangka memperoleh hasil guna yang optimum dan sesuai dengan perkembangan perusahaan dewasa ini, dipandang perlu melaksanakan " . $tipesk . "  terhadap karyawan lingkungan Medco Agro.";
if ($pg1 !== '')
    $paragraf1 = $pg1;
$paragraf2 = "Terhitung mulai tanggal " . $mulaiberlaku . ", perusahaan melakukan " . $tipesk . " terhadap saudara/(i):";

//$paragraf3="Jajaran direksi Minanga Group mengucapkan selamat berkarya.";	

$paragraf25 = "Dalam melaksanakan tugas / jabatan tersebut diatas berada dibawah dan bertanggung jawab kepada " . ucwords(strtolower($atasanbaru)) . " sebagai " . ucwords(strtolower($jabatanatasan)) . "  di " . $namaptx . ".";
$paragrafadd = "Masa percobaan sebagai Pelaksana Tugas " . $newjabatan . " selama 1 (satu) tahun.";
$paragraffadd2 = "Jika dikemudian hari ternyata terdapat kekeliruan dalam Surat Keputusan ini, akan diadakan perbaikan sebagaimana mestinya.";

if ($pg2 !== '') {
    $paragraf25 = $pg2;
    $paragrafadd = '';
    $paragraffadd2 = '';
}

$optPt = makeOption($dbname,'organisasi','kodeorganisasi,induk');
$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

$customSize = array(215.9, 355.6);
$pdf = new PDF('P', 'mm', $customSize);
$pdf->SetAutoPageBreak(false);
$pdf->SetFont('Arial', 'BU', 17);
$pdf->AddPage();
$pdf->SetY(10);
$pdf->SetX(20);
$pdf->SetFillColor(255, 255, 255);

// if ($tipe == 'PE') 
// {	
// 	//penyesuaian
//     $pdf->Cell(175, 7, 'SURAT KEPUTUSAN - PENYESUAIAN GAJI/TUNJANGAN', '0', 1, 'C');
//     $pdf->SetFont('Arial', '', 12);
//     $pdf->Cell(175, 5, "No." . substr($nosk, 12, 3) . " / SK-GAJI / HRD / HO / " . substr($tanggal, 3, 2) . " / " . substr($tanggal, 6, 4), 0, 1, 'C');
//     $pdf->Ln();
//     $pdf->Ln();
//     $pdf->SetFont('Arial', '', 10);
//     $pdf->Cell(175, 5, "Manajemen perusahaan memutuskan sebagai berikut:", 0, 1, 'L');
//     $pdf->Ln();
//     $pdf->Cell(30, 5, $_SESSION['lang']['namakaryawan'], 0, 0, 'L');
//     $pdf->Cell(40, 5, " : " . $namakaryawan, 0, 1, 'L'); #mahe
//     $pdf->Cell(30, 5, $_SESSION['lang']['nokaryawan'], 0, 0, 'L');
//     $pdf->Cell(40, 5, " : " . $nomorinduk, 0, 1, 'L');
//     $pdf->Cell(30, 5, 'TMK', 0, 0, 'L');
//     $pdf->Cell(40, 5, " : " . tanggalnormal($tanggalmasuk), 0, 1, 'L');
//     $pdf->Ln();
//     $pdf->SetFont('Arial', 'B', 12);
//     $pdf->Cell(30, 5, 'A.Status Karyawan', 0, 1, 'L');
//     $pdf->SetFont('Arial', 'B', 10);
//     $pdf->Cell(20, 5, 'No', 1, 0, 'C');
//     $pdf->Cell(50, 5, 'Deskripsi', 1, 0, 'C');
//     $pdf->Cell(50, 5, 'Dari', 1, 0, 'C');
//     $pdf->Cell(50, 5, 'Menjadi', 1, 1, 'C');
//     $pdf->SetFont('Arial', '', 10);
//     $pdf->Cell(20, 5, '1', 1, 0, 'L');
//     $pdf->Cell(50, 5, $_SESSION['lang']['functionname'], 1, 0, 'L');
//     $pdf->Cell(50, 5, $oldjabatan, 1, 0, 'L');
//     $pdf->Cell(50, 5, $newjabatan, 1, 1, 'L');
//     $pdf->Cell(20, 5, '2', 1, 0, 'L');
//     $pdf->Cell(50, 5, $_SESSION['lang']['levelname'], 1, 0, 'L');
//     $pdf->Cell(50, 5, $oldkodegolongan, 1, 0, 'L');
//     $pdf->Cell(50, 5, $newkodegolongan, 1, 1, 'L');
//     $pdf->Cell(20, 5, '3', 1, 0, 'L');
//     $pdf->Cell(50, 5, 'Divisi/Dept./Sect./Unit', 1, 0, 'L');
//     $pdf->Cell(50, 5, $bagian, 1, 0, 'L');
//     $pdf->Cell(50, 5, $kebagian, 1, 1, 'L');
//     $pdf->Cell(20, 5, '4', 1, 0, 'L');
//     $pdf->Cell(50, 5, $_SESSION['lang']['lokasitugas'], 1, 0, 'L');
//     $pdf->Cell(50, 5, $oldlokasitugas, 1, 0, 'L');
//     $pdf->Cell(50, 5, $newlokasitugas, 1, 1, 'L');
//     $pdf->Cell(20, 5, '5', 1, 0, 'L');
//     $pdf->Cell(50, 5, $_SESSION['lang']['poh'], 1, 0, 'L');
//     $pdf->Cell(100, 5, $lokasipenerimaan, 1, 1, 'L');


//     $pdf->Ln();
//     $pdf->SetFont('Arial', 'B', 12);
//     $pdf->Cell(30, 5, 'B.	Gaji & Tunjangan (Netto)', 0, 1, 'L');
//     $pdf->SetFont('Arial', 'B', 10);
//     $pdf->Cell(20, 5, 'No', 1, 0, 'C');
//     $pdf->Cell(50, 5, 'Deskripsi', 1, 0, 'C');
//     $pdf->Cell(50, 5, 'Dari', 1, 0, 'C');
//     $pdf->Cell(50, 5, 'Menjadi', 1, 1, 'C');
//     $pdf->SetFont('Arial', '', 10);

//     $strx = "select * from " . $dbname . ".sdm_riwayatjabatan_gaji where nomorsk='" . $nosk . "' order by idkomponen";
//     $resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
//     $resx->setFetchMode(PDO::FETCH_OBJ);
//     $arrx = array();
//     while ($barx = $resx->fetch()) {
//         $arrx[$nosk][$barx->idkomponen][$barx->status]=$barx->rupiah;
//     }
//     $nox=1;
//     foreach ($arrx[$nosk] as $key => $val) {
//             $pdf->Cell(20, 5, $nox, 1, 0, 'L');
//             $pdf->Cell(50, 5, ambilkomponengaji($key, $owlPDO, $dbname), 1, 0, 'L');
//             $pdf->Cell(50, 5, number_format($val['O'], 2), 1, 0, 'R');
//             $pdf->Cell(50, 5, number_format($val['N'], 2), 1, 1, 'R');
//             $nox++;
//     }
    

//     $pdf->Ln();
//     $pdf->SetFont('Arial', 'B', 12);
//     $pdf->Cell(30, 5, 'C. Lain Lain', 0, 1, 'L');
//     $pdf->SetFont('Arial', '', 10);
//     $pdf->Cell(5, 5, '', 0, 0, 'L');
//     $pdf->Cell(160, 5, 'Surat Keputusan ini berlaku terhitung mulai tanggal :' . $mulaiberlaku, 0, 1, 'L');

// }else if ($tipe == 'PG'){
//     $pdf->Cell(175, 5, $_SESSION['lang']['suratkeputusan'], 0, 1, 'C');
//     $pdf->SetX(20);
//     $pdf->SetFont('Arial', 'B', 10);
//     $pdf->Cell(175, 5, 'NO : ' . $nosk, 0, 1, 'C');
//     $pdf->Ln();
//     $pdf->SetX(20);
//     $pdf->Cell(175, 5,"TENTANG", 0, 1, 'C');
//     $pdf->SetX(20);
//     $pdf->Cell(175, 5,strtoupper($_SESSION['lang'][$ketHal]), 0, 1, 'C');
//     $pdf->SetX(20);
//     $pdf->Cell(175, 5,$namapt, 0, 1, 'C');
//     $pdf->Ln();

//     $pdf->SetX(20);
//     $pdf->SetFont('Arial', 'B', 10);
//     $pdf->Cell(30, 5,"Menimbang", 0, 'J');
//     $pdf->SetFont('Arial', '', 10);
//     $pdf->Cell(5, 5,":", 0, 'J');
//     $pdf->MultiCell(135, 5, $menimbang, 0, 'J');
//     $pdf->Ln();
    
//     $pdf->SetX(20);
//     $pdf->SetFont('Arial', 'B', 10);
//     $pdf->Cell(30, 5,"Mengingat", 0, 'J');
//     $pdf->SetFont('Arial', '', 10);
//     $pdf->Cell(5, 5,":", 0, 'J');
//     $pdf->MultiCell(135, 5, $mengingat, 0, 'J');
//     $pdf->Ln();
    
//     $pdf->SetFont('Arial', 'B', 10);
//     $pdf->SetX(20);
//     $pdf->Cell(175, 5,"MEMUTUSKAN", 0, 1, 'C');
    
//     $pdf->SetX(20);
//     $pdf->Cell(30, 5,"Menetapkan", 0, 'J');
//     $pdf->SetFont('Arial', '', 10);
//     $pdf->Cell(5, 5,":", 0,1, 'J');
    
//     $pdf->SetX(20);
//     $pdf->SetFont('Arial', 'B', 10);
//     $pdf->Cell(30, 5,"Pertama", 0, 'J');
//     $pdf->SetFont('Arial', '', 10);
//     $pdf->Cell(5, 5,":", 0, 'J');
//     $pdf->Cell(5, 5,"Mengangkat menjadi karyawan tetap", 0,1, 'J');
//     $pdf->Ln();

//     $pdf->SetX(40);
//     $pdf->Cell(25, 5,"Nama", 0, 'J');
//     $pdf->Cell(5, 5,":", 0,0, 'J');
//     $pdf->Cell(85, 5,$namakaryawan, 0,1, 'J');
    
//     $pdf->SetX(40);
//     $pdf->Cell(25, 5,"Jabatan", 0, 'J');
//     $pdf->Cell(5, 5,":", 0,0, 'J');
//     $pdf->Cell(85, 5,$oldjabatan, 0,1, 'J');
    
//     $pdf->SetX(40);
//     $pdf->Cell(25, 5,"Grade", 0, 'J');
//     $pdf->Cell(5, 5,":", 0,0, 'J');
//     $pdf->Cell(85, 5,$oldkodegolongan, 0,1, 'J');

//     $pdf->Ln();

//     $pdf->SetX(20);
//     $pdf->SetFont('Arial', 'B', 10);
//     $pdf->Cell(30, 5,'Kedua', 0, 'J');
//     $pdf->SetFont('Arial', '', 10);
//     $pdf->Cell(5, 5,":", 0, 'J');
//     $exptgl = explode('-',$mulaiberlaku);
//     $pdf->MultiCell(135, 5, $pg2." ".$exptgl[0]." ".numToMonth($exptgl[1],'I','long')." ".$exptgl[2], 0, 'J');
    
//     $pdf->Ln();

//     $pdf->SetX(20);
//     $pdf->SetFont('Arial', '', 10);
//     $pdf->MultiCell(170, 5, $pg3, 0, 'J');
    
//     $pdf->Ln();

//     $pdf->SetX(20);
//     $pdf->SetFont('Arial', '', 10);
//     $pdf->MultiCell(170, 5, $pg4, 0, 'J');
    
//     $pdf->Ln();
//     $pdf->SetX(20);
//     $pdf->Cell(175, 5,"Ditetapkan di : Jakarta", 0,1, 'C');
    
//     $pdf->SetX(20);
//     $exptgl = explode('-',$tanggal);
//     $pdf->Cell(175, 5,"Pada tanggal : ".$exptgl[0]." ".numToMonth($exptgl[1],'I','long')." ".$exptgl[2], 0,1, 'C');

// }else{
    $pdf->Cell(175, 5, $_SESSION['lang']['suratkeputusan'], 0, 1, 'C');
    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(175, 5, 'NO : ' . $nosk, 0, 1, 'C');
    $pdf->Ln();
    $pdf->SetX(20);
    $pdf->SetFont('Arial', '',9);
    $pdf->Cell(175, 5,"Tentang", 0, 1, 'C');
    $pdf->SetFont('Arial', 'BU', 10);
	$pdf->SetX(20);
    $pdf->Cell(175, 5,strtoupper($tipesk), 0, 1, 'C');
	$pdf->Ln();
    $pdf->SetFont('Arial', '', 9.2);
	$pdf->SetX(20);
    $pdf->Cell(175, 5, 'MANAGEMENT ' . $namaptx, 0, 1, 'C');
	$pdf->Ln();
	$pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);
	$pdf->Cell(35, 5,"MENIMBANG", 0, 'J');
	$pdf->Cell(5, 5,":", 0, 'J');
    $pdf->MultiCell(135, 5, $menimbang, 0, 'J');
    $pdf->Ln();
	
    $pdf->SetX(20);
    $pdf->Cell(35, 5,"MEMPERHATIKAN", 0, 'J');
    $pdf->Cell(5, 5,":", 0, 'J');
    $pdf->MultiCell(135, 5, $mengingat, 0, 'J');
    $pdf->Ln();
	
	$pdf->SetFont('Arial', 'B', 10);
	$pdf->SetX(20);
    $pdf->Cell(175, 5,"MEMUTUSKAN", 0, 1, 'C');
	
    $pdf->SetFont('Arial', '', 10);
	$pdf->SetX(20);
    $pdf->Cell(35, 5,"MENETAPKAN", 0, 'J');
	$pdf->SetFont('Arial', '', 10);
    $pdf->Cell(5, 5,":", 0,1, 'J');
	
	$pdf->SetX(60);
	// $pdf->SetFont('Arial', 'B', 10);
    // $pdf->Cell(30, 5,"Pertama", 0, 'J');
	// $pdf->SetFont('Arial', '', 10);
    // $pdf->Cell(5, 5,":", 0, 'J');
    
	$pdf->Cell(45, 5,"Nama", 0, 'J');
    $pdf->Cell(5, 5,":", 0,0, 'J');
    $pdf->Cell(85, 5,$namakaryawan, 0,1, 'J');
    
	$pdf->SetX(60);
	$pdf->Cell(45, 5,"NIK", 0, 'J');
    $pdf->Cell(5, 5,":", 0,0, 'J');
	$pdf->Cell(85, 5,$nikkaryawan, 0,1, 'J');
	
	$pdf->SetX(60);
	$pdf->Cell(45, 5,"Jabatan Lama", 0, 'J');
    $pdf->Cell(5, 5,":", 0,0, 'J');
	$pdf->Cell(85, 5,$oldjabatan, 0,1, 'J');
	
	$pdf->SetX(60);
	$pdf->Cell(45, 5,"Pangkat", 0, 'J');
    $pdf->Cell(5, 5,":", 0,0, 'J');
	$pdf->Cell(85, 5,$oldkodegolongan, 0,1, 'J');
	
	$pdf->SetX(60);
	$pdf->Cell(45, 5,"Divisi / Departement", 0, 'J');
    $pdf->Cell(5, 5,":", 0,0, 'J');
	$pdf->Cell(85, 5,$oldkodedepartmen, 0,1, 'J');
	
	$pdf->SetX(60);
	$pdf->Cell(45, 5,"Lokasi / PT", 0, 'J');
    $pdf->Cell(5, 5,":", 0,0, 'J');
	$pdf->Cell(85, 5,$optOrg[$oldlokasitugas], 0,1, 'J');

    $pdf->SetX(60);
	$pdf->Cell(45, 5,"Sub Bagian", 0, 'J');
    $pdf->Cell(5, 5,":", 0,0, 'J');
	$pdf->Cell(85, 5,$oldsubbagian, 0,1, 'J');
	
	$pdf->Ln();	
	
	$pdf->SetX(60);
	$pdf->SetFont('Arial', 'BU', 10);
	$pdf->Cell(20, 5,"Ditetapkan", 0,0, 'J');
	$pdf->SetFont('Arial', '', 10);
	$pdf->Cell(20, 5,"menjadi,", 0,1, 'J');
	
	$pdf->Ln();
	
	$pdf->SetX(60);
	$pdf->Cell(45, 5,"Jabatan baru", 0, 'J');
    $pdf->Cell(5, 5,":", 0,0, 'J');
	$pdf->Cell(85, 5,$newjabatan, 0,1, 'J');
	
	$pdf->SetX(60);
	$pdf->Cell(45, 5,"Pangkat", 0, 'J');
    $pdf->Cell(5, 5,":", 0,0, 'J');
	$pdf->Cell(85, 5,$newkodegolongan, 0,1, 'J');
	
	$pdf->SetX(60);
	$pdf->Cell(45, 5,"Divisi / Departement", 0, 'J');
    $pdf->Cell(5, 5,":", 0,0, 'J');
	$pdf->Cell(85, 5,$newkodedepartmen, 0,1, 'J');
	
	$pdf->SetX(60);
	$pdf->Cell(45, 5,"Lokasi / PT", 0, 'J');
    $pdf->Cell(5, 5,":", 0,0, 'J');
	$pdf->Cell(85, 5,$optOrg[$newlokasitugas], 0,1, 'J');

    $pdf->SetX(60);
	$pdf->Cell(45, 5,"Sub Bagian", 0, 'J');
    $pdf->Cell(5, 5,":", 0,0, 'J');
	$pdf->Cell(85, 5,$newsubbagian, 0,1, 'J');
	
    $pdf->Ln();

    $pdf->SetX(60);
    // $pdf->SetFont('Arial', 'B', 10);
    // $pdf->Cell(30, 5,'Kedua', 0, 'J');
    // $pdf->SetFont('Arial', '', 10);
    // $pdf->Cell(5, 5,":", 0, 'J');
    $exptgl = explode('-',$mulaiberlaku);
    $pdf->MultiCell(135, 5, $pg2, 0, 'J');
    
    /*$pdf->Ln();

    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(170, 5, $pg3, 0, 'J');*/
    
    $pdf->Ln();

    // $pdf->SetX(20);
    // $pdf->SetFont('Arial', '', 10);
    // $pdf->MultiCell(170, 5, $pg4, 0, 'J');
    
    $pdf->SetX(100);
    $pdf->Cell(175, 5,"Ditetapkan di ", 0,0, 'L');
    $pdf->SetX(135);
    $pdf->Cell(80, 5," : ".$namakota[$newlokasitugas], 0,1, 'L');
    $pdf->SetX(100);
    
    $exptgl = explode('-',$tanggal);
    $pdf->Cell(175, 5,"Pada tanggal ", 0,0, 'L');
    $pdf->SetX(135);
    $pdf->Cell(80, 5," : ".$exptgl[0]." ".numToMonth($exptgl[1],'I','long')." ".$exptgl[2], 0,1, 'L');
    $pdf->SetX(100);
    $pdf->Ln();
    $pdf->SetX(100);
    $pdf->Cell(175, 5,$namaptx, 0,0, 'L');


	// $pdf->Ln();
 //    $pdf->Ln();
	
	// $arrpg = array('2'=>$pg2,'3'=>$pg3,'4'=>$pg4,'5'=>$pg5,'6'=>$pg6);
	
	// $no=0;
	// foreach($arrpg as $key=>$val)
	// {
	// 	if($val!='')
	// 	{
	// 		$no++;
	// 		$keturut = ($no==1 ? "Kedua" : ($no==2 ? "Ketiga" : ($no==3 ? "Keempat" : ($no==4 ? "Kelima" : "Keenam"))));
			
	// 		$pdf->SetX(20);
	// 		$pdf->SetFont('Arial', 'B', 10);
	// 		$pdf->Cell(30, 5,$keturut, 0, 'J');
	// 		$pdf->SetFont('Arial', '', 10);
	// 		$pdf->Cell(5, 5,":", 0, 'J');
	// 		$pdf->MultiCell(135, 5, $val, 0, 'J');
	// 	}
	// }
	
	
	// $pdf->Ln();
	
	// $pdf->SetX(20);
 //    $pdf->Cell(30, 5,"Ditetapkan di", 0, 'J');
 //    $pdf->Cell(5, 5,":", 0, 'J');
	// $pdf->Cell(5, 5,"Jakarta", 0,1, 'J');
	
	// $pdf->SetX(20);
 //    $pdf->Cell(30, 5,"Pada tanggal", 0, 'J');
 //    $pdf->Cell(5, 5,":", 0, 'J');
	
	// $exptgl = explode('-',$tanggal);
	// $pdf->Cell(5, 5,$exptgl[0]." ".numToMonth($exptgl[1],'I','long')." ".$exptgl[2], 0,1, 'J');
	
	
	
	
	
    // $pdf->SetX(20);
    // $pdf->Cell(30, 5, $_SESSION['lang']['nama'], 0, 0, 'L');
    // $pdf->Cell(40, 5, " : " . $namakaryawan, 0, 1, 'L');
    // $pdf->SetX(20);
    // $pdf->Cell(30, 5, $_SESSION['lang']['nokaryawan'], 0, 0, 'L');
    // $pdf->Cell(40, 5, " : " . $nomorinduk, 0, 1, 'L');

    // $pdf->Ln();
    // $pdf->SetX(20);
    // $pdf->Cell(40, 5, $_SESSION['lang']['dari'] . " : ", 0, 1, 'L');
    // $pdf->SetX(20);
    // $pdf->Cell(30, 5, $_SESSION['lang']['lokasitugas'], 0, 0, 'L');
    // $pdf->Cell(40, 5, " : " . $oldlokasitugas, 0, 1, 'L');

    // $pdf->SetX(20);
    // $pdf->Cell(30, 5, $_SESSION['lang']['functionname'], 0, 0, 'L');
    // $pdf->Cell(40, 5, " : " . $oldjabatan, 0, 1, 'L');

    // $pdf->SetX(20);
    // $pdf->Cell(30, 5, $_SESSION['lang']['tipekaryawan'], 0, 0, 'L');
    // $pdf->Cell(40, 5, " : " . $oldtipe, 0, 1, 'L');

    // $pdf->SetX(20);
    // $pdf->Cell(30, 5, $_SESSION['lang']['levelname'], 0, 0, 'L');
    // $pdf->Cell(40, 5, " : " . $oldkodegolongan, 0, 1, 'L');


    // //===============ke
    // $pdf->Ln();
    // $pdf->SetX(20);
    // $pdf->Cell(40, 5, $_SESSION['lang']['ke'] . " : ", 0, 1, 'L');
    // $pdf->SetX(20);
    // $pdf->Cell(30, 5, $_SESSION['lang']['lokasitugas'], 0, 0, 'L');
    // $pdf->Cell(40, 5, " : " . $newlokasitugas, 0, 1, 'L');

    // $pdf->SetX(20);
    // $pdf->Cell(30, 5, $_SESSION['lang']['functionname'], 0, 0, 'L');
    // $pdf->Cell(40, 5, " : " . $newjabatan, 0, 1, 'L');

    // $pdf->SetX(20);
    // $pdf->Cell(30, 5, $_SESSION['lang']['tipekaryawan'], 0, 0, 'L');
    // $pdf->Cell(40, 5, " : " . $newtipe, 0, 1, 'L');

    // $pdf->SetX(20);
    // $pdf->Cell(30, 5, $_SESSION['lang']['levelname'], 0, 0, 'L');
    // $pdf->Cell(40, 5, " : " . $newkodegolongan, 0, 1, 'L');

    // if ($tipesk == 'Mutasi') {
        // $pdf->Ln();
        // $pdf->SetX(20);
        // $pdf->MultiCell(170, 5, $paragraf25, 0, 'J');

        // $pdf->Ln();
        // $pdf->SetX(20);
        // $pdf->MultiCell(170, 5, '', 0, 'J');
        // // $pdf->MultiCell(170, 5, $paragraf3, 0, 'J');
    // } else {
        // $pdf->Ln();
        // $pdf->SetX(20);
        // $pdf->MultiCell(170, 5, $paragrafadd . " " . $paragraf25 . " " . $paragraffadd2, 0, 'J');
    // }
// }
//=========penandatangan
// $pdf->Ln();
// $pdf->Ln();
// $pdf->Ln();
// $pdf->Cell(40, 5, "Jakarta, " . $tanggal, '0', 1, 'L');
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->SetFont('Arial', 'BU', 10);
$pdf->SetX(100);
$pdf->Cell(175, 5, "" . $direksi . " ", 'U', 1, 'L');

$pdf->SetFont('Arial', 'I', 10);
$pdf->SetX(100);
$pdf->Cell(175, 5, ($namajabatan == '' ? $_SESSION['lang']['direksi'] : $namajabatan), 0, 1, 'L');
//=====================tembusan	
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();

$pdf->SetFont('Arial', '', 10);
$pdf->SetX(20);
$pdf->Cell(25, 5, "Disampaikan kepada : ", 0, 1, 'J');

$pdf->SetFont('Arial', '', 10);
$arrTembusan = array($tembusan1,$tembusan2,$tembusan3,$tembusan4,$tembusan5);
$no = 1;
$pdf->SetX(20);
$pdf->Cell(50, 5,$no.". ".$namakaryawan, 0, 1, 'L');
foreach ($arrTembusan as $val)
{
	if($val!="")
	{
		$no++;
		$pdf->SetX(20);
		$pdf->Cell(50, 5,$no.". ".$val, 0, 1, 'L');
	}
}
// $pdf->Cell(50, 5, $tembusan1, 0, 1, 'L');
// $pdf->Cell(25, 5, "(ii) : ", 0, 0, 'R');
// $pdf->Cell(50, 5, $tembusan2, 0, 1, 'L');
// $pdf->Cell(25, 5, "(iii) : ", 0, 0, 'R');
// $pdf->Cell(50, 5, $tembusan3, 0, 1, 'l');
// $pdf->Cell(25, 5, "(iv) : ", 0, 0, 'R');
// $pdf->Cell(50, 5, $tembusan4, 0, 1, 'L');
// $pdf->Cell(25, 5, "(v) : ", 0, 0, 'R');
// $pdf->Cell(50, 5, $tembusan5, 0, 1, 'L');

//footer================================


// if (($tipe != 'PE')&&($tipe != 'PG')) {
//     $pdf->AddPage();
//     //========halaman baru

// 	// $pdf->Ln();
// 	// $pdf->Ln();
// 	$pdf->Ln();

// 	$pdf->SetFont('Arial', '', 10);
//     $pdf->SetX(5);
//     $pdf->Cell(40, 5, "Lampiran Surat Keputusan Direksi:", 0, 1, 'L');
//     $pdf->Ln();
//     $pdf->SetX(20);
//     $pdf->Cell(20, 5, "No", 0, 0, 'L');
//     $pdf->Cell(5, 5, ":", 0, 0, 'L');
//     $pdf->Cell(40, 5, $nosk, 0, 1, 'L');

//     $pdf->SetX(20);
//     $pdf->Cell(20, 5, $_SESSION['lang']['tanggal'], 0, 0, 'L');
//     $pdf->Cell(5, 5, ":", 0, 0, 'L');
//     $pdf->Cell(40, 5, $tanggal, 0, 1, 'L');

//     $pdf->SetFont('Arial', '', 10);
//     $pdf->Ln();
//     $pdf->SetX(20);
//     $pdf->Cell(40, 5, $_SESSION['lang']['dari'] . " : ", 0, 1, 'L');

//     $totaldari = 0;
//     $strx = "select * from " . $dbname . ".sdm_riwayatjabatan_gaji where nomorsk='" . $nosk . "' and status='O'";
//     $resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
//     $resx->setFetchMode(PDO::FETCH_OBJ);
//     while ($barx = $resx->fetch()) {
//         $arrdata=explode('###', ambilkomponengajix($barx->idkomponen, $owlPDO, $dbname));
//         $pdf->SetX(20);
//         $pdf->Cell(40, 5, $arrdata[0], 0, 0, 'L');
//         $pdf->Cell(5, 5, " :Rp.", 0, 0, 'L');
//         $pdf->Cell(40, 5, number_format($barx->rupiah, 2, ',', ','), 0, 1, 'R');
//         if($arrdata[1]=='1')
//         $totaldari += $barx->rupiah;
//         else
//         $totaldari -= $barx->rupiah;
//     }
    
//     //$pdf->SetX(20);	
//     //$pdf->Cell(40,5,$_SESSION['lang']['tjkebun'],0,0,'L');	
//     //$pdf->Cell(5,5," :Rp.",0,0,'L');
//     //$pdf->Cell(40,5,number_format($tjkebun,2,',',','),0,1,'R');	
//     //$pdf->SetX(20);	
//     //$pdf->Cell(40,5,$_SESSION['lang']['tjlokasi'],0,0,'L');	
//     //$pdf->Cell(5,5," :Rp.",0,0,'L');
//     //$pdf->Cell(40,5,number_format($tjlokasi,2,',',','),0,1,'R');				  	  	  

//     $pdf->SetX(20);
//     //$totaldari=$darigaji+$tjjabatan+$tjkebun+$tjlokasi;
    
//     // $pdf->Cell(40, 5, $_SESSION['lang']['total'], 0, 0, 'L');
//     // $pdf->Cell(5, 5, " :Rp.", 0, 0, 'L');
//     // $pdf->Cell(40, 5, number_format($totaldari, 2, ',', ','), 'T', 1, 'R');
// //================


//     $pdf->Ln();
//     $pdf->SetX(20);
//     $pdf->Cell(40, 5, $_SESSION['lang']['ke'] . " : ", 0, 1, 'L');

//     $totalke = 0;
//     $strx = "select * from " . $dbname . ".sdm_riwayatjabatan_gaji where nomorsk='" . $nosk . "' and status='N'";
//     $resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
//     $resx->setFetchMode(PDO::FETCH_OBJ);
//     while ($barx = $resx->fetch()) {
//         $arrdata=explode('###', ambilkomponengajix($barx->idkomponen, $owlPDO, $dbname));
//         $pdf->SetX(20);
//         $pdf->Cell(40, 5, $arrdata[0], 0, 0, 'L');
//         $pdf->Cell(5, 5, " :Rp.", 0, 0, 'L');
// 		if($barx->idkomponen=='69' || $barx->idkomponen=='45'){
// 			$pdf->Cell(40, 5, number_format($barx->rupiah, 2, ',', ','), 0, 0, 'R');
// 			$pdf->Cell(40, 5, "/Hari Kerja", 0, 1, 'L');
// 		}else{
// 			$pdf->Cell(40, 5, number_format($barx->rupiah, 2, ',', ','), 0, 1, 'R');
// 		}
//         if($arrdata[1]=='1')
//         $totalke += $barx->rupiah;
//         else
//         $totalke -= $barx->rupiah;
//     }

//     //$pdf->SetX(20);	
//     //$pdf->Cell(40,5,$_SESSION['lang']['tjkebun'],0,0,'L');	
//     //$pdf->Cell(5,5," :Rp.",0,0,'L');
//     //$pdf->Cell(40,5,number_format($ketjkebun,2,',',','),0,1,'R');	
//     //    $pdf->SetX(20);	
//     //    $pdf->Cell(40,5,$_SESSION['lang']['tjlokasi'],0,0,'L');
//     //$pdf->Cell(5,5," :Rp.",0,0,'L');	
//     //    $pdf->Cell(40,5,number_format($ketjlokasi,2,',',','),0,1,'R');		

//     $pdf->SetX(20);
//     //$totalke=$kegaji+$ketjjabatan+$ketjkebun+$ketjlokasi;
//     //$totalke = $kegaji + $ketjjabatan + $ketjmahal + $ketjsdaerah + $ketjkota + $ketjpembantu + $ketjtransport+$ketjtelekomunikasi+$ketjcop+$ketjmop+$ketjasuransi;
//     // $pdf->Cell(40, 5, $_SESSION['lang']['total'], 0, 0, 'L');
//     // $pdf->Cell(5, 5, " :Rp.", 0, 0, 'L');
//     // $pdf->Cell(40, 5, number_format($totalke, 2, ',', ','), 'T', 1, 'R');


// //=========penandatangan
//     $pdf->Ln();
//     $pdf->Ln();
//     $pdf->Ln();
//     $pdf->Ln();
//     $pdf->SetX(20);
//     $pdf->Cell(175, 5, (strtoupper($namajabatan) == '' ? $_SESSION['lang']['direksi'] : strtoupper($namajabatan)) . ",", 0, 1, 'C');
//     $pdf->Ln();
//     $pdf->Ln();
//     $pdf->Ln();
//     $pdf->SetX(20);
//     $pdf->Cell(175, 5, " " . strtoupper($direksi) . " ", 0, 1, 'C');
// }
$pdf->Ln();
ob_clean();
$pdf->Output($tipesk."_".$namakaryawan."_".$nosk,'I');
?>
