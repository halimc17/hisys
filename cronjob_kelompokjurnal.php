<?php
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$persekarang = date("Y-m");
$periodeberikut = nextperiode($persekarang);
$str="select * from ".$dbname.".keu_5kelompokjurnal where periode='".$persekarang."' ";
$res=fetchdata($str);
foreach($res as $val){
	$strx="select count(kodeorg) as jlhitem from ".$dbname.".keu_5kelompokjurnal where 
		kodeorg='".$val['kodeorg']."' and
		kodeunit='".$val['kodeunit']."' and
		kodekelompok='".$val['kodekelompok']."' and
		periode='".$periodeberikut."'";
	$resx=fetchdata($strx);
	$jlhitem=$resx[0]['jlhitem'];
	if($jlhitem <=0){
		$stri="insert into ".$dbname.".keu_5kelompokjurnal (kodeorg, kodeunit, kodekelompok, periode, keterangan, nokounter)
			   values('".$val['kodeorg']."','".$val['kodeunit']."','".$val['kodekelompok']."','".$periodeberikut."','".$val['keterangan']."','0')";
		$owlPDO->exec($stri); 
	}
}

function nextperiode($per){   
	$thnIni=substr($per,0,4);
	$blnIni=substr($per,5,2);
   
	if($blnIni=='12'){       
		$blnBerikut=1;
		$thnBerikut=$thnIni+1;
	}else{  
		$blnBerikut=$blnIni+1; 
		$thnBerikut=$thnIni;
	}

	if(strlen($blnBerikut)<2){
		$blnBerikut="0".$blnBerikut;
	}
  
	$perBerikut=$thnBerikut."-".$blnBerikut;
	return $perBerikut;
}
?>