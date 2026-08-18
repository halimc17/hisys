<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');



$proses = checkPostGet('proses', '');
$tPkary = checkPostGet('tPkary', '');
$tgl1=tanggalsystem(checkPostGet('tgl1',''));
$tgl2=tanggalsystem(checkPostGet('tgl2',''));
$karyId = checkPostGet('karyId', '');
$afdId = checkPostGet('afdId', '');
$kdUnit = checkPostGet('kdUnit', '');
$periode = checkPostGet('periode', '');


	if(($proses=='preview')||($proses=='excel')){
		if($periode==''){
        	exit("error:Periode Tidak Boleh Kosong");
	    } 
	    if($kdUnit==''){
	           exit("error:Unit Tidak Boleh Kosongsesama sahabat @TraxFM…");
	    }
	   
	    if($tgl2==''){
	    	exit('warning: Tanggal sampai tidak boleh kosong!');
	    }
	    if($tgl2<$tgl1){
	    	exit('warning: Urutan tanggalnya salah!'.$tgl2);
	    }
	    $wrtgl="periode='".$periode."' and kodeorg='".$kdUnit."'";
	    $scektgl="select * from ".$dbname.".sdm_5periodegaji where ".$wrtgl." 
	              and date(".$tgl2.") between tanggalmulai and tanggalsampai";
		$qcektgl=$owlPDO->query($scektgl) or die(print " Gagal: ".PDOException::getMessage());
		$rcektgl=owlBaris($qcektgl);
	    if($rcektgl==0){
	    	exit('warning: Tanggal Sampai Di luar Range Periode Gaji');
	    }
	}
	

$arrTgl=dates_inbetween($tgl1,$tgl2);
$optRegional=  makeOption($dbname, 'bgt_regional_assignment', 'kodeunit,regional');
$optNmKeg=  makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
$optSatKeg=  makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,satuan');
$prd=explode("-",$periode);
$arrBln=array(1=>"Januari",2=>"Februari",3=>"Maret",4=>"April",5=>"Mei",6=>"Juni",7=>"Juli",8=>"Agustus",9=>"September",10=>"Oktober",11=>"November",12=>"Desember");
array_multisort($arrTgl,SORT_ASC);

$garis=0;
if($proses=='excel'){
    $garis=1;
   $bgcolordt=" bgcolor=#DEDEDE";
}
$whereDt=" and lokasitugas='".$kdUnit."' and (subbagian='' or subbagian is null)";
if($afdId!=''){
	$whereDt=" and subbagian='".$afdId."'";
}
if($tPkary!=''){
	$whereDt.=" and b.tipekaryawan='".$tPkary."'";	
}
if($karyId!=''){
	$whereDt.=" and b.karyawanid='".$karyId."'";	
}
#ambil data dari kebun kehadiran untuk perawatan
$sHadir="select tanggal,a.karyawanid,umr,insentif,kodekegiatan,notransaksi,absensi,jhk from ".$dbname.".kebun_kehadiran_vw a
		 left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
         where tanggal between '".$tgl1."' and '".$tgl2."'  ".$whereDt."
         order by tanggal asc";
