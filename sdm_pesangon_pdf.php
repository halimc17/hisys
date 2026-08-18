<?php 
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/terbilang.php');
require('lib/fpdf.php');
require('lib/htmlparser.inc');
require('lib/htmltofpdf.php');

if(isset($_GET['nosurat'])){
	

$str="select a.*,b.namakaryawan as namapihakkedua,c.namakaryawan as namapihakpertama
	  from ".$dbname.".sdm_pesangon a 
	  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
	  left join ".$dbname.".datakaryawan c on a.pihakpertama=c.karyawanid 
	  where a.nosurat = '".$_GET['nosurat']."' order by a.tanggalberhenti desc limit 1 ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);			  
$bar=$res->fetch();

$profil = setheadreport($bar['kodeunit']);
$pdf=new PDF_HTML_Table();
$pdf->AddPage('P');
$pdf->SetMargins(15,15,15);//L,T,R
$pdf->Rect(7, 7, 197, 284, 'D');
$pdf->Ln(5);
$pdf->SetFont('Arial','B',12);
$xPT = $pdf->GetX();
$pdf->Cell(0,10,$profil['nama'],0,0,'C');
$pdf->Image($profil['logo'],$xPT,10,30);
$pdf->Ln(5);
$pdf->Cell(0,10,'(Oil Palm Plantation & Mill)',0,0,'C');
$pdf->Ln(15);
$pdf->SetFont('Arial','UB',10);
$pdf->Cell(0,10,'PERJANJIAN BERSAMA',0,0,'C');
$pdf->Ln(5);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(0,10,'No : '.$bar['nosurat'],0,0,'C');
$pdf->SetFont('Arial','',9);
$pdf->Ln(10);
$pdf->WriteHTML("Pada hari ini ".hari($bar['tanggal'])." tanggal ".terbilang(date('d',strtotime($bar['tanggal'])),2)." bulan ".numToMonth(date('m',strtotime($bar['tanggal'])),'I','long')." Tahun Dua Ribu Tujuh Belas (".date('d/m/Y',strtotime($bar['tanggal']))."), kami yang bertanda tangan di bawah ini :");
$pdf->Ln(5);

$txt = '
<table border="0">
<tbody>
<tr>
	<td width="50">1. Pihak Pertama</td>
	<td width="3">:</td>
	<td width="130">Selaku ...... '.$profil['nama'].' beralamat di '.$profil['alamat'].' dari dan oleh karena jabatannya bertindak untuk dan atas nama '.$profil['nama'].'.</td>
</tr>
</tbody>
</table>';

$pdf->WriteHTML($txt);
$pdf->WriteHTML('Selanjutnya disebut sebagai .............................................................................................. <b>Pihak Pertama</b>');
$pdf->Ln(5);
$txt = '
<table border="0">
<tbody>
<tr>
	<td width="50">2. Pihak Pertama</td>
	<td width="3">:</td>
	<td width="130">Selaku Pribadi dari dan oleh karena itu bertindak untuk dan atas nama Pribadi.</td>
