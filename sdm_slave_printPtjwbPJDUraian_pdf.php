<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
require_once('lib/terbilang.php');
$notransaksi = $_GET['notransaksi'];
function getUnique($array,$filt){
	$data = array();
	for($i=0; $i<count($array); $i++){
		$data[] = $array[$i][$filt];	
	}
	$result = array_unique($data);
	return $result;
}

function getFiltering($array,$name,$filt){
	$dataterpilih = array();
	if(count($array) > 0 ){
		for($i=0; $i<count($array); $i++){
			if($array[$i][$name] == $filt){
				$dataterpilih[] = $array[$i];
			}
		}
	}
	$result = $dataterpilih;
	return $result;
}

function isset_num($data,$name){
	$d = $data;
	$num = "false";
	if(isset($d[0][$name])){
		$num = $d[0][$name];
	}
	$result = $num;
	return $result;
}
function if_zero($num){
	$format = "";
	if($num <> "false" and $num <> 0){
		$format = number_format($num);
	}
	$result = $format;
	return $result;
}
function must_zero($num){
	$format = 0;
	if($num <> "false"){
		$format = $num;
	}
	$result = $format;
	return $result;
}
function getFirstdata($data,$name){
	$result = "";
	if(isset($data)){
		if(count($data) > 0){
			$result = $data[0][$name];
		}
	}
	return $result;
}

//=============
//create Header
class PDF extends FPDF {

    function Header() {
        global $namapt;
        /*
		$path = 'images/logo.jpg';
        $this->Image($path, 15, 2, 25);
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(255, 255, 255);
        $this->SetY(5);
        $this->Cell(130, 5, strtoupper($namapt), 0, 1, 'C');
        $this->SetFont('Arial', '', 15);
        $this->Cell(190, 5, '', 0, 1, 'C');
        $this->SetFont('Arial', '', 6);
        $this->SetY(30);
        $this->SetX(163);
        $this->Cell(30, 10, 'PRINT TIME : ' . date('d-m-Y H:i:s'), 0, 1, 'L');
        $this->Line(10, 32, 200, 32);
		*/
    }

    function Footer() {
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(10, 5, 'NOTE : ', 0, 1, 'L');
        $this->Cell(10, 5, '1. Verifikasi benefit wajib dilakukan oleh HCGA untuk wilayah Kantor Pusat', 0, 1, 'L');
        $this->Cell(10, 5, '2. Verifikasi benefit wajib dilakukan oleh KTU/PDCA/PIC yang sudah ditunjuk untuk Unit Operasional.', 0, 1, 'L');
    }

}

$opthrd=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$optorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$golongan=  makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');

