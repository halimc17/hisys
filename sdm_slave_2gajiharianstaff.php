<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$unit=checkPostGet('unit','');
$tipekar=checkPostGet('tipekar','');
$periode=checkPostGet('periode','');
//exit("error:".$unit);

$optNmKomponen=  makeOption($dbname, 'sdm_ho_component', 'id,name');
$optnmjab=  makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');

$nmorg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmtipekar=  makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');

$ketipeorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
$kept= makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
@$tipeorg = $ketipeorg[$unit];
//@$kodept=$kept[$unit];
@$kodept=$unit;



if($tipekar==''){
	$where.=" and tipekaryawan in ('0','7','8','9','10')";
}else{
	$where.=" and tipekaryawan='".$tipekar."'";
}
	


$str="select id,name from ".$dbname.".sdm_ho_component where plus='1'  and id not in ('26','28') ";
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
$str="select * from ".$dbname.".datakaryawan where lokasitugas='".$unit."'
		and (tanggalkeluar='00000-00-00' or tanggalkeluar>= '".$periode."') ".$where." order by namakaryawan asc ";
		//exit($str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$dtkarid[$bar['karyawanid']]=$bar['karyawanid'];
	$listidkar[$bar['karyawanid']]=$bar['karyawanid'];
	$nik[$bar['karyawanid']]=$bar['nik'];
	$nmkar[$bar['karyawanid']]=$bar['namakaryawan'];
	$stpajak[$bar['karyawanid']]=$bar['statuspajak'];
	$tpkar[$bar['karyawanid']]=$bar['tipekaryawan'];
	$jabatan[$bar['karyawanid']]=$bar['kodejabatan'];
	$dtbpjskerja[$bar['karyawanid']]=$bar['jms'];
	$dtbpjssehat[$bar['karyawanid']]=$bar['bpjs'];
	$dtbpjspensiun[$bar['karyawanid']]=$bar['pensiun'];
	
	//bentuk tpkar untuk foreach pejabat bkm
	$tpkarbkm[$bar['karyawanid']]=$bar['tipekaryawan'];
}
@$cekdata=count($dtkarid);
if($cekdata<1){
	exit("Warning:Data Kosong");
}

/*****************************************************************************************************************/

//lembur
$str="select a.*,b.subbagian from ".$dbname.".sdm_lemburdt a left join ".$dbname.".datakaryawan b
		on a.karyawanid=b.karyawanid where  kodeorg='".$kodept."'
		and tanggal between '".$periode."-01' and '".$periode."-31'  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$jamlembur[$bar['karyawanid']]+=$bar['jamaktual'];
	@$rupiah[$bar['karyawanid']]['33']+=$bar['uangkelebihanjam'];
}

//bkm
$str="select a.*,b.subbagian from ".$dbname.".kebun_kehadiran_vw a left join ".$dbname.".datakaryawan b
		on a.karyawanid=b.karyawanid where unit ='".$kodept."' and tanggal between  '".$periode."-01' and '".$periode."-31'  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['karyawanid']]['32']+=$bar['insentif'];
	@$hk[$bar['karyawanid']]+=$bar['jhk'];
	@$rupiah[$bar['karyawanid']]['1']+=$bar['umr'];
	
}

//panen
$str="select * from ".$dbname.".kebun_prestasi_vs_hk  
where unit ='".$kodept."'  and tanggal between '".$periode."-01' and '".$periode."-31'  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	if($bar['kodetipekaryawan']=='4'){
		@$rupiah[$bar['karyawanid']]['1']+=$bar['upahkerja'];
	}
	@$rupiah[$bar['karyawanid']]['37']+=$bar['upahpenalty'];
	@$rupiah[$bar['karyawanid']]['32']+=$bar['tpremi'];
	@$hk[$bar['karyawanid']]+=$bar['hkpanenperhari'];
	@$rupiah[$bar['karyawanid']]['34']+=$bar['rupiahpenalty'];
}


//absensi
$str="select * from ".$dbname.".sdm_absensidt_vw where lokasitugas ='".$kodept."'  and tanggal between '".$periode."-01' and '".$periode."-31'  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['karyawanid']]['32']+=$bar['premi'];
	@$hk[$bar['karyawanid']]+=$bar['nilaihk'];
	@$rupiah[$bar['karyawanid']]['34']+=$bar['penaltykehadiran'];
	if($bar['tipekaryawan']!=1){//kalau bns sudah ambil dari gapok	
		@$rupiah[$bar['karyawanid']]['1']+=$bar['umr'];
	}
	
	@$rupiah[$bar['karyawanid']]['63']+=$bar['insentif'];
	@$rupiah[$bar['karyawanid']]['64']+=$bar['insentiflibur'];
}






