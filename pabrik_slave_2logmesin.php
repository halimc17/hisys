<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$unit=checkPostGet('unit','');
$tgl1=tanggalsystemn(checkPostGet('tgl1',''));
$tgl2=tanggalsystemn(checkPostGet('tgl2',''));

$nmklas=makeOption($dbname,'pabrik_5logmesin_klasifikasi','kode,nama');
$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');


$arrnmklasifikasi=array("HU"=>"HEATING UP","PR"=>"PROSES","CN"=>"COOLING DOWN","BN"=>"BREAKDOWN","QP"=>"QUALITY PARAMETER");

// echo"<pre>";
// print_r($listdataht);
// echo"</pre>";



########################################################################################
#############prepare data
########################################################################################

$arrtgl=rangeTanggalarr($tgl1,$tgl2);

##produksi
$str="select * from ".$dbname.".pabrik_produksi where tanggal between '".$tgl1."' and '".$tgl2."' and kodeorg='".$unit."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){ 
	$tbs[$bar['tanggal']]=$bar['tbsdiolahnetto'];
	$cpo[$bar['tanggal']]=$bar['oer'];
	$pk[$bar['tanggal']]=$bar['oerpk'];
}

$str="select * from ".$dbname.".pabrik_pengolahan where tanggal between '".$tgl1."' and '".$tgl2."' and kodeorg='".$unit."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){ 
	@$lori[$bar['tanggal']]+=$bar['jumlahlori'];
}

$str="select * from ".$dbname.".pabrik_logmesin where tanggal between '".$tgl1."' and '".$tgl2."' and station like '".$unit."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){ 
	
	$arrklasifikasi[$bar['klasifikasi']]=$bar['klasifikasi'];
	$listklasifikasi[$bar['station']][$bar['klasifikasi']]=$bar['klasifikasi'];
	$start[$bar['tanggal']][$bar['station']][$bar['klasifikasi']]=$bar['start'];
	$stop[$bar['tanggal']][$bar['station']][$bar['klasifikasi']]=$bar['stop'];
	$jam[$bar['tanggal']][$bar['station']][$bar['klasifikasi']]=$bar['jam'];
}


#arr station
$str="select * from ".$dbname.".pabrik_5mr_list_station where  kode_station like '".$unit."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){ 
	$arrstation[$bar['kode_station']]=$bar['kode_station'];
}

$listqp=array();

$str="select * from ".$dbname.".pabrik_5mr_parameter_nilai where  kode_station like '".$unit."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){ 
	// $arrstation[$bar['kode_station']]=$bar['kode_station'];
	// $arrklasifikasi[$bar['kode_nilai']]=$bar['kode_nilai'];
	// $listklasifikasi[$bar['kode_station']][$bar['kode_nilai']]=$bar['kode_nilai'];
	$arrqp[$bar['kode_nilai']]=$bar['kode_nilai'];
	$nmarrqp[$bar['kode_nilai']]=$bar['nama'];
	$listqp[$bar['kode_station']][$bar['kode_nilai']]=$bar['kode_nilai'];
	$setupqp[$bar['kode_station']][$bar['kode_nilai']]=$bar['standard_nilai'];
	@$spanqp[$bar['kode_station']]+=1;
	
}

// echo"<pre>";
// print_r($spanqp);
// echo"<pre>";
@$carrstation=count($arrstation);
@$carrklasifikasi=count($arrklasifikasi);

if($carrstation==0 || $carrklasifikasi==0){
	exit("Warning:Data Kosong");
}

foreach($arrstation as $station){
	foreach($arrklasifikasi as $klasifikasi){
		if($listklasifikasi[$station][$klasifikasi]!=''){
			@$spanklasifikasi[$station]+=1;
			@$tspan+=$spanklasifikasi[$station];
		}
	}
}

#ambil kapasitas lori
$str="select sum(jumlahlori) as jumlahlori,tanggal from ".$dbname.".pabrik_pengolahan where  
		tanggal between '".$tgl1."' and '".$tgl2."' and kodeorg='".$unit."' group by tanggal ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){ 
	$jumlori[$bar['tanggal']]=$bar['jumlahlori'];
}

