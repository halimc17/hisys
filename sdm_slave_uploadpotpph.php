<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$tmplkodeorg = checkPostGet('tmplkodeorg', '');
$tmplperiode = checkPostGet('tmplperiode', '');


switch ($proses) {

	case 'getperiode':
		$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$sorg="select periode from ".$dbname.".setup_periodeakuntansi where kodeorg='".$tmplkodeorg."' order by periode desc";
		$qorg=$owlPDO->query($sorg) or die(print " Gagal: ".PDOException::getMessage());
		$qorg->setFetchMode(PDO::FETCH_ASSOC);
		while($rorg=$qorg->fetch())
		{   
		    $optOrg.="<option value='".$rorg['periode']."'>".$rorg['periode']."</option>";
		}

		echo $optOrg;
	break;

	case'download':
		header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=uploadpotonganpph.csv");

		echo $_SESSION['lang']['periode'].",".$_SESSION['lang']['unit'].",".$_SESSION['lang']['nik'].",".$_SESSION['lang']['namakaryawan'].",".$_SESSION['lang']['jumlah']."\n";

		$str="select * from ".$dbname.".sdm_gaji where kodeorg='".$tmplkodeorg."' and periodegaji='".$tmplperiode."' and  idkomponen='42'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($rDt=$res->fetch()){

			$strkr="select nik,namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$rDt['karyawanid']."'";
			$reskr=$owlPDO->query($strkr) or die(print " Gagal: ".PDOException::getMessage());
			$reskr->setFetchMode(PDO::FETCH_ASSOC);
			$barkr=$reskr->fetch();

			echo $tmplperiode.",".$tmplkodeorg.",'".$barkr['nik'].",".$barkr['namakaryawan']."\n";
		}

	break;
	
	default:
		# code...
		break;
}



?>