$str = "select * from " . $dbname . ".sdm_pjdinasht where notransaksi='" . $notransaksi . "'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {

    $uraian = $bar->hasilkerja;
    $jabatan = '';
    $namakaryawan = '';
    $bagian = '';
    $karyawanid = '';
    $strc = "select a.namakaryawan,a.karyawanid,a.bagian,b.namajabatan, a.nik, a.kodegolongan  
                    from " . $dbname . ".datakaryawan a left join  " . $dbname . ".sdm_5jabatan b
                        on a.kodejabatan=b.kodejabatan
                        where a.karyawanid=" . $bar->karyawanid;
	$resc=$owlPDO->query($strc) or die(print " Gagal: ".PDOException::getMessage());
	$resc->setFetchMode(PDO::FETCH_OBJ);
    while ($barc = $resc->fetch()) {
        $jabatan = $barc->namajabatan;
        $namakaryawan = $barc->namakaryawan;
        $bagian = $barc->bagian;
        $karyawanid = $barc->karyawanid;
        $karyawannik = $barc->nik;
        $kdgolongan=$barc->kodegolongan;
    }
    $strw = "select a.namaorganisasi from " . $dbname . ".datakaryawan b left join " . $dbname . ".organisasi a 
          on b.kodeorganisasi=a.kodeorganisasi where b.karyawanid=" . $karyawanid;
	$resw=$owlPDO->query($strw) or die(print " Gagal: ".PDOException::getMessage());
	$resw->setFetchMode(PDO::FETCH_OBJ);
    while ($barw = $resw->fetch()) {
        $namapt = $barw->namaorganisasi;
    }


    //===============================	  

    //Get advance payment from cash bank
    $uangmuka=0;
    $strupd = "select sum(a.jumlah) as uangmuka , b.nopo from ".$dbname.".keu_kasbankdt a  
    left join ".$dbname.".keu_tagihanht b on a.keterangan1= b.noinvoice
    where b.nopo ='".$bar->notransaksi."' and b.tipeinvoice = 'upd' ";
    $resupd=$owlPDO->query($strupd) or die(print " Gagal: ".PDOException::getMessage());
    $resupd->setFetchMode(PDO::FETCH_OBJ);
    while ($barupd = $resupd->fetch()) {
        $uangmuka = $barupd->uangmuka;
    }

    $kodeorg = $bar->kodeorg;
    $persetujuan = $bar->persetujuan;
    $hrd = $bar->hrd;
    $tujuan3 = $bar->tujuan3;
    $tujuan2 = $bar->tujuan2;
    $tujuan1 = $bar->tujuan1;
    $tanggalperjalanan = tanggalnormal($bar->tanggalperjalanan);
    $tanggalkembali = tanggalnormal($bar->tanggalkembali);
    $tugas1 = $bar->tugas1;
    $tugas2 = $bar->tugas2;
    $tugas3 = $bar->tugas3;
    $tujuanlain = $bar->tujuanlain;
    $tugaslain = $bar->tugaslain;
    $pesawat = $bar->pesawat;
    $darat = $bar->darat;
    $laut = $bar->laut;
    $mess = $bar->mess;
    $hotel = $bar->hotel;
    $statushrd = $bar->statushrd;

    if ($pesawat==1){
        $trans='Pesawat';
    } else if ($darat==1){
        $trans='Bus/Kereta Api';
    } else if ($laut==1){
        $trans='Kapal Laut';
    } else if (@$kendaraandinas==1){
        $trans='Kendaraan Dinas';
    } else if (@$kendaraanpribadi==1){
        $trans='Kendaraan Pribadi';
    } else if (@$kendaraanumum==1){
        $trans='Kendaraan Umum';
    }

    if ($statushrd == 0)
        $statushrd = $_SESSION['lang']['wait_approval'];
    else if ($statushrd == 1)
        $statushrd = $_SESSION['lang']['disetujui'];
    else
        $statushrd = $_SESSION['lang']['ditolak'];

    $statuspersetujuan = $bar->statuspersetujuan;
    if ($statuspersetujuan == 0)
        $perstatus = $_SESSION['lang']['wait_approval'];
    else if ($statuspersetujuan == 1)
        $perstatus = $_SESSION['lang']['disetujui'];
    else
        $perstatus = $_SESSION['lang']['ditolak'];

    //ambil PT
    $sorg="select induk from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."'";   
    $rorg=$owlPDO->query($sorg) or die(print " Gagal: ".PDOException::getMessage());
    $rorg->setFetchMode(PDO::FETCH_OBJ);
    $borg=$rorg->fetch();
    $induk=$borg->induk;

    //ambil bagian,jabatan persetujuan
    $perjabatan = '';
    $perbagian = '';
    $pernama = '';
    $strf = "select a.bagian,b.namajabatan,a.namakaryawan from " . $dbname . ".datakaryawan a left join
               " . $dbname . ".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
                   where karyawanid=" . $persetujuan;
    $resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
	$resf->setFetchMode(PDO::FETCH_OBJ);
	while ($barf = $resf->fetch()) {
        $perjabatan = $barf->namajabatan;
        $perbagian = $barf->bagian;
        $pernama = $barf->namakaryawan;
    }
//ambil jabatan, hrd

    $hjabatan = '';
    $hbagian = '';
    $hnama = '';
    $strf = "select a.bagian,b.namajabatan,a.namakaryawan from " . $dbname . ".datakaryawan a left join
               " . $dbname . ".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
                   where karyawanid=" . $hrd;
	$resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
	$resf->setFetchMode(PDO::FETCH_OBJ);
    while ($barf = $resf->fetch()) {
        $hjabatan = $barf->namajabatan;
        $hbagian = $barf->bagian;
        $hnama = $barf->namakaryawan;
    }
}

$pdf = new PDF('L', 'mm', 'A4');
$pdf->SetFont('Arial', 'B', 14);
$pdf->Setleftmargin(20);
$pdf->AddPage();

