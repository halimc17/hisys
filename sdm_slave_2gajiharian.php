<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$unit=checkPostGet('unit','');
$divisi=checkPostGet('divisi','');
$tipekar=checkPostGet('tipekar','');
$tgl1=tanggalsystemn(checkPostGet('tgl1',''));
$tgl2=tanggalsystemn(checkPostGet('tgl2',''));


$optNmKomponen=  makeOption($dbname, 'sdm_ho_component', 'id,name');
$optnmjab=  makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');

$nmorg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmtipekar=  makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');

$ketipeorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
$kept= makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
@$tipeorg = $ketipeorg[$unit];
@$kodept=$kept[$unit];

$arrtipekar=array();
$iTipe="select distinct tipekaryawan from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5tipekaryawan b on a.tipekaryawan=b.id 
	where lokasitugas='".$unit."' and alokasi=0 and tipekaryawan<>0";
	$nTipe=$owlPDO->query($iTipe) or die(print " Gagal: ".PDOException::getMessage());
	$nTipe->setFetchMode(PDO::FETCH_ASSOC);
	while($dTipe=$nTipe->fetch())
	{
	    $arrtipekar[$dTipe['tipekaryawan']]=$dTipe['tipekaryawan'];
	}

if($tipekar==''){
	$where=" and tipekaryawan not in ('0','7','8')";
	if (count($arrtipekar)>0) {
		$where=" and tipekaryawan in ('".implode("','",$arrtipekar)."')";
	}
}else{
	$where.=" and tipekaryawan='".$tipekar."'";
}
	
#= komponen yang tidak termasuk di slip gaji
$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and	kodeparameter='KOMGJEXSLP'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();	
$exslip=$bar['nilai'];

$str="select id,name from ".$dbname.".sdm_ho_component where plus='1'  and id not in (".$exslip.") ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$dtkomplus[$bar['id']]=$bar['id'];
	$nmkom[$bar['id']]=$bar['name'];
}

//ditambah 1 untuk total
$tbrskomplus=count($dtkomplus)+1;

$str="select id,name from ".$dbname.".sdm_ho_component where plus='0'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$dtkommin[$bar['id']]=$bar['id'];
	$nmkom[$bar['id']]=$bar['name'];
}
$tbrskommin=count($dtkommin)+1;

$where1='';
if(strlen($divisi)=='6'){
	$where1.=" and subbagian='".$divisi."'";
} else if(strlen($divisi)=='4'){
	$where1.=" and subbagian=''";
}

$regional=makeOption($dbname,'bgt_regional_assignment','kodeunit,regional');
@$regorg=$regional[$unit];




#bentuk list karyawan
$str="select * from ".$dbname.".datakaryawan where lokasitugas='".$unit."' ".$where1."
		and (tanggalkeluar='00000-00-00' or tanggalkeluar>= '".$tgl2."') ".$where." order by namakaryawan asc ";
		// echo $str;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$dtkarid[$bar['karyawanid']]=$bar['karyawanid'];
	$dtafd[$bar['subbagian']]=$bar['subbagian'];
	$listidkar[$bar['subbagian']][$bar['karyawanid']]=$bar['karyawanid'];
	$nik[$bar['subbagian']][$bar['karyawanid']]=$bar['nik'];
	$nmkar[$bar['subbagian']][$bar['karyawanid']]=$bar['namakaryawan'];
	$norek[$bar['subbagian']][$bar['karyawanid']]=$bar['norekeningbank'];
	$namabank[$bar['subbagian']][$bar['karyawanid']]=$bar['namabank'];
	$stpajak[$bar['subbagian']][$bar['karyawanid']]=$bar['statuspajak'];
	$kodecatux[$bar['subbagian']][$bar['karyawanid']]=$bar['kodecatu'];
	$tpkar[$bar['subbagian']][$bar['karyawanid']]=$bar['tipekaryawan'];
	$jabatan[$bar['subbagian']][$bar['karyawanid']]=$bar['kodejabatan'];
	$dtbpjskerja[$bar['subbagian']][$bar['karyawanid']]=$bar['jms']; #bpjstenaga u/ jkk,jkm,jht
	$dtbpjssehat[$bar['subbagian']][$bar['karyawanid']]=$bar['bpjs']; #bpjskes
	$dtbpjspensiun[$bar['subbagian']][$bar['karyawanid']]=$bar['pensiun'];#bpjs pensiun //jp
	//bentuk tpkar untuk foreach pejabat bkm
	$tpkarbkm[$bar['karyawanid']]=$bar['tipekaryawan'];
}

