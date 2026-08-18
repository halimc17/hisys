<?
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$persekarang = date("Y-m");
$periodeberikut = periodeberikut($persekarang);
$str="select * from ".$dbname.".keu_5kelompokjurnal where periode='".$persekarang."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);   
while($bar=$res->fetch()){
	$str1="select count(*) as jumlah from ".$dbname.".keu_5kelompokjurnal where 
		kodeorg	='".$bar['kodeorg']."' and
		kodeunit='".$bar['kodeunit']."' and
		kodekelompok='".$bar['kodekelompok']."' and
		periode='".$periodeberikut."'";
	$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
	$res1->setFetchMode(PDO::FETCH_ASSOC);   
	$bar1=$res1->fetch();
	
	if($bar1['jumlah']=='0'){
		$data = "insert into " . $dbname . ".keu_5kelompokjurnal(kodeorg, kodeunit, kodekelompok, periode, keterangan, nokounter)
			   values('".$bar['kodeorg']."','".$bar['kodeunit']."','".$bar['kodekelompok']."','".$periodeberikut."','".$bar['keterangan']."','0')";
		try{
            $owlPDO->exec($data); 
        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
	}
}


##### MOVE LIST NOTIFICATION TO HISTORICAL #####
$tglskrg=date("Y-m-d");
$blnlalu=tglbulanlalu($tglskrg);
$str="select * from ".$dbname.".list_notification where tanggal < '".$blnlalu."'";
$res=fetchdata($str);
foreach($res as $val){
	$strx="insert into ".$dbname.".hist_list_notification (id,kodetransaksi,kodenotification,detail,karyawanid,kodedepartement,kodetipekaryawan,kodejabatan,readnotif,shownotif,tanggal) values ('".$val['id']."','".$val['kodetransaksi']."','".$val['kodenotification']."','".$val['detail']."','".$val['karyawanid']."','".$val['kodedepartement']."','".$val['kodetipekaryawan']."','".$val['kodejabatan']."','".$val['readnotif']."','".$val['shownotif']."','".$val['tanggal']."')";
	$owlPDO->exec($strx);
	
	$strx="delete from ".$dbname.".list_notification where id='".$val['id']."'";
	$owlPDO->exec($strx);
}

$str="select * from ".$dbname.".list_notification where tanggal < '".$tglskrg."' and kodenotification='HTB'";
$res=fetchdata($str);
foreach($res as $val){
	$strx="insert into ".$dbname.".hist_list_notification (id,kodetransaksi,kodenotification,detail,karyawanid,kodedepartement,kodetipekaryawan,kodejabatan,readnotif,shownotif,tanggal) values ('".$val['id']."','".$val['kodetransaksi']."','".$val['kodenotification']."','".$val['detail']."','".$val['karyawanid']."','".$val['kodedepartement']."','".$val['kodetipekaryawan']."','".$val['kodejabatan']."','".$val['readnotif']."','".$val['shownotif']."','".$val['tanggal']."')";
	$owlPDO->exec($strx);
	
	$strx="delete from ".$dbname.".list_notification where id='".$val['id']."'";
	$owlPDO->exec($strx);
}









?>