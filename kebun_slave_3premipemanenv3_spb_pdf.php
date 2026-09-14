<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$prdlist = checkPostGet('prdlist','');
$unitlist = checkPostGet('unitlist','');
$afdlist = checkPostGet('afdlist','');
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
	if(count($unitDetailAkses) > 0){
		$where = " and kodeorg IN ".$gudang_detailAkses."  ";
	}else{
		$where = " and kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
	}
}
if($prdlist!=''){ $where.=" and periode='".$prdlist."' "; }
if($unitlist!=''){ $where.=" and kodeorg='".$unitlist."' "; }
if($afdlist!=''){ $where.=" and divisi='".$afdlist."' "; }
if($tgl1list!='' && $tgl2list!=''){ $where.=" and tanggalpanen between '".$tgl1list."' and '".$tgl2list."' "; }

class PDF extends FPDF
{
	function Header() {
		global $prdlist, $unitlist, $afdlist, $tgl1list, $tgl2list;
		$width = $this->w - $this->lMargin - $this->rMargin;
		$height = 11;
		$this->SetFont('Arial','B',11);
		$this->Cell($width,$height,'Daftar Premi Pemanen (SPB)',0,1,'C');
		$this->SetFont('Arial','',8);
		$ket = 'Periode: '.($prdlist!=''?$prdlist:'Seluruhnya');
		if($tgl1list!='' && $tgl2list!=''){ $ket.=' | Tanggal: '.tanggalnormal($tgl1list).' s/d '.tanggalnormal($tgl2list); }
		$ket.=' | Unit: '.($unitlist!=''?$unitlist:'Seluruhnya');
		$ket.=' | Divisi: '.($afdlist!=''?$afdlist:'Seluruhnya');
		$this->Cell($width,$height,$ket,0,1,'C');
		$this->Ln(2);
		$this->SetFont('Arial','B',6);
		$this->SetFillColor(220,220,220);
		$this->Cell(3/100*$width,$height,'No',1,0,'C',1);
		$this->Cell(5/100*$width,$height,'Periode',1,0,'C',1);
		$this->Cell(9/100*$width,$height,'Tanggal',1,0,'C',1);
		$this->Cell(13/100*$width,$height,'No Transaksi',1,0,'C',1);
		$this->Cell(5/100*$width,$height,'Unit',1,0,'C',1);
		$this->Cell(6/100*$width,$height,'Divisi',1,0,'C',1);
		$this->Cell(5/100*$width,$height,'Jjg',1,0,'C',1);
		$this->Cell(6/100*$width,$height,'Total Kg',1,0,'C',1);
		$this->Cell(6/100*$width,$height,'Premi Kg',1,0,'C',1);
		$this->Cell(7/100*$width,$height,'Premi Rp',1,0,'C',1);
		$this->Cell(6/100*$width,$height,'Brondol Kg',1,0,'C',1);
		$this->Cell(7/100*$width,$height,'Brondol Rp',1,0,'C',1);
		$this->Cell(5/100*$width,$height,'Denda',1,0,'C',1);
		$this->Cell(7/100*$width,$height,'Total',1,0,'C',1);
		$this->Cell(5/100*$width,$height,'Sts Jurnal',1,0,'C',1);
		$this->Cell(5/100*$width,$height,'Sts Panen',1,1,'C',1);
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

$str = "SELECT tanggalpanen, tahap, kodeorg, notransaksi, divisi, periode,
	sum((jjgbuahbesar+jjgbuahkecil)) as jjgpanen, sum((kgbuahkecil+kgbuahbesar)) as kgwb,
	sum((lbbuahkecil+lbbuahbesar)) as kglb1, sum((rplbbuahkecil+rplbbuahbesar)) as rplb1,
	sum(brondolan) as kgbrd, sum(rpbrondolan) as rpbrd, sum(dendapanen) as denda,
	sum((rplbbuahkecil+rplbbuahbesar)+(rphkbuahbesar+rphkbuahkecil)-(rphkbuahbesarpot+rphkbuahkecilpot)-dendapanen+rpbrondolan) as total,
	posting FROM ".$dbname.".kebun_3premipemanen
	where 1=1 ".$where." group by notransaksi order by notransaksi desc, periode desc, kodeorg asc, divisi asc";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);

$no=0;
$tjjg=$tkgwb=$tkglb1=$trplb1=$tkgbrd=$trpbrd=$tdenda=$ttotal=0;
while($bar=$res->fetch()){
	if(strlen($bar['notransaksi'])=='23'){
		if($bar['tahap']=='1'){
			$tglmin=$bar['periode']."-01";
			$tglmax=$bar['periode']."-15";
		}else{
			$tglmin=$bar['periode']."-06";
			$tglmax=tglakhir($bar['periode']."-01");
		}
		$tanggal3=tanggalnormal($tglmin)."-".tanggalnormal($tglmax);
	}else{
		$tanggal3=tanggalnormal($bar['tanggalpanen']);
	}
	$no++;
	$statusjurnal = $bar['posting']==1 ? 'Posted' : 'Not Posted';
	$statuspanen = $bar['posting']==1 ? 'Posted' : 'Not Posted';

	$pdf->Cell(3/100*$width,$height,$no,1,0,'C');
	$pdf->Cell(5/100*$width,$height,$bar['periode'],1,0,'C');
	$pdf->Cell(9/100*$width,$height,$tanggal3,1,0,'C');
	$pdf->Cell(13/100*$width,$height,$bar['notransaksi'],1,0,'L');
	$pdf->Cell(5/100*$width,$height,$bar['kodeorg'],1,0,'L');
	$pdf->Cell(6/100*$width,$height,$bar['divisi'],1,0,'L');
	$pdf->Cell(5/100*$width,$height,number_format($bar['jjgpanen'],0),1,0,'R');
	$pdf->Cell(6/100*$width,$height,number_format($bar['kgwb'],2),1,0,'R');
	$pdf->Cell(6/100*$width,$height,number_format($bar['kglb1'],2),1,0,'R');
	$pdf->Cell(7/100*$width,$height,number_format($bar['rplb1'],2),1,0,'R');
	$pdf->Cell(6/100*$width,$height,number_format($bar['kgbrd'],2),1,0,'R');
	$pdf->Cell(7/100*$width,$height,number_format($bar['rpbrd'],2),1,0,'R');
	$pdf->Cell(5/100*$width,$height,number_format($bar['denda'],2),1,0,'R');
	$pdf->Cell(7/100*$width,$height,number_format($bar['total'],2),1,0,'R');
	$pdf->Cell(5/100*$width,$height,$statusjurnal,1,0,'C');
	$pdf->Cell(5/100*$width,$height,$statuspanen,1,1,'C');

	$tjjg+=$bar['jjgpanen'];
	$tkgwb+=$bar['kgwb'];
	$tkglb1+=$bar['kglb1'];
	$trplb1+=$bar['rplb1'];
	$tkgbrd+=$bar['kgbrd'];
	$trpbrd+=$bar['rpbrd'];
	$tdenda+=$bar['denda'];
	$ttotal+=$bar['total'];
}

$pdf->SetFont('Arial','B',6);
$pdf->Cell(41/100*$width,$height,'GRAND TOTAL',1,0,'C');
$pdf->Cell(5/100*$width,$height,number_format($tjjg,0),1,0,'R');
$pdf->Cell(6/100*$width,$height,number_format($tkgwb,2),1,0,'R');
$pdf->Cell(6/100*$width,$height,number_format($tkglb1,2),1,0,'R');
$pdf->Cell(7/100*$width,$height,number_format($trplb1,2),1,0,'R');
$pdf->Cell(6/100*$width,$height,number_format($tkgbrd,2),1,0,'R');
$pdf->Cell(7/100*$width,$height,number_format($trpbrd,2),1,0,'R');
$pdf->Cell(5/100*$width,$height,number_format($tdenda,2),1,0,'R');
$pdf->Cell(7/100*$width,$height,number_format($ttotal,2),1,0,'R');
$pdf->Cell(10/100*$width,$height,'',1,1,'C');

$pdf->Output();
?>
