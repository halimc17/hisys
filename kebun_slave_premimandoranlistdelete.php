<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


$proses=checkPostGet('proses','');
$unit=checkPostGet('unitlist','');
$afd=checkPostGet('afdlist','');
$jabatan=checkPostGet('jabatanlist','');
$tanggal=checkPostGet('tanggal','');
$tglkontanan=checkPostGet('tglkontanan','');
$karyid=checkPostGet('karyid','');

switch($proses){    
    case'delete':
		#cek apakah periode gaji sudah di tutup
		$str = "select * from ".$dbname.".sdm_5periodegaji where periode like '" . substr($tanggal,0,7) . "' and kodeorg='".$unit."'  ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$jumdata+=$bar['sudahproses'];
		}
			if($jumdata>0){
				exit("Warning : Periode Gaji " . substr($tanggal,0,7) . " untuk Unit ".$unit." sudah di tutup !");
			}
		
		#cek apakah periode akutansi sudah di tutup
		$str = "select * from ".$dbname.".setup_periodeakuntansi where periode like '" . substr($tanggal,0,7) . "' and kodeorg='".$unit."'  ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$jumdata+=$bar['tutupbuku'];
		}
			if($jumdata>0){
				exit("Warning : Periode akuntansi " . substr($tanggal,0,7) . " untuk Unit ".$unit." sudah di tutup !");
			}
		
        $str = "delete from ".$dbname.".kebun_premikemandoran where kodeorg='".$unit."' and periode='".$tanggal."' and karyawanid='".$karyid."' and jabatan='".$jabatan."' and tanggalkontanan='".$tglkontanan."' ";
		
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
	break;
	 
	default:
}



?>