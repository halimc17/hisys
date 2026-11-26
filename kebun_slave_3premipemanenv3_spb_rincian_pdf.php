<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

#Tarikan PDF: rincian Premi Pemanen per karyawan (semua transaksi hasil filter List Data)
$prdlist = checkPostGet('prdlist','');
$unitlist = checkPostGet('unitlist','');
$afdlist = checkPostGet('afdlist','');
$notranslist = checkPostGet('notranslist','');
$statuslist = checkPostGet('statuslist','');
$tgl1list = checkPostGet('tgl1list','');
$tgl2list = checkPostGet('tgl2list','');
$tgl1list = ($tgl1list!='') ? tanggalsystemn($tgl1list) : '';
$tgl2list = ($tgl2list!='') ? tanggalsystemn($tgl2list) : '';

$where = "";
$unitDetailAkses = orgDetailuser($_SESSION['standard']['username'],'2');
$gudang_detailAkses=" (".$unitDetailAkses.") ";
if($_SESSION['empl']['subbagian']!=''){
	$where = " and divisi = '".$_SESSION['empl']['subbagian']."'";
}else{
	if(!empty($unitDetailAkses)){
		$where = " and kodeorg IN ".$gudang_detailAkses."  ";
	}else{
		$where = " and kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
	}
}
if($prdlist!=''){ $where.=" and periode='".$prdlist."' "; }
if($unitlist!=''){ $where.=" and kodeorg='".$unitlist."' "; }
if($afdlist!=''){ $where.=" and divisi='".$afdlist."' "; }
if($notranslist!=''){ $where.=" and notransaksi like '%".$notranslist."%' "; }
if($statuslist=='1'){ $where.=" and posting='1' "; }elseif($statuslist=='0'){ $where.=" and (posting<>'1' or posting is null) "; }
if($tgl1list!='' && $tgl2list!=''){ $where.=" and tanggalpanen between '".$tgl1list."' and '".$tgl2list."' "; }

$maksBaris=3000;
$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$nikkar=makeOption($dbname,'datakaryawan','karyawanid,nik');

#PT untuk logo/kop: unit yang dipilih, atau lokasi tugas user
$unitcetak=($unitlist!='') ? $unitlist : $_SESSION['empl']['lokasitugas'];
$ptkode=getindukPT($unitcetak);
$hdpt=setheadreport($ptkode,$ptkode);

function potongTeks($t,$maks){
	$t=(string)$t;
	return (strlen($t)>$maks) ? substr($t,0,$maks-3).'...' : $t;
}

class PDF extends FPDF
{
	function Header() {
		global $prdlist, $unitlist, $afdlist, $tgl1list, $tgl2list, $hdpt, $notranslist, $statuslist;
		$width = $this->w - $this->lMargin - $this->rMargin;
		$height = 11;
		if(file_exists($hdpt['logo'])){
			$this->Image($hdpt['logo'],$this->lMargin,$this->tMargin,42);
		}
		$this->SetFont('Arial','B',10);
		$this->SetXY($this->lMargin+50,$this->tMargin+6);
		$this->Cell(400,12,$hdpt['nama'],0,1,'L');
		$this->SetY($this->tMargin+40);
		$this->SetFont('Arial','B',11);
		$this->Cell($width,$height,'Rincian Premi Pemanen per Karyawan (SPB)',0,1,'C');
		$this->SetFont('Arial','',8);
		$ket = 'Periode: '.($prdlist!=''?$prdlist:'Seluruhnya');
		if($tgl1list!='' && $tgl2list!=''){ $ket.=' | Tanggal: '.tanggalnormal($tgl1list).' s/d '.tanggalnormal($tgl2list); }
		$ket.=' | Unit: '.($unitlist!=''?$unitlist:'Seluruhnya');
		$ket.=' | Divisi: '.($afdlist!=''?$afdlist:'Seluruhnya');
		if($notranslist!=''){ $ket.=' | No Transaksi: '.$notranslist; }
		if($statuslist!=''){ $ket.=' | Status: '.($statuslist=='1'?'Posted':'Belum Posting'); }
		$this->Cell($width,$height,$ket,0,1,'C');
		$this->Ln(2);
		$this->SetFont('Arial','B',6);
		$this->SetFillColor(220,220,220);
		$this->Cell(3/100*$width,$height,'No',1,0,'C',1);
		$this->Cell(6/100*$width,$height,'Tgl Panen',1,0,'C',1);
		$this->Cell(11/100*$width,$height,'No SPB',1,0,'C',1);
		$this->Cell(8/100*$width,$height,'NIK',1,0,'C',1);
		$this->Cell(14/100*$width,$height,'Nama Karyawan',1,0,'C',1);
		$this->Cell(9/100*$width,$height,'Blok Panen',1,0,'C',1);
		$this->Cell(5/100*$width,$height,'Jjg',1,0,'C',1);
		$this->Cell(6/100*$width,$height,'Kg',1,0,'C',1);
		$this->Cell(4/100*$width,$height,'HK',1,0,'C',1);
		$this->Cell(7/100*$width,$height,'Rp Upah',1,0,'C',1);
		$this->Cell(7/100*$width,$height,'Rp Lbh Basis',1,0,'C',1);
		$this->Cell(7/100*$width,$height,'Rp Brondol',1,0,'C',1);
		$this->Cell(5/100*$width,$height,'Denda',1,0,'C',1);
		$this->Cell(8/100*$width,$height,'Total',1,1,'C',1);
	}

