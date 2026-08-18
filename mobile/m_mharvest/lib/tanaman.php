<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');




function cekAkun($noakun){
$akunTanaman=array(	'126',
					'128',
					'611',
					'621');

$akun=  substr(str_replace(" ","",$noakun), 0,3);
$akuncip=  substr(str_replace(" ","",$noakun), 0,5);

$default=false;
foreach($akunTanaman as $val)
{
    if($akun==$val){
        $default=true;       
    }
	if($akuncip=='12813'){
		$default=false;     
	}	
}

return $default;
}

function cekAkunPiutang($noakun){
global $dbname;
global $owlPDO;

#= ambil parametar aplikasi	
$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='UM' and	kodeparameter='UMAKUNKB'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();	
	$arrdata=explode(',',$bar['nilai']);
	foreach($arrdata as $key){
		$arr[]=$key;
	}
	$default=false;
	$akun=  substr(str_replace(" ","",$noakun), 0,3);
	if(in_array($akun,$arr)){
		$default=true;
	}
	return $default;
}

function cekAkunHutang($noakun){
$akunHutang=array('211','212');
$akun=  substr(str_replace(" ","",$noakun), 0,3);
$default=false;
foreach($akunHutang as $val)
{

    if($akun==$val){
        $default=true;       
    }
}
return $default;
}
function cekAkunTrans($noakun){
$akunTrans=array('411');
$akun=  substr(str_replace(" ","",$noakun), 0,3);
$default=false;
foreach($akunTrans as $val)
{

    if($akun==$val){
        $default=true;       
    }
}
return $default;
}
?>