$pdf->SetX(60);
$pdf->SetFillColor(255, 255, 255);
$pdf->Cell(175, 5, 'LAPORAN PERTANGGUNGJAWABAN PERJALANAN DINAS (LPPD)', 0, 1, 'C');
$pdf->SetX(60);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(175, 5, 'NO : ' . $notransaksi, 0, 1, 'C');

$pdf->Ln();
$pdf->SetFont('Arial','',9);    
$pdf->Cell(30, 5,"Nama", 0, 'J');
$pdf->Cell(5, 5,":", 0,0, 'J');
$pdf->Cell(75, 5,$namakaryawan, 1,0, 'J');
$pdf->Cell(45, 5,'', 0,0, 'J');
$pdf->Cell(20, 5,"No. SPD", 0, 'J');
$pdf->Cell(5, 5,":", 0,0, 'J');
$pdf->Cell(75, 5,$notransaksi, 1,1, 'J');
$pdf->Cell(50, 1,'', 0,1, 'J');

$pdf->Cell(30, 5,"No. Induk Karyawan", 0, 'J');
$pdf->Cell(5, 5,":", 0,0, 'J');
$pdf->Cell(75, 5,$karyawannik, 1,0, 'J');
$pdf->Cell(45, 5,'', 0,0, 'J');
$pdf->Cell(20, 5,"Nama Atasan", 0, 'J');
$pdf->Cell(5, 5,":", 0,0, 'J');
$pdf->Cell(75, 5,$pernama, 1,1, 'J');
$pdf->Cell(50, 1,'', 0,1, 'J');

$pdf->Cell(30, 5,"Jabatan", 0, 'J');
$pdf->Cell(5, 5,":", 0,0, 'J');
$pdf->Cell(75, 5,$jabatan, 1,0, 'J');
$pdf->Cell(45, 5,'', 0,0, 'J');
$pdf->Cell(20, 5,"Departemen", 0, 'J');
$pdf->Cell(5, 5,":", 0,0, 'J');
$pdf->Cell(75, 5,$bagian, 1,1, 'J');
$pdf->Cell(50, 1,'', 0,1, 'J');

$pdf->Cell(30, 5,"Golongan", 0, 'J');
$pdf->Cell(5, 5,":", 0,0, 'J');
$pdf->Cell(75, 5,$golongan[$kdgolongan], 1,0, 'J');
$pdf->Cell(45, 5,'', 0,0, 'J');
$pdf->Cell(20, 5,"Nama PT", 0, 'J');
$pdf->Cell(5, 5,":", 0,0, 'J');
$pdf->Cell(75, 5,$optorg[$induk], 1,1, 'J');
$pdf->Cell(50, 1,'', 0,1, 'J');



// $pdf->SetX(20);
// $pdf->Cell(30, 5, $_SESSION['lang']['nik'], 0, 0, 'L');
// $pdf->Cell(50, 5, " : " . $karyawannik, 0, 1, 'L');
// $pdf->SetX(20);
// $pdf->Cell(30, 5, $_SESSION['lang']['namakaryawan'], 0, 0, 'L');
// $pdf->Cell(50, 5, " : " . $namakaryawan, 0, 1, 'L');
// $pdf->SetX(20);
// $pdf->Cell(30, 5, $_SESSION['lang']['bagian'], 0, 0, 'L');
// $pdf->Cell(50, 5, " : " . $bagian, 0, 1, 'L');
// $pdf->SetX(20);
// $pdf->Cell(30, 5, $_SESSION['lang']['functionname'], 0, 0, 'L');
// $pdf->Cell(50, 5, " : " . $jabatan, 0, 1, 'L');
// $pdf->SetX(20);
// $pdf->Cell(30, 5, $_SESSION['lang']['tanggaldinas'], 0, 0, 'L');
// $pdf->Cell(50, 5, " : " . $tanggalperjalanan, 0, 1, 'L');
// $pdf->SetX(20);
// $pdf->Cell(30, 5, $_SESSION['lang']['tanggalkembali'], 0, 0, 'L');
// $pdf->Cell(50, 5, " : " . $tanggalkembali, 0, 1, 'L');