//vhc
$str="select a.*,b.lokasitugas,b.subbagian,tipekaryawan from ".$dbname.".vhc_runhk_vw a left join ".$dbname.".datakaryawan b
		on a.idkaryawan=b.karyawanid where lokasitugas ='".$kodept."'
		and tanggal between '".$periode."-01' and '".$periode."-31'  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['idkaryawan']]['32']+=$bar['premi'];
	@$hk[$bar['idkaryawan']]+=$bar['hk'];
	if($bar['tipekaryawan']!=1){//kalau bns sudah ambil dari gapok	
		@$rupiah[$bar['idkaryawan']]['1']+=$bar['upah'];
	}
	@$rupiah[$bar['idkaryawan']]['34']+=$bar['penalty'];
}

#= tambahan ada formula dengan sumber komponen gajipokok ho dengan tipe basic, tapi sumber tersebut harus melalui proses
#= hjadi ini query untuk mengeluarkan komponen tersebut
$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and	kodeparameter='HRGJPPHO'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();	
$arrbpjs=explode(',',$bar['nilai']);
foreach($arrbpjs as $key){
	$komponentidak.="'".$key."',";
}

$str="select a.*,b.subbagian,b.lokasitugas,b.tipekaryawan from ".$dbname.".sdm_5gajipokokho a left join ".$dbname.".datakaryawan b
		on a.karyawanid=b.karyawanid where lokasitugas ='".$kodept."'  and tahun = '".substr($periode,0,4)."' and idkomponen not in (".$komponentidak."'59') ";
		//exit($str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	if($bar['idkomponen']==1){
		@$umrbln[$bar['karyawanid']]=$bar['jumlah'];
		if($bar['tipekaryawan']!=4){
			@$rupiah[$bar['karyawanid']][$bar['idkomponen']]=$bar['jumlah'];
			@$umrhari[$bar['karyawanid']]=$bar['jumlah']/25;
		}else{
			@$umrhari[$bar['karyawanid']]=$bar['jumlah']/25;
		}
	}else{
		@$rupiah[$bar['karyawanid']][$bar['idkomponen']]=$bar['jumlah'];
	}	
}



#= pendapatan lain ho
$str="select a.*,b.subbagian,b.lokasitugas,b.tipekaryawan from ".$dbname.".sdm_pendapatanlaindt a left join ".$dbname.".datakaryawan b
		on a.karyawanid=b.karyawanid where lokasitugas ='".$kodept."'  and periodegaji = '".$periode."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['karyawanid']][$bar['idkomponen']]=$bar['jumlah'];
}


#pendapatan lain
$str="select a.*,b.subbagian,b.lokasitugas,b.tipekaryawan from ".$dbname.".sdm_pendapatanho a left join ".$dbname.".datakaryawan b
		on a.karyawanid=b.karyawanid where lokasitugas ='".$kodept."'  and periode = '".$periode."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['karyawanid']][$bar['idkomponen']]=$bar['rpjumlah'];
}


#bentuk ambil premi lainnya
$str="select * from ".$dbname.".sdm_premi  
		where kodeorg ='".$kodept."'   
		and periode='".$periode."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['karyawanid']][$bar['jenis']]+=$bar['tpremi'];
}


/*****************************************************************************************************************/




#bentuk absen  mandor
$str="select a.nikmandor,a.tanggal,a.kodeorg,b.subbagian from ".$dbname.".kebun_aktifitas a 
		left join ".$dbname.".datakaryawan b on a.nikmandor=b.karyawanid where 
		kodeorg ='".$kodept."' 
		and tanggal between '".$periode."-01' and '".$periode."-31' and nikmandor!='' and (nospk='' or nospk is null)  ";
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

#mandor 1
$str="select a.nikmandor1,a.tanggal,a.kodeorg,b.subbagian from ".$dbname.".kebun_aktifitas a 
		left join ".$dbname.".datakaryawan b on a.nikmandor1=b.karyawanid where 
		kodeorg ='".$kodept."'   
		and tanggal between '".$periode."-01' and '".$periode."-31' and nikmandor1!='' and (nospk='' or nospk is null) ";
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