</tr>
</tbody>
</table>';
$pdf->WriteHTML($txt);
$pdf->WriteHTML('Selanjutnya disebut sebagai .............................................................................................. <b>Pihak Kedua</b>');
$pdf->Ln(10);
$pdf->WriteHTML("Bahwa Kedua Belah Pihak telah setuju dan sepakat berkenaan dengan Pengakhiran Hubungan Kerja Pihak
Kedua dengan ketentuan dan syarat-syarat sebagai berikut :");
if($bar['jenispesangon'] == 'Pesangon'){
$txt = '
<table border="0">
<tbody>
<tr>
	<td width="10" align="right">1.</td>
	<td width="170">Bahwa Kedua Belah Pihak telah setuju dan sepakat untuk Mengakhiri Hubungan Kerja terhitung tanggal '.tanggalbulan($bar['tanggalberhenti']).';</td>
</tr>
<tr>
	<td width="10" align="right">2.</td>
	<td width="170">Bahwa Pihak Pertama telah setuju dan sepakat menerima Pengakhiran Hubungan Kerja Pihak Kedua dari perusahaan milik Pihak Pertama;</td>
</tr>
<tr>
	<td width="10" align="right">3.</td>
	<td width="170">Bahwa Pihak Pertama telah setuju dan sepakat untuk memberikan Pesangon Sebesar:	 </td>
</tr>
<tr>
	<td width="20" align="right">a.</td>
	<td width="65" align="left">Pesangon</td>
	<td width="40" align="left">: Rp '.number_format($bar['jumlahp1562']).'</td>
</tr>
<tr>
	<td width="20" align="right">b.</td>
	<td width="65" align="left">Uang Penghargaan Masa Kerja</td>
	<td width="40" align="left">: Rp '.number_format($bar['jumlahp1563']).'</td>
</tr>
<tr>
	<td width="20" align="right">c.</td>
	<td width="65" align="left">Penggantian Perumahan dan Pengobatan</td>
	<td width="40" align="left">: Rp '.number_format($bar['jumlahp1564c']).'</td>
</tr>
<tr>
	<td width="20" align="right">d.</td>
	<td width="65" align="left">Penggantian Hak Cuti '.number_format($bar['p1564a']).' HK</td>
	<td width="40" align="left">: Rp '.number_format($bar['jumlahp1564a']).'</td>
</tr>
<tr>
	<td width="15" align="left"></td>
	<td width="70" align="left">TOTAL</td>
	<td width="40" align="left">: Rp '.number_format($bar['totalterima']).'</td>
</tr>
<tr>
	<td width="10" align="right">4.</td>
	<td width="170">Bahwa Pihak Kedua telah setuju dan sepakat menerima perhitungan dan pembayaran Uang Pesangon sebagai mana butir 3 (Tiga) di atas yakni sebesar '.terbilang($bar['totalterima'],3).' Rupiah;</td>
</tr>
<tr>
	<td width="10" align="right">5.</td>
	<td width="170">Bahwa Pihak Kedua telah setuju dan sepakat untuk tidak mengajukan tuntutan hukum apapun terhadap Pihak Pertama di kemudian hari berkaitan dengan Pemutusan Hubungan Kerja Pihak Kedua.</td>
</tr>
</tbody>
</table>
Demikian perjanjian bersama ini dibuat dan ditanda tangani tanpa tekanan dan paksaan dari pihak manapun, dan
dibuat dalam rangkap 2 (dua) bermaterai cukup yang masing masing mempunyai kekuatan hukum yang sama.';
$pdf->WriteHTML($txt);
$pdf->Cell(0,10,'Jakarta, '.tanggalbulan($bar['tanggal']),0,0,'C');
$txt = '
<table border="0" >
<tbody>
<tr>
	<td width="90" height="100" align="center">PIHAK PERTAMA</td>
	<td width="90" height="100" align="center">PIHAK KEDUA</td>
</tr>
</tbody>
</table>';
}
elseif($bar['jenispesangon']=='Kompensasi')
{
$txt = '
<table border="0">
<tbody>
<tr>
	<td width="10" align="right">1.</td>
	<td width="170">Bahwa Kedua Belah Pihak telah setuju dan sepakat untuk Mengakhiri Hubungan Kerja terhitung tanggal '.tanggalbulan($bar['tanggalberhenti']).';</td>
</tr>
<tr>
	<td width="10" align="right">2.</td>
	<td width="170">Bahwa Pihak Pertama telah setuju dan sepakat menerima Pengakhiran Hubungan Kerja Pihak Kedua dari perusahaan milik Pihak Pertama;</td>
</tr>
<tr>
	<td width="10" align="right">3.</td>
	<td width="170">Bahwa Pihak Pertama telah setuju dan sepakat untuk memberikan Kompensasi Sebesar: '.number_format($bar['totalterima']).'	 </td>
</tr>
<tr>
	<td width="10" align="right">4.</td>
	<td width="170">Bahwa Pihak Kedua telah setuju dan sepakat menerima perhitungan dan pembayaran Uang Pesangon sebagai mana butir 3 (Tiga) di atas yakni sebesar :'.terbilang($bar['totalterima'],3).' Rupiah;</td>
</tr>
<tr>
	<td width="10" align="right">5.</td>
	<td width="170">Bahwa Pihak Kedua telah setuju dan sepakat untuk tidak mengajukan tuntutan hukum apapun terhadap Pihak Pertama di kemudian hari berkaitan dengan Pemutusan Hubungan Kerja Pihak Kedua.</td>
</tr>
</tbody>
</table>
Demikian perjanjian bersama ini dibuat dan ditanda tangani tanpa tekanan dan paksaan dari pihak manapun, dan
dibuat dalam rangkap 2 (dua) bermaterai cukup yang masing masing mempunyai kekuatan hukum yang sama.';
$pdf->WriteHTML($txt);
$pdf->Cell(0,10,'Jakarta, '.tanggalbulan($bar['tanggal']),0,0,'C');
$txt = '
<table border="0" >
<tbody>
<tr>
	<td width="90" height="100" align="center">PIHAK PERTAMA</td>
	<td width="90" height="100" align="center">PIHAK KEDUA</td>
</tr>
</tbody>
</table>';	
}
elseif($bar['jenispesangon']=='Uang Pisah')
{
$txt = '
<table border="0">
<tbody>
<tr>
	<td width="10" align="right">1.</td>
	<td width="170">Bahwa Kedua Belah Pihak telah setuju dan sepakat untuk Mengakhiri Hubungan Kerja terhitung tanggal '.tanggalbulan($bar['tanggalberhenti']).';</td>
</tr>
<tr>
	<td width="10" align="right">2.</td>
	<td width="170">Bahwa Pihak Pertama telah setuju dan sepakat menerima Pengakhiran Hubungan Kerja Pihak Kedua dari perusahaan milik Pihak Pertama;</td>
</tr>
<tr>
	<td width="10" align="right">3.</td>
	<td width="170">Bahwa Pihak Pertama telah setuju dan sepakat untuk memberikan Uang Pisah Sebesar:	 </td>
</tr>
<tr>
	<td width="20" align="right">a.</td>
	<td width="60" align="left">Uang Pisah</td>
	<td width="40" align="left">: Rp '.number_format(($bar['jumlahsebelumpajak']-$bar['jumlahp1564a'])).'</td>
</tr>
<tr>
	<td width="20" align="right">b.</td>
	<td width="60" align="left">Penggantian Hak Cuti '.number_format($bar['p1564a']).' HK</td>
	<td width="40" align="left">: Rp '.number_format($bar['jumlahp1564a']).'</td>
</tr>
<tr>
	<td width="15" align="left"></td>
	<td width="65" align="left">TOTAL</td>
	<td width="40" align="left">: Rp '.number_format($bar['totalterima']).'</td>
</tr>
<tr>
	<td width="10" align="right">4.</td>
	<td width="170">Bahwa Pihak Kedua telah setuju dan sepakat menerima Uang Pisah sebagai mana butir 3 (Tiga) di atas yakni sebesar '.terbilang($bar['totalterima'],3).' Rupiah;</td>
</tr>
<tr>
	<td width="10" align="right">5.</td>
	<td width="170">Bahwa Pihak Kedua telah setuju dan sepakat untuk tidak mengajukan tuntutan hukum apapun terhadap Pihak Pertama di kemudian hari berkaitan dengan Pemutusan Hubungan Kerja Pihak Kedua.</td>
</tr>
</tbody>
</table>
Demikian perjanjian bersama ini dibuat dan ditanda tangani tanpa tekanan dan paksaan dari pihak manapun, dan
dibuat dalam rangkap 2 (dua) bermaterai cukup yang masing masing mempunyai kekuatan hukum yang sama.';
$pdf->WriteHTML($txt);
$pdf->Cell(0,10,'Jakarta, '.tanggalbulan($bar['tanggal']),0,0,'C');
$txt = '
<table border="0" >
<tbody>
<tr>
	<td width="90" height="100" align="center">PIHAK PERTAMA</td>
	<td width="90" height="100" align="center">PIHAK KEDUA</td>
</tr>
</tbody>
</table>';
}
$pdf->WriteHTML($txt);
$pdf->Ln(10);
$txt = '
<table border="0" >
<tbody>
<tr>
	<td width="90" align="center">'.$bar['namapihakpertama'].'</td>
	<td width="90" align="center">'.$bar['namapihakkedua'].'</td>
</tr>
<tr>
	<td width="90" align="center"></td>
	<td width="90" align="center"></td>
</tr>
<tr>
	<td width="90" align="center"></td>
	<td width="90" align="center">Paraf/ Initials: _________</td>
</tr>
</tbody>
</table>';


$pdf->WriteHTML($txt);

$pdf->Output();
}
?>