@$cekdata=count($dtkarid);
if($cekdata<1){
	exit("Warning:Data Kosong");
}


// echo"<pre>";
// print_r($dtkarid);
// echo"</pre>";

/*****************************************************************************************************************/

//lembur
$str="select a.*,b.subbagian from ".$dbname.".sdm_lemburdt a left join ".$dbname.".datakaryawan b
		on a.karyawanid=b.karyawanid where substring(kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')
		and tanggal between '".$tgl1."' and '".$tgl2."'  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$jamlembur[$bar['subbagian']][$bar['karyawanid']]+=$bar['jamaktual'];
	@$rupiah[$bar['subbagian']][$bar['karyawanid']]['33']+=$bar['uangkelebihanjam'];
}

//bkm
$str="select a.*,b.subbagian from ".$dbname.".kebun_kehadiran_vw a left join ".$dbname.".datakaryawan b
		on a.karyawanid=b.karyawanid where unit in 
		(select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."') and tanggal between '".$tgl1."' and '".$tgl2."' and notransaksi not like '%BOR%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['subbagian']][$bar['karyawanid']]['32']+=$bar['insentif'];
	@$hk[$bar['subbagian']][$bar['karyawanid']]+=$bar['jhk'];
	@$rupiah[$bar['subbagian']][$bar['karyawanid']]['1']+=$bar['umr'];
	
}
//borongan sendiri
$str="select a.*,b.subbagian from ".$dbname.".kebun_kehadiran_vw a left join ".$dbname.".datakaryawan b
		on a.karyawanid=b.karyawanid where unit in 
		(select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."') and tanggal between '".$tgl1."' and '".$tgl2."'  and notransaksi like '%BOR%'  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['subbagian']][$bar['karyawanid']]['30']+=$bar['insentif'];
}

//panen
$str="select * from ".$dbname.".kebun_prestasi_vs_hk  
where unit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')  and tanggal between '".$tgl1."' and '".$tgl2."'  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	if($bar['kodetipekaryawan']=='4'){
		@$rupiah[$bar['subbagian']][$bar['karyawanid']]['1']+=$bar['upahkerja'];
	}
	@$rupiah[$bar['subbagian']][$bar['karyawanid']]['37']+=$bar['upahpenalty'];
	@$rupiah[$bar['subbagian']][$bar['karyawanid']]['32']+=$bar['tpremi'];
	@$hk[$bar['subbagian']][$bar['karyawanid']]+=$bar['hkpanenperhari'];
	@$rupiah[$bar['subbagian']][$bar['karyawanid']]['34']+=$bar['rupiahpenalty'];
}


//absensi
$str="select * from ".$dbname.".sdm_absensidt_vw where lokasitugas in 
	(select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')  and tanggal between '".$tgl1."' and '".$tgl2."'  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['subbagian']][$bar['karyawanid']]['32']+=$bar['premi'];
	@$hk[$bar['subbagian']][$bar['karyawanid']]+=$bar['nilaihk'];
	@$rupiah[$bar['subbagian']][$bar['karyawanid']]['34']+=$bar['penaltykehadiran'];
	if($bar['tipekaryawan']!=1){//kalau bns sudah ambil dari gapok	
		@$rupiah[$bar['subbagian']][$bar['karyawanid']]['1']+=$bar['umr'];
	}
	
	@$rupiah[$bar['subbagian']][$bar['karyawanid']]['63']+=$bar['insentif'];
	@$rupiah[$bar['subbagian']][$bar['karyawanid']]['64']+=$bar['insentiflibur'];
}






//vhc
$str="select a.*,b.lokasitugas,b.subbagian,tipekaryawan from ".$dbname.".vhc_runhk_vw a left join ".$dbname.".datakaryawan b
		on a.idkaryawan=b.karyawanid where lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')  
		and tanggal between '".$tgl1."' and '".$tgl2."'  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['subbagian']][$bar['idkaryawan']]['32']+=$bar['premi'];
	@$hk[$bar['subbagian']][$bar['idkaryawan']]+=$bar['hk'];
	if($bar['tipekaryawan']!=1){//kalau bns sudah ambil dari gapok	
		@$rupiah[$bar['subbagian']][$bar['idkaryawan']]['1']+=$bar['upah'];
	}
	@$rupiah[$bar['subbagian']][$bar['idkaryawan']]['34']+=$bar['penalty'];
}

