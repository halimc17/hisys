<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
$nopengajuan = checkPostGet('nopengajuan', '');

//=============
//create Header
class PDF extends FPDF {

    function Header() {
        global $conn;
        global $dbname;
        global $tipe;
        global $owlPDO;
        global $yatas;
        global $nopengajuan;
        global $PT;


        $this->SetFillColor(255, 255, 255);

        $this->SetMargins(15, 5, 0);

        $kodeo=explode('/', $nopengajuan);
        $PT=$kodeo[2];		
		$arrHead = setheadreport('',$PT);
		$path = $arrHead['logo'];
        $this->Image($path, 60, 5, 0, 20);
        $this->SetFont('Arial', 'B', 18);
        $this->SetFillColor(255, 255, 255);
        $this->SetX(80);
        $this->SetTextColor(0, 150, 0);
        $this->Cell(60, 15, $arrHead['nama'], 0, 1, 'L');
        $this->SetTextColor(0, 0, 0);
        $yatas=$this->GetY();
        // $this->Line(15, 35, 205, 35);
	  
	  
        
			$height = 5;
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
        global $conn;
        global $dbname;
        global $owlPDO;
        $str1 = "select namaorganisasi,alamat,wilayahkota,telepon from " . $dbname . ".organisasi where kodeorganisasi='PMO'";
        $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_OBJ);
		while ($bar1 = $res1->fetch()) {
            $namapt = $bar1->namaorganisasi;
            $alamatpt = $bar1->alamat . ", " . $bar1->wilayahkota;
            $telp = $bar1->telepon;
        }
        // $this->SetY(-15);
        // $this->Line(15, 275, 205, 275);
        // $this->SetFont('Arial', 'I', 8);
        // $this->Cell(160, 5, (isset($alamatpt) ? $alamatpt : "") . ", Tel:" . (isset($telp) ? $telp : ""), 0, 1, 'L');
        // $this->Cell(10,5,'Page '.$this->PageNo(),0,0,'C');
    }

}




$optPt = makeOption($dbname,'organisasi','kodeorganisasi,induk');
$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$optkaryawan = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');

//get data spht dan spdt
$ssp = "select a.*, b.* from ".$dbname.".sdm_pengajuanspht a left join ".$dbname.". sdm_pengajuanspdt b on a.nopengajuan=b.nopengajuan where a.nopengajuan='".$nopengajuan."'";
$qsp = $owlPDO->query($ssp) or die (print "Gagal : ".PDOException::getMessage());
$qsp->setFetchMode(PDO::FETCH_OBJ);
$rsp=$qsp->fetch();
$kodesp=substr($rsp->idjenispelanggaran,0,3);
$jenissurat=$rsp->jenissurat;
$kodeorg=substr($rsp->kodeorg,0,2);
$tanggal=$rsp->tanggaldisetujui2;
$tanggaldari=$rsp->tanggaldari;
$tanggalsampai=$rsp->tanggalsampai;
$melihat=$rsp->mendengar;
$pembuat=$rsp->pembuat;
if ($rsp->sifatpelanggaran=='Minor') {
    $minor="V";
}else if ($rsp->sifatpelanggaran=='Moderat') {
    $moderat="V";
}else if ($rsp->sifatpelanggaran=='Serius') {
    $serius="V";
}
$nourutglobal=substr($rsp->nopengajuan,5,3);
$bulan = substr($tanggal, 5, 2);
$tahun = substr($tanggal, 0, 4);
$arrayRomawi = array("I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII");
$resultRomawi = $arrayRomawi[(int) $bulan - 1];

//get nama  dan kode organisasi
$snamaorg = "select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$kodeorg."%' and tipe='PT'";
$qnamaorg = $owlPDO->query($snamaorg) or die (print "Gagal : ".PDOException::getMessage());
$qnamaorg->setFetchMode(PDO::FETCH_OBJ);
$rnamaorg=$qnamaorg->fetch();
$namaorg=$rnamaorg->namaorganisasi;
$kodeorgsp=$rnamaorg->kodeorganisasi;

