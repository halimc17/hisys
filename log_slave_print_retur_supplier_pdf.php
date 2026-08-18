<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zFunction.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
$nodok = $_GET['notransaksi'];

//=============
//create Header
class PDF extends FPDF {

    function Header() {
        global $conn;
        global $dbname;
        global $nodok;
        global $userid;
        global $posted;
        global $tanggal;
        global $lastupdate;
        global $idsupplier;
        global $nosj;
        global $nofaktur;
        global $kodegudang;
        global $nopo;
        global $namasupplier;
        global $keterangan;
		global $owlPDO;
		
        $pt = '';
        $namapt = '';
        $alamatpt = '';
        $telp = '';
        $kodegudang = '';
        $status = 0;
        $str = "select a.*,b.namasupplier from " . $dbname . ".log_transaksiht a
                                    left join " . $dbname . ".log_5supplier b on a.idsupplier=b.supplierid 
                                    where notransaksi='" . $_GET['notransaksi'] . "'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $kodept = $bar->kodept;
            $kodegudang = $bar->kodegudang;
            $userid = $bar->user;
            $posted = $bar->postedby;
            $status = $bar->post;
            $tanggal = $bar->tanggal;
            $lastupdate = $bar->lastupdate;
            $idsupplier = $bar->idsupplier;
            $namasupplier = $bar->namasupplier;
            $nosj = $bar->nosj;
            $nofaktur = $bar->nofaktur;
            $nopo = $bar->nopo;
            $keterangan = $bar->keterangan;
            if ($status == 0)
                $status = 'Not Confirm';
            else
                $status = 'Confirmed';
            //ambil nama pt

            $str1 = "select * from " . $dbname . ".organisasi where kodeorganisasi='" . $kodept . "'";
			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_OBJ);
            while ($bar1 = $res1->fetch()) {
                $namapt = $bar1->namaorganisasi;
                $alamatpt = $bar1->alamat . ", " . $bar1->wilayahkota;
                $telp = $bar1->telepon;
            }
        }
        // $path = 'images/ksp.jpg';
        $arrHead = setheadreport(substr($kodegudang,0,4));
        $path=$arrHead['logopalma'];
        $nmorganisasi = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
        $this->Image($path, 15, 5, 18);
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(255, 255, 255);
        $this->SetY(5);
        $this->SetX(40);
        $this->Cell(60, 5, $namapt, 0, 1, 'L');
        $this->SetX(40);
        $this->MultiCell(150, 5, $alamatpt, 0, 'L');
        $this->SetX(40);
        $this->Cell(60, 5, "Tel: " . $telp, 0, 1, 'L');
        $this->SetFont('Arial', '', 15);
        $this->SetY(30);
        $this->Cell(190, 5, strtoupper($_SESSION['lang']['dokumenretur']), 0, 1, 'C');
        $this->SetFont('Arial', '', 6);
        $this->SetY(27);
        $this->SetX(163);
        $this->Cell(30, 10, 'PRINT TIME : ' . date('d-m-Y H:i:s'), 0, 1, 'L');
        $this->Line(10, 27, 200, 27);
        $this->SetFont('Arial', '', 9);
        $this->Cell(30, 4, $_SESSION['lang']['sloc'], 0, 0, 'L');
        $this->Cell(40, 4, ": " . $kodegudang. " - ". $nmorganisasi[$kodegudang], 0, 1, 'L');
        $this->Cell(30, 4, $_SESSION['lang']['docnum'], 0, 0, 'L');
        $this->Cell(40, 4, ": " . $nodok, 0, 1, 'L');
        $this->Cell(30, 4, $_SESSION['lang']['docstatus'], 0, 0, 'L');
        $this->Cell(40, 4, ": " . $status, 0, 1, 'L');
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(10, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

}

/*
  print"<pre>";
  print_r($_SESSION);
  print"</pre>";
 */
$pdf = new PDF('P', 'mm', 'A4');
$pdf->AddPage();
$nmorganisasi = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');



