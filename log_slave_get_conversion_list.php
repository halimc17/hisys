<?php

require_once('master_validation.php');
require_once('config/connection.php');

$kelompok = $_POST['kelompok'];
$kode = $_POST['kode'];
$satuan = $_POST['satuan'];

$str = " select * from " . $dbname . ".log_5stkonversi where kodebarang='" . $kode . "' order by jumlah";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no = 0;
while ($bar = $res->fetch()) {
	$no+=1;
	echo"<tr class=rowcontent>
		  <td>" . $no . "</td>
		  <td>" . $bar->darisatuan . "</td>
		  <td>" . $bar->satuankonversi . "</td>
		  <td>" . $bar->jumlah . "</td>
		  <td>" . $bar->keterangan . "</td>
		  <td><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delConversi('" . $bar->kodebarang . "','" . $bar->darisatuan . "','" . $bar->satuankonversi . "');\"></td>
		 </tr>";
}
	
?>