//get nosp
$snourutsp = "select distinct(nopengajuan) from ".$dbname.".sdm_pengajuanspdt where left(idjenispelanggaran,3)='".$kodesp."' and right(nopengajuan,4)='".$tahun."' and substr(nopengajuan,6,3) between '001' and '".$nourutglobal."' ";
$qnourutsp = $owlPDO->query($snourutsp) or die (print "Gagal : ".PDOException::getMessage());
$nourutsp = owlBaris($qnourutsp);
$nosp=$kodeorgsp."/".$nourutglobal."/".$kodesp."-".addZero($nourutsp, 3) . "/".$resultRomawi."/".$tahun;
//print_r($nosp);

//get data surat
$sjenissp = "select * from ".$dbname.".sdm_5jenissp where kode='".$kodesp."'";
$qjenissp = $owlPDO->query($sjenissp) or die (print "Gagal : ".PDOException::getMessage());
$qjenissp->setFetchMode(PDO::FETCH_OBJ);
$rjenissp=$qjenissp->fetch();
$jenissp=$rjenissp->keterangan;


//get data karyawan
$skarya = "select a.namakaryawan,a.nik,a.kodegolongan ,b.namajabatan from " . $dbname . ".datakaryawan a left join  " . $dbname . ".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
         where  karyawanid=" . $rsp->karyawanid;
$qkarya=$owlPDO->query($skarya) or die(print " Gagal: ".PDOException::getMessage());
$qkarya->setFetchMode(PDO::FETCH_OBJ);
$rkarya = $qkarya->fetch();
$nik          = $rkarya->nik;
$namakaryawan = $rkarya->namakaryawan;
$jabatan      = $rkarya->namajabatan;
$kodegolongan = $rkarya->kodegolongan;

//get tanggal sampai berlaku
// $tgl=explode('-', $tanggal);
// $tahun=$tgl[0];
// $bulan=$tgl[1];
// $tglex=$tgl[2];

// if(substr($kodesp,0,2)=='SP'){
//     $bulantambah=$bulan+6;
//     if ($bulantambah>12){
//         $bulantambah=$bulantambah-12;
//         $tahun=$tahun+1;
//     }
// }else if ($kodesp=='ST1'){
//     $bulantambah=$bulan+3;
//     if ($bulantambah>12){
//         $bulantambah=$bulantambah-12;
//         $tahun=$tahun+1;
//     }
// }
// }else if($kodesp,0,2=='SKR'){
//     $tglextambah=$tglex+7;
//     if(($bulan=='02') && ($tahun%4=0)){
//         $tglextambah=$tglextambah-29;
//         $bulan=$bulan+1;
//     }else if(($bulan=='02') && ($tahun%4!=0)){
//         $tglextambah=$tglextambah-28;
//         $bulan=$bulan+1;
//     }else{

//     }
// }



switch ($bulantambah) {
    case 1 :$bulantambah='Januari';break;
    case 2 :$bulantambah='Februari';break;
    case 3 :$bulantambah='Maret';break;
    case 4 :$bulantambah='April';break;
    case 5 :$bulantambah='Mei';break;
    case 6 :$bulantambah='Juni';break;
    case 7 :$bulantambah='Juli';break;
    case 8 :$bulantambah='Agustus';break;
    case 9 :$bulantambah='September';break;
    case 10:$bulantambah='Oktober';break;
    case 11:$bulantambah='November';break;
    case 12:$bulantambah='Desember';break;
    default:
break;
}
$tglsampai=$tglex." ".$bulantambah." ".$tahun;


//get grade
$sgrade = "select b.namagolongan from " . $dbname . ".datakaryawan a left join  " . $dbname . ".sdm_5golongan b on a.kodegolongan=b.kodegolongan where b.kodegolongan='".$kodegolongan."'";
$qgrade=$owlPDO->query($sgrade) or die(print " Gagal: ".PDOException::getMessage());
$qgrade->setFetchMode(PDO::FETCH_OBJ);
$rgrade = $qgrade->fetch();
$grade=$rgrade->namagolongan;

