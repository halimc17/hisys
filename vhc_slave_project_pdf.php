<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
//require_once('lib/zFunction.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/zLib.php');

/*echo "<pre>";
print_r($_GET);
echo "</pre>";
*/
//hanya kode proyek yang dipakai dari parameter (nama tabel tidak diambil dari browser); proyek harus milik unit pengguna
$colParts = explode(',', (string)(isset($_GET['column']) ? $_GET['column'] : ''));
$kodeProyek = trim($colParts[0]);
if (!preg_match('/^[A-Za-z0-9._\-]{1,20}$/', $kodeProyek)) {
    exit('Warning: Kode proyek tidak valid.');
}
$cekProyek = $owlPDO->query("select count(*) from " . $dbname . ".project where kode='" . $kodeProyek . "' and kodeorg in (" . getOrgDetail(2) . ")")->fetchColumn();
if (!$cekProyek) {
    exit('Warning: Proyek tidak ditemukan atau bukan unit Anda.');
}

$optNmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

//=============

//create Header
class PDF extends FPDF
{

    function Header()
    {
        global $conn;
        global $dbname;
        global $userid;
        global $notransaksi;
        global $posting;
        global $optNmorg;
        global $owlPDO;

        //print_r($_SESSION['org']);

        global $kodeProyek;
        $notransaksi = $kodeProyek;
        $str = "select * from " . $dbname . ".project where kode='" . $notransaksi . "' ";
        //print_r($str);
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $bar = $res->fetch();
        $posting = $bar->posting;
        //ambil nama pt
        $str1 = "select * from " . $dbname . ".organisasi where kodeorganisasi='" . $_SESSION['org']['kodeorganisasi'] . "'";
        $res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_OBJ);
        while ($bar1 = $res1->fetch()) {
            $namapt = $bar1->namaorganisasi;
            $alamatpt = $bar1->alamat . ", " . $bar1->wilayahkota;
            $telp = $bar1->telepon;
        }
        $sql2 = "select namakaryawan from " . $dbname . ".datakaryawan where karyawanid='" . $bar->updateby . "'";
        $resw = $owlPDO->query($sql2) or die(print " Gagal: " . PDOException::getMessage());
        $resw->setFetchMode(PDO::FETCH_OBJ);
        $res2 = $resw->fetch();


        $kodept = makeOption($dbname, "organisasi", "kodeorganisasi,induk", "kodeorganisasi='{$bar->kodeorg}'")[$bar->kodeorg];
        $arrHead = setheadreport($kodept, $bar->kodeorg);
        $path = $arrHead['logo'];
        //$path='images/logo.jpg';
        $this->Image($path, 10, 5, 0, 22);
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(255, 255, 255);
        $this->SetX(45);
        $this->Cell(60, 5, $arrHead['nama'], 0, 1, 'L');
        $this->SetX(45);
        $this->Cell(60, 5, $arrHead['alamat'], 0, 1, 'L');
        $this->SetX(45);
        $this->Cell(60, 5, "Tel: " . $arrHead['telepon'], 0, 1, 'L');
        $this->Ln();
        //$this->SetFont('Arial','U',15);
        $this->SetY(35);
        //$this->Cell(190,5,$_SESSION['lang']['header'],0,1,'C');
        $this->SetFont('Arial', '', 6);
        $this->SetY(27);
        $this->SetX(163);
        $this->Cell(30, 10, 'PRINT TIME : ' . date('d-m-Y H:i:s'), 0, 1, 'L');
        $this->Line(10, 27, 200, 27);
        $this->Ln();
        $this->SetFont('Arial', '', 9);
        $this->Cell(30, 4, $_SESSION['lang']['unitkerja'], 0, 0, 'L');
        $this->Cell(40, 4, ": " . $bar->kodeorg . " [" . $optNmorg[$bar->kodeorg] . "]", 0, 1, 'L');
        $this->Cell(30, 4, $_SESSION['lang']['kode'], 0, 0, 'L');
        $this->Cell(40, 4, ": " . $bar->kode, 0, 1, 'L');
        $this->Cell(30, 4, $_SESSION['lang']['nama'], 0, 0, 'L');
        $this->Cell(40, 4, ": " . $bar->nama, 0, 1, 'L');
        $this->Cell(30, 4, $_SESSION['lang']['tanggalmulai'], 0, 0, 'L');
        $this->Cell(40, 4, ": " . tanggalnormal($bar->tanggalmulai), 0, 1, 'L');
        $this->Cell(30, 4, $_SESSION['lang']['tanggalsampai'], 0, 0, 'L');
        $this->Cell(40, 4, ": " . tanggalnormal($bar->tanggalselesai), 0, 1, 'L');

