<?php
#PDF Penerimaan Barang Non-Inventory, dipanggil dari log_noninventory.php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
include_once('lib/zLib.php');

$param = $_POST; if(count($param)==0){ $param = $_GET; }

$scnotransaksi = checkPostGet('scnotransaksi', '');
$crnopo        = checkPostGet('crnopo', '');
$sctanggal     = checkPostGet('sctanggal', '');
$sctipe        = checkPostGet('sctipe', '');
$scsupplier    = checkPostGet('scsupplier', '');
$scunit        = checkPostGet('scunit', '');

#filter sama persis dengan log_slave_noninventory.php (case loaddata/excel), supaya tarikan PDF ikut filter yang aktif
$arrorgdet = getOrgDetail(2);
$where = "";
if($scnotransaksi!=''){
	$where.=" and notransaksi like '%".$scnotransaksi."%'";
}
if($crnopo!=''){
	$where.=" and nopo like '%".$crnopo."%'";
}
if($sctipe!=''){
	$where.=" and tipe='".$sctipe."'";
}
if($scunit!=''){
	$where.=" and unit='".$scunit."'";
}
if($scsupplier!=''){
	$where.=" and supplierid in (select supplierid from ".$dbname.".log_5supplier where namasupplier like '%".$scsupplier."%')";
}
if($sctanggal!=''){
	$txt_tgl=tanggalsystemn($sctanggal);
	$where.=" and tanggal='".$txt_tgl."'";
}

#data lintas unit (bukan per-PT), kop pakai org karyawan yang login - sama seperti tarikan lain yang serupa
$hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);

function bbT($v)
{
	return utf8_decode($v);
}

#teks yang kepanjangan dipotong+"..." supaya tidak tumpang tindih ke kolom sebelah
function bbFitTeks($pdf, $txt, $w, $f = 7)
{
	$txt = bbT($txt);
	$pdf->SetFont('Arial', '', $f);
	if ($pdf->GetStringWidth($txt) <= $w - 2) {
		return $txt;
	}
	while (strlen($txt) > 1 && $pdf->GetStringWidth($txt . '...') > $w - 2) {
		$txt = substr($txt, 0, -1);
	}
	return rtrim($txt) . '...';
}

#pindah halaman manual per baris (bukan auto-page-break FPDF) supaya aman untuk data banyak baris
function bbMuat($pdf, $h)
{
	if ($pdf->GetY() + $h > $pdf->h - $pdf->bMargin) {
		$pdf->AddPage();
	}
}

class PDF extends FPDF
{
	public $colW = array();

	function Header()
	{
		global $hdpt;
		$width = $this->w - $this->lMargin - $this->rMargin;
		if (file_exists($hdpt['logo'])) {
			$this->Image($hdpt['logo'], $this->lMargin, $this->tMargin, 18);
		}
		$this->SetFont('Arial', 'B', 11);
		$this->SetXY($this->lMargin + 22, $this->tMargin + 2);
		$this->Cell(160, 6, bbT($hdpt['nama']), 0, 1, 'L');
		$this->SetY($this->tMargin + 15);
		$this->SetFont('Arial', 'B', 13);
		$this->Cell($width, 7, 'PENERIMAAN BARANG NON-INVENTORY', 0, 1, 'C');
		$this->SetFont('Arial', '', 8);
		$this->Cell($width, 5, bbT('Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s')), 0, 1, 'C');
		$this->Ln(1);

		$this->SetFillColor(220, 220, 220);
		$this->SetFont('Arial', 'B', 8);
		$judul = array('No', $_SESSION['lang']['notransaksi'], $_SESSION['lang']['tipe'], $_SESSION['lang']['perusahaan'], $_SESSION['lang']['unit'], $_SESSION['lang']['tanggal'], $_SESSION['lang']['nopo'], $_SESSION['lang']['namasupplier'], $_SESSION['lang']['approval_status'], $_SESSION['lang']['posting'], 'Diposting Oleh', 'Tgl Posting');
		foreach ($judul as $i => $j) {
			$this->Cell($this->colW[$i], 6, bbT($j), 1, 0, 'C', true);
		}
		$this->Ln();
	}

	function Footer()
	{
		$this->SetY(-12);
		$this->SetFont('Arial', 'I', 7);
		$this->Cell(0, 8, 'Halaman ' . $this->PageNo() . ' / {nb}', 0, 0, 'R');
	}
}

