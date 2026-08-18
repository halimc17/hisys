<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method=checkPostGet('method','');
$notransaksi=checkPostGet('notransaksi','');
$bykel=checkPostGet('bykel','');
$bydet=checkPostGet('bydet','');
$byrp=checkPostGet('byrp','');

$nmtipe=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');

switch($method){   

	case 'bysetuju':
		$str="update ".$dbname.".sdm_pjdinasdt set jumlahhrd=jumlah
				where notransaksi='".$notransaksi."'";
        try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
		
		#update di ht
		$str="select sum(jumlahhrd*frekuensi) as jumlah from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jumlah=$bar['jumlah'];
			
		#update ke ht	
		$str="update ".$dbname.".sdm_pjdinasht set uangmuka='".$jumlah."' where notransaksi='".$notransaksi."' ";
        try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
	break;

    
    case 'byganti':
		
		$str="update ".$dbname.".sdm_pjdinasdt set jumlahhrd='".$byrp."'
				where notransaksi='".$notransaksi."' and jenisbiaya='".$bykel."' and detail='".$bydet."' ";
        try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
		
		#update di ht
		$str="select sum(jumlahhrd) as jumlah from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jumlah=$bar['jumlah'];
			
		#update ke ht	
		$str="update ".$dbname.".sdm_pjdinasht set uangmuka='".$jumlah."' where notransaksi='".$notransaksi."' ";
        try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }	
		
		
    break;

   
    
    
	default:
}
?>