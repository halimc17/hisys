<?php
require_once('master_validation.php');
include_once('lib/zLib.php');

#= proxy download supaya nama file yang di-download user rapi (mis. "Laporan_Jurnal_PPPE_2026-07-01_sd_2026-07-30.csv"),
#= terpisah dari nama file penyimpanan internal di tempExcel/ yang sengaja dibikin unik (ada timestamp+uniqid)
#= supaya tidak bentrok kalau ada beberapa proses export jalan bersamaan.

$f    = basename(checkPostGet('f', ''));
$name = checkPostGet('name', 'Laporan.csv');

if($f=='' || !preg_match('/^[A-Za-z0-9_.-]+\.csv\.gz$/', $f)){
	exit('Warning: File tidak valid.');
}

$path = __DIR__.'/tempExcel/'.$f;
if(!is_file($path)){
	exit('Warning: File tidak ditemukan atau sudah kedaluwarsa.');
}

#= bersihkan nama tampilan dari karakter yang bisa merusak header / path
$name = preg_replace('/[\/\\\\\r\n"]+/', '_', $name);
if($name==''){
	$name='Laporan.csv';
}

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="'.$name.'"');
header('Content-Transfer-Encoding: binary');

$gz = gzopen($path, 'rb');
while(!gzeof($gz)){
	echo gzread($gz, 131072);
}
gzclose($gz);
?>
