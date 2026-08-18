<?php
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');

$persetujuan1=checkPostGet('persetujuan1','');
$persetujuan2=checkPostGet('persetujuan2','');
$nodok=checkPostGet('nodok','');

$countno = 0;
$str = "select count(notransaksi) as countno from ".$dbname.".log_transaksiht where notransaksi='".$nodok."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
$countno = $bar['countno'];

$namaorganisasi = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'");
$to = getUserEmail($persetujuan1);
$to2 = getUserEmail($persetujuan2);
$namapengaju = getNamaKaryawan($_SESSION['standard']['userid']);
$subject="[Notifikasi]Persetujuan Barang Masuk Gudang dari Supplier a/n ".$namapengaju;

$body="<html>
	<head>
	<body>
		<dd>Dengan Hormat,</dd><br>
		<br>
		Pada hari ini, tanggal ".date('d-m-Y')." karyawan a/n  ".$namapengaju." mengajukan Permintaan Barang Masuk Gudang dari Supplier kepada bapak/ibu.
		<br>
		<br>
		Untuk menindak-lanjuti silahkan lakukan di menu Pengadaan->Transaksi->Administrasi Gudang->Approval Barang Masuk
		<br>
		Regards,<br>
		   ".$namaorganisasi[$_SESSION['empl']['lokasitugas']].".
		 </body>
		 </head>
	   </html>";
	 
if($countno > 0)
{
	if(isset($to))
		$kirim = kirimEmail($to, '', $subject, $body);

	if(isset($to2))
		$kirim2 = kirimEmail($to2, '', $subject, $body);
}

?>