	function Footer() {
		$this->SetY(-15);
		$this->SetFont('Arial','I',7);
		$this->Cell(0,10,'Print Time: '.date('H:i:s, d/m/Y').' By: '.$_SESSION['empl']['name'].'   Page '.$this->PageNo(),0,0,'L');
	}
}

$pdf = new PDF('L','pt','A4');
$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
$height = 12;
$pdf->AddPage();
$pdf->SetFillColor(255,255,255);
$pdf->SetFont('Arial','',6);

$str = "select notransaksi, kodeorg, divisi, periode, tanggalpanen, nospb, karyawanid, blok,
	(jjgbuahbesar+jjgbuahkecil) as jjg, (kgbuahbesar+kgbuahkecil) as kg, (hkbuahbesar+hkbuahkecil) as hk,
	((rphkbuahbesar+rphkbuahkecil)-(rphkbuahbesarpot+rphkbuahkecilpot)) as rpupah,
	(rplbbuahbesar+rplbbuahkecil) as rplb, rpbrondolan as rpbrd, dendapanen as denda,
	((rplbbuahkecil+rplbbuahbesar)+(rphkbuahbesar+rphkbuahkecil)-(rphkbuahbesarpot+rphkbuahkecilpot)-dendapanen+rpbrondolan) as total, posting
	from ".$dbname.".kebun_3premipemanen where 1=1 ".$where."
	order by periode asc, kodeorg asc, divisi asc, notransaksi asc, tanggalpanen asc, karyawanid asc, nospb asc, blok asc limit ".($maksBaris+1);
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);

$no=0;
$notrxSkr='';
$sub=array('jjg'=>0,'kg'=>0,'hk'=>0,'rpupah'=>0,'rplb'=>0,'rpbrd'=>0,'denda'=>0,'total'=>0);
$gt=$sub;
$terpotong=false;

function cetakSubtotal($pdf,$width,$height,$sub,$label){
	$pdf->SetFont('Arial','B',6);
	$pdf->SetFillColor(235,235,235);
	$pdf->Cell(51/100*$width,$height,$label,1,0,'R',1);
	$pdf->Cell(5/100*$width,$height,number_format($sub['jjg'],0),1,0,'R',1);
	$pdf->Cell(6/100*$width,$height,number_format($sub['kg'],2),1,0,'R',1);
	$pdf->Cell(4/100*$width,$height,number_format($sub['hk'],2),1,0,'R',1);
	$pdf->Cell(7/100*$width,$height,number_format($sub['rpupah'],2),1,0,'R',1);
	$pdf->Cell(7/100*$width,$height,number_format($sub['rplb'],2),1,0,'R',1);
	$pdf->Cell(7/100*$width,$height,number_format($sub['rpbrd'],2),1,0,'R',1);
	$pdf->Cell(5/100*$width,$height,number_format($sub['denda'],2),1,0,'R',1);
	$pdf->Cell(8/100*$width,$height,number_format($sub['total'],2),1,1,'R',1);
	$pdf->SetFillColor(255,255,255);
	$pdf->SetFont('Arial','',6);
}

