<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');

$pt = isset($_GET['pt']) ? $_GET['pt'] : '';
$gudang = isset($_GET['gudang']) ? $_GET['gudang'] : '';
$periode = isset($_GET['periode']) ? $_GET['periode'] : '';
//ambil namapt
$str = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $pt . "'";
$namapt = 'COMPANY NAME';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $namapt = strtoupper($bar->namaorganisasi);
}
//=======================================	
if (isset($_GET['unitDt'])) {//ini dari tab laporan stok per unit (tab 3)
    $str = "select 
                      a.kodeorg,
                      a.kodebarang,
                      sum(a.saldoakhirqty) as salakqty,
                      sum(a.nilaisaldoakhir) as salakrp,
                      sum(a.qtymasuk) as masukqty,
                      sum(a.qtykeluar) as keluarqty,
                      sum(qtymasukxharga) as masukrp,
                      sum(qtykeluarxharga) as keluarrp,                      
                      sum(a.saldoawalqty) as sawalqty,
                      sum(a.nilaisaldoawal) as sawalrp,
                        b.namabarang,b.satuan    
                        from " . $dbname . ".log_5saldobulanan a
                        left join " . $dbname . ".log_5masterbarang b
                        on a.kodebarang=b.kodebarang
                      where kodegudang like '" . $_GET['unitDt'] . "%' 
                      and periode='" . $periode . "' and (a.qtymasuk!=0 or a.qtykeluar!=0 or a.saldoakhirqty!=0)
                      group by a.kodebarang order by a.kodebarang";
} else if ($gudang == '') {
    $str = "select 
                      a.kodeorg,
                      a.kodebarang,
                      sum(a.saldoakhirqty) as salakqty,
                      sum(a.nilaisaldoakhir) as salakrp,
                      sum(a.qtymasuk) as masukqty,
                      sum(a.qtykeluar) as keluarqty,
                      sum(qtymasukxharga) as masukrp,
                      sum(qtykeluarxharga) as keluarrp,                      
                      sum(a.saldoawalqty) as sawalqty,
                      sum(a.nilaisaldoawal) as sawalrp,
                        b.namabarang,b.satuan    
                        from " . $dbname . ".log_5saldobulanan a
                        left join " . $dbname . ".log_5masterbarang b
                        on a.kodebarang=b.kodebarang
                      where kodeorg='" . $pt . "' 
                      and periode='" . $periode . "' and (a.qtymasuk!=0 or a.qtykeluar!=0 or a.saldoakhirqty!=0)
                      group by a.kodebarang order by a.kodebarang";
} else {
	$exgdg = substr($gudang,0,4);
    $str = "select
				a.kodeorg,
				a.kodebarang,
				sum(a.saldoakhirqty) as salakqty,
				a.hargarata as harat,
				sum(a.nilaisaldoakhir) as salakrp,
				sum(a.qtymasuk) as masukqty,
				sum(a.qtykeluar) as keluarqty,
				sum(a.qtymasukxharga) as masukrp,
				sum(a.qtykeluarxharga) as keluarrp,
				sum(a.saldoawalqty) as sawalqty,
				a.hargaratasaldoawal as sawalharat,
				sum(a.nilaisaldoawal) as sawalrp,
				b.namabarang,b.satuan 		 		      
				from " . $dbname . ".log_5saldobulanan a
				left join " . $dbname . ".log_5masterbarang b
				on a.kodebarang=b.kodebarang
				where kodeorg='" . $pt . "' 
				and periode='" . $periode . "'
				and kodegudang like '" . $exgdg . "%'  and (a.qtymasuk!=0 or a.qtykeluar!=0 or a.saldoakhirqty!=0)
				group by a.kodebarang 
				order by a.kodebarang";
}

//=================================================
class PDF extends FPDF {

