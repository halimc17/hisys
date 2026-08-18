<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
include_once('lib/zFunction.php');
$proses     =checkPostGet('proses','');
$prdlist    =checkPostGet('prdlist','');
$unitlist   =checkPostGet('unitlist','');
$afdlist    =checkPostGet('afdlist','');
$divisi    =checkPostGet('divisi','');
$notransaksi=checkPostGet('notransaksi','');
$prd        =checkPostGet('prd','');
$unit       =checkPostGet('unit','');
$tipe       =checkPostGet('tipe','');
$nikkar     =makeOption($dbname,'datakaryawan','karyawanid,nik');
$nmorg      =makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$jab        =getPostingJabatan('premipanen');
$tglEntry   =date('Ymd'); 
switch($proses){
	
	default:
}
?>