<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$unit=checkPostGet('unit','');

switch($proses){
	case 'getdivisi':

		$optdivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
		if(strlen($_SESSION['empl']['subbagian'])==''){
		$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$unit."%' 
		and tipe in ('AFDELING','BIBITAN','KEBUN','STATION','SIPIL','PABRIK','TRAKSI','WORKSHOP','MAINTENANCE') and length(kodeorganisasi)=6
		order by kodeorganisasi asc";
		$optdivisi.="<option value='".$unit."'>".$_SESSION['lang']['kantor']." / ".$_SESSION['lang']['umum']."</option>";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){	
				$optdivisi.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			}
		}
		else
		{
		$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$_SESSION['empl']['subbagian']."' 
		and tipe in ('AFDELING','BIBITAN','KEBUN','STATION','SIPIL','PABRIK','TRAKSI','WORKSHOP','MAINTENANCE') and length(kodeorganisasi)=6
		order by kodeorganisasi asc";	
		$optdivisi="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){	
				$optdivisi.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			}
		}
					
			echo $optdivisi;
			break;
}
?>