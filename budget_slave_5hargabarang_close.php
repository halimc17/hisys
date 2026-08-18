<?php
// file creator: dhyaz aug 10, 2011
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$tahunbudget=$_POST['tahunbudget'];
$regional   =$_POST['regional'];
$kodebarang =$_POST['kodebarang'];
$method     =$_POST['method'];

if($method=='buka'){
	$n='0';
}else{
	$n='1';
}
$str="UPDATE ".$dbname.".`bgt_masterbarang` 
SET `closed` = '".$n."'
WHERE `regional` = '".$regional."' AND `tahunbudget` = '".$tahunbudget."'"; # exit("error".$str);
try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
?>