    function Header() {
        global $namapt;
        global $pt;
        global $gudang;
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(20, 5, $namapt, '', 1, 'L');
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(190, 5, strtoupper($_SESSION['lang']['laporanstok']), 0, 1, 'C');
        $this->SetFont('Arial', '', 8);
        $this->Cell(140, 5, ' ', '', 0, 'R');
        $this->Cell(15, 5, $_SESSION['lang']['tanggal'], '', 0, 'L');
        $this->Cell(2, 5, ':', '', 0, 'L');
        $this->Cell(35, 5, date('d-m-Y H:i'), 0, 1, 'L');
        $this->Cell(140, 5, ' ', '', 0, 'R');
        $this->Cell(15, 5, $_SESSION['lang']['page'], '', 0, 'L');
        $this->Cell(2, 5, ':', '', 0, 'L');
        $this->Cell(35, 5, $this->PageNo(), '', 1, 'L');
        $this->Cell(140, 5, 'UNIT:' . $pt . '-' . $gudang, '', 0, 'L');
        $this->Cell(15, 5, 'User', '', 0, 'L');
        $this->Cell(2, 5, ':', '', 0, 'L');
        $this->Cell(35, 5, $_SESSION['standard']['username'], '', 1, 'L');
        $this->SetFont('Arial', '', 4);
        $this->Cell(5, 8, 'No.', 1, 0, 'C');
        $this->Cell(15, 8, $_SESSION['lang']['periode'], 1, 0, 'C');
        $this->Cell(15, 8, $_SESSION['lang']['kodebarang'], 1, 0, 'C');
        $this->Cell(40, 8, substr($_SESSION['lang']['namabarang'], 0, 30), 1, 0, 'C');
        $this->Cell(5, 8, $_SESSION['lang']['satuan'], 1, 0, 'C');
        $this->Cell(27, 4, $_SESSION['lang']['saldoawal'], 1, 0, 'C');
        $this->Cell(27, 4, $_SESSION['lang']['masuk'], 1, 0, 'C');
        $this->Cell(27, 4, $_SESSION['lang']['keluar'], 1, 0, 'C');
        $this->Cell(27, 4, $_SESSION['lang']['saldo'], 1, 1, 'C');
//=====================================
        $this->SetX(90);
        $this->Cell(9, 4, $_SESSION['lang']['kuantitas'], 1, 0, 'C');
        $this->Cell(9, 4, $_SESSION['lang']['hargasatuan'], 1, 0, 'C');
        $this->Cell(9, 4, $_SESSION['lang']['totalharga'], 1, 0, 'C');
        $this->Cell(9, 4, $_SESSION['lang']['kuantitas'], 1, 0, 'C');
        $this->Cell(9, 4, $_SESSION['lang']['hargasatuan'], 1, 0, 'C');
        $this->Cell(9, 4, $_SESSION['lang']['totalharga'], 1, 0, 'C');
        $this->Cell(9, 4, $_SESSION['lang']['kuantitas'], 1, 0, 'C');
        $this->Cell(9, 4, $_SESSION['lang']['hargasatuan'], 1, 0, 'C');
        $this->Cell(9, 4, $_SESSION['lang']['totalharga'], 1, 0, 'C');
        $this->Cell(9, 4, $_SESSION['lang']['kuantitas'], 1, 0, 'C');
        $this->Cell(9, 4, $_SESSION['lang']['hargasatuan'], 1, 0, 'C');
        $this->Cell(9, 4, $_SESSION['lang']['totalharga'], 1, 1, 'C');
    }

}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->AddPage();
$totalSaldoAwal = 0;
$totalMasuk = 0;
$totalKeluar = 0;
$totalSaldo = 0;

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no = 0;
while ($bar = $res->fetch()) {
    $no+=1;
    $kodebarang = $bar->kodebarang;
    $namabarang = $bar->namabarang;


    $kodebarang = $bar->kodebarang;
    $namabarang = $bar->namabarang;
    $salakqty = $bar->salakqty;
    $salakrp = $bar->salakrp;
    $masukqty = $bar->masukqty;
    $keluarqty = $bar->keluarqty;
    $masukrp = $bar->masukrp;
    $keluarrp = $bar->keluarrp;
    $sawalQTY = $bar->sawalqty;
    $sawalrp = $bar->sawalrp;

    @$sawalharat = $bar->sawalrp / $bar->sawalqty;
    @$haratmasuk = $bar->masukrp / $bar->masukqty;
    @$haratkeluar = $bar->keluarrp / $bar->keluarqty;
    @$harat = $bar->salakrp / $bar->salakqty;

    $pdf->Cell(5, 4, $no, 0, 0, 'C');
    $pdf->Cell(15, 4, $periode, 0, 0, 'C');
    $pdf->Cell(15, 4, $kodebarang, 0, 0, 'L');
    $pdf->Cell(40, 4, $namabarang, 0, 0, 'L');
    $pdf->Cell(5, 4, $bar->satuan, 0, 0, 'L');
    $pdf->Cell(9, 4, number_format($sawalQTY, 2, '.', ','), 0, 0, 'R');
    $pdf->Cell(9, 4, number_format($sawalharat, 2, '.', ','), 0, 0, 'R');
    $pdf->Cell(9, 4, number_format($sawalrp, 2, '.', ','), 0, 0, 'R');
    $pdf->Cell(9, 4, number_format($masukqty, 2, '.', ','), 0, 0, 'R');
    $pdf->Cell(9, 4, number_format($haratmasuk, 2, '.', ','), 0, 0, 'R');
    $pdf->Cell(9, 4, number_format($masukrp, 2, '.', ','), 0, 0, 'R');
    $pdf->Cell(9, 4, number_format($keluarqty, 2, '.', ','), 0, 0, 'R');
    $pdf->Cell(9, 4, number_format($haratkeluar, 2, '.', ','), 0, 0, 'R');
    $pdf->Cell(9, 4, number_format($keluarrp, 2, '.', ','), 0, 0, 'R');
    $pdf->Cell(9, 4, number_format($salakqty, 2, '.', ','), 0, 0, 'R');
    $pdf->Cell(9, 4, number_format($harat, 2, '.', ','), 0, 0, 'R');
    $pdf->Cell(9, 4, number_format($salakrp, 2, '.', ','), 0, 1, 'R');
    $totalSaldoAwal+=$sawalrp;
    $totalMasuk += $masukrp;
    $totalKeluar += $keluarrp;
    $totalSaldo += $salakrp;
}
$pdf->SetFont('Arial', 'B', 4);
$pdf->Cell(80, 4, $_SESSION['lang']['total'], 'T', 0, 'C');
$pdf->Cell(27, 4, number_format($totalSaldoAwal, 2), 'T', 0, 'R');
$pdf->Cell(27, 4, number_format($totalMasuk, 2), 'T', 0, 'R');
$pdf->Cell(27, 4, number_format($totalKeluar, 2), 'T', 0, 'R');
$pdf->Cell(27, 4, number_format($totalSaldo, 2), 'T', 0, 'R');
$pdf->Output();
?>