<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$notransaksi=checkPostGet('notransaksi','');
$bayar=checkPostGet('bayar','');
$tglbayar=checkPostGet('tglbayar','');

$scek="select karyawanid,tahunplafon,kodebiaya,updatetime from ".$dbname.".sdm_pengobatanht 
       where notransaksi='".$notransaksi."'";
$qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
$qcek->setFetchMode(PDO::FETCH_ASSOC);
$rcek=$qcek->fetch();

$karyawanid = $rcek['karyawanid'];
$kodebiaya = $rcek['kodebiaya'];
$optNamaKaryawan = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$karyawanid."'");
$optNamaBiaya = makeOption($dbname,'sdm_5jenisbiayapengobatan','kode,nama',"kode='".$kodebiaya."'");
$namakaryawan = $optNamaKaryawan[$karyawanid];
$namabiaya = $optNamaBiaya[$kodebiaya];

$sgapok="select distinct sum(jumlah) as jmlhgapok from ".$dbname.".sdm_5gajipokok where
        karyawanid='".$rcek['karyawanid']."' and tahun='".$rcek['karyawanid']."' and idkomponen =1";
$qgapok=$owlPDO->query($sgapok) or die(print " Gagal: ".PDOException::getMessage());
$qgapok->setFetchMode(PDO::FETCH_ASSOC);
$rgapok=$qgapok->fetch();

$sbayar="select sum(bebanperusahaan) as sudahbayar from ".$dbname.".sdm_pengobatanht
         where karyawanid='".$rcek['karyawanid']."' and tanggalbayar !='0000-00-00' 
         and jlhbayar!=0 and tahunplafon='".$rcek['karyawanid']."' and kodebiaya like '".$rcek['kodebiaya']."'";
$qbayar=$owlPDO->query($sbayar) or die(print " Gagal: ".PDOException::getMessage());
$qbayar->setFetchMode(PDO::FETCH_ASSOC);
$rbayar=$qbayar->fetch(); 
$bebanperusahaan=$rgapok['jmlhgapok']-$rbayar['sudahbayar'];
$str="update ".$dbname.".sdm_pengobatanht set jlhbayar=".$bayar.",
      tanggalbayar=".tanggalsystem($tglbayar).",posting=1
      where notransaksi='".$notransaksi."'";  
try{
	$owlPDO->exec($str); 
	$str1="update ".$dbname.".sdm_pengobatanht set bebanperusahaan=".$bebanperusahaan."
          where karyawanid='".$rcek['karyawanid']."' and tanggalbayar ='0000-00-00' 
          and jlhbayar=0 and tahunplafon='".$rcek['tahunplafon']."' and kodebiaya like '".$rcek['kodebiaya']."'
          and kodeorg ='".substr($notransaksi,0,4)."'";
	try{
		$owlPDO->exec($str); 
		
		//KIRIM EMAIL NOTIFIKASI
		$to = getUserEmail($karyawanid);
		$subject="[Notifikasi] Konfirmasi Pembayaran Klaim Pengobatan";
        $body="<html>
                 <head>
                 <body>
                   <dd>Dengan Hormat,</dd><br>
                   <br>
                   Telah diverifikasi pembayaran klaim pengobatan A/n : ".$namakaryawan."<br>
                   Jenis Biaya Pengobatan : ".$namabiaya."<br>
				   Jumlah : Rp. ".number_format($bayar,2)."<br>
                   Tanggal:".$tglbayar."
                   <br>
                   <br>
                   <br>
                   Regards,<br>
				   Departemen HRA<br>
                   Owl-Plantation System.
                 </body>
                 </head>
               </html>
               ";
		$kirim=kirimEmail($to,'',$subject,$body);
	}catch (PDOException $e){
		die();
	}
}catch (PDOException $e){
	echo " Gagal ".addslashes($e->getMessage());
	die();
}
?>
