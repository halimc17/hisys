<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
//require_once('lib/zFunction.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');

$proses = $_GET['proses'];
$id = $_GET['id'];
$lokasitugas = $_SESSION['empl']['lokasitugas'];
$userid = $_SESSION['standard']['userid'];

//create Header
class PDF extends FPDF {

    function Header() {
        global $conn;
        global $dbname;
        global $lokasitugas, $userid, $id;
        global $owlPDO;

        //ambil nama pt
        $str1 = "select a.namaorganisasi as nama1, b.namaorganisasi as nama2, b.alamat, b.wilayahkota, b.telepon from " . $dbname . ".organisasi a
            left join " . $dbname . ".organisasi b on a.induk=b.kodeorganisasi
            where a.kodeorganisasi = '" . $lokasitugas . "'";
        $res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_OBJ);
        while ($bar1 = $res1->fetch()) {
            $namapt = $bar1->nama2;
            $alamatpt = $bar1->alamat . ", " . $bar1->wilayahkota;
            $telp = $bar1->telepon;
            $namaorganisasi = $bar1->nama1;
        }

        $str2 = "select namakaryawan from " . $dbname . ".datakaryawan where karyawanid='" . $userid . "'";
        $res2 = $owlPDO->query($str2) or die(print " Gagal: " . PDOException::getMessage());
        $res2->setFetchMode(PDO::FETCH_OBJ);
        while ($bar2 = $res2->fetch()) {
            $namakaryawan = $bar2->namakaryawan;
        }

        $path = 'images/logo.jpg';
        $this->Image($path, 15, 5, 20);
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(255, 255, 255);
        $this->SetX(55);
        $this->Cell(60, 5, $namapt, 0, 1, 'L');
        $this->SetX(55);
        $this->Cell(60, 5, $alamatpt, 0, 1, 'L');
        $this->SetX(55);
        $this->Cell(60, 5, "Tel: " . $telp, 0, 1, 'L');
        $this->Ln();
        $this->SetFont('Arial', '', 10);
        $this->SetY(35);
        $this->Cell(190, 5, $_SESSION['lang']['prevmain'], 0, 1, 'C');
        $this->Cell(190, 5, $_SESSION['lang']['unit'] . ' : ' . $namaorganisasi, 0, 1, 'C');
        $this->SetFont('Arial', '', 6);
        $this->SetY(27);
        $this->SetX(163);
        $this->Cell(30, 10, 'PRINT TIME : ' . date('d-m-Y H:i:s') . ' BY: ' . $namakaryawan, 0, 1, 'R');
        $this->Line(10, 27, 200, 27);
        $this->Ln();

        //ambil data header
        $str3 = "select * from " . $dbname . ".schedulerht
            where id = '" . $id . "'";
        $res3 = $owlPDO->query($str3) or die(print " Gagal: " . PDOException::getMessage());
        $res3->setFetchMode(PDO::FETCH_OBJ);
        while ($bar3 = $res3->fetch()) {
            $jenis = $bar3->jenis;
            $mesin = $bar3->kodemesin;
            $satuan = $bar3->satuan;
            $atas = $bar3->batasatas;
            $peringatan = $bar3->batasreminder;
            $tanggal = $bar3->setiaptanggal;
            $tugas = $bar3->namatugas;
            $keterangan = $bar3->ketrangan;
            $email = $bar3->email;
        }

        $this->SetFont('Arial', '', 9);
        $this->Cell(35, 4, $_SESSION['lang']['jenis'], 0, 0, 'L');
        $this->Cell(40, 4, ": " . $jenis, 0, 1, 'L');
        $this->Cell(35, 4, $_SESSION['lang']['nmmesin'], 0, 0, 'L');
        $this->Cell(40, 4, ": " . $mesin, 0, 1, 'L');
        $this->Cell(35, 4, $_SESSION['lang']['satuan'], 0, 0, 'L');
        $this->Cell(40, 4, ": " . $satuan, 0, 1, 'L');
        $this->Cell(35, 4, $_SESSION['lang']['batasatas'], 0, 0, 'L');
        $this->Cell(40, 4, ": " . $atas, 0, 1, 'L');
        $this->Cell(35, 4, $_SESSION['lang']['peringatansetiap'], 0, 0, 'L');
        $this->Cell(40, 4, ": " . $peringatan . " Jam", 0, 1, 'L');
        $this->Cell(35, 4, $_SESSION['lang']['setiap'] . ' ' . $_SESSION['lang']['tanggal'], 0, 0, 'L');
        $this->Cell(40, 4, ": " . tanggalnormal($tanggal), 0, 1, 'L');
        $this->Cell(35, 4, $_SESSION['lang']['namatugas'], 0, 0, 'L');
        $this->Cell(40, 4, ": " . $tugas, 0, 1, 'L');
        $this->Cell(35, 4, $_SESSION['lang']['keterangan'], 0, 0, 'L');
        $this->Cell(40, 4, ": " . $keterangan, 0, 1, 'L');
        $this->Cell(35, 4, $_SESSION['lang']['email'], 0, 0, 'L');
        $this->Cell(40, 4, ": " . $email, 0, 1, 'L');
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(10, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->AddPage();

//ambil kelengkapan

$pdf->Ln();
$str = "select * from " . $dbname . ".schedulerdt where id='" . $id . "'";
$re = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$rCek = owlBaris($re);
if ($rCek > 0) {

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(220, 220, 220);
    $pdf->Cell(8, 5, 'No', 1, 0, 'L', 1);
    $pdf->Cell(30, 5, $_SESSION['lang']['kodebarang'], 1, 0, 'C', 1);
    $pdf->Cell(65, 5, $_SESSION['lang']['namabarang'], 1, 0, 'C', 1);
    $pdf->Cell(25, 5, $_SESSION['lang']['jumlah'], 1, 0, 'C', 1);
    $pdf->Cell(15, 5, $_SESSION['lang']['satuan'], 1, 0, 'C', 1);
    $pdf->Ln();
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetFont('Arial', '', 9);

    $no = 0;
    $svhc = "select a.*, b.namabarang, b.satuan from " . $dbname . ".schedulerdt a
        left join " . $dbname . ".log_5masterbarang b on a.kodebarang=b.kodebarang
        where a.id = '" . $id . "'
        ";
    $qvhc = $owlPDO->query($svhc) or die(print " Gagal: " . PDOException::getMessage());
    $qvhc->setFetchMode(PDO::FETCH_ASSOC);
    while ($rvhc = $qvhc->fetch()) {
        $no+=1;

        $pdf->Cell(8, 5, $no, 1, 0, 'L', 1);
        $pdf->Cell(30, 5, $rvhc['kodebarang'], 1, 0, 'C', 1);
        $pdf->Cell(65, 5, substr($rvhc['namabarang'], 0, 35), 1, 0, 'L', 1);
        $pdf->Cell(25, 5, number_format($rvhc['jumlah'], 2), 1, 0, 'R', 1);
        $pdf->Cell(15, 5, $rvhc['satuan'], 1, 0, 'L', 1);
        $pdf->Ln();
    }
}

$pdf->Output();
?>