#= vhc sipil

$str="select a.*,b.lokasitugas,b.subbagian,tipekaryawan from ".$dbname.".vhc_spl_kehadiran_vw a left join ".$dbname.".datakaryawan b
		on a.nik=b.karyawanid where lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')  
		and tanggal between '".$tgl1."' and '".$tgl2."'  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['subbagian']][$bar['nik']]['32']+=$bar['premi'];
	@$hk[$bar['subbagian']][$bar['nik']]+=$bar['jhk'];
	if($bar['tipekaryawan']!=1){//kalau bns sudah ambil dari gapok	
		@$rupiah[$bar['subbagian']][$bar['nik']]['1']+=$bar['umr'];
	}
}




#bentuk premi pejabat bkm
$str="select a.*,b.subbagian from ".$dbname.".kebun_premikemandoran a left join ".$dbname.".datakaryawan b
		on a.karyawanid=b.karyawanid where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')  and periode like '".substr($tgl1,0,7)."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['subbagian']][$bar['karyawanid']]['32']+=$bar['premiinput'];
}



$str="select a.*,b.subbagian,b.lokasitugas,b.tipekaryawan from ".$dbname.".sdm_5gajipokok a left join ".$dbname.".datakaryawan b
		on a.karyawanid=b.karyawanid where lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')  and tahun = '".substr($tgl1,0,4)."' and idkomponen not in ('59') ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	if($bar['idkomponen']==1){
		@$umrbln[$bar['subbagian']][$bar['karyawanid']]=$bar['jumlah'];
		if($bar['tipekaryawan']!=4){
			@$rupiah[$bar['subbagian']][$bar['karyawanid']][$bar['idkomponen']]=$bar['jumlah'];
			@$umrhari[$bar['karyawanid']]=$bar['jumlah']/25;
		}else{
			@$umrhari[$bar['karyawanid']]=$bar['jumlah']/25;
		}
	}else{
		@$rupiah[$bar['subbagian']][$bar['karyawanid']][$bar['idkomponen']]=$bar['jumlah'];
	}	
}




#pendapatan lain
$str="select a.*,b.subbagian,b.lokasitugas,b.tipekaryawan from ".$dbname.".sdm_pendapatanlaindt a left join ".$dbname.".datakaryawan b
		on a.karyawanid=b.karyawanid where lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')  and periodegaji = '".substr($tgl1,0,7)."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['subbagian']][$bar['karyawanid']][$bar['idkomponen']]=$bar['jumlah'];
}

#catu beras
//panen
$str="select a.*,b.subbagian,b.lokasitugas,b.tipekaryawan from ".$dbname.".sdm_catu a left join ".$dbname.".datakaryawan b
		on a.karyawanid=b.karyawanid where lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')  
		and periodegaji = '".substr($tgl1,0,7)."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['subbagian']][$bar['karyawanid']]['60']=$bar['jumlahrupiah'];
}


#bentuk ambil premi lainnya
$str="select * from ".$dbname.".sdm_premi  
		where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')  
		and periode='".substr($tgl1,0,7)."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['subbagian']][$bar['karyawanid']][$bar['jenis']]+=$bar['tpremi'];
}


#bentuk ambil premi lainnya
$str="select * from ".$dbname.".kebun_3premibmtbs  
		where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')  
		and periode='".substr($tgl1,0,7)."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['divisi']][$bar['karyawanid']]['32']+=$bar['rplb'];
}

#ini tidak perlu sudah saya masukkan ke kebun prestasi
/* $str="select * from ".$dbname.".kebun_3premipemanen  
		where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')  
		and periode='".substr($tgl1,0,7)."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['divisi']][$bar['karyawanid']]['32']+=($bar['rplb1']+$bar['rplb2']+$bar['rpbrd']);
} */


/*****************************************************************************************************************/
/*****************************************************************************************************************/
/*****************************************************************************************************************/
/*****************************************************************************************************************/



#bentuk absen  mandor BKM
$str="select a.nikmandor,a.tanggal,a.kodeorg,b.subbagian from ".$dbname.".kebun_aktifitas a 
		left join ".$dbname.".datakaryawan b on a.nikmandor=b.karyawanid where 
		kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."') 
		and tanggal between '".$tgl1."' and '".$tgl2."' and nikmandor!='' and (nospk='' or nospk is null)  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	//bentuk tanggal dulu
	$dtmandor[$bar['nikmandor']]=$bar['nikmandor'];
	$dttgl[$bar['tanggal']]=$bar['tanggal'];
	$dtpejabatbkm[$bar['nikmandor']][$bar['tanggal']]=$bar['nikmandor'];
	$dtpejabatbkmsubbag[$bar['nikmandor']][$bar['tanggal']]=$bar['subbagian'];
	$counttgl[$bar['nikmandor']][$bar['tanggal']]=1;
}


