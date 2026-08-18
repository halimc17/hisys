<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$to="simamora.hendry@gmail.com";
$subject="[Notifikasi]Persetujuan PP a/n JERRY";
	$body="<html>
			 <head>
			 <body>
			   Dengan Hormat,<br>
			   <br>
			   Pada hari ini, tanggal 22-08-2019 karyawan a/n JERRY mengajukan Permintaan Pembelian Barang
			   kepada bapak/ibu dengan nomor <b>001/08/2019/PR/BPJE</b> Untuk menindak-lanjuti, silahkan ikuti link dibawah.
			   <br>
			   <br>
			   <br>
			   Regards,<br>
			   Owl-Plantation System.
			 </body>
			 </head>
		   </html>
		   ";                                            
$kirim=kirimEmail($to,'',$subject,$body,'text/html');

$subject="[Notifikasi]Persetujuan PO a/n JERRY";
$body="<html>
	<head>
	<body>
		<dd>Dengan Hormat,</dd><br>
		<br>
		Pada hari ini, tanggal 22-08-2019 karyawan a/n JERRY mengajukan Purchase Order (PO) kepada bapak/ibu.
		<br>
		<br>
		Untuk menindak-lanjuti silahkan lakukan di menu MY ACCOUNT->APPROVAL->PO
		<br>
		Regards,<br>
		Owl-Plantation System.
	</body>
	</head>
</html>";
	$kirim = kirimEmail($to, '', $subject, $body,'text/html');

?>