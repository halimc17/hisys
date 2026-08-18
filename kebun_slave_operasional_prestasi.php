<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses = $_GET['proses'];
$param = $_POST;
switch($proses) {
    case 'add':
	
	
	
	
	#= query cari tanggal untuk rkh
	$str = "select tanggal from " . $dbname . ".kebun_aktifitas where notransaksi ='".$param['notransaksi']."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$tanggal=$bar['tanggal'];
	
	#cek data rkh
	#= cek apakah no bkm terdaftar
	$str = "select count(*) as jumlah from " . $dbname . ".kebun_rkh_vw where kodekegiatan='".$param['kodekegiatan']."'
			and tanggal='".$tanggal."' and kodeblok='".$param['kodeorg']."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$cekrkh=$bar['jumlah'];

	if($cekrkh<1){
		if($param['keterangan']==''){
			exit("Warning:Kegiatan tidak ada dalam RKH, harap mengisikan keterangan !");
		}
	}
	
	
	#ambil kegiatan rkb
	$str = "select sum(KBL+KHT+KHL) as jumlah from " . $dbname . ".kebun_rkbdt a 
			left join " . $dbname . ".kebun_rkbht b on a.norkb=b.norkb 
			where a.kodekegiatan='".$param['kodekegiatan']."' and a.periode='".substr($tanggal,0,7)."' and a.divisi='".substr($param['kodeorg'],0,6)."' and b.statuspersetujuan='1'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$hkrkb=$bar['jumlah'];
		
	#HK Realisasi
	$str = "select sum(jumlahhk) as jumlah from " . $dbname . ".kebun_prestasi a 
			left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi 
			where a.kodekegiatan='".$param['kodekegiatan']."' and b.tanggal like '".substr($tanggal,0,7)."%' and a.kodeorg like '".substr($param['kodeorg'],0,6)."%'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$hkreal=$bar['jumlah'];
	
	$ttlreal = $param['jumlahhk']+$hkreal;
	$selisih = $hkrkb - $ttlreal;
	
	#ambil dari setup
	$str = "select * from " . $dbname . ".setup_validasi_bkm_vs_rkb where kodeorg='".substr($param['kodeorg'],0,4)."'";
	$res=fetchdata($str);
	$setrkb = count($res);
	$stsrkb = $res[0]['status'];
	$prdrkb = $res[0]['periode'];
	
	if($selisih < 0){
		if($setrkb>0 and $stsrkb==1){
			if($tanggal>=$prdrkb){
				exit("WARNING !!!\nJumlah HK Realisasi sudah melebihi HK RKB !\n1. Jumlah HK di RKB = ".number_format($hkrkb,2)."\n2. Jumlah HK Realisasi = ".number_format($ttlreal,2)."\n3. Selisih (RKB - Realisasi) = ".number_format($selisih,2)."");
			}
		}else{
			exit("WARNING !!!\nJumlah HK Realisasi sudah melebihi HK RKB !\n1. Jumlah HK di RKB = ".number_format($hkrkb,2)."\n2. Jumlah HK Realisasi = ".number_format($ttlreal,2)."\n3. Selisih (RKB - Realisasi) = ".number_format($selisih,2)."");
		}
	}
	
	
	// if($selisih < 0 and $tanggal>'2019-02-28'){
		// exit("WARNING !!!\nJumlah HK Realisasi sudah melebihi HK RKB !\n1. Jumlah HK di RKB = ".number_format($hkrkb,2)."\n2. Jumlah HK Realisasi = ".number_format($ttlreal,2)."\n3. Selisih (RKB - Realisasi) = ".number_format($selisih,2)."");
	// }
	$cols = array(
	    'kodekegiatan','kodeorg','hasilkerja','jumlahhk','upahkerja',
	    'upahpremi','keterangan','notransaksi','tahuntanam','norma','statusblok',
	    'pekerjaanpremi','penalti1','penalti2','penalti3','penalti4','penalti5','nik'
	);
	$data = $param;
	unset($data['numRow']);
	# Additional Default Data
	$data['tahuntanam'] = 0;$data['norma'] = 0;$data['statusblok'] = '0';
	$data['pekerjaanpremi'] = '0';
	$data['penalti1'] = 0;$data['penalti2'] = 0;$data['penalti3'] = 0;
	$data['penalti4'] = 0;$data['penalti5'] = 0;$data['nik'] = '-';
	
	$query = insertQuery($dbname,'kebun_prestasi',$data,$cols);
	try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	
	unset($data['notransaksi']);unset($data['tahuntanam']);unset($data['norma']);
	unset($data['statusblok']);unset($data['pekerjaanpremi']);
	unset($data['penalti1']);unset($data['penalti2']);unset($data['penalti3']);
	unset($data['penalti4']);unset($data['penalti5']);unset($data['nik']);
	
	$res = "";
	foreach($data as $cont) {
	    $res .= "##".$cont;
	}
	
	$result = "{res:\"".$res."\",theme:\"".$_SESSION['theme']."\"}";
	echo $result;
	break;
    case 'edit':
	$data = $param;
	unset($data['notransaksi']);
	foreach($data as $key=>$cont) {
	    if(substr($key,0,5)=='cond_') {
		unset($data[$key]);
	    }
	}
	
	#= query cari tanggal untuk rkh
	$str = "select tanggal from " . $dbname . ".kebun_aktifitas where notransaksi ='".$param['notransaksi']."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$tanggal=$bar['tanggal'];
	
	#cek data rkh
	#= cek apakah no bkm terdaftar
	$str = "select count(*) as jumlah from " . $dbname . ".kebun_rkh_vw where kodekegiatan='".$param['kodekegiatan']."'
			and tanggal='".$tanggal."' and kodeblok='".$param['kodeorg']."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$cekrkh=$bar['jumlah'];

	if($cekrkh<1){
		if($param['keterangan']==''){
			exit("Warning : Kegiatan tidak ada dalam RKH, harap mengisikan keterangan !!!");
		}
	}
	
	#ambil kegiatan rkb
	$str = "select sum(KBL+KHT+KHL) as jumlah from " . $dbname . ".kebun_rkbdt a 
			left join " . $dbname . ".kebun_rkbht b on a.norkb=b.norkb 
			where a.kodekegiatan='".$param['kodekegiatan']."' and a.periode='".substr($tanggal,0,7)."' and a.divisi='".substr($param['kodeorg'],0,6)."' and b.statuspersetujuan='1'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$hkrkb=$bar['jumlah'];
		
	#HK Realisasi
	$str = "select sum(jumlahhk) as jumlah from " . $dbname . ".kebun_prestasi a 
			left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi 
			where a.kodekegiatan='".$param['kodekegiatan']."' and b.tanggal like '".substr($tanggal,0,7)."%' and a.kodeorg like '".substr($param['kodeorg'],0,6)."%'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$hkreal=$bar['jumlah'];
	
	#hk lama
	$str = "select sum(jumlahhk) as jumlah from " . $dbname . ".kebun_prestasi a 
			left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi 
			where a.kodekegiatan='".$param['kodekegiatan']."' and b.tanggal = '".$tanggal."' and a.kodeorg = '".$param['kodeorg']."' and a.notransaksi='".$param['notransaksi']."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$hkreallama=$bar['jumlah'];
	

	$ttlreal = $param['jumlahhk']+$hkreal-$hkreallama;
	$selisih = $hkrkb - $ttlreal;
	
	#ambil dari setup
	$str = "select * from " . $dbname . ".setup_validasi_bkm_vs_rkb where kodeorg='".substr($param['kodeorg'],0,4)."'";
	$res=fetchdata($str);
	$setrkb = count($res);
	$stsrkb = $res[0]['status'];
	$prdrkb = $res[0]['periode'];
	
	if($selisih < 0){
		if($setrkb>0 and $stsrkb==1){
			if($tanggal>=$prdrkb){
				exit("WARNING !!!\nJumlah HK Realisasi sudah melebihi HK RKB !\n1. Jumlah HK di RKB = ".number_format($hkrkb,2)."\n2. Jumlah HK Realisasi = ".number_format($ttlreal,2)."\n3. Selisih (RKB - Realisasi) = ".number_format($selisih,2)."");
			}
		}else{
			exit("WARNING !!!\nJumlah HK Realisasi sudah melebihi HK RKB !\n1. Jumlah HK di RKB = ".number_format($hkrkb,2)."\n2. Jumlah HK Realisasi = ".number_format($ttlreal,2)."\n3. Selisih (RKB - Realisasi) = ".number_format($selisih,2)."");
		}
	}
	
	// if($selisih < 0 and $tanggal>'2019-02-28'){
		// exit("WARNING !!!\nJumlah HK Realisasi sudah melebihi HK RKB !\n1. Jumlah HK di RKB = ".number_format($hkrkb,2)."\n2. Jumlah HK Realisasi = ".number_format($ttlreal,2)."\n3. Selisih (RKB - Realisasi) = ".number_format($selisih,2)."");
	// }
	
	$where = "notransaksi='".$param['notransaksi']."' and kodekegiatan='".
	    $param['cond_kodekegiatan']."' and kodeorg='".$param['cond_kodeorg']."'";
	$query = updateQuery($dbname,'kebun_prestasi',$data,$where);
	try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	echo json_encode($param);
	break;
    case 'delete':
	$where = "notransaksi='".$param['notransaksi']."' and kodekegiatan='".
	    $param['kodekegiatan']."' and kodeorg='".$param['kodeorg']."'";
	$query = "delete from `".$dbname."`.`kebun_prestasi` where ".$where;
	try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
    default:
    break;
}
?>