$str="select tbsdiolahnetto,tanggal,kadarkotoranpk,kadarairpk from ".$dbname.".pabrik_produksi where  
		tanggal between '".$tgl1."' and '".$tgl2."' and kodeorg='".$unit."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){ 
	$tbsolah[$bar['tanggal']]=$bar['tbsdiolahnetto'];
	$dataqp[$bar['tanggal']][$unit.'06']['MPK']=$bar['kadarairpk'];
	$dataqp[$bar['tanggal']][$unit.'06']['DPK']=$bar['kadarkotoranpk'];
}

#sumber dari pabrik_mr_roa (loses)
$str="select a.unit,b.kode_station as station,a.tanggal,a.parameter,a.nilai 
		from ".$dbname.".pabrik_mr_roa a left join ".$dbname.".pabrik_5mr_parameter_nilai b 
		on a.parameter=b.kode_nilai and a.unit=substr(b.kode_station,1,4) 
		where tanggal between '".$tgl1."' and '".$tgl2."' and unit='".$unit."' and kode_station is not null ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){ 
	$dataqp[$bar['tanggal']][$bar['station']][$bar['parameter']]=$bar['nilai'];
}

#sumber dari pabrik_mr_bfwt (loses)
$str="select a.unit,b.kode_station as station,a.tanggal,a.kode,a.nilaiph 
		from ".$dbname.".pabrik_mr_bfwt a left join ".$dbname.".pabrik_5mr_parameter_nilai b 
		on a.kode=b.kode_nilai and a.unit=substr(b.kode_station,1,4) 
		where tanggal between '".$tgl1."' and '".$tgl2."' and unit='".$unit."' and kode_station is not null ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){ 
	$dataqp[$bar['tanggal']][$bar['station']][$bar['kode']]=$bar['nilaiph'];
	if($bar['nilaiph']>0){//untuk pembagi mencari avg di boiler
		@$cdataqp[$bar['station']][$bar['kode']]+=1;
	}
}


foreach($arrtgl as $tgl){
	//lori
	@$dataqp[$tgl][$unit.'02']['LOR']=$tbsolah[$tgl]/$jumlori[$tgl];
}


// echo"<pre>";
// print_r($dataqp);
// echo"<pre>";

//$spanstation=count($arrstation);
########################################################################################
#############tampilkan data
########################################################################################

if($proses=='excel'){
	$border='border=1';
}else{
	$border='border=0';
}

echo"<pre>";
$stream="";


$stream.="<table cellspacing=1 class=sortable cellpadding=1 ".$border.">";
$stream.="<thead>";

$stream.="<tr>";
$stream.="<td rowspan=4 align=center>".$_SESSION['lang']['tanggal']."</td>";
$stream.="<td rowspan=4 align=center>".$_SESSION['lang']['hari']."</td>";
$stream.="<td colspan=4 align=center>".$_SESSION['lang']['produksi']."</td>";
foreach($arrstation as $station){
	$stream.="<td align=center colspan='".(($spanklasifikasi[$station]*3)+$spanqp[$station]+4)."'>".$station." - ".$nmorg[$station]."</td>";
}
$stream.="</tr>";


$stream.="<tr>";
$stream.="<td rowspan=2 align=center>".$_SESSION['lang']['tbs']."</td>";
$stream.="<td rowspan=2 align=center>".$_SESSION['lang']['jumlahlori']."</td>";
$stream.="<td rowspan=2 align=center>".$_SESSION['lang']['cpo']."</td>";
$stream.="<td rowspan=2 align=center>".$_SESSION['lang']['kernel']."</td>";
if(isset($arrstation))
foreach($arrstation as $station){
	if(isset($arrklasifikasi))
	foreach($arrklasifikasi as $klasifikasi){
		if($listklasifikasi[$station][$klasifikasi]!=''){
			$stream.="<td align=center colspan=3>".$arrnmklasifikasi[$klasifikasi]."</td>";
		}
	}
	$stream.="<td align=center colspan='".$spanqp[$station]."'>QP</td>";
	$stream.="<td align=center colspan='4'>NILAI</td>";
}
$stream.="</tr>";

$stream.="<tr>";
if(isset($arrstation))
foreach($arrstation as $station){
	if(isset($arrklasifikasi))
	foreach($arrklasifikasi as $klasifikasi){
		if($listklasifikasi[$station][$klasifikasi]!=''){
			$stream.="<td align=center>Start</td>";
			$stream.="<td align=center>".$_SESSION['lang']['stop']."</td>";
			$stream.="<td align=center>".$_SESSION['lang']['total']."</td>";
		}
	}
	if(isset($arrqp))
	foreach($arrqp as $qp){
		if(@$listqp[$station][$qp]!=''){
			$stream.="<td align=center>".$qp." - ".$nmarrqp[$qp]."</td>";
		}
	}
	$stream.="<td align=center rowspan=2>PE</td>";
	$stream.="<td align=center rowspan=2>MA</td>";
	$stream.="<td align=center rowspan=2>RQ</td>";
	$stream.="<td align=center rowspan=2>OEE</td>";
}

