<?php

session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses = checkPostGet('proses', '');
$periode = checkPostGet('periode', '');
$unit = checkPostGet('unit', '');
$tmpPeriod = explode('-',$periode);



switch ($proses) {
	
	case'saldoact':
		
		#= bentuk pindah saldo disini
		#= ambil saldo untuk noakun 115 saja
		
		if($tmpPeriod[1]==12) {
            $bulanLanjut = 1;
            $tahunLanjut = $tmpPeriod[0]+1;
        } else {
            $bulanLanjut = $tmpPeriod[1]+1;
            $tahunLanjut = $tmpPeriod[0];
        }
		
		createSaldoAwal($periode,$tahunLanjut.'-'.addZero($bulanLanjut,2),substr($unit,0,4));
		
	break;
		
    default:
	break;
}

function createSaldoAwal($dariperiode,$keperiode,$kodeorg){
    global $conn;
    global $dbname;
	global $owlPDO;
try {
	$owlPDO->beginTransaction();
	
    $sawal=Array();
    $mtdebet=Array();
    $mtkredit=Array();
    $salak=Array();
	
	$kodeorg = substr($kodeorg,0,4);
	
    #ambil saldoawal bulan berjalan
    $str="select awal".substr($dariperiode,5,2).",noakun from ".$dbname.".keu_saldobulanan
          where periode='".str_replace("-", "", $dariperiode)."' and kodeorg='".$kodeorg."'  and (noakun like '11501%' or noakun like '11504%') ";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_NUM);
	while($bar=$res->fetch()){
        $sawal[$bar[1]]=$bar[0];
        $mtdebet[$bar[1]]=0;
        $mtkredit[$bar[1]]=0;
        $salak[$bar[1]]=$bar[0];
    }
    #ambil transaksi transaksi bln berjalan
    $str="select debet,kredit,noakun from ".$dbname.".keu_jurnalsum_vw 
          where periode='".$dariperiode."' and kodeorg='".$kodeorg."' and (noakun like '11501%' or noakun like '11504%')  ";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch()){
		setIt($sawal[$bar->noakun],0);
        $mtdebet[$bar->noakun]=$bar->debet;
        $mtkredit[$bar->noakun]=$bar->kredit;
        $salak[$bar->noakun]=$mtdebet[$bar->noakun]+$sawal[$bar->noakun]-$mtkredit[$bar->noakun];
    }
   
    #delete saldo awal bulan selanjutnya;
	$str="delete from ".$dbname.".keu_saldobulanan where periode='".str_replace("-", "", $keperiode)."'
          and kodeorg='".$kodeorg."' and (noakun like '11501%' or noakun like '11504%');";
	$owlPDO->exec($str);
	#try{
		#$owlPDO->exec($str);
		#= jalankan ini
		$saldoditahan=0;
        foreach($salak as $key=>$val){
            if($salak[$key]!=''){
                $temp="insert into  ".$dbname.".keu_saldobulanan (kodeorg,periode,noakun,
                      awal".substr($keperiode,5,2).")values('". 
                       $kodeorg."','".str_replace("-", "", $keperiode)."','".$key."',".$salak[$key].")";
                   $owlPDO->exec($temp);
				   /* try{
						$owlPDO->exec($temp);
					}
					catch (PDOException $e) {
					   print " Gagal  !: " . $e->getMessage() . "\n"; 
					   die(); 
					 }*/
              
            }   
        }
	/* }
	catch (PDOException $e) {
	   print " Gagal  !: " . $e->getMessage() . "\n"; 
	   die(); 
	} */
	$owlPDO->commit();
	} catch (PDOException $e) {
	$owlPDO->rollback();
	echo "Error, " . addslashes($e->getMessage());
	die();
}
}

?>