$qHadir=$owlPDO->query($sHadir) or die(print " Gagal: ".PDOException::getMessage());
$qHadir->setFetchMode(PDO::FETCH_ASSOC);
while($rHadir=$qHadir->fetch())	{
	$lstTgl[$rHadir['tanggal']]=$rHadir['tanggal'];
	$lstKary[$rHadir['karyawanid']]=$rHadir['karyawanid'];
	$lstKeg[$rHadir['kodekegiatan']]=$rHadir['kodekegiatan'];
	$dtRpgaji[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['umr'];
	$dtRppremi[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['insentif'];
	$dtAbsensi[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['absensi'];
	$dtJhk[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['jhk'];
	$dtTransaksi[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['notransaksi'];
	@$jmlhRowTgl[$rHadir['karyawanid'].$rHadir['tanggal']]+=1;
	$dafkerja[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['kodekegiatan'];
}

#ambil data dari kebun prestasi untuk panen
$sHadir="select tanggal,a.karyawanid,upahkerja as umr,upahpremi as insentif,notransaksi,upahpenalty as potonganhk,
         rupiahpenalty as dendapanen  from ".$dbname.".kebun_prestasi_vw a left join 
         ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
         where tanggal between '".$tgl1."' and '".$tgl2."'  ".$whereDt."
         order by tanggal asc";
$qHadir=$owlPDO->query($sHadir) or die(print " Gagal: ".PDOException::getMessage());
$qHadir->setFetchMode(PDO::FETCH_ASSOC);
while($rHadir=$qHadir->fetch())		
{
	$lstTgl[$rHadir['tanggal']]=$rHadir['tanggal'];
	$lstKary[$rHadir['karyawanid']]=$rHadir['karyawanid'];
	$rHadir['kodekegiatan']=611010101;
	$lstKeg[$rHadir['kodekegiatan']]=$rHadir['kodekegiatan'];
	$dtRpgaji[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['umr'];
	$dtRppremi[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['insentif'];
	$dtTransaksi[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['notransaksi'];
	$dafkerja[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['kodekegiatan'];
	@$jmlhRowTgl[$rHadir['karyawanid'].$rHadir['tanggal']]+=1;
	$sGaji="select (sum(jumlah)/25) as umr from ".$dbname.".sdm_5gajipokok where 
	        idkomponen='1' and tahun='".substr($periode,0,4)."' and karyawanid='".$rHadir['karyawanid']."'";
	$qGaji=$owlPDO->query($sGaji) or die(print " Gagal: ".PDOException::getMessage());
	$qGaji->setFetchMode(PDO::FETCH_ASSOC);
	$rGaji=$qGaji->fetch();
	$rHadir['absensi']='';
	$rHadir['jhk']='0';
	if($rGaji['umr']==$rHadir['umr']){
		$rHadir['absensi']='H';
		$rHadir['jhk']='1';
	}
	$dtAbsensi[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['absensi'];
	$dtJhk[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['jhk'];
	if($rHadir['potonganhk']!='0'){
		$rHadir['kodekegiatan']="POTHKPANEN";
		$dtRpPotongan[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['potonganhk'];
		@$jmlhRowTgl[$rHadir['karyawanid'].$rHadir['tanggal']]+=1;
		$lstKeg[$rHadir['kodekegiatan']]=$rHadir['kodekegiatan'];
		$dafkerja[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['kodekegiatan'];
	}
	if($rHadir['dendapanen']!='0'){
		$rHadir['kodekegiatan']="DENDAPANEN";
		$dtRpDenda[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['dendapanen'];
		@$jmlhRowTgl[$rHadir['karyawanid'].$rHadir['tanggal']]+=1;
		$lstKeg[$rHadir['kodekegiatan']]=$rHadir['kodekegiatan'];
		$dafkerja[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['kodekegiatan'];
	}
}

#ambil data dari absensi yang nilai 1 HK hk tidak di bayar
$sHadir="select absensi,tanggal,a.karyawanid,premi,penaltykehadiran from ".$dbname.".sdm_absensidt a left join 
         ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
         where tanggal between '".$tgl1."' and '".$tgl2."'  ".$whereDt."
         order by tanggal asc";
$qHadir=$owlPDO->query($sHadir) or die(print " Gagal: ".PDOException::getMessage());
$qHadir->setFetchMode(PDO::FETCH_ASSOC);
while($rHadir=$qHadir->fetch())		
{
	$lstTgl[$rHadir['tanggal']]=$rHadir['tanggal'];
	$lstKary[$rHadir['karyawanid']]=$rHadir['karyawanid'];
	$lstKeg[$rHadir['absensi']]=$rHadir['absensi'];
	$whrdttp="karyawanid='".$rHadir['karyawanid']."'";
	$optCekTp=makeOption($dbname,'datakaryawan','karyawanid,tipekaryawan',$whrdttp);
	
	$sGaji="select (sum(jumlah)/25) as umr from ".$dbname.".sdm_5gajipokok where 
	        idkomponen in (1) and tahun='".substr($periode,0,4)."' and karyawanid='".$rHadir['karyawanid']."'";
	$qGaji=$owlPDO->query($sGaji) or die(print " Gagal: ".PDOException::getMessage());
	$qGaji->setFetchMode(PDO::FETCH_ASSOC);
	$rGaji=$qGaji->fetch();
	$optAbsni=makeOption($dbname,'sdm_5absensi','kodeabsen,kelompok');
	if($optAbsni[$rHadir['absensi']]==1){
		$rHadir['jhk']=1;
		if($optCekTp[@$rHadir['karyawanid']]!=4){
			if(($rHadir['absensi']=='H')||($rHadir['absensi']=='C')){
				$dtRpgaji[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['absensi']]=$rGaji['umr'];
			}else{
				$dtRpgaji[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['absensi']]=0;
			}
		}else{
			if($rHadir['absensi']=='H'){
				$dtRpgaji[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['absensi']]=$rGaji['umr'];	
			}else{
				$dtRpgaji[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['absensi']]=0;
			}	
		}
	}else{
		$rHadir['jhk']=0;
		$dtRpPotongan[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['absensi']]=$rGaji['umr'];
	}
	$dtAbsensi[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['absensi']]=$rHadir['absensi'];
	$dtJhk[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['absensi']]=$rHadir['jhk'];
	$dtRppremi[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['absensi']]=$rHadir['premi'];
	@$dtTransaksi[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['absensi']]=$rHadir['notransaksi'];
	@$jmlhRowTgl[$rHadir['karyawanid'].$rHadir['tanggal']]+=1;
	$dafkerja[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['absensi']]=$rHadir['absensi'];
	if($rHadir['penaltykehadiran']!='0'){
		$rHadir['kodekegiatan']='PNLTYKHDRAN';
		$dtRpPinalty[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['penaltykehadiran'];
		@$jmlhRowTgl[$rHadir['karyawanid'].$rHadir['tanggal']]+=1;	
		$dafkerja[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['kodekegiatan'];
		$lstKeg[$rHadir['kodekegiatan']]=$rHadir['kodekegiatan'];
	}
}
#ambil data dari kebun aktifitas
$sHadir="select distinct nikmandor,nikmandor1,keranimuat,tanggal from ".$dbname.".kebun_aktifitas a left join
		 ".$dbname.".datakaryawan b on a.nikmandor=b.karyawanid or a.nikmandor1=b.karyawanid or a.keranimuat=b.karyawanid
         where tanggal between '".$tgl1."' and '".$tgl2."'    ".$whereDt." order by tanggal asc";
$qHadir=$owlPDO->query($sHadir) or die(print " Gagal: ".PDOException::getMessage());
$qHadir->setFetchMode(PDO::FETCH_ASSOC);
while($rHadir=$qHadir->fetch())		
{
	$lstTgl[$rHadir['tanggal']]=$rHadir['tanggal'];
	$rHadir['kodekegiatan']='HAKTIFITAS';
	$rHadir['jhk']=1;
	$lstKeg[$rHadir['kodekegiatan']]=$rHadir['kodekegiatan'];
	if(intval($rHadir['nikmandor'])!=0){
		$whrckabs="karyawanid='".$rHadir['nikmandor']."' and tanggal='".$rHadir['tanggal']."'";
		$optCek=makeOption($dbname,'sdm_absensidt','karyawanid,absensi',$whrckabs);
		if(@$optCek[@$rHadir['karyawanid']]!=''){
			continue;
		}
		$sGaji="select (sum(jumlah)/25) as umr from ".$dbname.".sdm_5gajipokok where 
		        idkomponen in (1) and tahun='".substr($periode,0,4)."' and karyawanid='".$rHadir['nikmandor']."'";
		$qGaji=$owlPDO->query($sGaji) or die(print " Gagal: ".PDOException::getMessage());
		$qGaji->setFetchMode(PDO::FETCH_ASSOC);
		$rGaji=$qGaji->fetch();
		$lstKary[$rHadir['nikmandor']]=$rHadir['nikmandor'];
		$dtRpgaji[$rHadir['nikmandor'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rGaji['umr'];
		@$jmlhRowTgl[$rHadir['nikmandor'].$rHadir['tanggal']]+=1;	
		$dafkerja[$rHadir['nikmandor'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['kodekegiatan'];	
		$dtJhk[$rHadir['nikmandor'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['jhk'];
	}
	
	if(intval($rHadir['nikmandor1'])!=0){
		$whrckabs="karyawanid='".$rHadir['nikmandor1']."' and tanggal='".$rHadir['tanggal']."'";
		$optCek=makeOption($dbname,'sdm_absensidt','karyawanid,absensi',$whrckabs);
		if($optCek[@$rHadir['karyawanid']]!=''){
			continue;
		}
		$sGaji="select (sum(jumlah)/25) as umr from ".$dbname.".sdm_5gajipokok where 
		        idkomponen in (1) and tahun='".substr($periode,0,4)."' and karyawanid='".$rHadir['nikmandor1']."'";
		$qGaji=$owlPDO->query($sGaji) or die(print " Gagal: ".PDOException::getMessage());
		$qGaji->setFetchMode(PDO::FETCH_ASSOC);
		$rGaji=$qGaji->fetch();
		$lstKary[$rHadir['nikmandor1']]=$rHadir['nikmandor1'];
		$dtRpgaji[$rHadir['nikmandor1'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rGaji['umr'];
		@$jmlhRowTgl[$rHadir['nikmandor1'].$rHadir['tanggal']]+=1;	
		$dafkerja[$rHadir['nikmandor1'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['kodekegiatan'];
		$dtJhk[$rHadir['nikmandor1'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['jhk'];
	}
	if(intval($rHadir['keranimuat'])!=0){
		$whrckabs="karyawanid='".$rHadir['keranimuat']."' and tanggal='".$rHadir['tanggal']."'";
		$optCek=makeOption($dbname,'sdm_absensidt','karyawanid,absensi',$whrckabs);
		if(@$optCek[@$rHadir['karyawanid']]!=''){
			continue;
		}
		$sGaji="select (sum(jumlah)/25) as umr from ".$dbname.".sdm_5gajipokok where 
		        idkomponen in (1) and tahun='".substr($periode,0,4)."' and karyawanid='".$rHadir['keranimuat']."'";
		$qGaji=$owlPDO->query($sGaji) or die(print " Gagal: ".PDOException::getMessage());
		$qGaji->setFetchMode(PDO::FETCH_ASSOC);
		$rGaji=$qGaji->fetch();
		$lstKary[$rHadir['keranimuat']]=$rHadir['keranimuat'];
		$dtRpgaji[$rHadir['keranimuat'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rGaji['umr'];
		@$jmlhRowTgl[$rHadir['keranimuat'].$rHadir['tanggal']]+=1;	
		$dafkerja[$rHadir['keranimuat'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['kodekegiatan'];	
		$dtJhk[$rHadir['keranimuat'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['jhk'];
	}
	
	
}

#ambil data dari lembur
$sHadir="select tipelembur,tanggal,a.karyawanid,uangkelebihanjam as umr from ".$dbname.".sdm_lemburdt a  left join 
         ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
         where tanggal between '".$tgl1."' and '".$tgl2."'    ".$whereDt."
         order by tanggal asc";
$qHadir=$owlPDO->query($sHadir) or die(print " Gagal: ".PDOException::getMessage());
$qHadir->setFetchMode(PDO::FETCH_ASSOC);
while($rHadir=$qHadir->fetch())		
{
	$lstTgl[$rHadir['tanggal']]=$rHadir['tanggal'];
	$lstKary[$rHadir['karyawanid']]=$rHadir['karyawanid'];
	$lstKeg[$rHadir['tipelembur']]=$rHadir['tipelembur'];
	$dtRplembur[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['tipelembur']]=$rHadir['umr'];
	@$jmlhRowTgl[$rHadir['karyawanid'].$rHadir['tanggal']]+=1;
	$dafkerja[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['tipelembur']]=$rHadir['tipelembur'];
}

#ambil data dari traksi
$sHadir="select idkaryawan as karyawanid,upah as umr,premi,tanggal,penalty from ".$dbname.".vhc_runhk_vw a left join 
         ".$dbname.".datakaryawan b on a.idkaryawan=b.karyawanid
         where tanggal between '".$tgl1."' and '".$tgl2."' ".$whereDt."
         order by tanggal asc";
$qHadir=$owlPDO->query($sHadir) or die(print " Gagal: ".PDOException::getMessage());
$qHadir->setFetchMode(PDO::FETCH_ASSOC);
while($rHadir=$qHadir->fetch())		
{
	
	$lstTgl[$rHadir['tanggal']]=$rHadir['tanggal'];
	$lstKary[$rHadir['karyawanid']]=$rHadir['karyawanid'];
	if($rHadir['premi']!='0'){
		$rHadir['kodekegiatan']='PREMTRK';
		$dtRppremi[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['premi'];
		@$jmlhRowTgl[$rHadir['karyawanid'].$rHadir['tanggal']]+=1;	
		$dafkerja[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['kodekegiatan'];
		$lstKeg[$rHadir['kodekegiatan']]=$rHadir['kodekegiatan'];
	}
	if($rHadir['penalty']!='0'){
		$rHadir['kodekegiatan']='PNLTYTRK';
		$dtRppremi[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['penalty'];
		@$jmlhRowTgl[$rHadir['karyawanid'].$rHadir['tanggal']]+=1;	
		$dafkerja[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['kodekegiatan']]=$rHadir['kodekegiatan'];
		$lstKeg[$rHadir['kodekegiatan']]=$rHadir['kodekegiatan'];
	}
	$whrck="karyawanid='".$rHadir['karyawanid']."' and tanggal='".$rHadir['tanggal']."'";
	$optCek=makeOption($dbname,'sdm_absensidt_vw','karyawanid,tanggal',$whrck);
	if($optCek[$rHadir['karyawanid']]!=''){
		continue;
	}
}
#tunjangan dan potongan basic
$sTunjangan="select a.karyawanid,jumlah,idkomponen,plus from ".$dbname.".sdm_5gajipokok a 
             left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
             left join ".$dbname.".sdm_ho_component c on a.idkomponen=c.id 
             where type='basic' and id!=1 ".$whereDt."";
$qTunjangan=$owlPDO->query($sTunjangan) or die(print " Gagal: ".PDOException::getMessage());
$qTunjangan->setFetchMode(PDO::FETCH_ASSOC);
while($rTunjangan=$qTunjangan->fetch())		
{
	$rTunjangan['tanggal']=$periode."-28";
	$lstTgl[$rTunjangan['tanggal']]=$rTunjangan['tanggal'];
	$lstKeg[$rTunjangan['idkomponen']]=$rTunjangan['idkomponen'];
	$dafkerja[$rTunjangan['karyawanid'].$rTunjangan['tanggal'].$rTunjangan['idkomponen']]=$rTunjangan['idkomponen'];
	if($rTunjangan['plus']==1){
		$dtRptunjangan[$rTunjangan['karyawanid'].$rTunjangan['tanggal'].$rTunjangan['idkomponen']]=$rTunjangan['jumlah'];	
	}else{
		$dtRpPotongan[$rTunjangan['karyawanid'].$rTunjangan['tanggal'].$rTunjangan['idkomponen']]=$rTunjangan['jumlah'];
	}
	@$jmlhRowTgl[$rTunjangan['karyawanid'].$rTunjangan['tanggal']]+=1;
		
}



#potongan dari inputan
$sPot="select a.nik as karyawanid,tipepotongan,jumlahpotongan from ".$dbname.".sdm_potongandt a 
	   left join ".$dbname.".datakaryawan b on a.nik=b.karyawanid where periodegaji='".$periode."'
	   ".$whereDt."";
$qPot=$owlPDO->query($sPot) or die(print " Gagal: ".PDOException::getMessage());
$qPot->setFetchMode(PDO::FETCH_ASSOC);
while($rHadir=$qPot->fetch())		
{
	$rHadir['tanggal']=$periode."-28";
	$lstTgl[$rHadir['tanggal']]=$rHadir['tanggal'];
	$lstKeg[$rHadir['tipepotongan']]=$rHadir['tipepotongan'];
	$dafkerja[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['tipepotongan']]=$rHadir['tipepotongan'];
	@$jmlhRowTgl[$rHadir['karyawanid'].$rHadir['tanggal']]+=1;
	$dtRpPotongan[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['tipepotongan']]=$rHadir['jumlahpotongan'];
}
#premi dari inputan sdm_premi
$arrAngka=array("UANGMAKAN"=>"45","TJABSEN"=>"56","PREMITETAP"=>"40");
$sPot="select a.karyawanid as karyawanid,jenis as tipepotongan,premi as jumlahpotongan from ".$dbname.".sdm_premi a 
	   left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where periode='".$periode."'
	   ".$whereDt."";
$qPot=$owlPDO->query($sPot) or die(print " Gagal: ".PDOException::getMessage());
$qPot->setFetchMode(PDO::FETCH_ASSOC);
while($rHadir=$qPot->fetch())	
{
	$rHadir['tanggal']=$periode."-28";
	$lstTgl[$rHadir['tanggal']]=$rHadir['tanggal'];
	$rHadir['tipepotongan']=$arrAngka[$rHadir['tipepotongan']];
	//exit('warning'.$rHadir['tipepotongan']);
	$lstKeg[$rHadir['tipepotongan']]=$rHadir['tipepotongan'];
	$dafkerja[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['tipepotongan']]=$rHadir['tipepotongan'];
	@$jmlhRowTgl[$rHadir['karyawanid'].$rHadir['tanggal']]+=1;
	$dtRppremi[$rHadir['karyawanid'].$rHadir['tanggal'].$rHadir['tipepotongan']]=$rHadir['jumlahpotongan'];
}
	if(($proses=='preview')||($proses=='excel')){
		foreach($lstKary as $dtKaryId){
			@$no+=1;
			$whr="karyawanid='".$dtKaryId."'";
			$dtNmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
			$dtSubdivisi=makeOption($dbname,'datakaryawan','karyawanid,subbagian',$whr);
			$dtNik=makeOption($dbname,'datakaryawan','karyawanid,nik',$whr);
		    $tab.="<table>";
		    $tab.="<tr><td></td>";
		    $tab.="<tr><td>No.</td>";
		    $tab.="<td>&nbsp;</td>";
		    $tab.="<td  colspan=3>: ".$no."</td></tr>";
		    $tab.="<tr><td>".strtoupper($_SESSION['lang']['bulan'])."</td>";
		    $tab.="<td>&nbsp;</td>";
		    $tab.="<td  colspan=3>: ".strtoupper($arrBln[intval($prd[1])])."-".$prd[0]."</td></tr>";
		    $tab.="<tr><td colspan=2>NIP / ".strtoupper($_SESSION['lang']['namakaryawan'])."</td>";
		    $tab.="<td  colspan=3>: ".$dtNik[$dtKaryId]." / ".strtoupper($dtNmkar[$dtKaryId])."</td></tr>";
		    $tab.="<tr><td>".strtoupper($_SESSION['lang']['unitkerja'])."</td>";
		    $tab.="<td>&nbsp;</td>";
		    $sbdiv="kodeorganisasi='".$dtSubdivisi[$dtKaryId]."'";
		    $optDivisi=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',$sbdiv);
		    if($dtSubdivisi[$dtKaryId]!=''){
		    	$tab.="<td  colspan=3>: ".$dtSubdivisi[$dtKaryId]." / ".$optDivisi[$dtSubdivisi[$dtKaryId]]."</td>";	
		    }else{
		    	$tab.="<td  colspan=3>: Office</td>";
		    }
		    $tab.="</tr>";
		    
		    $tab.="</table>";
		    $tab.="<table cellpadding=1 cellspacing=1 border='".$garis."' class=sortable><thead>";
		    $tab.="<tr ".@$bgcolordt." align=center>";
		    $tab.="<td rowspan=2>".strtoupper($_SESSION['lang']['tanggal'])."</td>";
		    //$tab.="<td rowspan=2>".strtoupper($_SESSION['lang']['kodekegiatan'])."</td>";
		    $tab.="<td rowspan=2>".strtoupper($_SESSION['lang']['namakegiatan'])."</td>";
		    $tab.="<td rowspan=2>".strtoupper($_SESSION['lang']['notransaksi'])."</td>";
		    $tab.="<td rowspan=2>".strtoupper($_SESSION['lang']['absensi'])."</td>";
		    $tab.="<td rowspan=2>".strtoupper($_SESSION['lang']['satuan'])."</td>";
		    $tab.="<td rowspan=2>".strtoupper($_SESSION['lang']['jumlah'])."</td>";
		    $tab.="<td colspan=4>".strtoupper($_SESSION['lang']['penambahan'])."</td>";
		    $tab.="<td colspan=3>".strtoupper($_SESSION['lang']['pengurang'])."</td>";
		    $tab.="<td rowspan=2>".strtoupper($_SESSION['lang']['gajiBersih'])."</td></tr>";
		    $tab.="<tr><td>".strtoupper($_SESSION['lang']['upah'])."</td>";
		    $tab.="<td>".strtoupper($_SESSION['lang']['lembur'])."</td>";
		    $tab.="<td>".strtoupper($_SESSION['lang']['premi'])."</td>";
		    $tab.="<td>".strtoupper($_SESSION['lang']['stdtunjangan'])."</td>";
		    $tab.="<td>".strtoupper($_SESSION['lang']['penaltykehadiran'])."</td>";
		    $tab.="<td>PENALTY</td>";
		    $tab.="<td>".strtoupper($_SESSION['lang']['potongan'])."</td></tr>";
		    $tab.="</thead><tbody>";
			foreach($arrTgl as $dtTgl){
				foreach($lstKeg as $dtKeg){
					if(@$dafkerja[$dtKaryId.$dtTgl.$dtKeg]!=''){
						 $tab.="<tr class=rowcontent>";
						 if($dtTgl!=@$tglTem){
                            $tglTem=$dtTgl;
                            $aret=0;
                                $tab.="<td>".$dtTgl."</td>";
                        }else{
                           if($aret==0){
                                $tab.="<td rowspan=".($jmlhRowTgl[$dtKaryId.$dtTgl]-1).">&nbsp;</td>";
                                $aret=1;
                            }
                        }
                        switch ($dafkerja[$dtKaryId.$dtTgl.$dtKeg]) {
                        	case 'HAKTIFITAS':
                        		$optNmKeg[$dafkerja[$dtKaryId.$dtTgl.$dtKeg]]="Hadir (Inputan BKM)";
                        	break;
                        	case'PNLTYKHDRAN':
                        		$optNmKeg[$dafkerja[$dtKaryId.$dtTgl.$dtKeg]]="PINALTI KEHADIRAN";
                        	break;
                        	case'PNLTYTRK':
                        		$optNmKeg[$dafkerja[$dtKaryId.$dtTgl.$dtKeg]]="PINALTI (Inputan Traksi)";
                        	break;
                        	case'PREMTRK':
                        		$optNmKeg[$dafkerja[$dtKaryId.$dtTgl.$dtKeg]]="PREMI (Inputan Traksi)";
                        	break;
                        	case'DENDAPANEN':
                        		$optNmKeg[$dafkerja[$dtKaryId.$dtTgl.$dtKeg]]="DENDA PANEN";
                        	break;
                        	case'POTHKPANEN':
                        		$optNmKeg[$dafkerja[$dtKaryId.$dtTgl.$dtKeg]]="POTONGAN HK PANEN";
                        	break;
                        }
                        if(@$optNmKeg[$dafkerja[$dtKaryId.$dtTgl.$dtKeg]]==''){#cek apakah nama kegiatannya ada di master setup_kegiatan
                        	$optAbsensi=makeOption($dbname,'sdm_5absensi','kodeabsen,keterangan');
                        	if(@$optAbsensi[$dafkerja[$dtKaryId.$dtTgl.$dtKeg]]!=''){#cek apakah nama kegiatannya ada di sdm_5absensi
                        		$optNmKeg[$dafkerja[$dtKaryId.$dtTgl.$dtKeg]]=$optAbsensi[$dafkerja[$dtKaryId.$dtTgl.$dtKeg]];
                        		if($dafkerja[$dtKaryId.$dtTgl.$dtKeg]=='H'){
                        			$optSatKeg[$dafkerja[$dtKaryId.$dtTgl.$dtKeg]]="HK";	
                        		}
                        	}else{
                        		$arrKetLem=array("0"=>"Kerja","1"=>"Minggu","2"=>"Hari libur bukan minggu","3"=>"Hari raya");
                        		$optLembur=makeOption($dbname,'sdm_5lembur','tipelembur,tipelembur');
                        		if(@$optLembur[$dafkerja[$dtKaryId.$dtTgl.$dtKeg]]!=''){#cek apakah nama kegiatannya ada di sdm_5lembur
                        			$optNmKeg[$dafkerja[$dtKaryId.$dtTgl.$dtKeg]]="Lembur di hari ".$arrKetLem[$optLembur[$dafkerja[$dtKaryId.$dtTgl.$dtKeg]]];
                        			$optSatKeg[$dafkerja[$dtKaryId.$dtTgl.$dtKeg]]="JAM";
                        		}else{
                        			$optGajiCom=makeOption($dbname,'sdm_ho_component','id,name');
                        			if($optGajiCom[$dafkerja[$dtKaryId.$dtTgl.$dtKeg]]!=''){#cek apakah nama kegiatannya ada di sdm_ho_component
                        				$optNmKeg[$dafkerja[$dtKaryId.$dtTgl.$dtKeg]]=$optGajiCom[$dafkerja[$dtKaryId.$dtTgl.$dtKeg]];
                        			}
                        		}
                        	}
                        }
                        if(@$dtAbsensi[$dtKaryId.$dtTgl.$dtKeg]=='H'){
                        	$optSatKeg[$dafkerja[$dtKaryId.$dtTgl.$dtKeg]]='HK';
                        }
                        //$tab.="<td>".$dafkerja[$dtKaryId.$dtTgl.$dtKeg]."</td>";
                        $tab.="<td>".$optNmKeg[$dafkerja[$dtKaryId.$dtTgl.$dtKeg]]." ".strtoupper(@$tarif[$lstKary.$dtTgl.$lstKegiatan])."</td>";
                        $tab.="<td>".@$dtTransaksi[$dtKaryId.$dtTgl.$dtKeg]."</td>";
                        $tab.="<td>".@$dtAbsensi[$dtKaryId.$dtTgl.$dtKeg]."</td>";
                        $tab.="<td>".@$optSatKeg[$dafkerja[$dtKaryId.$dtTgl.$dtKeg]]."</td>";
                        $tab.="<td align=right>".@$dtJhk[$dtKaryId.$dtTgl.$dtKeg]."</td>";
                        $tab.="<td align=right>".@number_format($dtRpgaji[$dtKaryId.$dtTgl.$dtKeg],0)."</td>";
                        $tab.="<td align=right>".@number_format($dtRplembur[$dtKaryId.$dtTgl.$dtKeg],0)."</td>";
                        $tab.="<td align=right>".@number_format($dtRppremi[$dtKaryId.$dtTgl.$dtKeg],0)."</td>";
                        $tab.="<td align=right>".@number_format($dtRptunjangan[$dtKaryId.$dtTgl.$dtKeg],0)."</td>";
                        $tab.="<td align=right>".@number_format($dtRpDenda[$dtKaryId.$dtTgl.$dtKeg],0)."</td>";
                        $tab.="<td align=right>".@number_format($dtRpPinalty[$dtKaryId.$dtTgl.$dtKeg],0)."</td>";
                        $tab.="<td align=right>".@number_format($dtRpPotongan[$dtKaryId.$dtTgl.$dtKeg],0)."</td>";
                        @$total[$dtKaryId.$dtTgl.$dtKeg]=($dtRpgaji[$dtKaryId.$dtTgl.$dtKeg]+$dtRplembur[$dtKaryId.$dtTgl.$dtKeg]+$dtRptunjangan[$dtKaryId.$dtTgl.$dtKeg]+$dtRppremi[$dtKaryId.$dtTgl.$dtKeg])-($dtRpDenda[$dtKaryId.$dtTgl.$dtKeg]+$dtRpPinalty[$dtKaryId.$dtTgl.$dtKeg]+$dtRpPotongan[$dtKaryId.$dtTgl.$dtKeg]);
                        $tab.="<td align=right>".@number_format($total[$dtKaryId.$dtTgl.$dtKeg],0)."</td>";
                        $tab.="</tr>";
                        @$totUpahKary[$dtKaryId]+=$dtRpgaji[$dtKaryId.$dtTgl.$dtKeg];
                        @$totHk[$dtKaryId]+=$dtJhk[$dtKaryId.$dtTgl.$dtKeg];
                        $whrdttp="karyawanid='".$dtKaryId."'";
						$optCekTp=makeOption($dbname,'datakaryawan','karyawanid,tipekaryawan',$whrdttp);
						if($optCekTp!=4){
							if($totHk[$dtKaryId]>=25){
								$whrdttp.=" and tahun='".substr($periode, 0,4)."' and idkomponen=1";
								$optGapok=makeOption($dbname,'sdm_5gajipokok','karyawanid,jumlah',$whrdttp);
								$totUpahKary[$dtKaryId]=$optGapok[$dtKaryId];		
							}
						}
                        @$totLemburKary[$dtKaryId]+=$dtRplembur[$dtKaryId.$dtTgl.$dtKeg];
                        @$totPremiKary[$dtKaryId]+=$dtRppremi[$dtKaryId.$dtTgl.$dtKeg];
                        @$totTunjKary[$dtKaryId]+=$dtRptunjangan[$dtKaryId.$dtTgl.$dtKeg];
                        @$totDendKary[$dtKaryId]+=$dtRpDenda[$dtKaryId.$dtTgl.$dtKeg];
                        @$totPinaltyKary[$dtKaryId]+=$dtRpPinalty[$dtKaryId.$dtTgl.$dtKeg];
                        @$totPotongan[$dtKaryId]+=$dtRpPotongan[$dtKaryId.$dtTgl.$dtKeg];
                        
					}
				}
			}
			$tab.="<tr>";
			$tab.="<td colspan=5>&nbsp;</td>";
			$tab.="<td align=right>".@number_format($totHk[$dtKaryId],0)."</td>";
			$tab.="<td align=right>".@number_format($totUpahKary[$dtKaryId],0)."</td>";
            $tab.="<td align=right>".@number_format($totLemburKary[$dtKaryId],0)."</td>";
            $tab.="<td align=right>".@number_format($totPremiKary[$dtKaryId],0)."</td>";
            $tab.="<td align=right>".@number_format($totTunjKary[$dtKaryId],0)."</td>";
            $tab.="<td align=right>".@number_format($totDendKary[$dtKaryId],0)."</td>";
            $tab.="<td align=right>".@number_format($totPinaltyKary[$dtKaryId],0)."</td>";
            $tab.="<td align=right>".@number_format($totPotongan[$dtKaryId],0)."</td>";
            @$totalsma[$dtKaryId]=($totUpahKary[$dtKaryId]+$totLemburKary[$dtKaryId]+$totPremiKary[$dtKaryId]+$totTunjKary[$dtKaryId])-($totDendKary[$dtKaryId]+$totPinaltyKary[$dtKaryId]+$totPotongan[$dtKaryId]);
            $tab.="<td align=right>".@number_format($totalsma[$dtKaryId],0)."</td>";
            $tab.="</tr>";
		}
	}

switch($proses){
	case'getKary':
		$optKary="<option value=''>".$_SESSION['lang']['all']."</option>";
	    if(strlen($kdUnit)>4){
			$dtisi=0;
	        $whr=" subbagian='".$kdUnit."' ";
	    }else{
	        $whr=" lokasitugas='".$kdUnit."' ";
			$dtisi=1;
			$optAfd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$safd="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$kdUnit."'";
			$qafd=$owlPDO->query($safd) or die(print " Gagal: ".PDOException::getMessage());
			$qafd->setFetchMode(PDO::FETCH_ASSOC);
			while($rafd=$qafd->fetch())	
			{
				$optAfd.="<option value='".$rafd['kodeorganisasi']."'>".$rafd['namaorganisasi']."</option>";
			}
	    }

	    if($tPkary!=''){
	    	$dtisi=3;
	    	$whr.=" and a.tipekaryawan='".$tPkary."'";
	    }else{
	    	$whr.=" and a.tipekaryawan in (2,3,5,4,6)";
	    	
	    }
	    $sData="select distinct nik,a.karyawanid,namakaryawan from ".$dbname.".sdm_gaji b inner join 
	            ".$dbname.".datakaryawan a on b.karyawanid=a.karyawanid "
	         . " where ".$whr."  order by namakaryawan asc";
		$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
		while($rData=$qData->fetch())		
		{
	        $optKary.="<option value='".$rData['karyawanid']."'>".$rData['nik']."-".$rData['namakaryawan']."</option>";
	    }
		if($dtisi==1){
			echo $optAfd."####".$optKary;
		}else{
			echo $optKary;
		}
	break;
	case'getTglGaji':
		$sTgl="select distinct tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji
		       where kodeorg='".$kdUnit."' and periode='".$periode."'";
		$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
		$qTgl->setFetchMode(PDO::FETCH_ASSOC);
		$rTgl=$qTgl->fetch();
		echo tanggalnormal($rTgl['tanggalmulai'])."####".tanggalnormal($rTgl['tanggalsampai']);
	break;
	case'preview':
		echo $tab;
	break;
	case'excel':
 	$tab.="Print Time:".date('d-m-Y H:i:s')."<br>By:".$_SESSION['empl']['name'];
 	$nop_="rekapGajiPerKary__".$periode."__".$kdUnit;
    if(strlen($tab)>0){
     $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
     gzwrite($gztralala, $tab);
     gzclose($gztralala);
     echo "<script language=javascript1.2>
        window.location='tempExcel/".$nop_.".xls.gz';
        </script>";
	}
	break;
}


#array tanggal satu periode
function dates_inbetween($date1, $date2){

    $day = 60*60*24;

    $date1 = strtotime($date1);
    $date2 = strtotime($date2);

    $days_diff = round(($date2 - $date1)/$day); // Unix time difference devided by 1 day to get total days in between

    $dates_array = array();
    $dates_array[] = date('Y-m-d',$date1);

    for($x = 1; $x < $days_diff; $x++){
        $dates_array[] = date('Y-m-d',($date1+($day*$x)));
    }

    $dates_array[] = date('Y-m-d',$date2);
    if($date1==$date2){
        $dates_array = array();
        $dates_array[] = date('Y-m-d',$date1);        
    }
    return $dates_array;
}


?>