$stream.="</tr>";

		



$stream.="<tr>";
$stream.="<td align=center>".$_SESSION['lang']['kg']."</td>";
$stream.="<td align=center>Lori</td>";
$stream.="<td align=center>".$_SESSION['lang']['kg']."</td>";
$stream.="<td align=center>".$_SESSION['lang']['kg']."</td>";
if(isset($arrstation))
foreach($arrstation as $station){
	if(isset($arrklasifikasi))
	foreach($arrklasifikasi as $klasifikasi){
		if($listklasifikasi[$station][$klasifikasi]!=''){
			$stream.="<td align=center>(HH:MM)</td>";
			$stream.="<td align=center>(HH:MM)</td>";
			$stream.="<td align=center>(Jam)</td>";
		}
	}
	if(isset($arrqp))
	foreach($arrqp as $qp){
		if(@$listqp[$station][$qp]!=''){
			$stream.="<td align=center>".$setupqp[$station][$qp]."</td>";
		}
	}
	
}
$stream.="</tr>";
$stream.="</thead>";


if(isset($arrtgl))
foreach($arrtgl as $tgl){
	$stream.="<tr class=rowcontent>";
	$stream.="<td>".tanggalnormal($tgl)."</td>";
	$stream.="<td>".hari($tgl,'ID')."</td>";
	$stream.="<td align=right>".@number_format($tbs[$tgl])."</td>";
	$stream.="<td align=right>".@number_format($lori[$tgl])."</td>";
	$stream.="<td align=right>".@number_format($cpo[$tgl])."</td>";
	$stream.="<td align=right>".@number_format($pk[$tgl])."</td>";
	
	
		@$ttbs+=$tbs[$tgl];
		@$tcpo+=$cpo[$tgl];
		@$tpk+=$pk[$tgl];
		@$tlori+=$lori[$tgl];
	if(isset($arrstation))
	foreach($arrstation as $station){
		if(isset($arrklasifikasi))
		foreach($arrklasifikasi as $klasifikasi){
			if($listklasifikasi[$station][$klasifikasi]!=''){
					$stream.="<td align=left>".@$start[$tgl][$station][$klasifikasi]."</td>";
					$stream.="<td align=left>".@$stop[$tgl][$station][$klasifikasi]."</td>";
					$stream.="<td align=right>".@number_format($jam[$tgl][$station][$klasifikasi],2)."</td>";
					
					@$tjam[$station][$klasifikasi]+=$jam[$tgl][$station][$klasifikasi];
					
			}
		}
		if(isset($arrqp))
		foreach($arrqp as $qp){
			if(@$listqp[$station][$qp]!=''){
 				$stream.="<td align=right>".@$dataqp[$tgl][$station][$qp]."</td>";
				
				#untuk ambil sumproduct rumus
				@$datarqtbs[$station][$qp]+=$dataqp[$tgl][$station][$qp]*$tbs[$tgl];
				
				
				@$tdatarq[$station][$qp]+=$dataqp[$tgl][$station][$qp];
				
			}
		}
		
		
		@$pe[$tgl][$station]=$jam[$tgl][$station]['PR']/($jam[$tgl][$station]['HU']+$jam[$tgl][$station]['PR']+$jam[$tgl][$station]['CN'])*100;
		@$ma[$tgl][$station]=($jam[$tgl][$station]['PR']-$jam[$tgl][$station]['BN'])/$jam[$tgl][$station]['PR']*100;
		
		
		######untuk RQ
		
		#loading ramp 02
		@$rq[$tgl][$unit.'02']=$dataqp[$tgl][$unit.'02']['LOR']/$setupqp[$unit.'02']['LOR']*100;
		if($rq[$tgl][$unit.'02']>100){
			$rq[$tgl][$unit.'02']=100;
		}
		
		#STERILIZER 03
		@$rqcb[$tgl][$unit.'03']=$setupqp[$unit.'03']['CB']/$dataqp[$tgl][$unit.'03']['CB']*100;
		@$rqab[$tgl][$unit.'03']=$setupqp[$unit.'03']['AB']/$dataqp[$tgl][$unit.'03']['AB']*100;
		if($rqcb[$tgl][$unit.'03']>100){
			$rqcb[$tgl][$unit.'03']=100;
		}
		if($rqab[$tgl][$unit.'03']>100){
			$rqab[$tgl][$unit.'03']=100;
		}
		@$rqcb[$tgl][$unit.'03']=$rqcb[$tgl][$unit.'03']*0.5;
		@$rqab[$tgl][$unit.'03']=$rqab[$tgl][$unit.'03']*0.5;//pengali ini
		@$rq[$tgl][$unit.'03']=$rqcb[$tgl][$unit.'03']+$rqab[$tgl][$unit.'03'];
		
		
		#THRESER 04
		@$rqaa[$tgl][$unit.'04']=$setupqp[$unit.'04']['AA']/$dataqp[$tgl][$unit.'04']['AA']*100;
		@$rqba[$tgl][$unit.'04']=$setupqp[$unit.'04']['BA']/$dataqp[$tgl][$unit.'04']['BA']*100;
		if($rqaa[$tgl][$unit.'04']>100){
			$rqaa[$tgl][$unit.'04']=100;
		}
		if($rqba[$tgl][$unit.'04']>100){
			$rqba[$tgl][$unit.'04']=100;
		}
		@$rqaa[$tgl][$unit.'04']=$rqaa[$tgl][$unit.'04']*0.5;
		@$rqba[$tgl][$unit.'04']=$rqba[$tgl][$unit.'04']*0.5;//pengali ini
		@$rq[$tgl][$unit.'04']=$rqaa[$tgl][$unit.'04']+$rqba[$tgl][$unit.'04'];
		
		
		#PRESS 05
		@$rqac[$tgl][$unit.'05']=$setupqp[$unit.'05']['AC']/$dataqp[$tgl][$unit.'05']['AC']*100;
		@$rqad[$tgl][$unit.'05']=$setupqp[$unit.'05']['AD']/$dataqp[$tgl][$unit.'05']['AD']*100;
		if($rqac[$tgl][$unit.'05']>100){
			$rqac[$tgl][$unit.'05']=100;
		}
		if($rqad[$tgl][$unit.'05']>100){
			$rqad[$tgl][$unit.'05']=100;
		}
		@$rqac[$tgl][$unit.'05']=$rqac[$tgl][$unit.'05']*0.5;
		@$rqad[$tgl][$unit.'05']=$rqad[$tgl][$unit.'05']*0.5;//pengali ini
		@$rq[$tgl][$unit.'05']=$rqac[$tgl][$unit.'05']+$rqad[$tgl][$unit.'05'];
		
		
		#KERNEL 06
		@$rqmpk[$tgl][$unit.'06']=$setupqp[$unit.'06']['MPK']/$dataqp[$tgl][$unit.'06']['MPK']*100;
		@$rqdpk[$tgl][$unit.'06']=$setupqp[$unit.'06']['DPK']/$dataqp[$tgl][$unit.'06']['DPK']*100;
		@$rqbb[$tgl][$unit.'06']=$setupqp[$unit.'06']['BB']/$dataqp[$tgl][$unit.'06']['BB']*100;
		@$rqbc[$tgl][$unit.'06']=$setupqp[$unit.'06']['BC']/$dataqp[$tgl][$unit.'06']['BC']*100;
		@$rqbd[$tgl][$unit.'06']=$setupqp[$unit.'06']['BD']/$dataqp[$tgl][$unit.'06']['BD']*100;
		
		if($rqmpk[$tgl][$unit.'06']>100){
			$rqmpk[$tgl][$unit.'06']=100;
		}
		if($rqdpk[$tgl][$unit.'06']>100){
			$rqdpk[$tgl][$unit.'06']=100;
		}
		if($rqbb[$tgl][$unit.'06']>100){
			$rqbb[$tgl][$unit.'06']=100;
		}
		if($rqbc[$tgl][$unit.'06']>100){
			$rqbc[$tgl][$unit.'06']=100;
		}
		if($rqbd[$tgl][$unit.'06']>100){
			$rqbd[$tgl][$unit.'06']=100;
		}
		
		@$rqmpk[$tgl][$unit.'06']=$rqmpk[$tgl][$unit.'06']*0.2;
		@$rqdpk[$tgl][$unit.'06']=$rqdpk[$tgl][$unit.'06']*0.2;
		@$rqbb[$tgl][$unit.'06']=$rqbb[$tgl][$unit.'06']*0.2;
		@$rqbc[$tgl][$unit.'06']=$rqbc[$tgl][$unit.'06']*0.2;
		@$rqbd[$tgl][$unit.'06']=$rqbd[$tgl][$unit.'06']*0.2;
		
		$rq[$tgl][$unit.'06']=$rqmpk[$tgl][$unit.'06']+$rqdpk[$tgl][$unit.'06']+$rqbb[$tgl][$unit.'06']+$rqbc[$tgl][$unit.'06']+$rqbd[$tgl][$unit.'06'];
		
		
		#CLARIFICATION 07
		@$rqae[$tgl][$unit.'07']=$setupqp[$unit.'07']['AE']/$dataqp[$tgl][$unit.'07']['AE']*100;
		@$rqaf[$tgl][$unit.'07']=$setupqp[$unit.'07']['AF']/$dataqp[$tgl][$unit.'07']['AF']*100;
		if($rqae[$tgl][$unit.'07']>100){
			$rqae[$tgl][$unit.'07']=100;
		}
		if($rqaf[$tgl][$unit.'07']>100){
			$rqaf[$tgl][$unit.'07']=100;
		}
		@$rqae[$tgl][$unit.'07']=$rqae[$tgl][$unit.'07']*0.5;
		@$rqaf[$tgl][$unit.'07']=$rqaf[$tgl][$unit.'07']*0.5;//pengali ini
		@$rq[$tgl][$unit.'07']=$rqae[$tgl][$unit.'07']+$rqaf[$tgl][$unit.'07'];
		
		
		
		#BOILER 09
		@$rqdpwt001[$tgl][$unit.'09']=$setupqp[$unit.'09']['DPWT001']/$dataqp[$tgl][$unit.'09']['DPWT001']*100;
		@$rqdpwt009[$tgl][$unit.'09']=$setupqp[$unit.'09']['DPWT009']/$dataqp[$tgl][$unit.'09']['DPWT009']*100;
		@$rqpowt001[$tgl][$unit.'09']=$setupqp[$unit.'09']['POWT001']/$dataqp[$tgl][$unit.'09']['POWT001']*100;
		@$rqpowt009[$tgl][$unit.'09']=$setupqp[$unit.'09']['POWT009']/$dataqp[$tgl][$unit.'09']['POWT009']*100;
		@$rqpowt007[$tgl][$unit.'09']=$setupqp[$unit.'09']['POWT007']/$dataqp[$tgl][$unit.'09']['POWT007']*100;
		@$rqpowt002[$tgl][$unit.'09']=$setupqp[$unit.'09']['POWT002']/$dataqp[$tgl][$unit.'09']['POWT002']*100;
		
		if($rqdpwt001[$tgl][$unit.'09']>100){
			$rqdpwt001[$tgl][$unit.'09']=100;
		}
		if($rqdpwt009[$tgl][$unit.'09']>100){
			$rqdpwt009[$tgl][$unit.'09']=100;
		}
		if($rqpowt001[$tgl][$unit.'09']>100){
			$rqpowt001[$tgl][$unit.'09']=100;
		}
		if($rqpowt009[$tgl][$unit.'09']>100){
			$rqpowt009[$tgl][$unit.'09']=100;
		}
		if($rqpowt007[$tgl][$unit.'09']>100){
			$rqpowt007[$tgl][$unit.'09']=100;
		}
		if($rqpowt002[$tgl][$unit.'09']>100){
			$rqpowt002[$tgl][$unit.'09']=100;
		}
		
		$rqdpwt001[$tgl][$unit.'09']=$rqdpwt001[$tgl][$unit.'09']*0.2;
		$rqdpwt009[$tgl][$unit.'09']=$rqdpwt009[$tgl][$unit.'09']*0.2;
		$rqpowt001[$tgl][$unit.'09']=$rqpowt001[$tgl][$unit.'09']*0.2;
		$rqpowt009[$tgl][$unit.'09']=$rqpowt009[$tgl][$unit.'09']*0.2;
		$rqpowt007[$tgl][$unit.'09']=$rqpowt007[$tgl][$unit.'09']*0.2;
		$rqpowt002[$tgl][$unit.'09']=$rqpowt002[$tgl][$unit.'09']*0.2;
		
		$rq[$tgl][$unit.'09']=$rqdpwt001[$tgl][$unit.'09']+$rqdpwt009[$tgl][$unit.'09']+$rqpowt001[$tgl][$unit.'09']+$rqpowt009[$tgl][$unit.'09']+$rqpowt007[$tgl][$unit.'09']+$rqpowt002[$tgl][$unit.'09'];
		
		
		#THRESER 04-boiler sama saja rumusnya... yg membedakan pengali 0.5 , 0.2
		#sama dengan 03 rumusnya copas saja ganti cb/ab-nya
		#bisa dibuat dinamis.. yg beda hanya loading ramp
		#
		
		
		
		$oee[$tgl][$station]=$pe[$tgl][$station]*($ma[$tgl][$station]/100)*($rq[$tgl][$station]/100);
		// print_r($pe);
		$stream.="<td align=right>".@number_format($pe[$tgl][$station],2)."</td>";
		$stream.="<td align=right>".@number_format($ma[$tgl][$station],2)."</td>";
		$stream.="<td align=right>".@number_format($rq[$tgl][$station],2)."</td>";
		$stream.="<td align=right>".@number_format($oee[$tgl][$station],2)."</td>";
		
		$str=" delete from ".$dbname.".`pabrik_logmesin_oee` where station='".$station."' and tanggal='".$tgl."'";
		try{$owlPDO->exec($str); }
		catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}

		$str=" INSERT INTO ".$dbname.".`pabrik_logmesin_oee` (`station`, `tanggal`, `pe`, `ma`, `rq`,`oee`,`updateby`)
		values ('".$station."','".$tgl."','".$pe[$tgl][$station]."','".$ma[$tgl][$station]."','".$rq[$tgl][$station]."',
		'".$oee[$tgl][$station]."','".$_SESSION['standard']['userid']."')";
		try{$owlPDO->exec($str); }
		catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	}
	
	$stream.="</tr>";
}