//krani
$str="select a.keranimuat,a.tanggal,a.kodeorg,b.subbagian from ".$dbname.".kebun_aktifitas a 
		left join ".$dbname.".datakaryawan b on a.keranimuat=b.karyawanid where 
		kodeorg ='".$kodept."'  
and tanggal between '".$periode."-01' and '".$periode."-31' and keranimuat!='' and (nospk='' or nospk is null) ";
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




//krani panen
$str="select a.nikasisten,a.tanggal,a.kodeorg,b.subbagian from ".$dbname.".kebun_aktifitas a 
		left join ".$dbname.".datakaryawan b on a.nikasisten=b.karyawanid where 
		kodeorg ='".$kodept."'
		and tanggal between '".$periode."-01' and '".$periode."-31' and nikasisten!='' and (nospk='' or nospk is null) ";
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
			
			
			
			// }else{
				// //rupiah dimasukin ke premi
				// //$rupiah[$dtpejabatbkmsubbag[$karid][$tgl]][$karid]['32']+=$counttgl[$karid][$tgl]*$umrhari[$karid];
			// }
		}
	}
}

// echo"<pre>";
// print_r($hk);
// echo"</pre>";

// echo"<pre>";
// print_r($rupiah);
// echo"</pre>";
// echo"<pre>";
// print_r($umrhari);
// echo"</pre>";

/*****************************************************************************************************************/

//potongan HK
$str="select count(*) as jumlah,b.karyawanid,b.subbagian,b.lokasitugas,b.tipekaryawan 
		from ".$dbname.".sdm_hktdkdibayar_vw a left join ".$dbname.".datakaryawan b
		on a.karyawanid=b.karyawanid where b.lokasitugas='".$kodept."' and tanggal between '".$periode."-01' and '".$periode."-31'  group by karyawanid ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['karyawanid']]['37']=@$umrhari[$bar['karyawanid']]*$bar['jumlah'];
}



#sdm_potongan
$str="select a.*,b.subbagian,b.lokasitugas,b.tipekaryawan from ".$dbname.".sdm_potongandt a left join ".$dbname.".datakaryawan b
		on a.nik=b.karyawanid where lokasitugas='".$kodept."' and periodegaji = '".$periode."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['nik']][$bar['tipepotongan']]=$bar['jumlahpotongan'];
}


$str = "select a.*,b.lokasitugas,b.subbagian from ".$dbname.".sdm_angsuran a left join 
		".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
        where lokasitugas='".$kodept."' and start<='".$periode."' and end>='".$periode."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['karyawanid']][$bar['jenis']]=$bar['bulanan'];
}		


#bpjs


@$bpjsorg = 'HO';



#=================================================================================================
#== bentuk bpjs
#=================================================================================================

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
	foreach ($dtkarid as $karid){
		if(@$listidkar[$karid]!=''){
			
			if(@$dtbpjskerja[$karid]!=''){
				if(in_array($bar['jenisbpjs'],$arrker)){
					@$rupiah[$karid][$bar['jenisbpjs']]=$bar['bebankaryawan']/100*$umrbln[$karid];
					@$rupiah[$karid][$bar['jenisbpjsplus']]=$bar['bebanperusahaan']/100*$umrbln[$karid];
				}
			}
			
			if(@$dtbpjssehat[$karid]!=''){
				if(in_array($bar['jenisbpjs'],$arrkes)){
					@$rupiah[$karid][$bar['jenisbpjs']]=$bar['bebankaryawan']/100*$umrbln[$karid];
					@$rupiah[$karid][$bar['jenisbpjsplus']]=$bar['bebanperusahaan']/100*$umrbln[$karid];
				}
			}
			
			if(@$dtbpjspensiun[$karid]!=''){
				if(in_array($bar['jenisbpjs'],$arrpen)){
					@$rupiah[$karid][$bar['jenisbpjs']]=$bar['bebankaryawan']/100*$umrbln[$karid];
					@$rupiah[$karid][$bar['jenisbpjsplus']]=$bar['bebanperusahaan']/100*$umrbln[$karid];
				}
			}
			
			
			
		}
	}
}	
	
	
	


/*****************************************************************************************************************/

$coslpantafd=$tbrskomplus+$tbrskommin+8;
//$stream= "";
if ($proses == 'excel') {
    $stream.= "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.= "<table class='sortable' cellspacing='1'>";
}

array_multisort($dtkarid,SORT_ASC);



