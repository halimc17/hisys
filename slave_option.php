<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');


$proses = checkPostGet('proses', '');
$kdUnit = checkPostGet('kdUnit', '');
$kdorg = checkPostGet('kdorg', '');
$unit = checkPostGet('unit', '');
$pt = checkPostGet('pt', '');
$pt2 = checkPostGet('pt2', '');
$afd = checkPostGet('afd', '');
$jenis = checkPostGet('jenis', '');
$hasil = checkPostGet('hasil', '');
$sumber = checkPostGet('sumber', '');
$klp = checkPostGet('klp', '');
$inti = checkPostGet('inti', '');


switch ($proses) {
	case'getKegiatan':
		// $optbyy="<option value=''>".$_SESSION['lang']['all']."</option>";
		$str = "select * from " . $dbname . ".setup_kegiatan  where 1=1 and substr(noakun,1,3) in ('611','621') and noakun like '".$klp."%'  and namakegiatan not like '%NON AKTIF%' and namakegiatan not like '%TIDAK DIPAKAI%'"; //exit("error".$str);
		$res=fetchdata($str);
		foreach($res as $bar){
			$d=substr($bar['noakun'],0,3);
			if($d!=$n){			
				$optbyy.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
			}
			$e=$bar['noakun'];
			if($e!=$m){			
				$optbyy.="<optgroup label='".$e." - ".getNamaAkun($e)."'>";
			}
			$optbyy.="<option value=" . $bar['kodekegiatan'] . ">".$bar['kodekegiatan']." - " . $bar['namakegiatan'] . "</option>";
			$m=$e;
			if($e!=$m){			
				$optbyy.="</optgroup>";
			}
			$n=$d;
			if($d!=$n){			
				$optbyy.="</optgroup>";
			}
		}
		echo $optbyy;
	break;
	case'getdataunit':
		$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where CHAR_LENGTH(kodeorganisasi)='4' and induk='".$sumber."' order by induk, tipe asc ";
		//exit('error'.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optDiv.="<option value=''>".$_SESSION['lang']['all']."</option>";
		while ($bar = $res->fetch()) {			
			$optDiv.="<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - ".$bar['namaorganisasi']."</option>";
		}
	echo $optDiv;
	break;
	case'getBlok':
		$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='BLOK' and induk='".$afd."' and namaorganisasi not like '%NONAKTIF%' order by kodeorganisasi asc ";
		#exit('error'.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optDiv.="<option value=''>".$_SESSION['lang']['all']."</option>";
		while ($bar = $res->fetch()) {
			$optDiv.="<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - ".$bar['namaorganisasi']."</option>";
		}
	echo $optDiv;
	break;
	case 'getklbyy':
	
		if($sumber=='BBT'){
			$whereakun = " and substr(noakun,1,3) in ('128')";
		}elseif($sumber=='TBM'){
			$whereakun = " and substr(noakun,1,3) in ('126')";
		}elseif($sumber=='TM'){
			$whereakun = " and substr(noakun,1,3) in ('621')";
		}elseif($sumber=='PNN'){
			$whereakun = " and substr(noakun,1,3) in ('611')";
		}
		
		$str = "select * from " . $dbname . ".keu_5akun  where 1=1 ".$whereakun." and length(noakun)=5"; 
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optklp="<option value=''>".$_SESSION['lang']['all']."</option>";
		while ($bar = $res->fetch()) {
			$optklp.="<option value=" . $bar['noakun'] . ">" . $bar['noakun'] . " - " . $bar['namaakun'] . "</option>";
		}

		echo $optklp;
	break;
	
	case 'getkeg':
		$str="select * from ".$dbname.".setup_kegiatan where noakun like '".$sumber."%' and status=1 order by kodekegiatan"; 
		#exit("error".$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrakun[$bar['kodekegiatan']]=$bar['kodekegiatan'];
		}
		
        $str="select distinct kodekegiatan from ".$dbname.".kebun_prestasi where kodekegiatan like '".$sumber."%' and length(kodekegiatan)>7 order by kodekegiatan"; 
		#exit("error".$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$arrakun[$bar['kodekegiatan']]=$bar['kodekegiatan'];
			
		}
		
		$str="select distinct kodekegiatan from ".$dbname.".keu_jurnaldt where kodekegiatan like '".$sumber."%'"; 
		#exit("error".$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$arrakun[$bar['kodekegiatan']]=$bar['kodekegiatan'];
		}
		
		$nmakun=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
		$opt="<option value=''>" .$_SESSION['lang']['all']. "</option>";
		foreach(@$arrakun as $akun){
			$opt.="<option value=" . $akun. ">" . $akun. " - " . $nmakun[$akun] . "</option>";
		}
		
		echo $opt;
	break;
	case 'getnoakun':
		#ambil dari keu_5akun
		
		$whereakun = " and substr(noakun,1,3) in ('611','621','126','128')";
		$str="select * from ".$dbname.".keu_5akun where noakun like '".$sumber."%' ".$whereakun." and length(noakun)=7 and namaakun not like '%NON AKTIF%' order by noakun"; 
		#exit("error".$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrakun[$bar['noakun']]=$bar['noakun'];
		}
		
		#ambil dari jurnal karena ada akun yang non aktip tapi ada isinya
        $str="select distinct noakun from ".$dbname.".keu_jurnaldt where noakun like '".$sumber."%' ".$whereakun." order by noakun"; 
		#exit("error".$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrakun[$bar['noakun']]=$bar['noakun'];
			
		}
		
		$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
		$opt="<option value=''>" .$_SESSION['lang']['all']. "</option>";
		foreach($arrakun as $akun){
			$opt.="<option value=" . $akun. ">" . $akun. " - " . $nmakun[$akun] . "</option>";
		}
		
		echo $opt;
	break;
	case'getkegiatan':
		$keg="";
		$str = "select * from " . $dbname . ".setup_kegiatan where noakun like '".$sumber."%' and status='1' order by kodekegiatan asc ";
		#exit('error'.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($hasil=='pilihdata'){
			$keg.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		}elseif($hasil=='all'){
			$keg.="<option value=''>".$_SESSION['lang']['all']."</option>";
		}else{
			$keg.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		}
		while ($bar = $res->fetch()) {
			$keg.="<option value='" . $bar['kodekegiatan'] . "'>" . $bar['kodekegiatan'] . " - ".$bar['namakegiatan']."</option>";
		}
		if($sumber==''){
			$keg="<option value=''>".$_SESSION['lang']['all']."</option>";
		}
	echo $keg;
	break;
	case'getakun':
		$akun="";
		$str = "select * from " . $dbname . ".keu_5akun where length(noakun) = 7 and noakun like '".$sumber."%' and namaakun not like '%NON AKTIF%' order by noakun asc ";
		#exit('error'.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($hasil=='pilihdata'){
			$akun.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		}elseif($hasil=='all'){
			$akun.="<option value=''>".$_SESSION['lang']['all']."</option>";
		}else{
			$akun.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		}
		while ($bar = $res->fetch()) {
			$akun.="<option value='" . $bar['noakun'] . "'>" . $bar['noakun'] . " - ".$bar['namaakun']."</option>";
		}
		if($sumber==''){
			$akun="<option value=''>".$_SESSION['lang']['all']."</option>";
		}
		
	echo $akun;
	break;
	case'getEstate':
		if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING'){
			$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where LENGTH(kodeorganisasi)='4' and induk='".$pt."' order by kodeorganisasi asc ";
		}else if(trim($_SESSION['empl']['tipelokasitugas'])=='KANWIL'){
			$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi in (select distinct kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."') order by namaorganisasi";
		}else{
			$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where LENGTH(kodeorganisasi)='4' and induk='".$pt."' and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by kodeorganisasi asc ";
		}
		#exit('error'.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($hasil=='pilihdata'){
			$optDiv.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		}elseif($hasil=='all'){
			$optDiv.="<option value=''>".$_SESSION['lang']['all']."</option>";
		}else{
			$optDiv.="<option value=''>".$_SESSION['lang']['all']."</option>";
		}
		while ($bar = $res->fetch()) {
			$optDiv.="<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - ".$bar['namaorganisasi']."</option>";
		}
	echo $optDiv;
	break;
	case'getEstateRKB':
		$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where LENGTH(kodeorganisasi)='4' and induk='".$pt."' and tipe ='KEBUN' order by kodeorganisasi asc ";
		// exit('error'.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optDiv.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		while ($bar = $res->fetch()) {
			$optDiv.="<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - ".$bar['namaorganisasi']."</option>";
		}
	echo $optDiv;
	break;
	case'getdivisi2':
		$optDiv="";
		$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe in ('BIBITAN','AFDELING','STATION') and induk='".$kdUnit."' order by kodeorganisasi asc ";
		#exit('error'.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($hasil=='pilihdata'){
			$optDiv.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		}elseif($hasil=='all'){
			$optDiv.="<option value=''>".$_SESSION['lang']['all']."</option>";
		}else{
			$optDiv.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		}
		while ($bar = $res->fetch()) {
			$optDiv.="<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - ".$bar['namaorganisasi']."</option>";
		}
		
	echo $optDiv;
	break;
	case'getdivisi':
		$optDiv="";
		$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe in ('BIBITAN','AFDELING','STATION') and induk='".$kdUnit."' order by kodeorganisasi asc ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optDiv.="<option value=''>".$_SESSION['lang']['all']."</option>";
		if($jenis=='UMUM'){
			$optDiv.="<option value='UMUM'>UMUM - UMUM / KANTOR</option>";
		}
		while ($bar = $res->fetch()) {
			$optDiv.="<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - ".$bar['namaorganisasi']."</option>";
		}
		
	echo $optDiv;
	break;
	case'getdivisi2':
		$optDiv="";
		$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe in ('BIBITAN','AFDELING','STATION') and induk='".$kdUnit."' order by kodeorganisasi asc ";
		#exit('error'.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($hasil=='pilihdata'){
			$optDiv.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		}elseif($hasil=='all'){
			$optDiv.="<option value=''>".$_SESSION['lang']['all']."</option>";
		}else{
			$optDiv.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		}
		while ($bar = $res->fetch()) {
			$optDiv.="<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - ".$bar['namaorganisasi']."</option>";
		}
		
	echo $optDiv;
	break;

	case'getdivisi3':
		$optDiv="";
		$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe in ('BIBITAN','AFDELING','STATION','GUDANG','TRAKSI','WORKSHOP','SIPIL') and induk='".$kdUnit."' order by kodeorganisasi asc ";
		// exit('error'.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($hasil=='pilihdata'){
			$optDiv.="<option value=''>UMUM</option>";
		}elseif($hasil=='all'){
			$optDiv.="<option value=''>".$_SESSION['lang']['all']."</option>";
		}else{
			$optDiv.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		}
		while ($bar = $res->fetch()) {
			$optDiv.="<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - ".$bar['namaorganisasi']."</option>";
		}
		
	echo $optDiv;
	break;
	
			
	case'getDiv':
		$Div="";
		$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe in('AFDELING','STATION','KEBUN','PABRIK','TRAKSI','WORKSHOP','KANWIL','SIPIL') and kodeorganisasi like '".$unit."%' order by kodeorganisasi asc ";
		

		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$Div.="<option value=''>".$_SESSION['lang']['all']."</option>";
		while ($bar = $res->fetch()) {
			$Div.="<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - ".$bar['namaorganisasi']."</option>";
		}
		
	echo $Div;
	break;
	
	//BKM vs Finger Print
	case 'changediv':
	$optDiv="";
	$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='AFDELING' and induk='".$unit."' order by kodeorganisasi asc ";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$optDiv.="<option value=''>".$_SESSION['lang']['all']."</option>";
	while ($bar = $res->fetch()) {
		$optDiv.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - ".$bar['namaorganisasi']."</option>";
	}
	
	echo $optDiv;
	break;
	case'getUnitRegional':
		$optthntanam=$optorg.="<option value=''>".$_SESSION['lang']['all']."</option>";
		
		$str="select * from ".$dbname.".bgt_regional_assignment where subregional='".$pt."'";
		$res = fetchData($str);
		foreach($res as $bar){
			if(getNamaOrg($bar['kodeunit'],'tipe')=='KEBUN'){					
				if($inti!=''){
					if(getNamaOrg($bar['kodeunit'],'inti')==$inti){					
						$optorg.="<option value='".$bar['kodeunit']."'>".$bar['kodeunit']." - ".getNamaOrg($bar['kodeunit'])."</option>";
					}
				}else{				
					$optorg.="<option value='".$bar['kodeunit']."'>".$bar['kodeunit']." - ".getNamaOrg($bar['kodeunit'])."</option>";
				}
			}	
		}
	echo $optorg;
	break;
	
	case'getUnitThnTnm':
		$optthntanam=$optorg.="<option value=''>".$_SESSION['lang']['all']."</option>";
		if($inti!=''){
			$whr="and inti='".$inti."'";	
		}

		// $optorg.= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='KEBUN' and induk ='".$pt."' ".$whr,'2','0',true);
		foreach(getOrgDetail(23) as $key => $val){
			$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
			$d=$induk[$key];
			if($d!=$n){			
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
				$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
			}
			$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
			$n=$d;
			if($d!=$n){			
				$optorg.="</optgroup>";
			}
		}

		if($pt!=''){
			$whr="and induk='".$pt."'";	
		}
		
		$sData="select distinct tahuntanam from ".$dbname.".setup_blok a left join ".$dbname.".organisasi b 
		        on left(a.kodeorg,4)=b.kodeorganisasi where b.tipe='KEBUN' and tahuntanam!=0 ".$whr." order by tahuntanam asc";
		$rData=fetchdata($sData);
		foreach($rData as $row=>$isData){
			$optthntanam.="<option value='".$isData['tahuntanam']."'>".$isData['tahuntanam']."</option>";
		}
	echo $optorg."####".$optthntanam;
	break;
	
	case'getAfdThnTnm':
		$optThnTnm=$optDiv.="<option value=''>".$_SESSION['lang']['all']."</option>";
		$optDiv.= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe in ('AFDELING','BIBITAN') and induk ='".$kdorg."' ",'2','0',true);
		
		if($kdorg!=''){
			$whr="and induk='".$kdorg."'";	
		}
		
		$sData="select distinct tahuntanam from ".$dbname.".setup_blok a left join ".$dbname.".organisasi b 
		        on left(a.kodeorg,6)=b.kodeorganisasi where b.tipe='AFDELING' and tahuntanam!=0 ".$whr." order by tahuntanam asc"; 
		$rData=fetchdata($sData);
		foreach($rData as $row=>$isData){
			$optThnTnm.="<option value='".$isData['tahuntanam']."'>".$isData['tahuntanam']."</option>";
		}
		
	echo $optDiv."####".$optThnTnm;
	break;
	
	case'getUnit':
		$tipe="KEBUN";
	    if($_POST['tipe']!=''){
	    	$tipe=$_POST['tipe'];
	    }
		$optorg="";
		$optorg.="<option value=''>".$_SESSION['lang']['all']."</option>";
		$optorg.= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='".$tipe."' and induk ='".$pt."' ",'2','0',true);
	echo $optorg;
	break;
	case'getAfdeling':
		$tipe="AFDELING";
	    if($_POST['tipe']!=''){
	    	$tipe=$_POST['tipe'];
	    }
		$optorg="";
		$optorg.="<option value=''>".$_SESSION['lang']['all']."</option>";
		$optorg.= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='".$tipe."' and induk ='".$unit."' ",'2','0',true);
	echo $optorg;
	break;
	case'getThnTnm':
		$optTt.="<option value=''>".$_SESSION['lang']['all']."</option>";
		// $optTt.= makeOption($dbname,'setup_blok','tahuntanam,tahuntanam',"left(kodeorg,6) ='".$afd."' ",'0','0',true);
		// $optTt.= makeOption($dbname,'bgt_blok','thntnm,thntnm',"left(kodeblok,6) ='".$afd."' ",'0','0',true);
		
		$sData="select distinct tahuntanam from ".$dbname.".setup_blok where tahuntanam!=0 and left(kodeorg,6) ='".$afd."' order by tahuntanam asc"; 
		$rData=fetchdata($sData);
		foreach($rData as $row=>$isData){
			$data[$isData['tahuntanam']]=$isData['tahuntanam'];
		}
		
		$sData="select distinct thntnm from ".$dbname.".bgt_blok where thntnm!=0 and left(kodeblok,6) ='".$afd."' order by thntnm asc"; 
		$rData=fetchdata($sData);
		foreach($rData as $row=>$isData){
			$data[$isData['thntnm']]=$isData['thntnm'];
		}
		
		foreach($data as $tt){
			$optTt.="<option value='".$tt."'>".$tt."</option>";
		}
		
		
	echo $optTt;
	break;
	
	case'getThnTnm2':
		if($afd!=''){
			$whr=" tahuntanam!=0 and left(kodeorg,6) ='".$afd."'";
		}else{
			$whr=" tahuntanam!=0 and left(kodeorg,4) ='".$kdorg."'";	
		}
		$optTt.="<option value=''>".$_SESSION['lang']['all']."</option>";
		$optTt.= makeOption($dbname,'setup_blok','tahuntanam,tahuntanam',$whr,'0','0',true);
		
	echo $optTt;
	break;
	
	case'getUnit2':
		$optorg="";
		$optorg.="<option value=''>".$_SESSION['lang']['all']."</option>";
		$optorg.= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='KEBUN' and induk ='".$pt2."' ",'2','0',true);
	echo $optorg;
	break;
	case'getUnitLapPremi':
		$optorg="";
		if($_SESSION['empl']['tipelokasitugas']=='KEBUN'){
			$optorg.= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."' and tipe='KEBUN' and induk ='".$pt."' ",'2','0',true);
		}else{
			$optorg.="<option value=''>".$_SESSION['lang']['all']."</option>";
			$optorg.= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='KEBUN' and induk ='".$pt."' ",'2','0',true);
		}

	echo $optorg;
	break;
	
	case'getunit':
		$optDiv="";
		$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe like 'GUDANG%' and alokasi='".$kdUnit."' order by kodeorganisasi asc ";
		
		//exit('error '.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optDiv.="<option value=''>".$_SESSION['lang']['all']."</option>";
		while ($bar = $res->fetch()) {
			$optDiv.="<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - ".$bar['namaorganisasi']."</option>";
		}
		
	echo $optDiv;
	break;
	
	case'getkodevhsunit':
		$optvhc="";
		$str = "select * from " . $dbname . ".vhc_5master where kodetraksi like '".$kdUnit."%' order by kodevhc asc ";
		
		//exit('error '.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optvhc.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		while ($bar = $res->fetch()) {
			
			$e="";
			if(getNopol($bar['kodevhc'])!=''){
				$e.= " - ".getNopol($bar['kodevhc']);
			}
			if(getNopol($bar['kodevhc'],'d')!=''){
				$e.= " - ".getNopol($bar['kodevhc'],'d');
			}
			$optvhc.="<option value='" . $bar['kodevhc'] . "'>" . $bar['kodevhc'].$e. "</option>";
		}
		
	echo $optvhc;
	break;

	case 'getUnitGaji':

		$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 and induk='".$pt."' order by namaorganisasi asc ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$optorg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		}

        echo $optorg;
	break;
	
}
?>