$stream.="<thead><tr>";
$stream.="<td align=center colspan=2>".$_SESSION['lang']['total']."</td>";
$stream.="<td align=right>".@number_format($ttbs)."</td>";
$stream.="<td align=right>".@number_format($tlori)."</td>";
$stream.="<td align=right>".@number_format($tcpo)."</td>";
$stream.="<td align=right>".@number_format($tpk)."</td>";

if(isset($arrstation))
foreach($arrstation as $station){
		if(isset($arrklasifikasi))
		foreach($arrklasifikasi as $klasifikasi){
			if($listklasifikasi[$station][$klasifikasi]!=''){
					$stream.="<td align=left>-</td>";
					$stream.="<td align=left>-</td>";
					$stream.="<td align=right>".@number_format($tjam[$station][$klasifikasi],2)."</td>";
			}
		}
		if(isset($arrqp))
		foreach($arrqp as $qp){
			if(@$listqp[$station][$qp]!=''){
				if($station==$unit.'09'){
					$stream.="<td align=right>".@number_format($tdatarq[$station][$qp]/$cdataqp[$station][$qp],2)."</td>";
				}else{
					$stream.="<td align=right>".@number_format($datarqtbs[$station][$qp]/$ttbs,2)."</td>";
				}
			}
		}
		$stream.="<td align=right></td>";
		$stream.="<td align=right></td>";
		$stream.="<td align=right></td>";
		$stream.="<td align=right></td>";
}



$stream.="</tr></thead>";

$stream.="</table>";







$stream.="<tbody></table>";
switch($proses){
######PREVIEW
    case 'preview':
        echo $stream;
    break;

######EXCEL	
    case 'excel':
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg=date("Ymd");
        $nop_="laporan_oee_".$unit;
        if(strlen($stream)>0)
        {
                if ($handle = opendir('tempExcel')) {
                        while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && $file != "index.html") {
                                @unlink('tempExcel/'.$file);
                        }
                        }	
                        closedir($handle);
                }
                $handle=fopen("tempExcel/".$nop_.".xls",'w');
                if(!fwrite($handle,$stream))
                {
                        echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                        exit;
                }
                else
                {
                        echo "<script language=javascript1.2>
                        window.location='tempExcel/".$nop_.".xls';
                        </script>";
                }
                fclose($handle);
        }     
        break;	
}
?>