while($bar=$res->fetch()){
	if($no>=$maksBaris){ $terpotong=true; break; }
	if($bar['notransaksi']!=$notrxSkr){
		if($notrxSkr!=''){
			cetakSubtotal($pdf,$width,$height,$sub,'Subtotal '.$notrxSkr);
			$sub=array('jjg'=>0,'kg'=>0,'hk'=>0,'rpupah'=>0,'rplb'=>0,'rpbrd'=>0,'denda'=>0,'total'=>0);
		}
		$notrxSkr=$bar['notransaksi'];
		$pdf->SetFont('Arial','B',6);
		$pdf->SetFillColor(205,225,245);
		$pdf->Cell($width,$height,'Transaksi: '.$bar['notransaksi'].'   Unit: '.$bar['kodeorg'].'   Divisi: '.$bar['divisi'].'   Periode: '.$bar['periode'].'   Status: '.($bar['posting']==1 ? 'Posted' : 'Belum Posting'),1,1,'L',1);
		$pdf->SetFillColor(255,255,255);
		$pdf->SetFont('Arial','',6);
	}
	$no++;
	$pdf->Cell(3/100*$width,$height,$no,1,0,'C');
	$pdf->Cell(6/100*$width,$height,tanggalnormal($bar['tanggalpanen']),1,0,'C');
	$pdf->Cell(11/100*$width,$height,potongTeks($bar['nospb'],22),1,0,'L');
	$pdf->Cell(8/100*$width,$height,potongTeks(@$nikkar[$bar['karyawanid']],16),1,0,'L');
	$pdf->Cell(14/100*$width,$height,potongTeks(@$nmkar[$bar['karyawanid']],28),1,0,'L');
	$pdf->Cell(9/100*$width,$height,potongTeks($bar['blok'],18),1,0,'L');
	$pdf->Cell(5/100*$width,$height,number_format($bar['jjg'],0),1,0,'R');
	$pdf->Cell(6/100*$width,$height,number_format($bar['kg'],2),1,0,'R');
	$pdf->Cell(4/100*$width,$height,number_format($bar['hk'],2),1,0,'R');
	$pdf->Cell(7/100*$width,$height,number_format($bar['rpupah'],2),1,0,'R');
	$pdf->Cell(7/100*$width,$height,number_format($bar['rplb'],2),1,0,'R');
	$pdf->Cell(7/100*$width,$height,number_format($bar['rpbrd'],2),1,0,'R');
	$pdf->Cell(5/100*$width,$height,number_format($bar['denda'],2),1,0,'R');
	$pdf->Cell(8/100*$width,$height,number_format($bar['total'],2),1,1,'R');
	foreach(array('jjg','kg','hk','rpupah','rplb','rpbrd','denda','total') as $kk){
		$sub[$kk]+=$bar[$kk];
		$gt[$kk]+=$bar[$kk];
	}
}
if($no==0){
	$pdf->Cell($width,$height,'Data tidak ada untuk filter ini.',1,1,'C');
}else{
	cetakSubtotal($pdf,$width,$height,$sub,'Subtotal '.$notrxSkr);
	cetakSubtotal($pdf,$width,$height,$gt,'GRAND TOTAL');
}
if($terpotong){
	$pdf->SetFont('Arial','I',7);
	$pdf->Cell($width,$height,'Catatan: tarikan dibatasi '.$maksBaris.' baris pertama. Persempit filter (periode, unit, divisi, tanggal) untuk melihat sisanya.',0,1,'L');
}

$pdf->Output();