$hari = hari($tanggal, $_SESSION['language']);
$tanggal = tanggalnormal($tanggal);
$lastupdate = tanggalnormal($lastupdate);
$resc = str_replace("#DATE_REPARAM#", $hari . ", " . $tanggal, $_SESSION['lang']['prebast']);
$resc = str_replace("#SLOC_PARAM#", $kodegudang. " - ". $nmorganisasi[$kodegudang], $resc);
$resc = str_replace("#VENDOR_PARAM#", $namasupplier, $resc);
$pdf->Ln();
$pdf->Ln();
$pdf->MultiCell(170, 5, $resc, 0, 'L');
$pdf->Cell(30, 4, $_SESSION['lang']['nopo'], 0, 0, 'L');
$pdf->Cell(40, 4, ": " . $nopo, 0, 1, 'L');
$pdf->Cell(30, 4, $_SESSION['lang']['suratjalan'], 0, 0, 'L');
$pdf->Cell(40, 4, ": " . $nosj, 0, 1, 'L');
$pdf->Cell(30, 4, $_SESSION['lang']['faktur'], 0, 0, 'L');
$pdf->Cell(40, 4, ": " . $nofaktur, 0, 1, 'L');
//    $pdf->Ln();
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(220, 220, 220);
$pdf->Cell(8, 5, 'No', 1, 0, 'L', 1);
$pdf->Cell(30, 5, $_SESSION['lang']['kodebarang'], 1, 0, 'C', 1);
$pdf->Cell(100, 5, $_SESSION['lang']['namabarang'], 1, 0, 'C', 1);
$pdf->Cell(20, 5, $_SESSION['lang']['satuan'], 1, 0, 'C', 1);
$pdf->Cell(20, 5, $_SESSION['lang']['kuantitas'], 1, 1, 'C', 1);
$pdf->SetFillColor(255, 255, 255);
$pdf->SetFont('Arial', '', 9);

$str = "select * from " . $dbname . ".log_transaksidt where notransaksi='" . $_GET['notransaksi'] . "'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no = 0;
while ($bar = $res->fetch()) {
    $no+=1;

    $kodebarang = $bar->kodebarang;
    $satuan = $bar->satuan;
    $jumlah = $bar->jumlah;
    $namabarang = '';
    $strv = "select namabarang from " . $dbname . ".log_5masterbarang where kodebarang='" . $bar->kodebarang . "'";
	$resv=$owlPDO->query($strv) or die(print " Gagal: ".PDOException::getMessage());
	$resv->setFetchMode(PDO::FETCH_OBJ);
    while ($barv = $resv->fetch()) {
        $namabarang = $barv->namabarang;
    }
    $pdf->Cell(8, 5, $no, 1, 0, 'L', 1);
    $pdf->Cell(30, 5, $kodebarang, 1, 0, 'L', 1);
    $pdf->Cell(100, 5, $namabarang, 1, 0, 'L', 1);
    $pdf->Cell(20, 5, $satuan, 1, 0, 'L', 1);
    $pdf->Cell(20, 5, number_format($jumlah, 2, '.', ','), 1, 1, 'R', 1);
}
//footer================================
$pdf->Cell(20, 4, $_SESSION['lang']['catatan'], 0, 0, 'L');
$pdf->Cell(40, 4, ": " . $keterangan, 0, 1, 'L');
$pdf->Ln();

//get user;
$namakaryawan = namakaryawan($dbname, $conn, $userid);
$pdf->Cell(20, 4, $_SESSION['lang']['dbuat_oleh'], 0, 0, 'L');
$pdf->Cell(40, 4, ": " . $namakaryawan." / ".$tanggal, 0, 1, 'L');
//get posted by
if ($posted != '')
    $posted = namakaryawan($dbname, $conn, $posted);
else
    $posted = '';
$pdf->Cell(20, 4, $_SESSION['lang']['posted'], 0, 0, 'L');
$pdf->Cell(40, 4, ": " . $posted." / ".$lastupdate, 0, 1, 'L');
$pdf->Output();
?>