$pdf = new PDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
#margin bawah 16 dipakai murni sebagai batas bbMuat (auto-page-break FPDF tetap mati) supaya baris
#terakhir tidak tumpang tindih dengan teks "Halaman.." di Footer() yang ada di -12
$pdf->SetAutoPageBreak(false, 16);
$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
#urutan kolom: No,NoTransaksi,Tipe,PT,Unit,Tanggal,NoPO,Supplier(flexible),StatusApproval,Posting,DipostingOleh,TglPosting
$colFixed = array(8, 28, 14, 12, 12, 18, 26, 30, 16, 30, 24); #semua kolom kecuali Supplier
$flex = $width - array_sum($colFixed); #sisa lebar jadi lebar kolom Supplier
$pdf->colW = array_merge(array_slice($colFixed, 0, 7), array($flex), array_slice($colFixed, 7));
$pdf->AddPage();
$h = 6;
$no = 0;

$str = "select * from ".$dbname.".log_noninventory where unit in (".$arrorgdet.") ".$where." order by tanggal desc, pt asc, unit asc, nopo asc";
$res = fetchdata($str);
$ada = false;
foreach($res as $val){
	$ada = true;
	$optnamasupplier = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$val['supplierid']."'");

	if ($val['persetujuan'] == 0) {
		$statusapp = $_SESSION['lang']['belumdiajukan'];
	} else {
		if ($val['persetujuan'] == 1) {
			$table = "approval"; $whereapp = "status = '1'"; $ket = $_SESSION['lang']['disetujui'];
		} else if ($val['persetujuan'] == 9) {
			$table = "approval"; $whereapp = "status = '0'"; $ket = $_SESSION['lang']['wait_approval'];
		} else if ($val['persetujuan'] == 2) {
			$table = "approval"; $whereapp = "status = '2'"; $ket = $_SESSION['lang']['ditolak'];
		}
		$strApp = "SELECT a.karyawanid, b.namakaryawan FROM ".$dbname.".".$table." a
				JOIN ".$dbname.".datakaryawan b ON a.karyawanid = b.karyawanid
				WHERE notransaksi = '".$val['notransaksi']."' AND ".$whereapp."
				ORDER BY level DESC LIMIT 1";
		$resApp = fetchdata($strApp);
		$statusapp = $ket." (".$resApp[0]['namakaryawan'].")";
	}

	$no++;
	bbMuat($pdf, $h);
	$pdf->SetFont('Arial', '', 7);
	$c = $pdf->colW;
	$pdf->Cell($c[0], $h, $no, 1, 0, 'C');
	$pdf->Cell($c[1], $h, bbFitTeks($pdf, $val['notransaksi'], $c[1]), 1, 0, 'L');
	$pdf->Cell($c[2], $h, $val['tipe'], 1, 0, 'C');
	$pdf->Cell($c[3], $h, $val['pt'], 1, 0, 'C');
	$pdf->Cell($c[4], $h, $val['unit'], 1, 0, 'C');
	$pdf->Cell($c[5], $h, tanggalnormal($val['tanggal']), 1, 0, 'C');
	$pdf->Cell($c[6], $h, bbFitTeks($pdf, $val['nopo'], $c[6]), 1, 0, 'L');
	$pdf->Cell($c[7], $h, bbFitTeks($pdf, @$optnamasupplier[$val['supplierid']], $c[7]), 1, 0, 'L');
	$pdf->Cell($c[8], $h, bbFitTeks($pdf, $statusapp, $c[8]), 1, 0, 'L');
	$pdf->Cell($c[9], $h, ($val['posting']=='1'?'Posted':'Not Posted'), 1, 0, 'C');
	$pdf->Cell($c[10], $h, bbFitTeks($pdf, ($val['posting']=='1'?getNamaKaryawan($val['postedby']):''), $c[10]), 1, 0, 'L');
	$pdf->Cell($c[11], $h, ($val['posting']=='1' and $val['postedtime']!='0000-00-00 00:00:00'?tanggalnormald($val['postedtime']):''), 1, 0, 'C');
	$pdf->Ln();
}
if (!$ada) {
	$pdf->SetFont('Arial', '', 9);
	$pdf->Cell($width, 8, 'Data tidak ditemukan', 1, 1, 'C');
}
$pdf->Output();