//get data pembuat
$spembuat = "select a.namakaryawan from " . $dbname . ".datakaryawan a where  a.karyawanid=" . $pembuat;
$qpembuat=$owlPDO->query($spembuat) or die(print " Gagal: ".PDOException::getMessage());
$qpembuat->setFetchMode(PDO::FETCH_OBJ);
$rpembuat = $qpembuat->fetch();
$pembuat = $rpembuat->namakaryawan;

//get data pejabat1
$spejabat1 = "select a.namakaryawan,b.namajabatan from " . $dbname . ".datakaryawan a left join  " . $dbname . ".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
         where  a.karyawanid=" . $rsp->pejabat1;
$qpejabat1=$owlPDO->query($spejabat1) or die(print " Gagal: ".PDOException::getMessage());
$qpejabat1->setFetchMode(PDO::FETCH_OBJ);
$rpejabat1 = $qpejabat1->fetch();
$pejabat1 = $rpejabat1->namakaryawan;
$jabatan1 = $rpejabat1->namajabatan;

//get tgldisetujui
$sttd1 = "select left(tanggal,10) as tgldisetujui from " . $dbname . ".approval where  notransaksi='".$nopengajuan."'";
$qttd1=$owlPDO->query($sttd1) or die(print " Gagal: ".PDOException::getMessage());
$qttd1->setFetchMode(PDO::FETCH_OBJ);
$rttd1 = $qttd1->fetch();
$tgldisetujui=$rttd1->tgldisetujui;

//get ttd pejabat1
$sttd1 = "select file from " . $dbname . ".setup_ttd where  karyawanid=" . $rsp->pejabat1;
$qttd1=$owlPDO->query($sttd1) or die(print " Gagal: ".PDOException::getMessage());
$qttd1->setFetchMode(PDO::FETCH_OBJ);
$rttd1 = $qttd1->fetch();
$dir1=$rttd1->file;

//get data pejabat2
$spejabat2 = "select a.namakaryawan,b.namajabatan from " . $dbname . ".datakaryawan a left join  " . $dbname . ".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
         where  a.karyawanid=" . $rsp->pejabat2;
$qpejabat2=$owlPDO->query($spejabat2) or die(print " Gagal: ".PDOException::getMessage());
$qpejabat2->setFetchMode(PDO::FETCH_OBJ);
$rpejabat2 = $qpejabat2->fetch();
$pejabat2 = $rpejabat2->namakaryawan;
$jabatan2 = $rpejabat2->namajabatan;

//get ttd pejabat2
$sttd2 = "select file from " . $dbname . ".setup_ttd where  karyawanid=" . $rsp->pejabat2;
$qttd2=$owlPDO->query($sttd2) or die(print " Gagal: ".PDOException::getMessage());
$qttd2->setFetchMode(PDO::FETCH_OBJ);
$rttd2 = $qttd2->fetch();
$dir2=$rttd2->file;

$pdf = new PDF('P', 'mm', 'A4');
$pdf->SetFont('Arial', 'B', 14);
$pdf->AddPage();
$pdf->SetY($yatas+5);
$pdf->SetX(20);
$pdf->SetFillColor(255, 255, 255);