#= sipil
$str="select a.mandor,a.tanggal,a.kodeorg,b.subbagian from ".$dbname.".vhc_splht a 
		left join ".$dbname.".datakaryawan b on a.mandor=b.karyawanid where 
		kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."') 
		and tanggal between '".$tgl1."' and '".$tgl2."' and mandor!='' ";
		// echo $str;exit();
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	//bentuk tanggal dulu
	$dtmandor[$bar['mandor']]=$bar['mandor'];
	$dttgl[$bar['tanggal']]=$bar['tanggal'];
	$dtpejabatbkm[$bar['mandor']][$bar['tanggal']]=$bar['mandor'];
	$dtpejabatbkmsubbag[$bar['mandor']][$bar['tanggal']]=$bar['subbagian'];
	$counttgl[$bar['mandor']][$bar['tanggal']]=1;
}


#mandor 1
$str="select a.nikmandor1,a.tanggal,a.kodeorg,b.subbagian from ".$dbname.".kebun_aktifitas a 
		left join ".$dbname.".datakaryawan b on a.nikmandor1=b.karyawanid where 
		kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')  
		and tanggal between '".$tgl1."' and '".$tgl2."' and nikmandor1!='' and (nospk='' or nospk is null) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	//bentuk tanggal dulu
	$dtmandor[$bar['nikmandor1']]=$bar['nikmandor1'];
	$dttgl[$bar['tanggal']]=$bar['tanggal'];
	$dtpejabatbkm[$bar['nikmandor1']][$bar['tanggal']]=$bar['nikmandor1'];
	$dtpejabatbkmsubbag[$bar['nikmandor1']][$bar['tanggal']]=$bar['subbagian'];
	$counttgl[$bar['nikmandor1']][$bar['tanggal']]=1;
}

#= mandor 1 sipil
$str="select a.mandor1,a.tanggal,a.kodeorg,b.subbagian from ".$dbname.".vhc_splht a 
		left join ".$dbname.".datakaryawan b on a.mandor1=b.karyawanid where 
		kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')  
		and tanggal between '".$tgl1."' and '".$tgl2."' and mandor1!=''  ";		

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	//bentuk tanggal dulu
	$dtmandor[$bar['mandor1']]=$bar['mandor1'];
	$dttgl[$bar['tanggal']]=$bar['tanggal'];
	$dtpejabatbkm[$bar['mandor1']][$bar['tanggal']]=$bar['mandor1'];
	$dtpejabatbkmsubbag[$bar['mandor1']][$bar['tanggal']]=$bar['subbagian'];
	$counttgl[$bar['mandor1']][$bar['tanggal']]=1;
}


//krani
$str="select a.keranimuat,a.tanggal,a.kodeorg,b.subbagian from ".$dbname.".kebun_aktifitas a 
		left join ".$dbname.".datakaryawan b on a.keranimuat=b.karyawanid where 
		kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')  
and tanggal between '".$tgl1."' and '".$tgl2."' and keranimuat!='' and (nospk='' or nospk is null) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	//bentuk tanggal dulu
	$dtmandor[$bar['keranimuat']]=$bar['keranimuat'];
	$dttgl[$bar['tanggal']]=$bar['tanggal'];
	$dtpejabatbkm[$bar['keranimuat']][$bar['tanggal']]=$bar['keranimuat'];
	$dtpejabatbkmsubbag[$bar['keranimuat']][$bar['tanggal']]=$bar['subbagian'];
	$counttgl[$bar['keranimuat']][$bar['tanggal']]=1;
}

#= krani sipil
$str="select a.krani,a.tanggal,a.kodeorg,b.subbagian from ".$dbname.".vhc_splht a 
		left join ".$dbname.".datakaryawan b on a.krani=b.karyawanid where 
		kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')  
