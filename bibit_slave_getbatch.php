<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');

$kodeorg=checkPostGet('kodeorg','');
$tipe=checkPostGet('tipe','');

$hasil='';
 
if($tipe=='batch')
{
    $str="select distinct a.batch from ".$dbname.".bibitan_batch a
        left join ".$dbname.".bibitan_mutasi b on a.batch=b.batch
        where b.kodeorg like '".$kodeorg."%'
        order by a.batch";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    $hasil="<option value=''>".$_SESSION['lang']['all']."</option>";
    while($bar=$res->fetch())
    {
        $hasil.="<option value='".$bar->batch."'>".$bar->batch."</option>";
    }    
}else{
    
}

echo $hasil;
?>