<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
if(isTransactionPeriod())//check if transaction period is normal
{
	$notransaksi	=$_POST['notransaksi'];
//==============================
	$str="select * from ".$dbname.".log_transaksidt where notransaksi='".$notransaksi."' and statussaldo=1";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	if(owlBaris($res)>0){
		exit(" Error, transaksi sudah dalam proses posting");
	}
//========================  
	$str="select post, norequest from ".$dbname.".log_transaksiht where notransaksi='".$notransaksi."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$ststus=0;
	while($bar=$res->fetch())
	{ 
		$status=$bar->post;
		$norequest = $bar->norequest;
	}
	if($status==1)
	{
		//block if posted
		echo " Gagal/Error, Document has been posted";
	} else {
		//delete detail first
		$str="delete from ".$dbname.".log_transaksidt where notransaksi='".$notransaksi."'";
		try{
			$owlPDO->exec($str);

			//delete header
			$str="delete from ".$dbname.".log_transaksiht where notransaksi='".$notransaksi."'";
			try{
				$owlPDO->exec($str);
				
				$str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."'";
				try
				{
					$owlPDO->exec($str); 
				}
				catch(PDOException $e)
				{
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
				
				// Delete Referensi di Surat Jalan & Packing List
				$strSJ = "update ".$dbname.".log_suratjalandt set notransaksireferensi=''
					where notransaksireferensi='".$notransaksi."'";
				if($owlPDO->query($strSJ)){
					$spl="update ".$dbname.".log_packingdt set notransaksireferensi='' where notransaksireferensi='".$notransaksi."'";
					try{
						$owlPDO->exec($spl); 
					}catch(PDOException $e){
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						die(); 
					}
				}else{
					$spl="update ".$dbname.".log_packingdt set notransaksireferensi='' where notransaksireferensi='".$notransaksi."'";
					try{
						$owlPDO->exec($spl); 
					}catch(PDOException $e){
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						die(); 
					}
				}			
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
			
			if($norequest=='')
			{
				$str="delete from ".$dbname.".log_pemakaianpresentase where notransaksi='".$notransaksi."'";
			}
			else
			{
				$str="update ".$dbname.".log_permintaanpicdt set realisasi='0' where notransaksi='".$norequest."'";
			}
			$_SESSION['pic'] = array();
			try{ $owlPDO->exec($str); }catch(PDOException $e ){ print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	}
}
?>