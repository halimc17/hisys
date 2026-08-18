<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zFunction.php');
//=============================================

//check if transaction period is normal

if(isTransactionPeriod()){
	$gudang=$_POST['gudang'];
    
	$str="select induk from ".$dbname.".organisasi where kodeorganisasi = '".substr($gudang,0,4)."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
		$ptgudang=$bar->induk;
	}
	
	$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi = '".$ptgudang."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
		$namaptgudang=$bar->namaorganisasi;
	}
	
	$blehh="<option value='".$ptgudang."'>".$namaptgudang."</option>";
	echo $blehh;
}else{
	echo " Error: Transaction Period missing";
}
?>