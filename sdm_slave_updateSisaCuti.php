<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$method = checkPostGet('method','');
$kodeorg = checkPostGet('kodeorg','');
$karyawanid = checkPostGet('karyawanid','');
$periode = checkPostGet('periode','');
$sisa = checkPostGet('sisa','');
$hakcuti = checkPostGet('hakcuti','');
$diambil = checkPostGet('diambil','');

switch($method)
{
	case 'updatehakcuti':
		if($hakcuti < $diambil)
		{
			exit("Gagal : Jumlah hak cuti harus lebih besar atau sama dengan sisa cuti");
		}
		
		$sisa = $hakcuti - $diambil;
		$str="update ".$dbname.".sdm_cutiht set 
			sisa=".$sisa.",hakcuti=".$hakcuti."
			where kodeorg='".$kodeorg."' and karyawanid=".$karyawanid." and periodecuti='".$periode."'";
		try
		{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e) 
		{
			print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
		}
		echo $sisa;
	break;
	
	case 'updatesisacuti':
		#cek dulu data hak dan diambil
		$str=" select * from ".$dbname.".sdm_cutiht where 
				kodeorg='".$kodeorg."'
				and karyawanid=".$karyawanid."
				and periodecuti='".$periode."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$hakcuti=$bar['hakcuti'];
			$diambil=$bar['diambil'];
			
		if($diambil==0){
			$str="update ".$dbname.".sdm_cutiht 
			  set sisa=".$sisa.",hakcuti=".$sisa."
			 where 
			  kodeorg='".$kodeorg."'
			  and karyawanid=".$karyawanid."
			  and periodecuti='".$periode."'";	  
			try{
				$owlPDO->exec($str); 
			}catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
			}
		}else{
			$str="update ".$dbname.".sdm_cutiht 
			  set sisa=".$sisa."
			 where 
			  kodeorg='".$kodeorg."'
			  and karyawanid=".$karyawanid."
			  and periodecuti='".$periode."'";	  
			try{
				$owlPDO->exec($str); 
			}catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
			}
		}
	break;
}
?>