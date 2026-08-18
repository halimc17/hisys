<?php
// file creator: dhyaz aug 10, 2011
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$tahunbudget=$_POST['tahunbudget'];
$kodeorg=$_POST['kodeorg'];
$kodegolongan=$_POST['kodegolongan'];
$upah=$_POST['upah'];

$str="DELETE FROM ".$dbname.".bgt_upah WHERE tahunbudget='".$tahunbudget."' AND kodeorg='".$kodeorg."' AND golongan='".$kodegolongan."'";
try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }

$str="INSERT INTO ".$dbname.".`bgt_upah` (
`tahunbudget` ,
`kodeorg` ,
`golongan` ,
`jumlah` ,
`updateby` ,
`lastupdate`
)
VALUES (
'".$tahunbudget."', '".$kodeorg."', '".$kodegolongan."', '".$upah."' , '".$_SESSION['standard']['userid']."',
CURRENT_TIMESTAMP 
)";
try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
?>
