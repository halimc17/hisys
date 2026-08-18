<?php
//ini_set('display_errors',0);
//error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');

$userid = checkPostGet('userid','');
$component = checkPostGet('idx','');
$total = checkPostGet('total','');
$start = checkPostGet('start','');
$finish = checkPostGet('finish','');
$lama = checkPostGet('lama','');
$rpbulan = checkPostGet('rpbulan','');
$tahap1 = checkPostGet('tahap1','0');
$tahap2 = checkPostGet('tahap2','0');
$tahap3 = checkPostGet('tahap3','0');
$tahap4 = checkPostGet('tahap4','0');
$tahap5 = checkPostGet('tahap5','0');
$active = checkPostGet('active','');
$method = checkPostGet('method','');
$keterangan = checkPostGet('keterangan','');
$totaldetail = checkPostGet('totaldetail','');

$nmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$dt = mktime(0, 0, 0, intval(substr($start, 5, 2)) + ($lama - 1), 15, substr($start, 0, 4));
$end = date('Y-m', $dt);

switch ($method) {
	case'update':
	try {
		$owlPDO->beginTransaction();
		
		$angsbln = $total / $lama;
		$str = "update " . $dbname . ".sdm_angsuran set total=" . $total . ",updateby='" . $_SESSION['standard']['username'] . "', active=" . $active . ",jlhbln=" . $lama . ",start='" . $start . "',end='" . $finish . "',bulanan=" . $rpbulan . ", keterangan='" . $keterangan ."',tahap1='".$tahap1."',tahap2='".$tahap2."',tahap3='".$tahap3."',tahap4='".$tahap4."',tahap5='".$tahap5."' where karyawanid=" . $userid . " and jenis='86' and start='".$start."'";
		$owlPDO->exec($str); 
		
		$str = "delete from " . $dbname . ".sdm_angsurandt  where karyawanid=" . $userid . " and jenis='86' and start='".$start."'";
		$owlPDO->exec($str); 
		
		$ttldetail=0;
		for($i=1;$i<=$totaldetail;$i++){			
			$rpdetail = checkPostGet('rpdetail_'.$i,'');
			$bulandet = checkPostGet('bulan_'.$i,'');
			
			$str = "insert into " . $dbname . ".sdm_angsurandt (karyawanid,jenis,jenis2,bulan,start,jumlah,status)
			values('".$userid."','86','".$component."','".$bulandet."','".$start."','".$rpdetail."','1')";
			$owlPDO->exec($str);
			
			$ttldetail+=$rpdetail;
			
		}
		$selisih = $ttldetail-$total;
		if(abs($selisih)>=1){
			throw new PDOException("Jumlah detail dengan Total hutang tidak sama, selisih : ".number_format($selisih,2)."");
		}
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
	break;
	case'insert':
	try {
	$owlPDO->beginTransaction();
		if($userid=='' and $component=='' and $total==''){
			throw new PDOException("Nama Karyawan, Jenis Angsuran dan Total wajib diisi !");
		}
		
		$str="select * from ".$dbname.".sdm_angsuran where karyawanid='".$userid."' and start='".$start."' and jenis='86'";
		if(count(fetchdata($str))>0){
			throw new PDOException("Karyawan ini telah memiliki Angsuran pada periode ".$start." !");
		}
		
		
		#ambil lokasi tugas
		$optloktugas = makeOption($dbname,'datakaryawan','karyawanid,lokasitugas',"karyawanid='".$userid."'");
		
		$str="select * from ".$dbname.".sdm_5periodegaji where kodeorg='".$optloktugas[$userid]."' and sudahproses='0' order by periode desc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$periode=$bar['periode'];
		}
		if($start<$periode){
			throw new PDOException("Periode gaji : ".$start." sudah ditutup !");
		}
		
		$angsbln = $total / $lama;
		$str = "insert into " . $dbname . ".sdm_angsuran (karyawanid,jenis,jenis2,total,updateby,jlhbln,bulanan,active,start,end,keterangan,tahap1,tahap2,tahap3,tahap4,tahap5)
		values(" . $userid . ",'86','" . $component . "'," . $total . ",'" . $_SESSION['standard']['username'] . "'," . $lama . "," . $rpbulan . "," . $active . ",'" . $start . "','" . $end . "','".$keterangan."','".$tahap1."','".$tahap2."','".$tahap3."','".$tahap4."','".$tahap5."')";
		$owlPDO->exec($str); 
		
		$ttldetail=0;
		for($i=1;$i<=$totaldetail;$i++){			
			$rpdetail = checkPostGet('rpdetail_'.$i,'');
			$bulandet = checkPostGet('bulan_'.$i,'');
			
			$str = "insert into " . $dbname . ".sdm_angsurandt (karyawanid,jenis,jenis2,bulan,start,jumlah,status)
			values('".$userid."','86','".$component."','".$bulandet."','".$start."','".$rpdetail."','1')";
			$owlPDO->exec($str);
			
			$ttldetail+=$rpdetail;
			
		}
		$selisih = $ttldetail-$total;
		if(abs($selisih)>=1){
			throw new PDOException("Jumlah detail dengan Total hutang tidak sama, selisih : ".number_format($selisih,2)."");
		}
		
		#exit("error".$str);
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}
		
		
	break;
	case'delete':
		$str = "delete from " . $dbname . ".sdm_angsuran  
		where karyawanid=" . $userid . "
		and jenis='" . $component."' and start='".$start."'";
		try{
			$owlPDO->exec($str); 
			}
		catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
}

?>