and tanggal between '".$tgl1."' and '".$tgl2."' and krani!='' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	//bentuk tanggal dulu
	$dtmandor[$bar['krani']]=$bar['krani'];
	$dttgl[$bar['tanggal']]=$bar['tanggal'];
	$dtpejabatbkm[$bar['krani']][$bar['tanggal']]=$bar['krani'];
	$dtpejabatbkmsubbag[$bar['krani']][$bar['tanggal']]=$bar['subbagian'];
	$counttgl[$bar['krani']][$bar['tanggal']]=1;
}

		



//krani panen
$str="select a.nikasisten,a.tanggal,a.kodeorg,b.subbagian from ".$dbname.".kebun_aktifitas a 
		left join ".$dbname.".datakaryawan b on a.nikasisten=b.karyawanid where 
		kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')
		and tanggal between '".$tgl1."' and '".$tgl2."' and nikasisten!='' and (nospk='' or nospk is null) ";
		//echo $str;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	//bentuk tanggal dulu
	$dtmandor[$bar['nikasisten']]=$bar['nikasisten'];
	$dttgl[$bar['tanggal']]=$bar['tanggal'];
	$dtpejabatbkm[$bar['nikasisten']][$bar['tanggal']]=$bar['nikasisten'];
	$dtpejabatbkmsubbag[$bar['nikasisten']][$bar['tanggal']]=$bar['subbagian'];
	$counttgl[$bar['nikasisten']][$bar['tanggal']]=1;
}


### tambahan pejabat sipil mandor,mandor1,kerani








// echo"<pre>";
// print_r($counttgl);
// echo"</pre>";
if(isset($dtmandor))
foreach ($dtmandor as $karid){
	foreach($dttgl as $tgl){
		if(@$dtpejabatbkm[$karid][$tgl]!=''){
			$day = date('D', strtotime($tgl));
			if($day=='Sun')$libur=true; else $libur=false;
			// kamus hari libur
			$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$tgl."' and (kebun='GLOBAL' or kebun='".$unit."')";
			$queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
			$queorg->setFetchMode(PDO::FETCH_ASSOC);                   
			while($roworg=$queorg->fetch()){
				if($roworg['keterangan']=='libur')$libur=true;
				if($roworg['keterangan']=='masuk')$libur=false;
			}
			if($libur==false){
				if(@$tpkarbkm[$karid]!=1){
					@$rupiah[$dtpejabatbkmsubbag[$karid][$tgl]][$karid]['1']+=$counttgl[$karid][$tgl]*$umrhari[$karid];
					//@$hk[$dtpejabatbkmsubbag[$karid][$tgl]][$karid]+=$counttgl[$karid][$tgl];
				}
				@$hk[$dtpejabatbkmsubbag[$karid][$tgl]][$karid]+=$counttgl[$karid][$tgl];
			}else{
				//jika khl dan ada H maka bayar
				if(@$tpkarbkm[$karid]==4){
					@$rupiah[$dtpejabatbkmsubbag[$karid][$tgl]][$karid]['1']+=$counttgl[$karid][$tgl]*$umrhari[$karid];
					@$hk[$dtpejabatbkmsubbag[$karid][$tgl]][$karid]+=$counttgl[$karid][$tgl];
				}
			}
		}
	}
}

/*****************************************************************************************************************/
#sdm_potongan
$str="select a.*,b.subbagian,b.lokasitugas,b.tipekaryawan from ".$dbname.".sdm_potongandt a left join ".$dbname.".datakaryawan b
		on a.nik=b.karyawanid where lokasitugas='".$unit."' and periodegaji = '".substr($tgl1,0,7)."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['subbagian']][$bar['nik']][$bar['tipepotongan']]=$bar['jumlahpotongan'];
}


$str = "select a.*,b.lokasitugas,b.subbagian from ".$dbname.".sdm_angsuran a left join 
		".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
        where lokasitugas='".$unit."' and start<='".substr($tgl1,0,7)."' and end>='".substr($tgl1,0,7)."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['subbagian']][$bar['karyawanid']][$bar['jenis']]=$bar['bulanan'];
}		


#bpjs

if ($tipeorg == 'PABRIK') {
    $bpjsorg = 'PABRIK';
} else {
    $bpjsorg = 'KEBUN';
}

#= parameter aplikasi 

#= kerja
$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and	kodeparameter='HRBPJSKER'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();	
	$arrbpjs=explode(',',$bar['nilai']);
	foreach($arrbpjs as $key){
		$arrker[]=$key;
	}

#= kesehatan
$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and	kodeparameter='HRBPJSKES'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();	
	$arrbpjs=explode(',',$bar['nilai']);
	foreach($arrbpjs as $key){
		$arrkes[]=$key;
	}