$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 8);
// $pdf->Cell(172, 5, strtoupper($_SESSION['lang']['hasiltugas'] . ':'), 'B', 1, 'L');
$pdf->Cell(20, 10, 'Tanggal', 1, 0, 'C');
$pdf->Cell(35, 10, 'Lokasi', 1, 0, 'C');
$pdf->Cell(40, 10, 'Uang Perjalanan Dinas', 1, 0, 'C');
$pdf->Cell(28, 5, 'Penginapan', 1, 0, 'C');
$yinap=$pdf->getY();
$pdf->Cell(25, 10, 'Transportasi', 1, 0, 'C');
$pdf->Cell(17, 10, 'Aiport Tax', 1, 0, 'C');
$pdf->Cell(20, 10, 'Lain-lain', 1, 0, 'C');
$pdf->Cell(52, 10, 'Keterangan', 1, 0, 'C');
$pdf->Cell(32, 10, 'Jumlah', 1, 1, 'C');

$pdf->SetY($yinap+5);
$pdf->SetX(115);
$pdf->Cell(15, 5, 'Hotel', 1, 0, 'C');
$pdf->Cell(13, 5, 'Mess', 1, 1, 'C');
$bwh=$pdf->getY();

$str = "select a.*,b.keterangan as jns from " . $dbname . ".sdm_pjdinasdt a
      left join " . $dbname . ".sdm_5jenisbiayapjdinas b on a.jenisbiaya=b.id
          where a.notransaksi='" . $notransaksi . "' and sumber='1'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no = 0;
$total = 0;
$gtot	= 0;
$totalhrd = 0;
$datasumber1 = array();
while ($bar = $res->fetch()){ 
	$sdm_pjdinasdtsumber1['tanggal'] 		= $bar->tanggal;
	$sdm_pjdinasdtsumber1['jenisbiaya']	    = $bar->jenisbiaya;
	$sdm_pjdinasdtsumber1['jns'] 			= $bar->jns;
	$sdm_pjdinasdtsumber1['detail'] 		= $bar->detail;
	$sdm_pjdinasdtsumber1['flag'] 			= $bar->flag;
	$sdm_pjdinasdtsumber1['jumlah'] 		= $bar->jumlah;
	$sdm_pjdinasdtsumber1['jumlahhrd'] 		= $bar->jumlahhrd;
	$sdm_pjdinasdtsumber1['keterangan'] 	= $bar->keterangan;
	$sdm_pjdinasdtsumber1['notransaksi']	= $bar->notransaksi;
	$datasumber1[] = $sdm_pjdinasdtsumber1; 
}

