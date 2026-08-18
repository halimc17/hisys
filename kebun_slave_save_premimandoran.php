<?php
ini_set('display_errors',0);
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$unit=checkPostGet('unit','');
$prd=checkPostGet('prd','');
$denda=checkPostGet('denda','');
$denda=str_replace(',','',$denda);
$premi=checkPostGet('premi','');
$premi=str_replace(',','',$premi);
$mandor=checkPostGet('mandor','');
$premitotal=checkPostGet('premitotal','');
$premitotal=str_replace(',','',$premitotal);
$pembagi=checkPostGet('pembagi','');
$premiawal=checkPostGet('premiawal','');
$premiawal=str_replace(',','',$premiawal);
$jabatan=checkPostGet('jabatan','');
$kontanan=checkPostGet('kontanan','');
$tahap=checkPostGet('tahap','');
$tglmulai=tanggalsystemn(checkPostGet('tglmulai',''));

if($tglmulai=='--'){
	$tglmulai='0000-00-00';
}else{
	$tglmulai=$tglmulai;
}

switch($proses){
    case'savedata':
		#cek posting
		$str="select * from ".$dbname.".kebun_premikemandoran where `kodeorg`='".$unit."' and `karyawanid`='".$mandor."' and `periode`='".$prd."' and tahap='".$tahap."' and jabatan='".$jabatan."' and kontanan='".$kontanan."' and tanggalkontanan='".$tglmulai."' and posting='1'";
		$res=fetchdata($str);
		if(count($res)>0){
			exit("Warning : Transaksi Sudah di POSTING !!!");
		}
		
		#delete 1st
		$str="delete from ".$dbname.".kebun_premikemandoran where `kodeorg`='".$unit."' and `karyawanid`='".$mandor."' and `periode`='".$prd."' and jabatan='".$jabatan."' and kontanan='".$kontanan."' and tanggalkontanan='".$tglmulai."' and tahap='".$tahap."'";
		#exit('error'.$str);
		try{
			$owlPDO->exec($str); 
			}
		catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		$str="insert into ".$dbname.".kebun_premikemandoran (`kodeorg`,`periode`,`tahap`,`karyawanid`,`jabatan`,
				`pembagi`,`premisumber`,`premikomputer`,`denda`,`premiinput`,`updateby`,`kontanan`,`tanggalkontanan`)
				values ('".$unit."','".$prd."','".$tahap."','".$mandor."','".$jabatan."',
				'".$pembagi."','".$premiawal."','".$premi."','".$denda."','".$premitotal."','".$_SESSION['standard']['userid']."','".$kontanan."','".$tglmulai."')";
		#exit('error'.$str);
		try{
			$owlPDO->exec($str); 
			}
		catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
    break; 
    default:
}

?>