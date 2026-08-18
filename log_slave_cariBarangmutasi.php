<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
//=============================================


$method=checkPostGet('method','');
$periode=checkPostGet('periode','');
$gudang=checkPostGet('gudang','');

switch($method){
	case 'getbarang':
	
	echo $str = "select distinct a.kodebarang, b.namabarang 
	from ".$dbname.".log_5saldobulanan a 
	left join log_5masterbarang b on b.kodebarang=a.kodebarang
	where a.periode = '".$periode."'
	and a.kodegudang = '".$gudang."'
    order by a.kodebarang asc";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    $optbrg = '';
    $optbrg .= "<option value=''></option>";
    while ($bar = $res->fetch()) {
        $optbrg.="<option value=" . $bar['kodebarang'] . ">" . $bar['kodebarang'] . " - " . $bar['namabarang'] . "</option>";
    }
	echo $optbrg;
	break;
	default;
}

?>