<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$proses=checkPostGet('proses','');
$kebun=checkPostGet('kebun','');
$divisi=checkPostGet('divisi','');

if($proses=='divisi'){
	if($kebun==''){
		$op="<option value=''></option>";
		$op2="<option value=''></option>";
		echo $op."###".$op2;
		exit();
	}
	$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi
			where kodeorganisasi like '".$kebun."%' and tipe='AFDELING'";
		  $op="<option value=''>".$_SESSION['lang']['all']."</option>";
		  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		  $res->setFetchMode(PDO::FETCH_OBJ);
		  while($bar=$res->fetch()) 
		  {
			  $op.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
		  }
		  
	$str2="select a.nikmandor as nik, b.namakaryawan as nama, b.lokasitugas from ".$dbname.".kebun_aktifitas a
        left join ".$dbname.".datakaryawan b on a.nikmandor=b.karyawanid 
        where a.kodeorg like '%".$kebun."%' and a.nikmandor != ''
        group by a.nikmandor
        order by b.namakaryawan";
      $op2="<option value=''>".$_SESSION['lang']['all']."</option>";
      $res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
	  $res2->setFetchMode(PDO::FETCH_OBJ);
      while($bar2=$res2->fetch()) 
      {
          $op2.="<option value='".$bar2->nik."'>".$bar2->nama."[".$bar2->nik."]</option>";
      }
	
	echo $op."###".$op2;
}else if($proses=='getMandor'){
	$str2="select a.nikmandor as nik, b.namakaryawan as nama, b.lokasitugas from ".$dbname.".kebun_aktifitas a
			left join ".$dbname.".datakaryawan b on a.nikmandor=b.karyawanid
			where a.kodeorg like '".$kebun."%' and b.subbagian like '".$divisi."%' and a.nikmandor != '' 
			group by a.nikmandor
			order by b.namakaryawan";
	$op2="<option value=''>".$_SESSION['lang']['all']."</option>";
	$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
	$res2->setFetchMode(PDO::FETCH_OBJ);
	while($bar2=$res2->fetch()) 
	{
	  $op2.="<option value='".$bar2->nik."'>".$bar2->nama."[".$bar2->nik."]</option>";
	}
		
	echo $op2;
}
?>