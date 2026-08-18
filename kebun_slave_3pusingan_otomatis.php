<?php
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

function seminggulalu($tgl){
	#membuat tanggal kemarin dari parameter kiriman
	#$tgl format : 2015-12-25;
	$tgl=str_replace('-','',$tgl);
	$newdate = strtotime('- 15 day',strtotime($tgl));
	$newdate = date('Y-m-d', $newdate);
	return $newdate;
}

$kdorg=checkPostGet('kdorg','');
$tgl1 =tanggalsystemn(checkPostGet('tgl1',''));
$tgl2 =tanggalsystemn(checkPostGet('tgl2',''));
if($tgl1=='--' or $tgl2=='--'){	
	$tgl2 = date('Y-m-d');
	$tgl1 = seminggulalu($tgl2);
}

$rangetanggal = rangeTanggal($tgl1, $tgl2);
$wh="";
if($kdorg!=''){
	$wh="and divisi like '".$kdorg."%'";
}

				
$str="select distinct(substr(divisi,1,4)) as kdorg from ".$dbname.".kebun_rekappnn_vw where tanggal between '".$tgl1."' and '".$tgl2."' ".$wh."";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$no='0';
while($bar=$res->fetch()){
	$kdorganisasi[$bar['kdorg']]=$bar['kdorg'];
}