#= pensiun
$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and	kodeparameter='HRBPJSPEN'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();	
	$arrbpjs=explode(',',$bar['nilai']);
	foreach($arrbpjs as $key){
		$arrpen[]=$key;
	}
	
$str = "select * from " . $dbname . ".sdm_5bpjs where lokasibpjs='" . $bpjsorg . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar = $res->fetch()){
	foreach ($dtafd as $afd){
		foreach ($dtkarid as $karid){
			if(@$listidkar[$afd][$karid]!=''){
				if(@$dtbpjskerja[$afd][$karid]!=''){
					if(in_array($bar['jenisbpjs'],$arrker)){
						@$rupiah[$afd][$karid][$bar['jenisbpjs']]=$bar['bebankaryawan']/100*$umrbln[$afd][$karid];
						@$rupiah[$afd][$karid][$bar['jenisbpjsplus']]=$bar['bebanperusahaan']/100*$umrbln[$afd][$karid];
					}
				}
				
				if(@$dtbpjssehat[$afd][$karid]!=''){
					if(in_array($bar['jenisbpjs'],$arrkes)){
						@$rupiah[$afd][$karid][$bar['jenisbpjs']]=$bar['bebankaryawan']/100*$umrbln[$afd][$karid];
						@$rupiah[$afd][$karid][$bar['jenisbpjsplus']]=$bar['bebanperusahaan']/100*$umrbln[$afd][$karid];
					}
				}
				
				if(@$dtbpjspensiun[$afd][$karid]!=''){
					if(in_array($bar['jenisbpjs'],$arrpen)){
						@$rupiah[$afd][$karid][$bar['jenisbpjs']]=$bar['bebankaryawan']/100*$umrbln[$afd][$karid];
						@$rupiah[$afd][$karid][$bar['jenisbpjsplus']]=$bar['bebanperusahaan']/100*$umrbln[$afd][$karid];
					}
				}
				
			}
		}
	}
}	
	
// echo"<pre>";
// print_r($rupiah);
// echo"</pre>";

/*****************************************************************************************************************/

$coslpantafd=$tbrskomplus+$tbrskommin+8;
//$stream= "";
if ($proses == 'excel') {
    $stream.= "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.= "<table class='sortable' cellspacing='1'>";
}

array_multisort($dtafd,SORT_ASC);



$stream.="<thead><tr class=rowcontent>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['nomor']."</th>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['divisi']."</th>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['nik2']."</th>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['namakaryawan']."</th>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['jabatan']."</th>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['tipekaryawan']."</th>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['status']." pajak</th>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['status']." catu</th>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['rekening']."</th>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['hk']."</th>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['lembur']."</th>";
	
	$stream.="<th align=center colspan=".$tbrskomplus.">".$_SESSION['lang']['penambah']."</th>";
	$stream.="<th align=center colspan=".$tbrskommin.">".$_SESSION['lang']['pengurang']."</th>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['total']."</th>";
	
$stream.="</tr>";

$stream.="<tr>";
foreach ($dtkomplus as $komplus){
		$stream.="<th align=center>".$nmkom[$komplus]."</th>";
}
$stream.="<th align=center>".$_SESSION['lang']['total']."</th>";
foreach ($dtkommin as $kommin){
		$stream.="<th align=center>".$nmkom[$kommin]."</th>";
}
$stream.="<th align=center>".$_SESSION['lang']['total']."</th>";
$stream.="</tr>";	
$stream.="</thead>";




