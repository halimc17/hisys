<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zFunction.php');
require_once('lib/zLib.php');
//=============================================
if(isTransactionPeriod()){
	//check if transaction period is normal
    $blok=$_POST['blok'];
	$sData="select * from ".$dbname.".setup_blok where indukblok like '".$blok."%' limit 1 ";
	$rData=fetchdata($sData);
	if(count($rData)>0){
		// status blok
		$optstatusblok="<option value=''></option>"; 
		$str_s="select statusblok from ".$dbname.".setup_blok group by statusblok ";
		$res_s=$owlPDO->query($str_s) or die(print " Gagal: ".PDOException::getMessage());
		$res_s->setFetchMode(PDO::FETCH_OBJ);
		while($bar_s=$res_s->fetch()){
			$optstatusblok.="<option value='".$bar_s->statusblok."'>".$bar_s->statusblok."</option>";
		}
	}else{
		$optstatusblok="<option value=''></option>"; 
	}

	$induk=$_POST['induk'];
	$subunitx=$_POST['subunitx'];
	$untukunit = checkPostGet('untukunit','');
	$tanggal=tanggalsystemn(checkPostGet('tanggal',''));
	$periode=substr($tanggal,0,7);
	
	$optVTipe = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$induk."'");
	$optVRegional = makeOption($dbname,'bgt_regional_assignment','kodeunit,regional',"kodeunit='".$untukunit."'");
	
	if($optVTipe[$induk] == 'SIPIL'){
		$blehh="<option value=''></option>";
		$str="select norumah,keterangan from ".$dbname.".sdm_perumahanht where kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional = '".$optVRegional[$untukunit]."') order by norumah ASC";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch()){
			$blehh.="<option value='".$bar->norumah."'>".$bar->norumah."-".$bar->keterangan."</option>";
		}
	}else{
		$blehh="<option value=''></option>";
		$wh='';
		if(strlen($induk)>4){
			// $wh=" and kodeorganisasi not in (select kodeorg from ".$dbname.".setup_blok where luasareaproduktif<='0')";
			if(strlen($blok) > 3){
				$wh=" and kodeorganisasi not in (select kodeorg from ".$dbname.".setup_blok where luasareaproduktif<='0')";
			}else{
				$wh=" and kodeorganisasi in (select kodeorg from ".$dbname.".setup_blok where luasareaproduktif > '0' and statusblok = '".$blok."' )";
			}
		}
		
		// $wh_statusblok="";
		// if(strlen($blok) > 3){
			// $wh_statusblok="";
		// }else{
		// 	$wh_statusblok=" and kodeorganisasi in (select kodeorg from ".$dbname.".setup_blok where statusblok = '".$blok."' )";
		// }

		// $str="select distinct kodeorganisasi,namaorganisasi,tipe from ".$dbname.".organisasi where induk='".$induk."' and tipe not like '%gudang%' ".$wh." order by kodeorganisasi"; #exit("error".$str);
		if(strlen($induk) == '6'){
			$str="select distinct kodeorganisasi,namaorganisasi,tipe,indukblok,namaindukblok from ".$dbname.".organisasi where induk='".$induk."' and tipe not like '%gudang%' ".$wh." ".$wh_statusblok." group by indukblok order by kodeorganisasi"; #exit("error".$str);
		}else{
			$str="select distinct kodeorganisasi,namaorganisasi,tipe,indukblok,namaindukblok from ".$dbname.".organisasi where induk='".$induk."' and tipe not like '%gudang%' ".$wh." ".$wh_statusblok." order by kodeorganisasi"; #exit("error".$str);
		}


		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch()){
			$ind = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar->kodeorganisasi."'");
			$d=$ind[$bar->kodeorganisasi];
			if($d!=$n){			
				$blehh.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
			}
			
			// cek tipe
			if($bar->tipe == 'BLOK'){
				if(($bar->indukblok) == $subunitx){
					$blehh.="<option value='".$bar->indukblok."' selected>".$bar->indukblok." - ".$bar->namaindukblok."</option>";
				}else{
					$blehh.="<option value='".$bar->indukblok."'>".$bar->indukblok." - ".$bar->namaindukblok."</option>";
				} 
			}else{
				if(($bar->kodeorganisasi) == $subunitx){
					$blehh.="<option value='".$bar->kodeorganisasi."' selected>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
				}else{
					$blehh.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
				} 
			}
			
			$n=$d;
			if($d!=$n){			
				$blehh.="</optgroup>";
			}
			
		}
	}
	
	#ambil project
	if(substr($induk,0,2)=='AK' or substr($induk,0,2)=='PB'){
		$blehh='';
		$str="select kode,nama from ".$dbname.".project where kode='".$induk."'"; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch()){
			if(($bar->kode) == $subunitx){
				$blehh.="<option value='".$bar->kode."' selected>".$bar->kode."-".$bar->nama."</option>";
			}else{
				$blehh.="<option value='".$bar->kode."'>".$bar->kode."-".$bar->nama."</option>";
			}
		}
		$sData="select kodepabrikasi,namapabrikasi from ".$dbname.".pabrikasi_5masterht where kodepabrikasi='".$induk."' and status=1 order by namapabrikasi asc";
	    $rData=fetchdata($sData);
	    foreach ($rData as $isiPabrikasi) {
	        if($isiPabrikasi['kodepabrikasi']==$subunitx){
	         	$blehh.="<option value=" . $isiPabrikasi['kodepabrikasi'] . " selected>" . $isiPabrikasi['kodepabrikasi'] . " - " . $isiPabrikasi['namapabrikasi'] . "</option>";
	     	}else{
	     		$blehh.="<option value=" . $isiPabrikasi['kodepabrikasi'] . ">" . $isiPabrikasi['kodepabrikasi'] . " - " . $isiPabrikasi['namapabrikasi'] . "</option>";
	     	}
	    }          
   }elseif(substr($induk,0,1)=='S' && strlen($induk) > 6 ){
		$blehh='';
		$str="select notransaksi,project from ".$dbname.".lgl_pengajuanspkht where koderekanan='".$induk."'"; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch()){
			$blehh.="<option value='".$bar->notransaksi."'>".$bar->notransaksi."-".$bar->project."</option>";
			
		}          
   }else{
	    $sData="select kodepabrikasi,namapabrikasi from ".$dbname.".pabrikasi_5masterht where kodeorg='".$induk."' and status=1 order by namapabrikasi asc";
	    $rData=fetchdata($sData);
	    foreach ($rData as $isiPabrikasi) {
	        if($isiPabrikasi['kodepabrikasi']==$subunitx){
	         	$blehh.="<option value=" . $isiPabrikasi['kodepabrikasi'] . " selected>Pabrikasi :" . $isiPabrikasi['kodepabrikasi'] . " - " . $isiPabrikasi['namapabrikasi'] . "</option>";
	     	}else{
	     		$blehh.="<option value=" . $isiPabrikasi['kodepabrikasi'] . ">Pabrikasi :" . $isiPabrikasi['kodepabrikasi'] . " - " . $isiPabrikasi['namapabrikasi'] . "</option>";
	     	}
	    }          
	}
	
	#ambil project
	$str="select kode,nama from ".$dbname.".project where kodeorg='".$induk."' and posting=0";   #exit("error".$str); 
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	if(count(fetchdata($str))>0){
		$blehh.="<optgroup label='PROJECT'>";
	}
	while($bar=$res->fetch()){
		if(($bar->kode) == $subunitx){
			$blehh.="<option value='".$bar->kode."' selected>".$bar->kode."-".$bar->nama."</option>";
		}else{
			$blehh.="<option value='".$bar->kode."'>".$bar->kode." - ".$bar->nama."</option>";
		}
	}
	
	if(count(fetchdata($str))>0){
		$blehh.="</optgroup>";
	}
	#tambahan untuk kontraktor
	#update untuk solar yang akan masuk ke piutang kontraktor
	$str="select * from ".$dbname.".log_5supplier a
		left join ".$dbname.".log_5supkelompok b on a.supplierid = b.supplierid 
		where b.tipe='KONTRAKTOR' and b.status=1"; 
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	if(count(fetchdata($str))>0){
		$blehh.="<optgroup label='KONTRAKTOR'>";
	}
	
	while($bar=$res->fetch()){
		if(($bar->supplierid) == $subunitx){
			$blehh.="<option value='".$bar->supplierid."' selected>".$bar->namasupplier."</option>";
		}else{
			$blehh.="<option value='".$bar->supplierid."'>".$bar->supplierid." - ".$bar->namasupplier."</option>";
		}
	}
	
	if(count(fetchdata($str))>0){
		$blehh.="</optgroup>";
	}
	
	$str = "SELECT * FROM " . $dbname . ".log_5supplier a
			left join log_5supkelompok b on a.supplierid=b.supplierid
			where a.status=1 and b.tipe in ('SUPPLIERTBSKUD') order by a.namasupplier asc";
	if(count(fetchdata($str))>0){
		$blehh.="<optgroup label='KUD TBS'>";
	}

	#penambahan SUPPLIERTBSEXT	
	$str = "SELECT * FROM " . $dbname . ".log_5supplier a
			left join log_5supkelompok b on a.supplierid=b.supplierid
			where a.status=1 and b.tipe in ('SUPPLIERTBSEXT') order by a.namasupplier asc";
	if(count(fetchdata($str))>0){
		$blehh.="<optgroup label='TBS EKS'>";
	}

	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while ($bar = $res->fetch()) {
		if(($bar->supplierid) == $subunitx){
			$blehh.="<option value='".$bar->supplierid."' selected>".$bar->namasupplier."</option>";
		}else{
			$blehh.="<option value='".$bar->supplierid."'>".$bar->supplierid." - ".$bar->namasupplier."</option>";
		}
	}
	if(count(fetchdata($str))>0){
		$blehh.="</optgroup>";
	}
	
	$str = "SELECT * FROM " . $dbname . ".log_5supplier a
			left join log_5supkelompok b on a.supplierid=b.supplierid
			where a.status=1 and b.tipe in ('SUPPLIERTBS') order by a.namasupplier asc";
	if(count(fetchdata($str))>0){
		$blehh.="<optgroup label='SUPPLIER TBS'>";
	}			
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while ($bar = $res->fetch()) {
		if(($bar->supplierid) == $subunitx){
			$blehh.="<option value='".$bar->supplierid."' selected>".$bar->namasupplier."</option>";
		}else{
			$blehh.="<option value='".$bar->supplierid."'>".$bar->supplierid." - ".$bar->namasupplier."</option>";
		}
	}
	if(count(fetchdata($str))>0){
		$blehh.="</optgroup>";
	}
	
	$vtipeorg = "";
	if($induk == ''){
		$vtipeorg = "";
	}else{
		$opttipeorg = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi = '".$induk."'");
		$vtipeorg = $opttipeorg[$induk];
	}
	
	$opt="<option value=''></option>";
	
	#= cek apakah vhc_5master_hist terisi atau tidak
	$str = "select count(*) as jumlah from ".$dbname.".vhc_5master_hist where kodetraksi like '".$_POST['induk']."%' and status='1' and periode='".$periode."'"; 
	// exit("Warningsistem:".$str);
	$res = fetchData($str);
	foreach($res as $row) {
		$jumlah=$row['jumlah'];
	}
	
	if($jumlah>0){
		$str = "select * from ".$dbname.".vhc_5master_hist where kodetraksi like '".$_POST['induk']."%' and status='1' and periode='".$periode."'"; 
		$res = fetchData($str);
		foreach($res as $row) {
			$opt.="<option value='".$row['kodevhc']."'>".$row['kodevhc']." ".($row['nopol']!=''?"- ".$row['nopol']:'')." ".($row['detailvhc']!=''?"- ".$row['detailvhc']:'')."</option>";
		}
	}else{
		$str = "select * from ".$dbname.".vhc_5master where kodetraksi like '".$_POST['induk']."%' and status='1'"; 
		$res = fetchData($str);
		foreach($res as $row) {
			$opt.="<option value='".$row['kodevhc']."'>".$row['kodevhc']." ".($row['nopol']!=''?"- ".$row['nopol']:'')." ".($row['detailvhc']!=''?"- ".$row['detailvhc']:'')."</option>";
		}	
	}
	
	// echo $blehh."####".$vtipeorg."####".$opt;

	echo $optstatusblok."####".$blehh."####".$vtipeorg."####".$opt;

}else{
	echo " Error: Transaction Period missing";
}
?>