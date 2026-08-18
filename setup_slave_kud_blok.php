<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

$afdeling=checkPostGet('afdeling','');

$optblok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select t1.kodeorg as kodeorganisasi, t1.kodeorg as namaorganisasi from ".$dbname.".setup_blok t1, ".$dbname.".organisasi t2
	 where t1.kodeorg=t2.kodeorganisasi and t2.tipe='BLOK' and t2.induk='".$afdeling."' and t1.intiplasma='P' 
	 order by t1.kodeorg ASC";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	$optblok.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}

$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUNPLASMA'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optSup="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$res->fetch())
{
	$optSup.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}

echo $optblok."##".$optSup;

?>