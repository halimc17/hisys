<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

# Get POST
$ngapain = $_POST['ngapain'];
$noakun = $_POST['noakun'];

$table='';

if($ngapain=='ambilkegiatan'){
    $str="select max(kodekegiatan) as kodekegiatan from ".$dbname.".`setup_kegiatan` where kodekegiatan like '".$noakun."%'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        $max=$bar->kodekegiatan;
    }

    $ambildua=substr($max,-2);
    $ambildua=intval($ambildua)+1;
    if(strlen($ambildua)==1)$kegiatan=$noakun.'0'.$ambildua; else $kegiatan=$noakun.$ambildua;

    $table.=$kegiatan;
	echo $table;
} elseif($ngapain=='getAkun') {
	if($noakun=='8')
	{
		$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',
						  "detail=1 and (noakun like '".$noakun."%') or (noakun like '7%' and detail=1)",'2',true);
	}
	else{
		
	
	$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',
						  "detail=1 and noakun like '".$noakun."%'",'2',true);
	}
	echo json_encode($optAkun);
}