foreach($kdorganisasi as $kdorg){
	$no++;
	$tglkemarinlusa = strtotime('-4 day',strtotime($tgl1));
	$tglkemarinlusa = date('Y-m-d', $tglkemarinlusa);
	
	$tglkemarin = strtotime('-1 day',strtotime($tgl1));
	$tglkemarin = date('Y-m-d', $tglkemarin);
	
	$str = "select * from ".$dbname.".setup_blok where kodeorg like '".$kdorg."%'";
	$res = fetchdata($str);
	foreach($res as $val){
		$luasblok[$val['kodeorg']]+=$val['luasareaproduktif'];
	}
	#ambil data dari kebun_pusingan
	$str=" select * from ".$dbname.".kebun_pusingan where blok like '".$kdorg."%' "
			. " and tanggal = '".$tglkemarin."'  order by blok asc ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$datakmrn[$bar['blok']][$bar['tanggal']]=$bar['angka'];
	}

	#bentuk data blok dari rekap panen
	$str="select distinct(blok) as blok,divisi,tahuntanam from ".$dbname.".kebun_rekappnn_vw where "
			. " divisi like '".$kdorg."%' and  tanggal between '".$tgl1."' and '".$tgl2."' order by blok asc ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$kdblok[$bar['blok']]=$bar['blok'];
	}
	
	
	$str="select distinct(blok) as blok,divisi,tahuntanam from ".$dbname.".kebun_pusingan_vw where "
			. " unit = '".$kdorg."'  order by blok asc ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$kdblok[$bar['blok']]=$bar['blok'];
	}
	
	
	#bentuk data panen
	$luaspanen=array();
	$str=" select * from ".$dbname.".kebun_rekappnn_vw where divisi like '".$kdorg."%' "
			. " and tanggal between '".$tglkemarinlusa."' and '".$tgl2."' order by blok asc ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$data[$bar['blok']][$bar['tanggal']]=array('panen'=>'P');
		@$angka[$bar['blok']][$bar['tanggal']]=0;
	}
	
	$noblok=0;
	foreach($kdblok as $blok){
		$noblok+=1;
		foreach($rangetanggal as $listtanggal => $tgl){

			########cara hitung tanggal kemarin###############
			$tglkemarin = strtotime('-1 day',strtotime($tgl));
			$tglkemarin = date('Y-m-d', $tglkemarin);

			$tglkemarinlusa = strtotime('-2 day',strtotime($tgl));
			$tglkemarinlusa = date('Y-m-d', $tglkemarinlusa);
			
			$tgltigaharilalu = strtotime('-3 day',strtotime($tgl));
			$tgltigaharilalu = date('Y-m-d', $tgltigaharilalu);
			
			$tglempatharilalu = strtotime('-4 day',strtotime($tgl));
			$tglempatharilalu = date('Y-m-d', $tglempatharilalu);
			
			$tgllimaharilalu = strtotime('-5 day',strtotime($tgl));
			$tgllimaharilalu = date('Y-m-d', $tgllimaharilalu);
			
			$tglenamharilalu = strtotime('-6 day',strtotime($tgl));
			$tglenamharilalu = date('Y-m-d', $tglenamharilalu);
			
			$tgltujuhharilalu = strtotime('-7 day',strtotime($tgl));
			$tgltujuhharilalu = date('Y-m-d', $tgltujuhharilalu);
			
			$tgldelapanharilalu = strtotime('-8 day',strtotime($tgl));
			$tgldelapanharilalu = date('Y-m-d', $tgldelapanharilalu);
			
			$tglsembilanharilalu = strtotime('-9 day',strtotime($tgl));
			$tglsembilanharilalu = date('Y-m-d', $tglsembilanharilalu);
			
			$tgl10harilalu = strtotime('-10 day',strtotime($tgl));
			$tgl10harilalu = date('Y-m-d', $tgl10harilalu);
			
			$luaspanen=array();
			$str = "select * from ".$dbname.".kebun_pusingan where blok = '".$blok."' and angka='1' and tanggal <= '".$tgl."' order by tanggal desc limit 1";
			$res = fetchdata($str);
			foreach($res as $val){
				$sql = "select sum(luaspanen) as luaspanen, blok, tanggal from ".$dbname.".kebun_rekappnn_vw where blok = '".$blok."' and tanggal between '".$val['tanggal']."' and '".$tgl."'";
				$req = fetchdata($sql);
				foreach($req as $bar){
					$luaspanen[$bar['blok']][$tgl]+=$bar['luaspanen'];
				}
			}
			
			
			
			$bloklama=isset($bloklama)?$bloklama:'';
			if($bloklama==$blok){
				$angkakemarin=$angkakemarin;
			}else{
				$angkakemarin=0;
			}
			
			$datakmrn[$blok][$tglkemarin]=isset($datakmrn[$blok][$tglkemarin])?$datakmrn[$blok][$tglkemarin]:'';
			if($datakmrn[$blok][$tglkemarin]!=''){
				$angkakemarin=$datakmrn[$blok][$tglkemarin];
			}
			
			$data[$blok][$tgl]['panen']=isset($data[$blok][$tgl]['panen'])?$data[$blok][$tgl]['panen']:'';
			$data[$blok][$tglkemarin]['panen']=isset($data[$blok][$tglkemarin]['panen'])?$data[$blok][$tglkemarin]['panen']:'';
			$data[$blok][$tglkemarinlusa]['panen']=isset($data[$blok][$tglkemarinlusa]['panen'])?$data[$blok][$tglkemarinlusa]['panen']:'';
			
			if($data[$blok][$tgl]['panen']=='P' && ($data[$blok][$tglkemarin]['panen']=='P' || $data[$blok][$tglkemarinlusa]['panen']=='P' || $data[$blok][$tgltigaharilalu]['panen']=='P' || $data[$blok][$tglempatharilalu]['panen']=='P' || $data[$blok][$tgllimaharilalu]['panen']=='P' || $data[$blok][$tglenamharilalu]['panen']=='P' || $data[$blok][$tgltujuhharilalu]['panen']=='P' || $data[$blok][$tgldelapanharilalu]['panen']=='P' || $data[$blok][$tglsembilanharilalu]['panen']=='P' || $data[$blok][$tgl10harilalu]['panen']=='P')){
				$angka=$angkakemarin+1; 
			}else if($data[$blok][$tgl]['panen']=='' && ($data[$blok][$tglkemarin]['panen']=='P' || $data[$blok][$tglkemarinlusa]['panen']=='P' || $data[$blok][$tgltigaharilalu]['panen']=='P' || $data[$blok][$tglempatharilalu]['panen']=='P' || $data[$blok][$tgllimaharilalu]['panen']=='P' || $data[$blok][$tglenamharilalu]['panen']=='P' || $data[$blok][$tgltujuhharilalu]['panen']=='P' || $data[$blok][$tgldelapanharilalu]['panen']=='P' || $data[$blok][$tglsembilanharilalu]['panen']=='P' || $data[$blok][$tgl10harilalu]['panen']=='P')){
				$angka=$angkakemarin+1;
			}else if($data[$blok][$tgl]['panen']=='' && ($data[$blok][$tglkemarin]['panen']=='' || $data[$blok][$tglkemarinlusa]['panen']=='' || $data[$blok][$tgltigaharilalu]['panen']=='P' || $data[$blok][$tglempatharilalu]['panen']=='P' || $data[$blok][$tgllimaharilalu]['panen']=='P' || $data[$blok][$tglenamharilalu]['panen']=='P' || $data[$blok][$tgltujuhharilalu]['panen']=='P' || $data[$blok][$tgldelapanharilalu]['panen']=='P' || $data[$blok][$tglsembilanharilalu]['panen']=='P' || $data[$blok][$tgl10harilalu]['panen']=='P')){
				$angka=$angkakemarin+1;  
			}else{
				$angka=1; 
			}
			
			
			$str="insert into ".$dbname.".kebun_pusingan (`blok`, `angka`, `tanggal`, `keterangan`,`updateby`) 
			values ('".$blok."','".$angka."','".$tgl."','".$data[$blok][$tgl]['panen']."','')";
			//$owlPDO->exec($str);
			try{$owlPDO->exec($str); }catch (PDOException $e) {
				$str="update  ".$dbname.".kebun_pusingan set angka='".$angka."',keterangan='".$data[$blok][$tgl]['panen']."', updateby='' where blok='".$blok."' and tanggal='".$tgl."' ";
				//$owlPDO->exec($str);
				//try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
				try{$owlPDO->exec($str); }catch (PDOException $e) {}
			}
			
			$angkakemarin=$angka;
			$bloklama=$blok;
		}
	}
} # Tutup while

?>