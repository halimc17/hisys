<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses=$_GET['proses'];
$param=$_POST;

	switch ($proses) {
		case 'getNoakun':
		//'113%' or noakun like '114%' or noakun like '211%' or noakun like '118%')
			$optPerd=$optnoakun="<option value=''></option>";			
			$sNoakun="select distinct noakun from ".$dbname.".keu_jurnaldt_vw where 
			          kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."') 
			          and left(noakun,3) in (113,114,116,118,211,129) order by noakun asc";
			//echo $sNoakun;
			//exit('warning');
			$rNoakun=fetchData($sNoakun);
			foreach($rNoakun as $row=>$data){
				$optNmAkun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$data['noakun']."'");
				$optnoakun.="<option value='".$data['noakun']."'>".$data['noakun']." - ".$optNmAkun[$data['noakun']]."</option>";	
			}
			$sPeriodeAkn="select distinct periode from ".$dbname.".setup_periodeakuntansi where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."') order by periode desc";
			$rPeriodeAkn=fetchData($sPeriodeAkn);
			foreach($rPeriodeAkn as $row=>$data){
				$optPerd.="<option value='".$data['periode']."'>".$data['periode']."</option>";	
			}
			echo $optnoakun."####".$optPerd;
		break;
		
	}




?>