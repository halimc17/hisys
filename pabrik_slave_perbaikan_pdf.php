<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/zLib.php');

// $tmp = explode(',', $_GET['column']);
// $notran = $tmp[0];


	$notran=checkPostGet('notransaksi','');

if($notran==''){
	$tmp = explode(',', $_GET['column']);
	$notran = $tmp[0];
}

//exit("Error:$notran");
//create Header
class PDF extends FPDF {

    function Header() {
        
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(10, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

}

$nmBrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$pdf = new PDF('P', 'mm', 'A4');
$pdf->AddPage();

$pdf->Ln();

$iHt = "select * from " . $dbname . ".pabrik_rawatmesinht where notransaksi='" . $notran . "' ";
$nHt = $owlPDO->query($iHt) or die(print " Gagal: " . PDOException::getMessage());
$nHt->setFetchMode(PDO::FETCH_ASSOC);
$dHt = $nHt->fetch();


$arrHead = setheadreport(getindukPT($dHt['pabrik']),'');
$path=$arrHead['logo'];

// $path = 'images/logo.jpg';
$pdf->Image($path, 15, 3, 15);

$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(170, 2);
$pdf->Cell(35, 5, 'Form No :', 0, 1, 'L');
$pdf->SetX(170);
$pdf->Cell(35, 5, 'No. Job Order', 1, 1, 'C');
$pdf->SetX(170);
$pdf->Cell(35, 5, $dHt['notransaksi'], 1, 1, 'C');



$pdf->SetFont('Arial', 'B', 12);
$pdf->SetY(10);
$pdf->Cell(190, 5, 'JOB  ORDER  MAINTENANCE', 0, 1, 'C');
$pdf->SetFont('Arial', '', 6);
$pdf->Ln();
$pdf->SetFillColor(220, 220, 220);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(20, 5, $_SESSION['lang']['tanggal'], 1, 0, 'L', '1');
$pdf->Cell(50, 5, tanggalnormal($dHt['tanggal']), 1, 0, 'L');
$pdf->Cell(15, 5, $_SESSION['lang']['jam'], 1, 0, 'L', '1');
$pdf->Cell(20, 5, substr($dHt['jam'], 0, 5), 1, 0, 'L');
$pdf->Cell(25, 5, $_SESSION['lang']['namapemohon'], 1, 0, 'L', '1');
$pdf->Cell(50, 5, $nmKar[$dHt['namapemohon']], 1, 0, 'L');
$pdf->Cell(15, 5, $dHt['statuspemohon'], 1, 1, 'L');

//$pdf->SetFillColor(0, 0, 100);
//$pdf->SetFillColor(100, 95, 0, 0);
$awalYMesin = $pdf->GetY();
$pdf->SetY($awalYMesin);
$pdf->SetX(1000);
$pdf->MultiCell(50, 5, $nmOrg[$dHt['mesin']], 0, 'L');
$akhirYMesin = $pdf->GetY();
$heightMesin = $akhirYMesin - $awalYMesin;
$pdf->SetY($awalYMesin);

$pdf->SetFillColor(220, 220, 220);
$pdf->Cell(20, $heightMesin, $_SESSION['lang']['mesin'], 1, 0, 'L', '1');
$pdf->Cell(85, $heightMesin, '', 1, 0, 'L');

// $pdf->Cell(15, $heightMesin, $_SESSION['lang']['nomor'], 1, 0, 'L');
// $pdf->Cell(20, $heightMesin, '', 1, 0, 'L');

$pdf->Cell(25, $heightMesin, $_SESSION['lang']['station'], 1, 0, 'L', '1');
$pdf->Cell(65, $heightMesin, $nmOrg[$dHt['statasiun']], 1, 1, 'L');

$pdf->SetY($awalYMesin);
$pdf->SetX(30);
$pdf->MultiCell(50, 5, $nmOrg[$dHt['mesin']], 0, 'L');

$pdf->SetFont('Arial', 'U', 9);
$pdf->Cell(195, 5, $_SESSION['lang']['uraiankerusakan'].'  /  Prev, Maint. /  Calibration  /  Project  : ', 'LR', 1, 'L');
$pdf->SetFont('Arial', '', 9);
$pdf->MultiCell(195, 5, $dHt['kegiatan'], 'LBR', 'J');

$pdf->SetFont('Arial', '', 9);
$pdf->Cell(20, 5, $_SESSION['lang']['mulai'], 1, 0, 'L', '1');
$pdf->Cell(30, 5, tanggalnormal(substr($dHt['jammulai'], 0, 10)) . ' ' . substr($dHt['jammulai'], 11, 2) . ':' . substr($dHt['jammulai'], 14, 2), 1, 0, 'L');
$pdf->Cell(20, 5, $_SESSION['lang']['selesai'], 1, 0, 'L', '1');
$pdf->Cell(30, 5, tanggalnormal(substr($dHt['jamselesai'], 0, 10)) . ' ' . substr($dHt['jamselesai'], 11, 2) . ':' . substr($dHt['jamselesai'], 14, 2), 1, 0, 'L');
$pdf->Cell(30, 5, $_SESSION['lang']['jumlahjamperbaikan'], 1, 0, 'L','1');
$pdf->Cell(15, 5, $dHt['jumlahjamperbaikan'], 1, 0, 'L');
$pdf->Cell(20, 5, $_SESSION['lang']['status'], 1, 0, 'L','1');
$pdf->Cell(30, 5, $dHt['statusketuntasan'], 1, 1, 'L');

$pdf->Cell(30, 5, $_SESSION['lang']['pelaksana'].' - M / E  :', 'TL', 0, 'L');



$iKarJum = "select count(*) as jumlah from " . $dbname . ".pabrik_rawatmesindt_karyawan where notransaksi='" . $notran . "' ";
$nKarJum = $owlPDO->query($iKarJum) or die(print " Gagal: " . PDOException::getMessage());
$nKarJum->setFetchMode(PDO::FETCH_ASSOC);
$dKarJum = $nKarJum->fetch();
$jumKar = $dKarJum['jumlah'];

$iKar = "select * from " . $dbname . ".pabrik_rawatmesindt_karyawan where notransaksi='" . $notran . "' ";
$nKar = $owlPDO->query($iKar) or die(print " Gagal: " . PDOException::getMessage());
$nKar->setFetchMode(PDO::FETCH_ASSOC);
$tempKar = "";
$noKar = 0;
while ($dKar = $nKar->fetch()) {

    $noKar+=1;
    if ($noKar == $jumKar) {
        $separator = '.';
    } else {
        $separator = ',';
    }


    $tempKar.=$nmKar[$dKar['karyawanid']] . $separator . ' ';
}

$pdf->Cell(30, 5, $tempKar, 0, 0, 'L');
$pdf->SetX(205);
$pdf->Cell(1, 5, '', 'L', 1, 'L');

$akhirYnamaKar = $pdf->GetY();


$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(220, 220, 220);
$pdf->Cell(9, 15, 'No', 1, 0, 'C', 1);
$pdf->Cell(60, 15, 'Item Check', 1, 0, 'C', 1);
$pdf->Cell(51, 5, $_SESSION['lang']['kondisi'], 1, 1, 'C', 1);
$pdf->SetX(79);
$currenX = $pdf->GetX();
$pdf->Cell(17, 10, 'Normal', 1, 0, 'C', 1);
$pdf->Cell(17, 5, 'Perlu', 'TRL', 0, 'C', 1);
$pdf->Cell(17, 10,'Rusak', 1, 1, 'C', 1);
$akhirY = $pdf->GetY();
$pdf->SetXY(96, $akhirY - 5);
$pdf->Cell(17, 5, 'Perbaikan', 'BRL', 1, 'C', 1);
$pdf->SetY($akhirYnamaKar);
$pdf->SetX($currenX + 51);
if($_SESSION['language']=='EN'){
    $pdf->Cell(59, 15, 'Replaced Spare Parts', 1, 0, 'C', 1);
}else{
    $pdf->Cell(59, 15, 'Spare Part  Yang  Diganti', 1, 0, 'C', 1);
}
$pdf->Cell(16, 15, $_SESSION['lang']['jumlah'], 1, 1, 'C', 1);

$pdf->SetFillColor(255, 255, 255);
$pdf->SetFont('Arial', '', 9);
$arrPekerjaan = array();
$iPekerjaan = "select * from " . $dbname . ".pabrik_rawatmesindt_pekerjaan where notransaksi='" . $notran . "' "
        . " order by nomor asc ";
$nPekerjaan = $owlPDO->query($iPekerjaan) or die(print " Gagal: " . PDOException::getMessage());
$nPekerjaan->setFetchMode(PDO::FETCH_ASSOC);
while ($dPekerjaan = $nPekerjaan->fetch()) {
    @$noPkj+=1;
    $arrPekerjaan[$noPkj]['item'] = $dPekerjaan['rincian'];
    $arrPekerjaan[$noPkj]['kondisi'] = $dPekerjaan['kondisi'];
}

$iBarang = "select * from " . $dbname . ".pabrik_rawatmesindt where notransaksi='" . $notran . "' ";

$nBarang = $owlPDO->query($iBarang) or die(print " Gagal: " . PDOException::getMessage());
$nBarang->setFetchMode(PDO::FETCH_ASSOC);
while ($dBarang = $nBarang->fetch()) {
    @$noBarang+=1;
    $arrPekerjaan[$noBarang]['sparepart'] =$nmBrg[$dBarang['kodebarang']];
    $arrPekerjaan[$noBarang]['jumlah'] = $dBarang['jumlah'];
}


$test = $pdf->GetY();
$noPekerjaan = 0;
for ($noPekerjaan+=1; $noPekerjaan <= 15; $noPekerjaan++) {

    $height = 5;
    $awalY = $pdf->GetY();
    $pdf->SetY($awalY);
    $pdf->SetX(1000);
    $pdf->MultiCell(60, $height, setIt($arrPekerjaan[$noPekerjaan]['item'], ''), '0', 'L');
    $akhirYPekerjaan = $pdf->GetY();

    $pdf->SetY($awalY);
    $pdf->SetX(1000);
    $pdf->MultiCell(59, $height, setIt($arrPekerjaan[$noPekerjaan]['sparepart'], ''), '0', 'L');
    $akhirYBarang = $pdf->GetY();

    $akhirY = max($akhirYPekerjaan, $akhirYBarang);
    $height2 = $akhirY - $awalY;
    $pdf->SetY($awalY);

    if ($akhirY == $akhirYPekerjaan) {
        $multiHeightPekerjaan = $height;
    } else {
        $multiHeightPekerjaan = $height2;
    }

    if ($akhirY == $akhirYBarang) {
        $multiHeightBarang = $height;
    } else {
        $multiHeightBarang = $height2;
    }

    if (isset($arrPekerjaan[$noPekerjaan])) {
        $currentX = $pdf->GetX();
        $pdf->Cell(9, $height2, '', 1, 0, 'C', 1);
        $pdf->Cell(60, $height2, '', 1, 0, 'C', 1);
        $pdf->Cell(17, $height2, '', 1, 0, 'C', 1);
        $pdf->Cell(17, $height2, '', 1, 0, 'C', 1);
        $pdf->Cell(17, $height2, '', 1, 0, 'C', 1);
        $pdf->Cell(59, $height2, '', 1, 0, 'C', 1);
        $pdf->Cell(16, $height2, '', 1, 1, 'C', 1);

        $pdf->SetY($awalY);
        $pdf->SetX($currentX);
        $pdf->MultiCell(9, $height, $noPekerjaan, 0, 'C');
        $pdf->SetY($awalY);
        $pdf->SetX($currentX + 9);
        $currentX = $pdf->GetX();
        $pdf->MultiCell(60, $height, $arrPekerjaan[$noPekerjaan]['item'], 0, 'J');
        $pdf->SetY($awalY);
        $pdf->SetX($currentX + 60);
        $currentX = $pdf->GetX();
        if (setIt($arrPekerjaan[$noPekerjaan]['kondisi'], '') == 'normal') {
            $pdf->Cell(17, $height2, 'V', 0, 0, 'C');
            $pdf->Cell(17, $height2, '', 0, 0, 'C');
            $pdf->Cell(17, $height2, '', 0, 0, 'C');
        } else if (setIt($arrPekerjaan[$noPekerjaan]['kondisi'], '') == 'perbaikan') {
            $pdf->Cell(17, $height2, '', 0, 0, 'C');
            $pdf->Cell(17, $height2, 'V', 0, 0, 'C');
            $pdf->Cell(17, $height2, '', 0, 0, 'C');
        } else if (setIt($arrPekerjaan[$noPekerjaan]['kondisi'], '') == 'rusak') {
            $pdf->Cell(17, $height2, '', 0, 0, 'C');
            $pdf->Cell(17, $height2, '', 0, 0, 'C');
            $pdf->Cell(17, $height2, 'V', 0, 0, 'C');
        } else {
            $pdf->Cell(17, $height2, '', 0, 0, 'C');
            $pdf->Cell(17, $height2, '', 0, 0, 'C');
            $pdf->Cell(17, $height2, '', 0, 0, 'C');
        }
        $pdf->SetY($awalY);
        $pdf->SetX($currentX + 51);
        $currentX = $pdf->GetX();
        $pdf->MultiCell(59, $height, setIt($arrPekerjaan[$noPekerjaan]['sparepart'], ''), 0, 'L');
        $pdf->SetY($awalY);
        $pdf->SetX($currentX + 59);
        $currentX = $pdf->GetX();
        $pdf->MultiCell(16, $height, setIt($arrPekerjaan[$noPekerjaan]['jumlah'], ''), 0, 'R');
        $pdf->SetY($awalY);
        $pdf->Ln($height2);
    } else {
        $pdf->Cell(9, 5, $noPekerjaan, 1, 0, 'C', 1);
        $pdf->Cell(60, 5, '', 1, 0, 'L', 1);
        if ($arrPekerjaan[$noPekerjaan]['kondisi'] == 'normal') {
            $pdf->Cell(17, 5, '', 1, 0, 'C', 1);
            $pdf->Cell(17, 5, '', 1, 0, 'C', 1);
            $pdf->Cell(17, 5, '', 1, 0, 'C', 1);
        } else if ($arrPekerjaan[$noPekerjaan]['kondisi'] == 'perbaikan') {
            $pdf->Cell(17, 5, '', 1, 0, 'C', 1);
            $pdf->Cell(17, 5, '', 1, 0, 'C', 1);
            $pdf->Cell(17, 5, '', 1, 0, 'C', 1);
        } else if ($arrPekerjaan[$noPekerjaan]['kondisi'] == 'rusak') {
            $pdf->Cell(17, 5, '', 1, 0, 'C', 1);
            $pdf->Cell(17, 5, '', 1, 0, 'C', 1);
            $pdf->Cell(17, 5, '', 1, 0, 'C', 1);
        } else {
            $pdf->Cell(17, 5, '', 1, 0, 'C', 1);
            $pdf->Cell(17, 5, '', 1, 0, 'C', 1);
            $pdf->Cell(17, 5, '', 1, 0, 'C', 1);
        }
        $pdf->Cell(59, 5, '', 1, 0, 'L', 1);
        $pdf->Cell(16, 5, '', 1, 1, 'C', 1);
    }
}

$akhirBarang = $pdf->GetY();
/* if($akhirYJob>$akhirBarang)
  {
  $akhirDipakai=$akhirYJob;
  }
  else
  { */
$akhirDipakai = $akhirBarang;
//}

$pdf->setY($akhirDipakai);

$pdf->SetFont('Arial', 'BU', 9);
$pdf->Cell(120, 5, $_SESSION['lang']['hasilkerjajumlah'].'  :', 'TRL', 0, 'L', 1);
$pdf->Cell(75, 5, 'Comment / Saran  -  Mill Head Maintenance', 'TRL', 1, 'L', 1);

$awalYx = $pdf->GetY();
$height = 5;
$pdf->SetY($awalYx);
$pdf->SetX(1000);
$pdf->MultiCell(120, $height, $dHt['hasilkerja'], 0, 'J');
$akhirYHK = $pdf->GetY();

$pdf->SetY($awalYx);
$pdf->SetX(1000);
$pdf->MultiCell(75, $height, $dHt['komentarmainten'], 0, 'J');
$akhirYComment = $pdf->GetY();

$akhirYx = max($akhirYHK, $akhirYComment);
$height2 = $akhirYx - $awalYx;

$pdf->SetY($awalYx);
$pdf->SetX(10);
$pdf->Cell(120, $height2, '', 'LRB', 0, 'L', 1);
$pdf->Cell(75, $height2, '', 'LRB', 0, 'L', 1);
$pdf->Ln();
$AkhirYA = $pdf->GetY();

$pdf->SetFont('Arial', '', 9);

$pdf->SetY($awalYx);
$pdf->SetX(10);
$pdf->MultiCell(120, $height, $dHt['hasilkerja'], 0, 'J');
$pdf->SetY($awalYx);
$pdf->SetX(130);
$pdf->MultiCell(75, $height, $dHt['komentarmainten'], 0, 'J');

$pdf->SetY($AkhirYA);
$ybaru = $pdf->GetY();
//echo $akhirKometar;
$pdf->SetFont('Arial', '', 9);
//$pdf->SetXY(90, $ybaru);
$pdf->SetFont('Arial', 'BU', 9);
$pdf->Cell(80, 5, 'Comment / Saran  -  Spv. / Mill Head. Proses  :', 'TRL', 0, 'L');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(40, 5, 'M / E  Maintenance', 1, 0, 'C');
$pdf->Cell(37.5, 5, 'Mill Head.  Maintenance', 1, 0, 'C');
$pdf->Cell(37.5, 5, 'Spv / Mill Head Proses', 1, 0, 'C');

$yakhirJudul = $pdf->GetY() + 5;
$pdf->SetXY(10, $yakhirJudul);
$pdf->Cell(80, 25, '', 'BRL', 'T');
$pdf->SetX(10);
$pdf->MultiCell(80, 5, $dHt['komentarproses'], 0, 'J');
$pdf->SetXY(90, $yakhirJudul);
$pdf->MultiCell(40, 20, '', 'RB', 'T');
$pdf->SetXY(130, $yakhirJudul);
$pdf->MultiCell(37.5, 20, '', 'RB', 'T');
$pdf->SetXY(167.5, $yakhirJudul);
$pdf->MultiCell(37.5, 20, '', 'RB', 'T');

$pdf->SetX(90);
$pdf->Cell(40, 5, '', 'RB', 0, 'C');
$pdf->Cell(37.5, 5, '', 'RB', 0, 'C');
$pdf->Cell(37.5, 5, '', 'RB', 0, 'C');

$iuupl = "select namafile from " . $dbname . ".listfileupload where notransaksi='" . $notran . "' ";
$hasil = fetchData($iuupl);
if(count($hasil)>0){
    $pdf->Ln();
    // ambil ukuran halaman (A4 default = 210 x 297 mm)
    $pageWidth  = 210;
    $pageHeight = 297;

    // tentukan ukuran gambar
    $imgWidth  = 50; // dalam mm
    $imgHeight = 30;  // dalam mm


    // tambahkan gambar
    foreach ($hasil as $b) {
        @$nn++;
        if($nn==1){
            $x = ($pageWidth - $imgWidth)/2 - 30;            // center horizontal
            $l = ($pageWidth - $imgWidth)/2 - 30;
        }else{
            $x = ($pageWidth - $imgWidth)/2 +30;            // center horizontal
            $l = ($pageWidth - $imgWidth)/2 - 30;
        }
        $y = $pageHeight - $imgHeight - 76;           // 86 mm dari bawah
        $pdf->SetY($y);
        $pdf->SetX($x);
        $pdf->Cell($l, 5, 'Lampiran Ke- '.$nn , 0, 0, 'C');
    }
    foreach ($hasil as $v) {
        @$n++;
        if($n==1){
            $x = ($pageWidth - $imgWidth)/2 - 30;            // center horizontal
        }else{
            $x = ($pageWidth - $imgWidth)/2 + 30;            // center horizontal
        }
        // hitung posisi supaya gambar di bawah tengah
        $y = $pageHeight - $imgHeight - 70;           // 80 mm dari bawah
        $pdf->Image('fileupload/servicepabrik/'.$v['namafile'], $x, $y, $imgWidth, $imgHeight);
    }
}
$pdf->Output();
?>
