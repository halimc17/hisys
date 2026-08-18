<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$notransaksi=checkPostGet('notransaksi','');
$prd=checkPostGet('prd','');
$unit=checkPostGet('unit','');
$afd=checkPostGet('afd','');
$keg=checkPostGet('keg','');
$sesi=checkPostGet('sesi','');
$nospb=checkPostGet('nospb','');
$kary=checkPostGet('kary','');
$tgl=checkPostGet('tgl','');
$jjgkry=checkPostGet('jjgkry','');
$bjrwb=checkPostGet('bjrwb','');
$kgwb=checkPostGet('kgwb','');
$hk=checkPostGet('hk','');
$nilai1hk=checkPostGet('nilai1hk','');
$rppremi=checkPostGet('rppremi','');
$rphk=checkPostGet('rphk','');
$kontanan=checkPostGet('kontanan','');

$jjgkry=str_replace(',','',$jjgkry);
$bjrwb=str_replace(',','',$bjrwb);
$kgwb=str_replace(',','',$kgwb);
$hk=str_replace(',','',$hk);
$nilai1hk=str_replace(',','',$nilai1hk);
$rppremi=str_replace(',','',$rppremi);
$rphk=str_replace(',','',$rphk);

switch($proses){
	case'deleteTrans':
		#Validasi :
		#1. Cek Prd Akuntansi
		$str="select * from ".$dbname.".setup_periodeakuntansi where periode = '".$prd."' and kodeorg='".$unit."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if($bar['tutupbuku']=='1'){
			exit('Error : Periode Akuntansi Sudah di Tutup.');
		}
		
		#2. Cek Prd Gaji
		$str="select * from ".$dbname.".sdm_5periodegaji where periode = '".$prd."' and kodeorg='".$unit."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if($bar['sudahproses']=='1'){
			exit('Error : Periode Gaji Sudah di Tutup.');
		}
		#3. Cek Transaksi sudah di posting belum
		$str="select * from ".$dbname.".kebun_3premibmtbs where periode = '".$prd."' and kodeorg='".$unit."' and notransaksi = '".$notransaksi."' and posting='1' and kontanan='".$kontanan."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if($bar['posting']=='1'){
			exit('Error : Transaksi notransaksi : '.$notransaksi.' unit : '.$unit.' periode : '.$prd.' sudah di Posting.');
		}

		if ($keg!='') {
			$wh.=" and kegiatan='".$keg."' ";
		}
		
		#Hapus Transaksi
		$str="delete from ".$dbname.".kebun_3premibmtbs where `notransaksi` ='".$notransaksi."' ".$wh." and kontanan='".$kontanan."'";
		try{
			$owlPDO->exec($str); 
			}
		catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		
	break;
    case'savedata':
		$str="select * from ".$dbname.".kebun_spbht where	tanggal like '".$prd."%' and nospb like '%".$afd."%' and kodeorg='".$unit."' and posting='0'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$row=$res->rowCount();
		if($row>0){
			exit('Error : Ada transaksi SPB yang belum di Posting');
		}
		#if($rplb>0 and $lbbss>0){
			$str="insert into ".$dbname.".kebun_3premibmtbs (`notransaksi`,`nospb`,`kodeorg`,`divisi`,`periode`,`kegiatan`,
				 `karyawanid`,`tanggal`,`sesi`,`jjgkry`,`bjrwb`,`kgwb`,`hk`,`rphk`,`nilai1hk`,`rppremi`,`updateby`,`kontanan`)
				  values ('".$notransaksi."','".$nospb."','".$unit."','".$afd."','".$prd."','".$keg."','".$kary."','".$tgl."','".$sesi."','".$jjgkry."','".$bjrwb."','".$kgwb."',
				  '".$hk."','".$rphk."','".$nilai1hk	."','".$rppremi."','".$_SESSION['standard']['userid']."','".$kontanan."')";
			
			try{
				$owlPDO->exec($str); 
				}
			catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}			
		#}

    break; 
	case 'getAfd':
		$optafd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		if ($unit != "") {
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$unit."' and tipe in ('AFDELING','TRAKSI') ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optafd.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			}
		}
		echo $optafd;
	break;
    default:
}

?>