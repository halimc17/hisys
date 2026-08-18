<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');

$param = $_GET;
$kodeorg  = $param['kodeorg'];
$dataorg = array();
$dtstr="select * from ".$dbname.".organisasi where  kodeorganisasi = '".$kodeorg."'";
$str=$owlPDO->query($dtstr);
$str->setFetchMode(PDO::FETCH_OBJ);
 while($bar=$str->fetch()){
	$dataorg[$bar->kodeorganisasi] = $bar;
 }
$tahunbulan = implode("",explode('-',$param['periode']));

#1. ambil periode akuntansi
$str=$owlPDO->query("select tanggalmulai,tanggalsampai,periode from ".$dbname.".setup_periodeakuntansi where 
      kodeorg ='".$kodeorg."' and tutupbuku=0");
$tgmulai='';
$tgsampai='';
$periode='';
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch()){
    $tgsampai   = $bar->tanggalsampai;
    $tgmulai    = $bar->tanggalmulai;
    $periode      =$bar->periode;
}
if($tgmulai=='' || $tgsampai=='')
    exit("Error: Accounting period is not registered");

 
#pastikan semua kegiatan ada noakun pada saat entry
#antisipasi penggantian kegiatan sipil
$str=$owlPDO->query("select distinct notransaksi,kodekegiatan from ".$dbname.".vhc_spl_kehadiran_vw
      where tanggal like '".$periode."%' and kodekegiatan not in (SELECT kodekegiatan FROM ".$dbname.".vhc_kegiatan)
      and posting=1");
$str->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($str);
if($numrows>0){
    echo "Error : Ada Kegiatan Sipil yang tidak memilik noakun, silakan hubungi administrator\n";
    
	while($barf=$str->fetch()){
		print_r($barf);
	}
	exit();
}


#2 periksa apakah sudah posting semua
$str1=$owlPDO->query("select distinct * from ".$dbname.".vhc_spl_kehadiran_vw where posting=0 
       and kodeorg='".$kodeorg."'
	   and tanggal>='".$tgmulai."' and tanggal<='".$tgsampai."'");
$str1->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($str1);
if($numrows>0){
	$t.="Sipil:\n";
	while($bart=$str1->fetch()){
	   $t.=$bart->notransaksi."\n";
	}	 
	exit("Error: there are transactions that have not posted:\n".$t);
}

#3 ambil rupiah di jurnal dan gaji
$sAkun=$owlPDO->query("select noakundebet,sampaidebet from ".$dbname.".keu_5parameterjurnal where jurnalid='SIPL8'");
$sAkun->setFetchMode(PDO::FETCH_ASSOC);
$numrows=owlBaris($sAkun);
if($numrows<1){
	exit('warning: No.Akun pada parameterjurnal belum ada untuk kode : SIPL8');
}
$rAkun=$sAkun->fetch();
$akunDbt=$rAkun['noakundebet'];
$akunSmpDbt=$rAkun['sampaidebet'];
#jurnal
$sJurnal=$owlPDO->query("select sum(jumlah) as jumlah,nodok as koderumah from ".$dbname.".keu_jurnaldt_vw where 
		  kodeorg='".$kodeorg."' and noakun between '".$akunDbt."' and '".$akunSmpDbt."'
		  and tanggal>='".$tgmulai."' and tanggal<='".$tgsampai."' 
		  and nodok!='' 
		  group by nodok");
$sJurnal->setFetchMode(PDO::FETCH_ASSOC);
while($rJurnal=$sJurnal->fetch()){
	$perAlokasi[$rJurnal['koderumah']]+=$rJurnal['jumlah'];
}


//Biaya GAJI subbagian SIPIL
$whrsipil="induk='".$kodeorg."' and tipe='SIPIL'";
$optSipil=makeOption($dbname,'organisasi','induk,kodeorganisasi',$whrsipil);
$subbagian=$optSipil[$kodeorg];

$sOperator=$owlPDO->query("select a.karyawanid from ".$dbname.".vhc_5operator a left join ".$dbname.".datakaryawan b 
			on a.karyawanid=b.karyawanid where lokasitugas='".$kodeorg."'");
$sOperator->setFetchMode(PDO::FETCH_ASSOC);
$numrows=owlBaris($sOperator);
while($rOperator=$sOperator->fetch()){
	$optList[$rOperator['karyawanid']]=$rOperator['karyawanid'];
}
if($numrows<=0){
	$listOp = "";
}else{
	$listOp = " and a.karyawanid not in (".implode(",", $optList).")";
}

#total gaji dimana subbagiannya adalah sipil
$sGaji=$owlPDO->query("select sum(jumlah) as jumlah from ".$dbname.".sdm_gaji a left join 
		".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where
		subbagian='".$subbagian."' and periodegaji='".$param['periode']."'
		".$listOp."
		and idkomponen in (1,32)");
$sGaji->setFetchMode(PDO::FETCH_ASSOC);
$rGaji=$sGaji->fetch();

$sPeriode = $owlPDO->query("select tanggalmulai, tanggalsampai from ".$dbname.".sdm_5periodegaji where periode = '".$param['periode']."' and kodeorg='".$kodeorg."'");
$sPeriode->setFetchMode(PDO::FETCH_ASSOC);
$rPeriode=$sPeriode->fetch();
$tglAwal = $rPeriode['tanggalmulai'];
$tglAkhir = $rPeriode['tanggalsampai'];

$sGaji2 = $owlPDO->query("select sum(umr+insentif) as jumlah from ".$dbname.".kebun_kehadiran_vw a
left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
where subbagian='".$subbagian."' and tanggal between '".$tglAwal."' and '".$tglAkhir."' 
".$listOp."");
$sGaji2->setFetchMode(PDO::FETCH_ASSOC);
$rGaji2=$sGaji2->fetch();



$sGaji3 = $owlPDO->query("select sum(a.upahkerja+(a.upahpremi+a.premibasis)+(a.rupiahpenalty+a.upahpenalty)) as jumlah from ".$dbname.".kebun_prestasi_vw a 
left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
where subbagian='".$subbagian."' and tanggal between '".$tglAwal."' and '".$tglAkhir."' 
".$listOp."");
$sGaji3->setFetchMode(PDO::FETCH_ASSOC);
$rGaji3=$sGaji3->fetch();

$totalGaji=$rGaji['jumlah']-$rGaji2['jumlah']-$rGaji3['jumlah'];


#4 HK yang di ambil dari prestasi dan memproporsi total gaji
$sJhk=$owlPDO->query("select sum(jhk) as jhk,alokasi as koderumah from ".$dbname.".vhc_spl_kehadiran_vw 
	   where kodeorg='".$kodeorg."' and tanggal>='".$tgmulai."' and tanggal<='".$tgsampai."'
	   group by alokasi");
$sJhk->setFetchMode(PDO::FETCH_ASSOC);
$numrows=owlBaris($sJhk);
if($numrows>0){
	while($rJhk=$sJhk->fetch()){
		$totalHk+=$rJhk['jhk'];
		$perRumah[$rJhk['koderumah']]+=$rJhk['jhk'];
	}
	foreach($perRumah as $dtRmh=>$lstRumah){
		@$hkPerumah[$dtRmh]=($lstRumah/$totalHk)*$totalGaji;
		$perAlokasi[$dtRmh]+=$hkPerumah[$dtRmh];
	}	
	foreach($perRumah as $dtRmh=>$lstRumah){
		@$byPerHk[$dtRmh]=$perAlokasi[$dtRmh]/$lstRumah;
	}
}

  
echo"<button  onclick=prosesAlokasiSipil(1) id=btnproses>Process</button>
	 <font ><br>Note: If it does not work please reprocess, the old data will be deleted.</font>
	 <table class=sortable cellspacing=1 border=0>
	 <thead>
	   <tr class=rowheader>
	   <td>No</td>
	   <td>Period</td>
	   <td>Alokasi</td>
	   <td>Organization</td>
	   <td>Price/HK</td>
	   <td>Total HK</td>
	   <td>Type</td>
	   </tr>
	 </thead>
	 <tbody>";
	 if(!empty($byPerHk)){
	 	foreach($byPerHk as $key=>$jlh){
		$no+=1;
		echo"<tr class=rowcontent id='row".$no."'>
		   <td>".$no."</td>
		   <td id='periode".$no."'>".$_POST['periode']."</td>
		   <td id='norumah".$no."'>".$key."</td>
		   <td id='kdsipil".$no."'>".$subbagian."</td>    
		   <td id='jumlah".$no."' align=right>".$jlh."</td> 
		   <td id='hk".$no."' align=right>".$perRumah[$key]."</td>    
		   <td id='jenis".$no."'>ALK_SIPIL</td>
		   </tr>";
		}
	 }
	 
echo"</tbody><tfoot></tfoot></table>";