foreach ($dtafd as $afd){
	if(@$nmorg[$afd]==''){
		@$kdafd='Umum';
	}else{
		@$kdafd=$afd;
	}
	foreach ($dtkarid as $karid){
		if(@$listidkar[$afd][$karid]!=''){
			
			
			#cek hanya data dengan netto 0 yang di tampilkan
			foreach ($dtkomplus as $komplus){
				if(@$rupiah[$afd][$karid]['1']>@$umrbln[$afd][$karid] && $tpkar[$afd][$karid]!='4'){
					$rupiah[$afd][$karid]['1']=$umrbln[$afd][$karid];
				}
				@$cektkomplus[$afd][$karid]+=$rupiah[$afd][$karid][$komplus];
			}
			foreach ($dtkommin as $kommin){
				@$cektkommin[$afd][$karid]+=$rupiah[$afd][$karid][$kommin];
			}
			@$cektnettokar[$afd][$karid]=$cektkomplus[$afd][$karid]-$cektkommin[$afd][$karid];
			##tutup cek
			#= lepas cek dulu, untuk melihat data 26 s/d 31
			#= karna pengurangnya hitung bulanan, sehingga data minus, 
			
			// if($cektnettokar[$afd][$karid]>0){			
				@$no++;
				$stream.="<tr class=rowcontent>";
				$stream.="<td align=center>".$no."</td>";
				$stream.="<td>".$kdafd."</td>";
				$stream.="<td>'".$nik[$afd][$karid]."</td>";
				$stream.="<td>".$nmkar[$afd][$karid]."</td>";
				$stream.="<td>".$optnmjab[$jabatan[$afd][$karid]]."</td>";
				$stream.="<td>".$nmtipekar[$tpkar[$afd][$karid]]."</td>";
				$stream.="<td>".$stpajak[$afd][$karid]."</td>";
				$stream.="<td>".$kodecatux[$afd][$karid]."</td>";
				$stream.="<td>".$namabank[$afd][$karid]." - ".$norek[$afd][$karid]."</td>";
				
				$stream.="<td align=right>".@number_format($hk[$afd][$karid],2)."</td>";
				$stream.="<td align=right>".@number_format($jamlembur[$afd][$karid],2)."</td>";
				foreach ($dtkomplus as $komplus){
					if(@$rupiah[$afd][$karid]['1']>@$umrbln[$afd][$karid] && $tpkar[$afd][$karid]!='4'){
						$rupiah[$afd][$karid]['1']=$umrbln[$afd][$karid];
					}
					$stream.="<td align=right>".@number_format($rupiah[$afd][$karid][$komplus])."</td>";
					@$tkomplus[$afd][$karid]+=$rupiah[$afd][$karid][$komplus];
					@$subtkomplus[$afd][$komplus]+=$rupiah[$afd][$karid][$komplus];
				}
				$stream.="<td align=right>".@number_format($tkomplus[$afd][$karid])."</td>";
				foreach ($dtkommin as $kommin){
					$stream.="<td align=right>".@number_format($rupiah[$afd][$karid][$kommin])."</td>";
					@$tkommin[$afd][$karid]+=$rupiah[$afd][$karid][$kommin];
					@$subtkommin[$afd][$kommin]+=$rupiah[$afd][$karid][$kommin];
				}
				$stream.="<td align=right>".@number_format($tkommin[$afd][$karid])."</td>";
				@$tnettokar[$afd][$karid]=$tkomplus[$afd][$karid]-$tkommin[$afd][$karid];
				$stream.="<td align=right>".@number_format($tnettokar[$afd][$karid])."</td>";
				$stream.="</tr>";
				@$ttlhk[$afd]+=$hk[$afd][$karid];
				@$ttllembur[$afd]+=$jamlembur[$afd][$karid];
			// }
						
		}
	}
	$stream.="<tr bgcolor=lightgray>";
	$stream.="<td align=center colspan=9>".$_SESSION['lang']['subtotal']." ".$kdafd." - ".@$nmorg[$kdafd]."</td>";
	$stream.="<td align=right>".@number_format($ttlhk[$afd],2)."</td>";
	$stream.="<td align=right>".@number_format($ttllembur[$afd],2)."</td>";
	foreach ($dtkomplus as $komplus){
		$stream.="<td align=right>".@number_format($subtkomplus[$afd][$komplus])."</td>";
		@$tsubtkomplus[$afd]+=$subtkomplus[$afd][$komplus];
		@$gtkomplus[$komplus]+=$subtkomplus[$afd][$komplus];
	}
	$stream.="<td align=right>".@number_format($tsubtkomplus[$afd])."</td>";
	foreach ($dtkommin as $kommin){
		$stream.="<td align=right>".@number_format($subtkommin[$afd][$kommin])."</td>";
		@$tsubtkommin[$afd]+=$subtkommin[$afd][$kommin];
		@$gtkommin[$kommin]+=$subtkommin[$afd][$kommin];
	}
	$stream.="<td align=right>".@number_format($tsubtkommin[$afd])."</td>";
	@$tsubtnetto[$afd]=$tsubtkomplus[$afd]-$tsubtkommin[$afd];
	
	$stream.="<td align=right>".@number_format($tsubtnetto[$afd])."</td>";
	$stream.="</tr>";
	
	#bentuk grandtotal
	@$gthk+=$ttlhk[$afd];
	@$gtllembur+=$ttllembur[$afd];
	@$gtnetto+=$tsubtnetto[$afd];
	
}
$stream.="<thead><tr>";
$stream.="<td align=center colspan=9>".$_SESSION['lang']['grnd_total']."</td>";
	$stream.="<td align=right>".@number_format($gthk,2)."</td>";
	$stream.="<td align=right>".@number_format($gtllembur,2)."</td>";
	foreach ($dtkomplus as $komplus){
		$stream.="<td align=right>".@number_format($gtkomplus[$komplus])."</td>";
		@$tgtkomplus+=$gtkomplus[$komplus];
	}
	$stream.="<td align=right>".@number_format($tgtkomplus)."</td>";
	foreach ($dtkommin as $kommin){
		$stream.="<td align=right>".@number_format($gtkommin[$kommin])."</td>";
		@$tgtkommin+=$gtkommin[$kommin];
	}
	$stream.="<td align=right>".@number_format($tgtkommin)."</td>";
	$stream.="<td align=right>".@number_format($gtnetto)."</td>";
	
