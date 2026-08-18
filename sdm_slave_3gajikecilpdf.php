<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
//require_once('lib/zFunction.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/zLib.php');
//=============
$tmp = explode(',', $_GET['column']);
$kdOrg = $tmp[0];
$tgl = $tmp[1];
$tppot = $tmp[2];

//create Header
class PDF extends FPDF {

    function Header() {
        global $conn;
        global $dbname;
        global $userid;
        global $kdOrg;
        global $tgl;
        global $tppot;
        global $optTipePot;
        global $owlPDO;

        $sInduk = "select induk from " . $dbname . ".organisasi where kodeorganisasi='" . $kdOrg . "'";
		$qInduk=$owlPDO->query($sInduk) or die(print " Gagal: ".PDOException::getMessage());
		$qInduk->setFetchMode(PDO::FETCH_ASSOC);
        $rInduk = $qInduk->fetch();

        $str1 = "select * from " . $dbname . ".organisasi where kodeorganisasi='" . $_SESSION['org']['kodeorganisasi'] . "'";
		$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_OBJ);
        while ($bar1 = $res1->fetch()) {
            $nama = $bar1->namaorganisasi;
            $alamatpt = $bar1->alamat . ", " . $bar1->wilayahkota;
            $telp = $bar1->telepon;
        }

        $optTipePot = makeOption($dbname, 'sdm_ho_component', 'id,name');
        $optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
        $sIsi = "select * from " . $dbname . ".sdm_potonganht where 
			   kodeorg='" . $kdOrg . "' and periodegaji='" . $tgl . "' and tipepotongan='" . $tppot . "'";
        $qIsi=$owlPDO->query($sIsi) or die(print " Gagal: ".PDOException::getMessage());
		$qIsi->setFetchMode(PDO::FETCH_ASSOC);
		$rIsi = $qIsi->fetch();

        $sOrg = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $rIsi['kodeorg'] . "'";
        $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
		$qOrg->setFetchMode(PDO::FETCH_ASSOC);
		$rOrg = $qOrg->fetch();

        $path = 'images/logo.jpg';
        $this->Image($path, 15, 10, 20);
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(255, 255, 255);
        $this->SetX(40);
        $this->Cell(60, 5, $nama, 0, 1, 'L');
        $this->SetX(40);

        $this->MultiCell(150, 5, $alamatpt, 0);

        //$this->Cell(60,5,$alamatpt,0,1,'L');	
        $this->SetX(40);
        $this->Cell(60, 5, "Tel: " . $telp, 0, 1, 'L');
        $this->Ln();
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(20, 5, '', '', 1, 'L');
        // $this->Cell(20,5,$nama,'',1,'L');
        $this->SetFont('Arial', '', 8);
        //$this->Line(10,30,200,30);	


        $akhirY = $this->GetY() - 5;



        $this->Line(10, $akhirY, 200, $akhirY);

        $akhirYline = $this->GetY();

        $this->SetY($akhirYline);


        $this->Cell(15, 5, $_SESSION['lang']['unit'], '', 0, 'L');
        $this->Cell(2, 5, ':', '', 0, 'L');
        $this->Cell(175, 5, $rIsi['kodeorg'] . "  " . $optNmOrg[$rIsi['kodeorg']], '', 1, 'L');

        $this->Cell(15, 5, $_SESSION['lang']['periode'], '', 0, 'L');
        $this->Cell(2, 5, ':', '', 0, 'L');
        $this->Cell(175, 5, $tgl, '', 1, 'L');
        $this->Ln();
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(10, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->AddPage();

$pdf->Ln();

$pdf->SetFont('Arial', 'U', 10);
$pdf->SetY(55);
$pdf->Cell(190, 5, strtoupper($_SESSION['lang']['list']), 0, 1, 'C');
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetFillColor(220, 220, 220);
$pdf->Cell(8, 5, 'No', 1, 0, 'L', 1);
$pdf->Cell(15, 5, 'NIK', 1, 0, 'C', 1);
$pdf->Cell(60, 5, $_SESSION['lang']['namakaryawan'], 1, 0, 'C', 1);
$pdf->Cell(40, 5, $_SESSION['lang']['tipekaryawan'], 1, 0, 'C', 1);
//$pdf->Cell(25, 5, $_SESSION['lang']['lokasitugas'], 1, 0, 'C', 1);
$pdf->Cell(22, 5, $_SESSION['lang']['rp'], 1, 1, 'C', 1);
//$pdf->Cell(45, 5, $_SESSION['lang']['keterangan'], 1, 1, 'C', 1);
$arrNmtp = array("0", "Staff", "3" => "KBL", "4" => "KHT");

$pdf->SetFillColor(255, 255, 255);
$pdf->SetFont('Arial', '', 8);
if ($_SESSION['empl']['tipelokasitugas'] == 'KANWIL') {
    $str = "select * from " . $dbname . ".sdm_potongandt where periodegaji='" . $tgl . "' "
            . "and kodeorg in (select distinct kodeunit from " . $dbname . ".bgt_regional_assignment where regional='" . $_SESSION['empl']['regional'] . "')
		  and tipepotongan='" . $tppot . "'  order by nik asc"; // echo $str;exit();
} else {
    $str = "select * from " . $dbname . ".sdm_potongandt where periodegaji='" . $tgl . "' "
            . "and kodeorg='" . $kdOrg . "'
		  and tipepotongan='" . $tppot . "'  order by nik asc";
}
$re=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$re->setFetchMode(PDO::FETCH_ASSOC);
$no = $totPot = 0;
while ($res = $re->fetch()) {
    $height = 5;
    $test = $pdf->GetY();
    $awalY = $pdf->GetY();
    $pdf->SetX(1000);
    $pdf->MultiCell(45, $height, $res['keterangan'], '0', 'L');
    $akhirYakun = $pdf->GetY();
    $akhirY = $akhirYakun;
    $height2 = $akhirY - $awalY;
    $pdf->SetY($awalY);

    $sKry = "select nik,namakaryawan,tipekaryawan from " . $dbname . ".datakaryawan where karyawanid='" . $res['nik'] . "'";
    $qKry=$owlPDO->query($sKry) or die(print " Gagal: ".PDOException::getMessage());
	$qKry->setFetchMode(PDO::FETCH_ASSOC);
    $rKry = $qKry->fetch();
    $no+=1;

    // $pdf->SetY(100);
    $pdf->Cell(8, $height2, $no, 1, 0, 'R', 1);
    $pdf->Cell(15, $height2, $rKry['nik'], 1, 0, 'C', 1);
    $pdf->Cell(60, $height2, $rKry['namakaryawan'], 1, 0, 'L', 1);
    $pdf->Cell(40, $height2, (isset($arrNmtp[$rKry['tipekaryawan']]) ? $arrNmtp[$rKry['tipekaryawan']] : ""), 1, 0, 'L', 1);
    //$pdf->Cell(25, $height2, $res['kodeorg'], 1, 0, 'L', 1);
    $pdf->Cell(22, $height2, number_format($res['jumlahpotongan']), 1, 1, 'R', 1);
    //$pdf->MultiCell(45, $height, $res['keterangan'], 1, 'L', 1);
    $totPot+=$res['jumlahpotongan'];
	
	if($akhirYakun>250)
	{
		$pdf->AddPage();
	}
	
	
}

$pdf->Cell(123, 5, $_SESSION['lang']['total'], 1, 0, 'C', 1);
$pdf->Cell(22, 5, number_format($totPot), 1, 1, 'R', 1);
//$pdf->Cell(45, 5, '', 1, 1, 'L', 1);
$pdf->Output();