$datatanggal = getUnique($datasumber1,'tanggal');
$awalxlist=$pdf->getX();
foreach($datatanggal as $tgl){
	$no+=1;

	$datanbytanggal = getFiltering($datasumber1,'tanggal',$tgl);
	//$jenisbiaya	= getUnique($datanbytanggal,'jenisbiaya');
	$UANG_PERJALANAN_DINAS 	= getFiltering($datanbytanggal,'jenisbiaya','1');
	$TRANSPORTASI 			= getFiltering($datanbytanggal,'jenisbiaya','2');
	$PENGINAPAN 			= getFiltering($datanbytanggal,'jenisbiaya','3');
	$LAINNYA 				= getFiltering($datanbytanggal,'jenisbiaya','4');
	$PULSA 					= getFiltering($datanbytanggal,'jenisbiaya','5');
	$flag 					= "";
	$jml					= 0;
	$jml					+= must_zero(isset_num($UANG_PERJALANAN_DINAS,'jumlah'));
	$jml				   	+=  must_zero(isset_num($TRANSPORTASI,'jumlah'));
	$jml				   	+=  must_zero(isset_num($PENGINAPAN,'jumlah'));
	$jml				   	+=  must_zero(isset_num($LAINNYA,'jumlah'));
	$jml				   	+=  must_zero(isset_num($PULSA,'jumlah'));
	$jmlhrd					= 0;
	$jmlhrd					+= must_zero(isset_num($UANG_PERJALANAN_DINAS,'jumlahhrd'));
	$jmlhrd				   	+=  must_zero(isset_num($TRANSPORTASI,'jumlahhrd'));
	$jmlhrd				   	+=  must_zero(isset_num($PENGINAPAN,'jumlahhrd'));
	$jmlhrd				   	+=  must_zero(isset_num($LAINNYA,'jumlahhrd'));
	$jmlhrd				   	+=  must_zero(isset_num($PULSA,'jumlahhrd'));
	$lain_lain				=	must_zero(isset_num($LAINNYA,'jumlah'))+must_zero(isset_num($PULSA,'jumlah'));
	$hidden=$titlebaris='';
	
    
    $pdf->SetY($bwh);
    $atas=$pdf->getY();
	$awalxlist=$pdf->getX();

    $pdf->Cell(20, 5, tanggalnormal($tgl), 'L', 0, 'C');
    $pdf->Cell(35, 5, getFirstdata($datanbytanggal,'detail') , 'L', 0, 'C');

    $pdf->Cell(40, 5, if_zero(isset_num($UANG_PERJALANAN_DINAS,'jumlahhrd')), 'L', 0, 'C');
	if(isset($PENGINAPAN[0]['flag'])){
		$flag = getFirstdata($PENGINAPAN,'flag');
	}
	if($flag == '1'){ 
		//mess
		$pdf->Cell(15, 5, '', 'L', 0, 'C');
		$pdf->Cell(13, 5, if_zero(isset_num($PENGINAPAN,'jumlahhrd')), 'L', 0, 'C');
	}else if($flag == '2'){
		//hotel
		$pdf->Cell(15, 5, if_zero(isset_num($PENGINAPAN,'jumlahhrd')), 'L', 0, 'C');
		$pdf->Cell(13, 5, '', 'L', 0, 'C');
	}else{
		$pdf->Cell(15, 5, '', 'L', 0, 'C');
		$pdf->Cell(13, 5, if_zero(isset_num($PENGINAPAN,'jumlahhrd')), 'L', 0, 'C');
	}

    $pdf->Cell(25, 5, if_zero(isset_num($TRANSPORTASI,'jumlahhrd')), 'L', 0, 'C');
    $pdf->Cell(17, 5, '', 'L', 0, 'C');//lain-lain
	$pdf->Cell(20, 5, if_zero($lain_lain), 'L', 0, 'C');

	$height = 0;
    $awalygaris=$pdf->GetY();
    $pdf->SetX(1000);
    //$pdf->MultiCell(45,5,$bar->keterangan,'LR',1,'J');
    $akhirygaris=$pdf->GetY();
    $tinggiygaris=$akhirygaris-$awalygaris;
    $heightgaris=$tinggiygaris;
    $pdf->SetY($akhirygaris-$tinggiygaris);
    $awalylist=$pdf->GetY();
	/*
    if($heightgaris>$awalxlist){
        $pdf->Line($awalxlist, $awalylist, $awalxlist, $awalylist+$heightgaris);
        $pdf->Line($awalxlist+25, $awalylist, $awalxlist+25, $awalylist+$heightgaris);
        $pdf->Line($awalxlist+50, $awalylist, $awalxlist+50, $awalylist+$heightgaris);
        $pdf->Line($awalxlist+90, $awalylist, $awalxlist+90, $awalylist+$heightgaris);
        $pdf->Line($awalxlist+105, $awalylist, $awalxlist+105, $awalylist+$heightgaris);
        $pdf->Line($awalxlist+118, $awalylist, $awalxlist+118, $awalylist+$heightgaris);
        $pdf->Line($awalxlist+143, $awalylist, $awalxlist+143, $awalylist+$heightgaris);
        $pdf->Line($awalxlist+165, $awalylist, $awalxlist+165, $awalylist+$heightgaris);
        $pdf->Line($awalxlist+269, $awalylist, $awalxlist+269, $awalylist+$heightgaris);
    }
	*/
    $pdf->SetX(205);
    $pdf->Cell(52,5,getFirstdata($datanbytanggal,'keterangan'),'L',1,'L');
    $bwh=$pdf->getY();
    $pdf->SetY($atas);
    $pdf->SetX(257);
    $pdf->Cell(10, 5, 'Rp. ', 'L', 0, 'C');
    $pdf->Cell(22, 5, number_format($jmlhrd), 'R', 1, 'R');
    $pdf->Line($awalxlist, $awalylist+$heightgaris, $awalxlist+269, $awalylist+$heightgaris);
    $gtot+=$jmlhrd;
	/*
    if($pdf->GetY() > 150) {
        $i=0;
        $bwh=$bwh-20;
        $bwh=$pdf->GetY()-$bwh;
        $bwh=$bwh+20;
        $pdf->AddPage();
        $pdf->Line(15,$bwh,205,$bwh);
    }
	*/
	$last = $pdf->getY();
	if($no == 23 or $no == 54) {
		$Y_akhir=$pdf->getY();
		$pdf->Line($awalxlist, $Y_akhir, $awalxlist+269, $Y_akhir);
		$pdf->Ln(5);
		$pdf->SetFont('Arial', 'B', 8);
		$pdf->Cell(20, 10, 'Tanggal', 1, 0, 'C');
		$pdf->Cell(35, 10, 'Lokasi', 1, 0, 'C');
		$pdf->Cell(40, 10, 'Uang Perjalanan Dinas', 1, 0, 'C');
		$pdf->Cell(28, 5, 'Penginapan', 1, 0, 'C');
		$yinap=$pdf->getY();
		$pdf->Cell(25, 10, 'Transportasi', 1, 0, 'C');
		$pdf->Cell(17, 10, 'Aiport Tax', 1, 0, 'C');
		$pdf->Cell(20, 10, 'Lain-lain', 1, 0, 'C');
		$pdf->Cell(52, 10, 'Keterangan', 1, 0, 'C');
		$pdf->Cell(32, 10, 'Jumlah', 1, 1, 'C');

		$pdf->SetY($yinap+5);
		$pdf->SetX(115);
		$pdf->Cell(15, 5, 'Hotel', 1, 0, 'C');
		$pdf->Cell(13, 5, 'Mess', 1, 1, 'C');
		$bwh=$pdf->getY();
	}
}