$stream.="</tr></thead>";


// foreach ($dtkarid as $karid){
	// $stream.="<tr class=rowcontent>";
	// $stream.="<td>".$karid."<td>";
	// $stream.="</tr>";
// }


$stream.="<tbody></table>";
switch($proses){
	case 'getdivisi':
		$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$unit."%' and tipe ='AFDELING' order by kodeorganisasi asc";
		$optdivisi.="<option value=''></option>";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){	
			$optdivisi.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		}	

			echo $optdivisi;
	break;

	case 'getdivisitipe':
	$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='HRJBTNESGJ' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	$hrjbtnesgj=$bar['nilai'];

	if (trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING' ||trim($_SESSION['empl']['tipelokasitugas'])=='KANWIL') {
		$tpkry="";
	}else{
		$tpkry="and b.id not in (".$hrjbtnesgj.")";
	}



		$optdivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
		if(strlen($_SESSION['empl']['subbagian'])==''){
		$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$unit."%' 
		and tipe in ('AFDELING','BIBITAN','KEBUN','STATION','SIPIL','PABRIK','TRAKSI','WORKSHOP','MAINTENANCE') and length(kodeorganisasi)=6
		order by kodeorganisasi asc";
		$optdivisi.="<option value='".$unit."'>".$_SESSION['lang']['kantor']." / ".$_SESSION['lang']['umum']."</option>";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){	
				$optdivisi.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			}
		}else{
		$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$_SESSION['empl']['subbagian']."' 
		and tipe in ('AFDELING','BIBITAN','KEBUN','STATION','SIPIL','PABRIK','TRAKSI','WORKSHOP','MAINTENANCE') and length(kodeorganisasi)=6
		order by kodeorganisasi asc";	
		$optdivisi="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){	
				$optdivisi.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			}
		}

		//$optTipe="<option value=''>".$_SESSION['lang']['all']."</option>";
		$iTipe="select distinct tipekaryawan,tipe from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5tipekaryawan b on a.tipekaryawan=b.id 
		where lokasitugas='".$unit."' and alokasi=0 and tipekaryawan<>0 ".$tpkry." ";
		$nTipe=$owlPDO->query($iTipe) or die(print " Gagal: ".PDOException::getMessage());
		$nTipe->setFetchMode(PDO::FETCH_ASSOC);
		while($dTipe=$nTipe->fetch())
		{
		    $optTipe.="<option value=".$dTipe['tipekaryawan'].">".$dTipe['tipe']."</option>";
		}

			echo $optdivisi."#####".$optTipe;
		break;
######PREVIEW
    case 'preview':
        echo $stream;
    break;

######EXCEL	
    case 'excel':
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg=date("Ymd");
        //$nop_="laporan_total_komponen_gaji_".$kdorg."_".$per1."_sd_".per2;
        $nop_="laporan_gaji_harian";
        if(strlen($stream)>0)
        {
                if ($handle = opendir('tempExcel')) {
                        while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && $file != "index.html") {
                                @unlink('tempExcel/'.$file);
                        }
                        }	
                        closedir($handle);
                }
                $handle=fopen("tempExcel/".$nop_.".xls",'w');
                if(!fwrite($handle,$stream))
                {
                        echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                        exit;
                }
                else
                {
                        echo "<script language=javascript1.2>
                        window.location='tempExcel/".$nop_.".xls';
                        </script>";
                }
                fclose($handle);
        }     
        break;	
}
?>