<?
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include('kebun_slave_3pusingan_otomatis.php');
include('setup_pindahapproval.php');

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
		$owlPDO->exec($data); 
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

##### DEL LIST NOTIFICATION HISTORICAL #####
$strx = "delete from ".$dbname.".hist_list_notification where tanggal < '".$blnlalu."'";
$owlPDO->exec($strx);


// ### ATT_LOG pindahkan jika sudah 3 bulan
// $enamblnlalu=substr(enamblnlalu($tglskrg),0,7)."-01";
// $str = "select * from ".$dbname.".att_log where substr(scan_date,1,10) < '".$enamblnlalu."' limit 100000";
// $res = fetchdata($str);
// foreach($res as $val){
	// $strx="insert into ".$dbname.".att_log_hist (sn,scan_date,pin,verifymode,inoutmode,reserved,work_code,att_id,flag,waktuupload,latitude,longitude) 
	// values ('".$val['sn']."','".$val['scan_date']."','".$val['pin']."','".$val['verifymode']."','".$val['inoutmode']."','".$val['reserved']."','".$val['work_code']."','".$val['att_id']."','".$val['flag']."','".$val['waktuupload']."','".$val['latitude']."','".$val['longitude']."')";
	// $owlPDO->exec($strx);
	
	// $strx="delete from ".$dbname.".att_log where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
	// $owlPDO->exec($strx);
// }

// ### shift pindahkan jika sudah 3 bulan
// $tigablnlalu=substr(tigablnlalu($tglskrg),0,7)."-01";
// $str = "select * from ".$dbname.".sdm_5shiftanggota where tanggal < '".$tigablnlalu."' limit 100000";
// $res = fetchdata($str);
// foreach($res as $val){
	// $strx="insert into ".$dbname.".sdm_5shiftanggota_hist (kodeorg,subbagian,bagian,karyawanid,idshift,shift,namashift,tanggal,posting,createby,createtime,updateby,updatetime) 
	// values ('".$val['kodeorg']."','".$val['subbagian']."','".$val['bagian']."','".$val['karyawanid']."','".$val['idshift']."','".$val['shift']."','".$val['namashift']."','".$val['tanggal']."','".$val['posting']."','".$val['createby']."','".$val['createtime']."','".$val['updateby']."','".$val['updatetime']."')";
	// $owlPDO->exec($strx);
	
	// $strx="delete from ".$dbname.".sdm_5shiftanggota where karyawanid='".$val['karyawanid']."' and tanggal='".$val['tanggal']."'";
	// $owlPDO->exec($strx);
// }

#bjr setup
$hasil=0;
$str = "select * from ".$dbname.".setup_blok";
$res = fetchdata($str);
foreach($res as $bar){
	$sql = "select * from ".$dbname.".kebun_5bjr where periode = '".$persekarang."' and kodeorg='".$bar['kodeorg']."'";
	$req = fetchdata($sql);
	if(count($req)>0){
	}else{
		#ambil berdasarkan produksi 3 bulan lalu
		// $sql = "select sum(kgwb/jjg) as bjr from ".$dbname.".kebun_spb_vw where substr(tanggal,1,7) BETWEEN '".periodelalu(periodelalu(periodelalu($persekarang)))."' and '".periodelalu($persekarang)."' and blok ='".$bar['kodeorg']."'";
		// $req = fetchdata($sql);
		// $bjract = $req[0]['bjr'];
		
		#ambil bjr dari setup
		$query = "select * from ".$dbname.".kebun_5bjr where periode = '".periodelalu($persekarang)."' and kodeorg='".$bar['kodeorg']."'";
		$reque = fetchdata($query);
		foreach($reque as $val){
			$strinsert = "insert into ".$dbname.".kebun_5bjr (`kodeorg`, `kelaspohon`, `bjr`, `tahunproduksi`,`periode`,`updateby`) 
			values ('".$val['kodeorg']."','','".$val['bjr']."','".substr($persekarang,0,4)."','".$persekarang."','".$val['updateby']."')";
			$owlPDO->exec($strinsert);
			$hasil++;
			//echo $val['kodeorg']." = ".$val['bjr']."<br>";
		}
	}
}

$tigabllalu=date('Y-m', strtotime($tglskrg." -2 month"));
$bulanlalu=date('Y-m', strtotime($tglskrg." -1 month"));
$bulanini = date("Y-m");

#karyawan bhl tidak aktif > 4 bulan maka dinon aktifkan otomatis
if(date('d')>='25'){	
	$str = "select distinct nikpemel, nik from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where tanggal between '".$bulanini."-01' and '".$tglskrg."'";
	$res = fetchdata($str);
	foreach($res as $bar){
		if($bar['nikpemel']!=''){		
			$hadir[$bar['nikpemel']]=$bar['nikpemel'];
		}else{
			$hadir[$bar['nik']]=$bar['nik'];
		}
	}
	$str = "select distinct nikmandor, nikmandor1, nikasisten, keranimuat from ".$dbname.".kebun_aktifitas where tanggal between '".$bulanini."-01' and '".$tglskrg."'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$hadir[$bar['nikmandor']]=$bar['nikmandor'];
		$hadir[$bar['nikmandor1']]=$bar['nikmandor1'];
		$hadir[$bar['nikasisten']]=$bar['nikasisten'];
		$hadir[$bar['keranimuat']]=$bar['keranimuat'];
	}
		
	$str = "select distinct karyawanid from ".$dbname.".sdm_absensidt where tanggal between '".$bulanini."-01' and '".$tglskrg."'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$hadir[$bar['karyawanid']]=$bar['karyawanid'];
	}

	$str = "select distinct idkaryawan as karyawanid from ".$dbname.".vhc_runhk_vw where tanggal between '".$bulanini."-01' and '".$tglskrg."'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$hadir[$bar['karyawanid']]=$bar['karyawanid'];
	}	

	$str = "select distinct karyawanid from ".$dbname.".sdm_gaji_vw where periodegaji between '".$tigabllalu."' and '".$bulanlalu."'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$hadir[$bar['karyawanid']]=$bar['karyawanid'];
	}


	// $str = "select * from ".$dbname.".datakaryawan where tanggalkeluar = '0000-00-00' and tipekaryawan='4'";
	// $res = fetchdata($str);
	// foreach($res as $bar){
	// 	if(empty($hadir[$bar['karyawanid']])){
	// 		$strupd=" update ".$dbname.".datakaryawan set tanggalkeluar='".tglbulandepan($bulanini."-01")."', statuskaryawan='Keluar' where karyawanid='".$bar['karyawanid']."'";
	// 		try{$owlPDO->exec($strupd);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	// 	}
	// }
}


// echo "BJR ".$hasil;

function enamblnlalu($tgl){
	$tgl=str_replace('-','',$tgl);
	$newdate = strtotime('-6 month',strtotime($tgl));
	$newdate = date('Y-m-d', $newdate);
	return $newdate;
}
function tigablnlalu($tgl){
	$tgl=str_replace('-','',$tgl);
	$newdate = strtotime('-3 month',strtotime($tgl));
	$newdate = date('Y-m-d', $newdate);
	return $newdate;
}

?>