if($last > 119 and $last < 174) {
	//if($no < 23){
		$lastest = 174 - floor($last);
		$rowkosong = $lastest/5;
		/* Apabila harus Turun beda page, dibuat Row Kosong untuk sisa ruas Page */
		//$rowKosong=23-$no;
		for($i=0; $i<$rowkosong; $i++){
			$pdf->SetY($bwh);
			$atas=$pdf->getY();
			$awalxlist=$pdf->getX();
			$pdf->Cell(20, 5, '', 'L', 0, 'C');
			$pdf->Cell(35, 5, '', 'L', 0, 'C');
			$pdf->Cell(40, 5, '', 'L', 0, 'C');
			$pdf->Cell(15, 5, '', 'L', 0, 'C');
			$pdf->Cell(13, 5, '', 'L', 0, 'C');
			$pdf->Cell(25, 5, '', 'L', 0, 'C');
			$pdf->Cell(17, 5, '', 'L', 0, 'C');
			$pdf->Cell(20, 5, '', 'L', 0, 'C');
			$height = 0;
			$awalygaris=$pdf->GetY();
			$pdf->SetX(1000);
			//$pdf->MultiCell(45,5,$bar->keterangan,'LR',1,'J');
			$akhirygaris=$pdf->GetY();
			$tinggiygaris=$akhirygaris-$awalygaris;
			$heightgaris=$tinggiygaris;
			$pdf->SetY($akhirygaris-$tinggiygaris);
			$awalylist=$pdf->GetY();
			$pdf->SetX(205);
			$pdf->Cell(52,5,'','L',1,'L');
			$bwh=$pdf->getY();
			$pdf->SetY($atas);
			$pdf->SetX(257);
			$pdf->Cell(10, 5, '', 'L', 0, 'C');
			$pdf->Cell(22, 5, '', 'R', 1, 'R');
			$pdf->Line($awalxlist, $awalylist+$heightgaris, $awalxlist+269, $awalylist+$heightgaris);
		}
	//}
	$Y_akhir=$pdf->getY();
	$pdf->Line($awalxlist, $Y_akhir, $awalxlist+269, $Y_akhir);
	$bwh=$bwh;
	$bwh=$pdf->GetY()-$bwh;
	$bwh=$bwh+20;
	$pdf->AddPage();
}else{
	$Y_akhir=$pdf->getY();
	$pdf->Line($awalxlist, $Y_akhir, $awalxlist+269, $Y_akhir);
	$bwh=$bwh;
	$bwh=$pdf->GetY()-$bwh;
	$bwh=$bwh+$last;
}
$strum = "select * from " . $dbname . ".sdm_pjdinasdt where notransaksi='" . $notransaksi . "' and sumber='0' order by tanggal";
$resum=$owlPDO->query($strum) or die(print " Gagal: ".PDOException::getMessage());
$resum->setFetchMode(PDO::FETCH_OBJ);
$gtotum = 0;
while ($barum = $resum->fetch()) {
    $barum->frekuensi=1;
    //$starttime=strtotime($barum->tanggal);// tanggal pengajuan
    //$endtime=strtotime($barum->tanggalsampai);//tanggal sampai
    //$timediff= $endtime-$starttime;
    //$days=intval($timediff/86400);
    $totalum=$barum->frekuensi*$barum->jumlah;
    $gtotum+=$totalum;
}
$selisih	= 0;
$selisih = $uangmuka-$gtot;
$pdf->SetY($bwh);
$pdf->Ln(2);
$pdf->SetX(183);
$pdf->Cell(24, 5, 'Total Biaya', 0, 0, 'C');
$pdf->Cell(82, 5, number_format($gtot), 1, 1, 'R');
$pdf->Cell(82, 2, '', 0, 1, 'C');
$pdf->SetX(183);
$pdf->Cell(24, 5, 'Uang Muka', 0, 0, 'C');
$pdf->Cell(82, 5,  number_format($uangmuka), 1, 1, 'R');
$pdf->Cell(82, 2, '', 0, 1, 'C');
$pdf->SetX(183);
$pdf->Cell(24, 5, 'Selisih', 0, 0, 'C');
$pdf->Cell(82, 5, number_format($selisih), 1, 1, 'R');
$pdf->Ln(2);
$GetY1 = $pdf->getY();