if ($jenissurat=='PHK') {
    
    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);    
    $pdf->Cell(8, 5,"No. ", 0, 'J');
    $pdf->Cell(2, 5,":", 0,0, 'J');
    $pdf->Cell(85, 5,$nopengajuan, 0,1, 'J');

    $pdf->SetX(20);   
    $pdf->Cell(8, 5,"Hal ", 0, 'J');
    $pdf->Cell(2, 5,":", 0,0, 'J');
    $pdf->SetFont('Arial', 'UB', 10); 
    $pdf->Cell(85, 5,'Pemutusan Hubungan Kerja', 0,1, 'J');
    $pdf->SetFont('Arial', '', 10); 
    $pdf->Cell(85, 5,'', 0,1, 'J');

    $pdf->SetX(20);
    $pdf->Cell(50, 5,"Jakarta, ".tanggalbulan($tgldisetujui), 0,1, 'J'); 
    $pdf->Cell(85, 5,'', 0,1, 'J');

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetX(20);  
    $pdf->Cell(85, 5,'Kepada Yth.', 0,1, 'J');
    $pdf->SetX(20);
    $pdf->Cell(85, 5,'Sdra. '.$namakaryawan, 0,1, 'J');
    $pdf->SetX(20);
    $pdf->Cell(85, 5,'di', 0,1, 'J');
    $pdf->SetFont('Arial', 'UB', 10);
    $pdf->SetX(20);
    $pdf->Cell(85, 5,'Tempat', 0,1, 'J');

    $pdf->Cell(85, 5,'', 0,1, 'J');
    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(85, 5,'Dengan Hormat,', 0,1, 'J');
    $pdf->Cell(85, 5,'', 0,1, 'J');

    $jenispel="";
    $smelihat = "select idjenispelanggaran from ".$dbname.".sdm_pengajuanspdt where nopengajuan='".$nopengajuan."'";
    $qmelihat = $owlPDO->query($smelihat) or die (print "Gagal : ".PDOException::getMessage());
    $qmelihat->setFetchMode(PDO::FETCH_OBJ);
    while ($rmelihat=$qmelihat->fetch()) {
        $whrmelihat="idjenispelanggaran='".$rmelihat->idjenispelanggaran."'";
        $optmelihat=makeOption($dbname,'sdm_5jenispelanggaran','idjenispelanggaran,pelanggaran',$whrmelihat);
        $jenispel.=$optmelihat[$rmelihat->idjenispelanggaran].", ";
    }

    $isi="Sehubungan ".$jenispel." maka dengan ini Perusahaan memutuskan hubungan kerja terhadap Saudara ".$namakaryawan." terhitung tanggal ".tanggalbulan($tanggaldari).". Untuk itu seluruh inventaris milik Perusahaan yang berada dalam penguasaan Saudara agar dapat segera diserahterimakan kembali kepada Perusahaan dan kepada Saudara dilarang memasuki wilayah kerja Perusahaan tanpa memperoleh izin Pimpinan Perusahaan terlebih dahulu.";
    
    $pdf->SetX(20);
    $pdf->MultiCell(175, 5,$isi,0, 'J');
    $pdf->Cell(85, 5,'', 0,1, 'J');
    $pdf->SetX(20);
    $pdf->Cell(85, 5,'Demikian untuk diketahui', 0,1, 'J');
    $pdf->Cell(85, 5,'', 0,1, 'J');
    $pdf->Cell(85, 5,'', 0,1, 'J');
    $pdf->SetX(20);
    $pdf->Cell(85, 5,'Hormat kami,', 0,1, 'J');
    $pdf->Cell(85, 20,'', 0,1, 'J');
    $pdf->SetX(20);
    $ysp=$pdf->GetY();
    $pdf->Line(21, $ysp, 60, $ysp);
    $pdf->Cell(50, 5,$pembuat, 0,1, 'J');

}else{

    if($kodesp=='SP1'){
        $pdf->Cell(175, 5, 'SURAT PERINGATAN I', 0, 1, 'C');
    }else if ($kodesp=='SP2'){
        $pdf->Cell(175, 5, 'SURAT PERINGATAN II', 0, 1, 'C');
    }else if ($kodesp=='SP3'){
        $pdf->Cell(175, 5, 'SURAT PERINGATAN III', 0, 1, 'C');
    }else if ($kodesp=='SKR'){
        $pdf->Cell(175, 5, 'SKORSING', 0, 1, 'C');
    }else if ($kodesp=='PHK'){
        $pdf->Cell(175, 5, 'SURAT KEPUTUSAN', 0, 1, 'C');
    }else if ($kodesp=='ST1'){
        $pdf->Cell(175, 5, 'SURAT TEGURAN TERTULIS', 0, 1, 'C');
    }

    $ysp=$pdf->GetY();
    $pdf->Line(15, $ysp, 205, $ysp);
    $pdf->SetX(20);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(175, 5, 'NO : ' . $nopengajuan, 0, 1, 'C');
    $pdf->Ln(10);
    
    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(25, 5,"Diberikan Kepada :", 0, 1, 'J');
    $pdf->Ln();
    $pdf->SetX(20);
    $pdf->SetFont('Arial', '', 10);    
    $pdf->Cell(50, 5,"Nama Karyawan", 0, 'J');
    $pdf->Cell(5, 5,":", 0,0, 'J');
    $pdf->Cell(85, 5,$namakaryawan, 0,1, 'J');
    
    $pdf->SetX(20);
    $pdf->Cell(50, 5,"NIK", 0, 'J');
    $pdf->Cell(5, 5,":", 0,0, 'J');
    $pdf->Cell(85, 5,$nik, 0,1, 'J');
    
    $pdf->SetX(20);
    $pdf->Cell(50, 5,"Jabatan", 0, 'J');
    $pdf->Cell(5, 5,":", 0,0, 'J');
    $pdf->Cell(85, 5,$jabatan, 0,1, 'J');
    
    $pdf->SetX(20);
    $pdf->Cell(50, 5,"Hari dan tanggal diberikan SP", 0, 'J');
    $pdf->Cell(5, 5,":", 0,0, 'J');
    $pdf->Cell(85, 5,hari($tanggal,'ID').", ".tanggalbulan($tgldisetujui), 0,1, 'J');

    $pdf->Ln(); 
    $pdf->SetX(20);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(5, 5,"1. ", 0, 0, 'J');
    $pdf->SetFont('Arial', 'BU', 10);
    $pdf->Cell(32, 5,"Dasar Pelanggaran", 0, 0, 'J');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(5, 5," : ", 0, 1, 'J');
    $pdf->Ln();

    //get melihat
    $smelihat = "select idjenispelanggaran from ".$dbname.".sdm_pengajuanspdt where nopengajuan='".$nopengajuan."'";
    $qmelihat = $owlPDO->query($smelihat) or die (print "Gagal : ".PDOException::getMessage());
    $qmelihat->setFetchMode(PDO::FETCH_OBJ);
    while ($rmelihat=$qmelihat->fetch()) {
        $no+=1;
        #namakaryawan
        $whrmelihat="idjenispelanggaran='".$rmelihat->idjenispelanggaran."'";
        $optmelihat=makeOption($dbname,'sdm_5jenispelanggaran','idjenispelanggaran,pelanggaran',$whrmelihat);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetX(25);
        $pdf->Cell(5, 5,'- ', 0, 0, 'J');
        $pdf->MultiCell(175, 5,$optmelihat[$rmelihat->idjenispelanggaran], 0, 1, 'J');
    }
    
    $pdf->Ln();
    $pdf->SetX(20);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(5, 5,"2. ", 0, 0, 'J');
    $pdf->SetFont('Arial', 'BU', 10);
    $pdf->Cell(32, 5,"Tindakan Kedisiplinan", 0, 1, 'J');
    $pdf->Ln();
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetX(25);
    $pdf->MultiCell(225, 5,'Berdasarkan hal tersebut pada tanggal '.$tgldisetujui.', maka diberikan tindakan sebagai berikut :', 0, 1, 'J');
    $pdf->SetX(25);
    $pdf->MultiCell(225, 5,'Sifat pelanggaran tersebut adalah : ', 0, 1, 'J');
    $pdf->SetX(25);
    $pdf->Cell(50, 5,"( ".$minor." ) Minor" , 0,0, 'C');
    $pdf->Cell(50, 5,"( ".$moderat." ) Moderat", 0,0, 'C');
    $pdf->Cell(50, 5,"( ".$serius." ) Serius", 0,1, 'C');
    $pdf->SetX(25);
    $pdf->MultiCell(225, 5,'Sesuai dengan peraturan disiplin yang berlaku dalam Peraturan Perusahaan, maka Saudara diberikan : ', 0, 1, 'J');
    $pdf->SetX(30);
    $pdf->SetFont('Arial', 'B', 10);

    if($kodesp=='SP1'){
        $sp1='V';
    }else if($kodesp=='SP2'){
        $sp2='V';
    }else if($kodesp=='SP3'){
        $sp3='V';
    }

    if(substr($kodesp,0,2)=='SP'){
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(50, 5,"( ".$sp1." ) Peringatan I" , 0,0, 'C');
        $pdf->Cell(50, 5,"( ".$sp2." ) Peringatan II", 0,0, 'C');
        $pdf->Cell(50, 5,"( ".$sp3." ) Peringatan III", 0,1, 'C');
    }else if ($kodesp=='SKR'){
        $pdf->MultiCell(225, 5,'Skorsing ( dari tanggal '.tanggalbulan($tanggaldari).' sampai dengan tanggal '.tanggalbulan($tanggalsampai).')', 0, 1, 'J');
    }else if ($kodesp=='PHK'){
        $pdf->Cell(175, 5, 'Surat Keputusan', 0, 1, 'L');
    }else if ($kodesp=='ST1'){
        $pdf->Cell(175, 5, 'Teguran Tertulis', 0, 1, 'L');
    }


    $pdf->SetFont('Arial', '', 10);
    $pdf->Ln();
    $pdf->SetX(25);
    $pdf->MultiCell(225, 5,'Jenis sanksi pelanggaran disiplin diberikan : ', 0, 1, 'J');
    
    //get sanksi pelanggaran
    $smelihat = "select sanksipelanggaran from ".$dbname.".sdm_sanksipelanggaransp where nopengajuan='".$nopengajuan."'";
    $qmelihat = $owlPDO->query($smelihat) or die (print "Gagal : ".PDOException::getMessage());
    $qmelihat->setFetchMode(PDO::FETCH_OBJ);
    while ($rmelihat=$qmelihat->fetch()) {
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetX(30);
        $pdf->Cell(5, 5,'- ', 0, 0, 'J');
        $pdf->MultiCell(175, 5,$rmelihat->sanksipelanggaran, 0, 1, 'J');
    }

    $pdf->Ln(10);
    $pdf->SetX(20);
    $pdf->Cell(100, 5,'Surat Peringatan ini dikeluarkan oleh : ', 0, 1, 'J');
    $pdf->Cell(50, 20,'', 0, 1, 'J');
    $pdf->SetX(20);
    $pdf->Cell(60, 5,$pembuat, 'T', 1, 'C');
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Ln(30);
    $pdf->SetX(20);
    $pdf->Cell(50, 5,"Tembusan :" , 0,1, 'L');

    //get tembusan
    $no=0;
    $smelihat = "select * from ".$dbname.".sdm_tembusansp where nopengajuan='".$nopengajuan."'";
    $qmelihat = $owlPDO->query($smelihat) or die (print "Gagal : ".PDOException::getMessage());
    $qmelihat->setFetchMode(PDO::FETCH_OBJ);
    while ($rmelihat=$qmelihat->fetch()) {
        $no+=1;
        $pdf->SetX(25);
        $pdf->Cell(5, 5,$no.'. ', 0, 0, 'J');
        $pdf->Cell(5, 5,$rmelihat->tembusan, 0, 1, 'J');
    }















}
    
    
 //    $pdf->AddPage();
	// $pdf->SetX(20);
 //    $pdf->SetFont('Arial', 'B', 10);
 //    $pdf->Cell(25, 5,"MENUNJUK", 0, 0, 'J');
	// $pdf->Cell(30, 5," : ", 0, 1, 'J');
 //    $pdf->SetFont('Arial', '', 10);
 //    $pdf->SetX(25);
 //    $pdf->Cell(5, 5,"1. ", 0, 0, 'J');
	// $pdf->Cell(5, 5,"Keputusan Manajemen", 0, 1, 'J');
 //    $pdf->SetX(25);
 //    $pdf->Cell(5, 5,"2. ", 0, 0, 'J');
 //    $pdf->Cell(5, 5,"Peraturan Perusahaan", 0, 1, 'J');
 //    $pdf->SetX(25);
 //    $pdf->Cell(5, 5,"3. ", 0, 0, 'J');
 //    $pdf->Cell(5, 5,"Kebijakan Pimpinan tentang ".$jenissp, 0, 1, 'J');
 //    $pdf->Ln();
	
 //    $pdf->SetX(20);
	// $pdf->SetFont('Arial', 'B', 10);
 //    $pdf->Cell(25, 5,"MENIMBANG", 0, 0, 'J');
 //    $pdf->Cell(30, 5," : ", 0, 1, 'J');
	// $pdf->SetFont('Arial', '', 10);
 //    $pdf->SetX(20);
 //    $pdf->Cell(5, 5,"Bahwa perlu dikeluarkan Sanksi berupa ".$jenissp, 0, 1, 'J');
 //    $pdf->Ln();

 //    $pdf->SetX(20);
 //    $pdf->SetFont('Arial', 'B', 10);
 //    $pdf->Cell(25, 5,"MELIHAT", 0, 0, 'J');
 //    $pdf->Cell(30, 5," : ", 0, 1, 'J');
    

 //    //get melihat
 //    $smelihat = "select idjenispelanggaran from ".$dbname.".sdm_pengajuanspdt where nopengajuan='".$nopengajuan."'";
 //    $qmelihat = $owlPDO->query($smelihat) or die (print "Gagal : ".PDOException::getMessage());
 //    $qmelihat->setFetchMode(PDO::FETCH_OBJ);
 //    while ($rmelihat=$qmelihat->fetch()) {
 //        $no+=1;
 //        #namakaryawan
 //        $whrmelihat="idjenispelanggaran='".$rmelihat->idjenispelanggaran."'";
 //        $optmelihat=makeOption($dbname,'sdm_5jenispelanggaran','idjenispelanggaran,pelanggaran',$whrmelihat);
 //        $pdf->SetFont('Arial', '', 10);
 //        $pdf->SetX(20);
 //        $pdf->Cell(5, 5,$no.'.', 0, 0, 'J');
 //        $pdf->MultiCell(175, 5,$optmelihat[$rmelihat->idjenispelanggaran], 0, 1, 'J');
 //    }
    
 //    $pdf->Ln();
	
	// $pdf->SetFont('Arial', 'B', 10);
	// $pdf->SetX(20);
 //    $pdf->Cell(30, 5,"MEMUTUSKAN", 0, 1, 'J');
	// $pdf->SetFont('Arial', '', 10);
 //    $pdf->SetX(20);
 //    if(substr($kodesp,0,2)=='SP'){
 //        $pdf->MultiCell(175, 5,"Memberikan ".$jenissp." yang berlaku selama 6 (enam) bulan yaitu sejak tanggal penetapan SK ini sampai dengan tanggal ".$tglsampai." kepada :", 0, 1, 'J');
 //    }else if ($kodesp=='SKR'){
 //        $pdf->MultiCell(175, 5,"Memberikan ".$jenissp." yang berlaku sejak tanggal penetapan SK ini kepada :", 0, 1, 'J');
 //    }else if ($kodesp=='PHK'){
 //        $pdf->MultiCell(175, 5,"Memberikan Surat ".$jenissp." yang berlaku sejak tanggal penetapan SK ini kepada :", 0, 1, 'J');
 //    }else if ($kodesp=='ST1'){
 //        $pdf->MultiCell(175, 5,"Memberikan ".$jenissp." yang berlaku selama 3 (tiga) bulan yaitu sejak tanggal penetapan SK ini sampai dengan tanggal ".$tglsampai." kepada :", 0, 1, 'J');
 //    }
 //        $pdf->Ln();

	// $pdf->SetX(25);
	// $pdf->SetFont('Arial', '', 10);    
	// $pdf->Cell(30, 5,"Nama", 0, 'J');
 //    $pdf->Cell(5, 5,":", 0,0, 'J');
 //    $pdf->Cell(85, 5,$namakaryawan, 0,1, 'J');
    
	// $pdf->SetX(25);
	// $pdf->Cell(30, 5,"NIK", 0, 'J');
 //    $pdf->Cell(5, 5,":", 0,0, 'J');
	// $pdf->Cell(85, 5,$nik, 0,1, 'J');
	
	// $pdf->SetX(25);
	// $pdf->Cell(30, 5,"Jabatan", 0, 'J');
 //    $pdf->Cell(5, 5,":", 0,0, 'J');
	// $pdf->Cell(85, 5,$jabatan, 0,1, 'J');
	
	// $pdf->SetX(25);
	// $pdf->Cell(30, 5,"Grade", 0, 'J');
 //    $pdf->Cell(5, 5,":", 0,0, 'J');
	// $pdf->Cell(85, 5,$grade, 0,1, 'J');
 //    $pdf->Ln();
    
 //    if ($kodesp=='PHK'){
 //    $pdf->SetFont('Arial', '', 10);
 //    $pdf->SetX(20);
 //    $pdf->MultiCell(175, 5,"Sebelum meninggalkan Perusahaan saudara dipersilahkan untuk melaksanakan serah terima pekerjaan dan jabatan.", 0, 1, 'J');
 //    $pdf->Ln();

 //    $pdf->SetFont('Arial', '', 10);
 //    $pdf->SetX(20);
 //    $pdf->MultiCell(175, 5,"Demikian Surat Keputusan ini dibuat dan diberikan kepada yang bersangkutan untuk diketahui.", 0, 1, 'J');
 //    $pdf->Ln(10);
 //    }else{
 //    $pdf->SetFont('Arial', '', 10);
 //    $pdf->SetX(20);
 //    $pdf->MultiCell(175, 5,"Dengan dikeluarkannya ".$jenissp." ini, diharapkan adanya perubahan sikap dan disiplin kerja serta tanggung jawab saudara yang lebih baik di kemudian hari.", 0, 1, 'J');
 //    $pdf->Ln();

 //    $pdf->SetFont('Arial', '', 10);
 //    $pdf->SetX(20);
 //    $pdf->MultiCell(175, 5,"Apabila saudara mengulangi pelanggaran ini ataupun pelanggaran lainnya, maka kami akan memberikan sanksi yang lebih tegas kepada saudara sesuai dengan peraturan perusahaan.", 0, 1, 'J');
 //    $pdf->Ln(10);
 //    }

 //    $pdf->SetX(20);
 //    $pdf->SetFont('Arial', '', 10);
 //    $pdf->Cell(25, 5,"Jakarta, ".$tgldisetujui, 0, 1, 'J');

 //    $pdf->SetX(20);
 //    $pdf->SetFont('Arial', '', 10);
 //    $pdf->Cell(25, 5,"Hormat Kami,", 0, 1, 'J');
 //    $y=$pdf->GetY();
 //    $pdf->Ln(20);
 //    if ($dir1!=''){
 //        $pdf->Image($dir1, 20, $y, 0, 20);
 //    }
 //    $pdf->SetX(20);
 //    $pdf->Cell(100, 5,$pejabat1, 0, 0, 'J');
 //    $pdf->Cell(25, 5,$pejabat2, 0, 1, 'J');
 //    $pdf->SetX(20);
 //    if ($dir2!=''){
 //        $pdf->Image($dir2, 120, $y, 0, 20);
 //    }
 //    $pdf->Cell(100, 5,$jabatan1, 0, 0, 'J');
 //    $pdf->Cell(25, 5,$jabatan2, 0, 1, 'J');

$pdf->Ln();
$pdf->Output();
?>
