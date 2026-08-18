<?php
require_once('master_validation.php');
require_once('lib/zLib.php');


$proses = checkPostGet('proses', '');
$kodeorg= checkPostGet('kodeorg', '');
$prd    = checkPostGet('prd', '');
$rupiah = checkPostGet('harga', '');


$arrbi  = explode('-',$prd); 
$tahun  = $arrbi[0]; 
$bulan  = $arrbi[1];


switch ($proses) {
    case 'insert':
		if($rupiah>0){			
			$str = "delete from " . $dbname . ".bgt_hargatbs where tahun='" . $tahun . "' and kodeorg='" . $kodeorg . "' ";
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
			
			$str = "insert into " . $dbname . ".bgt_hargatbs (`tahun`, `kodeorg`, `rupiah`,`updateby`)
					values ('" . $tahun . "','" . $kodeorg . "','" . $rupiah . "','".$_SESSION['standard']['userid']."')";
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}
		
	break;
}

?>