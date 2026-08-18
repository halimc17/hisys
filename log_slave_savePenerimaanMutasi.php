<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
//====================================
//default setting on database 
//1=Masuk,2=Pengembalian pengeluaran, 3=penerimaan mutasi,5=Pengeluaran,6=Pengembalian penerimaan,7 pengeluaran mutasi 
$tipetransaksi=3;
//=============================================

try {
$owlPDO->beginTransaction();
//check if transaction period is normal
	if(isTransactionPeriod()){
		$nodok        =$_POST['nodok'];
		$referensi    =$_POST['referensi'];    
		$tanggal      =tanggalsystem($_POST['tanggal']); 
		
		// $gudangx      =$_POST['gudangx'];  #kodegudang HT
		// $kodegudang   =$_POST['kodegudang']; #gudangx HT
		// $pemilikbarang=$_POST['pemilikbarang']; #kodept HT
		
		$str = "select * from ".$dbname.".log_transaksiht where notransaksi = '".$referensi."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$gudangx      =$bar['kodegudang'];  #kodegudang HT
			$kodegudang   =$bar['gudangx']; #gudangx HT
			$pemilikbarang=$bar['kodept']; #kodept HT
			$nodok        =notrans($kodegudang);#ambil notransaksi terakhir		}
		$str = "select * from ".$dbname.".log_transaksidt where notransaksi = '".$referensi."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$kodebarang   =$bar['kodebarang']; #kodebarang DT
			$satuan       =$bar['satuan']; #satuan DT
			$jumlah       =str_replace(",","",$bar['jumlah']);  #jumlah DT
			$post         =0;
			$user         =$_SESSION['standard']['userid'];
			$nopp         =$bar['nopp']; #nopp DT
			
			//Periksa apakah no transaksi sudah ada dan no refrensi sama dengan no refrensi yang akan masuk
			$sChk2 = "select notransaksireferensi from ".$dbname.".log_transaksi_vw where notransaksi = '".$nodok."'";
			$qChk2=$owlPDO->query($sChk2) or die(print " Gagal: ".PDOException::getMessage());
			$qChk2->setFetchMode(PDO::FETCH_ASSOC);
			$cChk2=owlBaris($qChk2);
			$rChk2 = $qChk2->fetch();
			
			if($cChk2 >= 1){
				if($rChk2['notransaksireferensi'] != $referensi){
					throw new PDOException("reloadpage");
				}
			}
			
			if($jumlah>0){
				//1 cek apakah sudah terekan di header
				//status=0 belum ada apa2
				//status=1 ada header
				//status=2 ada detail dan header
				//status=3 sudah di posting
				//status=7 sudah ada yang diposting pada tanggal yang lebih besar dengan barang yang sama dan pt yang sama
				
				$status=0;
				$str="select * from ".$dbname.".log_transaksiht where notransaksi='".$nodok."'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				if(owlBaris($res)==1){
				   $status=1;
				}
				
				$str="select * from ".$dbname.".log_transaksidt where notransaksi='".$nodok."'
					  and kodebarang='".$kodebarang."'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				if(owlBaris($res)>0){
				   $status=2;
				}	 
				
				$str="select * from ".$dbname.".log_transaksiht where notransaksi='".$nodok."'
					  and post=1";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				if(owlBaris($res)>0){
				   $status=3;
				}
				
			
				//harga satuan ==============================
				$strx="select a.hargarata from ".$dbname.".log_transaksidt a
					left join ".$dbname.".log_transaksiht b 
					on a.notransaksi=b.notransaksi
					where a.kodebarang='".$kodebarang."'
					and a.notransaksi='".$referensi."'
					and  b.tipetransaksi=7
					order by a.notransaksi desc limit 1";  
				
				$hargasatuan=0;
				$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
				$resx->setFetchMode(PDO::FETCH_OBJ);
				while($barx=$resx->fetch()) {
					$hargasatuan=$barx->hargarata;
				}
				
				if($hargasatuan==0 or $hargasatuan=='') {
					throw new PDOException("Harga rata - rata nol untuk barang ".getNamaBrg($kodebarang));
				}
				
				//==================ambil jumlah lalu====================
				$jumlahlalu=0;
				$str="select a.jumlah as jumlah,a.notransaksi as notransaksi 
					from ".$dbname.".log_transaksidt a,
					".$dbname.".log_transaksiht b
					where a.notransaksi=b.notransaksi
					and a.kodebarang='".$kodebarang."'
					and a.notransaksi<='".$nodok."'
					and b.kodegudang='".$kodegudang."'
					order by notransaksi desc limit 1";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
				while($bar=$res->fetch()) {
					$jumlahlalu=$bar->jumlah;
				}
				
				//=============================start input/update	
				if($status==0) {
					//get kode pt penerima barang
					$sKdPt="select distinct induk from ".$dbname.".organisasi where kodeorganisasi='".substr($kodegudang,0,4)."'";
					$qKdPt=$owlPDO->query($sKdPt) or die(print " Gagal: ".PDOException::getMessage());
					$qKdPt->setFetchMode(PDO::FETCH_ASSOC);
					$rKdpt=$qKdPt->fetch();
					if($rKdpt['induk']==''){
						throw new PDOException("Kode PT Penerima Kosong");
					}
					
					$str="insert into ".$dbname.".log_transaksiht (
							`tipetransaksi`,`notransaksi`,`tanggal`,
							`kodept`,`kodegudang`,`user`,
							`gudangx`,`notransaksireferensi`,`post`)
					values(".$tipetransaksi.",'".$nodok."',".$tanggal.",
							'".$rKdpt['induk']."','".$kodegudang."',".$user.",
							'".$gudangx."','".$referensi."',".$post."
					)";
					
					
					$owlPDO->exec($str);//insert hedaer
					//update sumber pada pengeluaran mutasi
					$str="update ".$dbname.".log_transaksiht 
						set notransaksireferensi='".$nodok."'
						where notransaksi='".$referensi."'
						and kodegudang='".$gudangx."'";
					$owlPDO->exec($str); 
					
					$strd="insert into ".$dbname.".log_transaksidt (
						`notransaksi`,`kodebarang`,
						`satuan`,`jumlah`,`jumlahlalu`,hargasatuan,`nopp`)
						values('".$nodok."','".$kodebarang."',
						'".$satuan."',".$jumlah.",".$jumlahlalu.",".$hargasatuan.",'".$nopp."')";
					$owlPDO->exec($strd); //insert detail
					
				}
				//============================
				//status=1
				
				if($status==1){
					$str="insert into ".$dbname.".log_transaksidt (
						`notransaksi`,`kodebarang`,
						`satuan`,`jumlah`,`jumlahlalu`,hargasatuan,`nopp`)
						values('".$nodok."','".$kodebarang."',
						'".$satuan."',".$jumlah.",".$jumlahlalu.",".$hargasatuan.",'".$nopp."')";
					$owlPDO->exec($str); 
				}	
				
				//============================return message
				//status=3
				if($status==3){	
				   throw new PDOException("Data has been posted");
				}
			}
		}
	}else{
		echo " Error: Transaction Period missing";
	}
#execute
	$owlPDO->commit();
} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}

function notrans($gudang){
	global $dbname;
	global $owlPDO;
	$num=1;//default value 
	$str="select max(notransaksi) as notransaksi from ".$dbname.".log_transaksiht where tipetransaksi<5 and tanggal>=".$_SESSION['gudang'][$gudang]['start']." and tanggal<=".$_SESSION['gudang'][$gudang]['end']."
		and kodegudang='".$gudang."' order by notransaksi";	
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	if(owlBaris($res)>0){
		while($bar=$res->fetch()){
			$num=$bar->notransaksi;
			if($num!=''){
				$num=intval(substr($num,6,5))+1;
			}else{
			  $num=1;	
			}	
		}
	}
	if($num<10)
	   $num='0000'.$num;
	else if($num<100)
	   $num='000'.$num;
	else if($num<1000)
	   $num='00'.$num;	   
	else if($num<10000)
	   $num='0'.$num;
	else
	   $num=$num;
		
	$num=$_SESSION['gudang'][$gudang]['tahun'].$_SESSION['gudang'][$gudang]['bulan'].$num."-GR-".$gudang;
	return $num;
}
?>