$stream.="<thead><tr class=rowcontent>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['nomor']."</th>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['nik2']."</th>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['namakaryawan']."</th>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['jabatan']."</th>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['tipekaryawan']."</th>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['status']."</th>";
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


	foreach ($dtkarid as $karid){
		if(@$listidkar[$karid]!=''){
			
			#cek hanya data dengan netto 0 yang di tampilkan
			foreach ($dtkomplus as $komplus){
				if(@$rupiah[$karid]['1']>@$umrbln[$karid] && $tpkar[$karid]!='4'){
					$rupiah[$karid]['1']=$umrbln[$karid];
				}
				@$cektkomplus[$karid]+=$rupiah[$karid][$komplus];
			}
			foreach ($dtkommin as $kommin){
				@$cektkommin[$karid]+=$rupiah[$karid][$kommin];
			}
			@$cektnettokar[$karid]=$cektkomplus[$karid]-$cektkommin[$karid];
			##tutup cek
			
			
						
				@$no++;
				$stream.="<tr class=rowcontent>";
				$stream.="<td align=center>".$no."</td>";
				$stream.="<td>".$nik[$karid]."</td>";
				$stream.="<td>".$nmkar[$karid]."</td>";
				$stream.="<td>".$optnmjab[$jabatan[$karid]]."</td>";
				$stream.="<td>".$nmtipekar[$tpkar[$karid]]."</td>";
				$stream.="<td>".$stpajak[$karid]."</td>";
				$stream.="<td align=right>".@number_format($hk[$karid],2)."</td>";
				$stream.="<td align=right>".@number_format($jamlembur[$karid],2)."</td>";
				foreach ($dtkomplus as $komplus){
					if(@$rupiah[$karid]['1']>@$umrbln[$karid] && $tpkar[$karid]!='4'){
						$rupiah[$karid]['1']=$umrbln[$karid];
					}
					$stream.="<td align=right>".@number_format($rupiah[$karid][$komplus])."</td>";
					@$tkomplus[$karid]+=$rupiah[$karid][$komplus];
					@$subtkomplus[$komplus]+=$rupiah[$karid][$komplus];
				}
				$stream.="<td align=right>".@number_format($tkomplus[$karid])."</td>";
				foreach ($dtkommin as $kommin){
					$stream.="<td align=right>".@number_format($rupiah[$karid][$kommin])."</td>";
					@$tkommin[$karid]+=$rupiah[$karid][$kommin];
					@$subtkommin[$kommin]+=$rupiah[$karid][$kommin];
				}
				$stream.="<td align=right>".@number_format($tkommin[$karid])."</td>";
				@$tnettokar[$karid]=$tkomplus[$karid]-$tkommin[$karid];
				$stream.="<td align=right>".@number_format($tnettokar[$karid])."</td>";
				$stream.="</tr>";
				@$ttlhk+=$hk[$karid];
				@$ttllembur+=$jamlembur[$karid];
						
		}
	
}
	/*$stream.="<tr bgcolor=lightgray>";
	$stream.="<td align=right>".@number_format($ttlhk,2)."</td>";
	$stream.="<td align=right>".@number_format($ttllembur,2)."</td>";
	foreach ($dtkomplus as $komplus){
		$stream.="<td align=right>".@number_format($subtkomplus[$komplus])."</td>";
		@$tsubtkomplus+=$subtkomplus[$komplus];
		@$gtkomplus[$komplus]+=$subtkomplus[$komplus];
	}
	$stream.="<td align=right>".@number_format($tsubtkomplus)."</td>";
	foreach ($dtkommin as $kommin){
		$stream.="<td align=right>".@number_format($subtkommin[$kommin])."</td>";
		@$tsubtkommin+=$subtkommin[$kommin];
		@$gtkommin[$kommin]+=$subtkommin[$kommin];
	}
	$stream.="<td align=right>".@number_format($tsubtkommin)."</td>";
	@$tsubtnetto=$tsubtkomplus-$tsubtkommin;
	
	$stream.="<td align=right>".@number_format($tsubtnetto)."</td>";
	$stream.="</tr>";
	
	#bentuk grandtotal
	@$gthk+=$ttlhk;
	@$gtllembur+=$ttllembur;
	@$gtnetto+=$tsubtnetto;*/
	

$stream.="<thead><tr>";
$stream.="<td align=center colspan=6>".$_SESSION['lang']['grnd_total']."</td>";
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