$pdf->Cell(82, 2, '', 0, 1, 'C');

$pdf->SetX(140);
$pdf->Cell(24, 5, 'Terbilang : ', 0, 0, 'R');

$pdf->Line(165, $GetY1, 289, $GetY1);
$pdf->SetX(167);
if($selisih!=''){
    $pdf->MultiCell(120,5,terbilang($selisih,1),0,1,'TL');
}
$GetY2 = $pdf->getY();
$pdf->Line(165, $GetY1, 165, $GetY2+7);
$pdf->Line(289, $GetY1, 289, $GetY2+7);
$pdf->Line(165, $GetY2+7, 289, $GetY2+7);

$pdf->Cell(125, 2, '', 0, 1, 'C');

$pdf->Ln(10);
$height=5;
$pdf->Cell(50,$height,'Finance & Accounting',1,0,'C');         
$pdf->Cell(10,$height,'',0,0,'C');         
$pdf->Cell(50,$height,'Verifikasi Benefit',1,0,'C');
$pdf->Cell(10,$height,'',0,0,'C');
$pdf->Cell(86,$height,'Disetujui oleh,',1,0,'C');
$pdf->Cell(62,$height,'Dibuat oleh,',1,1,'C');
$yawal=$pdf->GetY();
$pdf->Ln(); 
$pdf->Ln(); 
$pdf->Ln(); 
$pdf->Ln();
$yakhir=$pdf->GetY();
$pdf->Line(20, $yawal, 20, $yakhir);
$pdf->Line(70, $yawal, 70, $yakhir);
$pdf->Line(80, $yawal, 80, $yakhir);
$pdf->Line(130, $yawal, 130, $yakhir);
$pdf->Line(140, $yawal, 140, $yakhir);
$pdf->Line(180, $yawal, 180, $yakhir);
$pdf->Line(226, $yawal, 226, $yakhir);
$pdf->Line(288, $yawal, 288, $yakhir);
$pdf->Cell(50,$height,'',1,0,'C');
$pdf->Cell(10,$height,'',0,0,'C');            
$pdf->Cell(50,$height,$opthrd[$hrd],1,0,'C');
$pdf->Cell(10,$height,'',0,0,'C');
$pdf->Cell(40,$height,'',1,0,'C');
$pdf->Cell(46,$height,$pernama,1,0,'C');
$pdf->Cell(62,$height,$namakaryawan,1,1,'C');
$yakhirg=$pdf->GetY();

$pdf->Ln();
$pdf->SetX(20);
$pdf->SetFont('Arial', '', 10);
// $pdf->MultiCell(172, 5, $uraian, 0, 'J');

//footer================================
$pdf->Ln();
$pdf->Output();
?>