        $this->Cell(30, 4, $_SESSION['lang']['updateby'], 0, 0, 'L');
        $this->Cell(40, 4, ": " . $res2->namakaryawan . "" . ($bar->lastupdate == '0000-00-00 00:00:00' ? '' : ' - ' . waktunormal($bar->lastupdate)), 0, 1, 'L');
    }


    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(10, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

//ambil kelengkapan

$pdf->Ln();
$pdf->SetY(30);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(190, 5, strtoupper($_SESSION['lang']['project']), 0, 1, 'C');
if ($posting < 1) {
    $pdf->Cell(190, 5, '(' . $_SESSION['lang']['onprogress'] . ')', 0, 1, 'C');
} else {
    $pdf->Cell(190, 5, '(' . $_SESSION['lang']['selesai'] . ')', 0, 1, 'C');
}

//tabel rincian kegiatan selebar halaman (190 mm); teks panjang dipotong "..."
$kolW = array(8, 20, 38, 38, 14, 14, 14, 22, 22);
$kolJudul = array('No', "Kode " . $_SESSION['lang']['kegiatan'], $_SESSION['lang']['deskripsi'], $_SESSION['lang']['namakegiatan'], $_SESSION['lang']['satuan'], $_SESSION['lang']['volume'], $_SESSION['lang']['bobot'] . ' %', $_SESSION['lang']['dari'], $_SESSION['lang']['sampai']);

function kepalaTabelProyek($pdf, $kolW, $kolJudul)
{
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetFillColor(220, 220, 220);
    foreach ($kolJudul as $i => $j) {
        $pdf->Cell($kolW[$i], 6, utf8_decode($j), 1, ($i == count($kolJudul) - 1 ? 1 : 0), 'C', 1);
    }
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetFont('Arial', '', 7);
}

function potongTeksProyek($pdf, $txt, $w)
{
    $txt = utf8_decode((string)$txt);
    $pdf->SetFont('Arial', '', 7);
    if ($pdf->GetStringWidth($txt) <= $w - 2) {
        return $txt;
    }
    while (strlen($txt) > 1 && $pdf->GetStringWidth($txt . '...') > $w - 2) {
        $txt = substr($txt, 0, -1);
    }
    return rtrim($txt) . '...';
}

function tglProyek($d)
{
    return ($d == '' || $d == '0000-00-00') ? '-' : tanggalnormal($d);
}

$pdf->SetY(76);
kepalaTabelProyek($pdf, $kolW, $kolJudul);

$str = "select * from " . $dbname . ".project_dt where kodeproject='" . $notransaksi . "' order by tanggalmulai, kegiatan";
$resw = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$resw->setFetchMode(PDO::FETCH_ASSOC);
$no = 0;
$totBobot = 0;
while ($res = $resw->fetch()) {
    $no += 1;
    $totBobot += $res['bobot'];
    if ($pdf->GetY() + 5 > 272) {
        $pdf->AddPage();
        $pdf->SetY(76);
        kepalaTabelProyek($pdf, $kolW, $kolJudul);
    }
    $pdf->Cell($kolW[0], 5, $no, 1, 0, 'C', 1);
    $pdf->Cell($kolW[1], 5, potongTeksProyek($pdf, $res['kegiatan'], $kolW[1]), 1, 0, 'L', 1);
    $pdf->Cell($kolW[2], 5, potongTeksProyek($pdf, $res['deskripsi'], $kolW[2]), 1, 0, 'L', 1);
    $pdf->Cell($kolW[3], 5, potongTeksProyek($pdf, $res['namakegiatan'], $kolW[3]), 1, 0, 'L', 1);
    $pdf->Cell($kolW[4], 5, potongTeksProyek($pdf, $res['satuan'], $kolW[4]), 1, 0, 'C', 1);
    $pdf->Cell($kolW[5], 5, number_format($res['volume'], 2), 1, 0, 'R', 1);
    $pdf->Cell($kolW[6], 5, number_format($res['bobot'], 2), 1, 0, 'R', 1);
    $pdf->Cell($kolW[7], 5, tglProyek($res['tanggalmulai']), 1, 0, 'C', 1);
    $pdf->Cell($kolW[8], 5, tglProyek($res['tanggalselesai']), 1, 1, 'C', 1);
}

if ($no == 0) {
    $pdf->Cell(array_sum($kolW), 6, 'Belum ada rincian kegiatan untuk proyek ini', 1, 1, 'C', 1);
} else {
    if ($pdf->GetY() + 5 > 272) {
        $pdf->AddPage();
        $pdf->SetY(76);
    }
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetFillColor(238, 243, 250);
    $pdf->Cell(array_sum(array_slice($kolW, 0, 6)), 5, 'Total (' . $no . ' kegiatan)', 1, 0, 'R', 1);
    $pdf->Cell($kolW[6], 5, number_format($totBobot, 2), 1, 0, 'R', 1);
    $pdf->Cell($kolW[7] + $kolW[8], 5, '', 1, 1, 'C', 1);
}

//footer================================


$pdf->Output();
