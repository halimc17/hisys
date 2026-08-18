<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
//=============================================

    $blok=$_POST['kodeblok'];
	$opt = "";
	$opt="<option value=''></option>";
	if(empty($blok)){
		
		$str="select * from ".$dbname.".vhc_5master order by kodetraksi,kodevhc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar1=$res->fetch())
		{
			$str="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$bar1->kodebarang."'";
			$res1=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_OBJ);
			$namabarang='';
			while($bar=$res1->fetch())
			{
				$namabarang=$bar->namabarang;
			}
			$opt.="<option value='".$bar1->kodevhc."'>".$bar1->kodevhc." ".($bar1->nopol!=''?"- ".$bar1->nopol:'')." ".($bar1->detailvhc!=''?"- ".$bar1->detailvhc:'')."</option>";
		}
	}else{
		$str = "select * from ".$dbname.".vhc_5master where kodetraksi='".$blok."'";
		$res = fetchData($str);
		if(!empty($res))
			foreach($res as $row) {
				$str="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$row['kodebarang']."'";
				$res1=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res1->setFetchMode(PDO::FETCH_OBJ);
				$namabarang='';
				while($bar=$res1->fetch())
				{
					$namabarang=$bar->namabarang;
				}
				$opt.="<option value='".$row['kodevhc']."'>".$row['kodevhc']." ".($row['nopol']!=''?"- ".$row['nopol']:'')." ".($row['detailvhc']!=''?"- ".$row['detailvhc']:'')."</option>";
			}
	}
		
	echo $opt;