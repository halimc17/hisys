<?php
require_once('master_validation.php');
include_once('config/connection.php');
include('lib/zLib.php');


$jnsIjin=$_POST['jnsIjin'];
$periode=$_POST['periode'];
$tglAwal=$_POST['tglAwal'];
$tglEnd=$_POST['tglEnd'];
$sisapost = $_POST['sisa'];
$tglijin = tanggalsystem($_POST['tglijin']);
$tahunplafon=date('Y');

if($periode=='')
{
	$periode=0;
}
$karyawanid=$_POST['karyawanid'];
if($karyawanid=='')
{
	$karyawanid=$_SESSION['standard']['userid'];
}
$sisa='0';

if($jnsIjin!='CUTI18')
{
		#idjenis yang memotong hakcuti
		$potonganijin = makeOption($dbname,"sdm_5jenisijin","idjenis,idjenis","statuspotongan = 1");
		$imp_pot = implode("','",array_values($potonganijin));

		// otomatis hitung ulang cuti. formula dari laporan ijin/cuti
		// in(0,1,2,3,6,7,8,9,12)
		$str1="select * from ".$dbname.".sdm_cutiht where karyawanid=".$_SESSION['standard']['userid']." 
			   and periodecuti=".$periode;
		$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_OBJ);
		$numrows=owlBaris($res1);

		## CEK STAFF
		$str="select SUM(jumlahhari) as jumlahhari,karyawanid from ".$dbname.".sdm_ijin where periodecuti='".$periode."' and idjenis IN ('$imp_pot') and statuspersetujuan = '1' and statuspersetujuan_cancel not in ('1', '2', '9') and karyawanid ='".$_SESSION['standard']['userid']."' ";
		$res=fetchdata($str);
		foreach($res as $val){
			$ambil+=$val['jumlahhari'];
		}
		
		## CEK NON STAFF
		$str="select SUM(jumlahhari) as jumlahhari,karyawanid from ".$dbname.".sdm_ijinnonstaff where periodecuti='".$periode."' and idjenis IN ('$imp_pot') and stpersetujuan4 = '1' and statuspersetujuan_cancel not in ('1', '2', '9') and karyawanid ='".$_SESSION['standard']['userid']."' ";
		$res=fetchdata($str);
		foreach($res as $val) {
			@$ambil+=$val['jumlahhari'];
		}

		while($bar1=$res1->fetch()){
			if($periode == '2020'){ // kalo 2020 ga diudpate
				$sisax = $bar1->sisa; 
				$ambil = $bar1->diambil;
			} else {
				if ($periode < date('Y') && $bar1->sisa >= 6) {
					$ambil = 6;
				}
				$sisax = ($bar1->hakcuti - $ambil);
			}
		}	

		$supdate = "update ".$dbname.".sdm_cutiht set diambil='".$ambil."', sisa='".$sisax."' where karyawanid='".$_SESSION['standard']['userid']."' and periodecuti='".$periode."'";
    	try{
		$owlPDO->exec($supdate); 
		}catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
			die();
		}
		
	if ($periode != $tahunplafon && $periode != 0) {
		// $sisa = $sisapost;
		#################
		$karyawanid = $_SESSION['standard']['userid'];
		$hariini = date("Y-m-d");
		$str1="select a.*,b.namakaryawan,b.tanggalmasuk, b.nik
			from ".$dbname.".sdm_cutiht a
			left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
			where 1=1
			and a.periodecuti='".$periode."' 
			and a.karyawanid = '".$karyawanid."'
			and (tanggalkeluar='0000-00-00' or tanggalkeluar>='".$hariini."') order by b.namakaryawan"; 
		$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_OBJ);
		$res1->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res1->fetch();
			$diambil=$bar['diambil'];
			$hakcuti=$bar['hakcuti'];
			// $sisa=$bar['sisa'];

		//Get Personalia by notransaksi : 
		$sdhambil=0;
		$str="select a.notransaksi,a.jumlahhari,a.statuspersetujuan,b.idjenis,b.statuspotongan from ".$dbname.".sdm_ijin a 
		left join ".$dbname.".sdm_5jenisijin b on a.idjenis=b.idjenis  where karyawanid='".$karyawanid."' and periodecuti='".$periode."' and a.statuspersetujuan_cancel!='1' and a.statuspersetujuan !='2' order by notransaksi asc";
		$res=fetchdata($str);
		foreach ($res as $key => $val){
			if($val['statuspersetujuan']=='1' && $val['statuspotongan']!='0') {
				$sdhambil+=$val['jumlahhari'];
			}
		}

		/**
		 * Ini sisa sudah ambil cuti carry over
		 * Harusnya bisa di refactor lagi tapi udah pusing
		 */

		$sdhambil2=array();
		$str="select a.notransaksi,a.jumlahhari,a.statuspersetujuan,b.idjenis,b.statuspotongan,darijam from ".$dbname.".sdm_ijin a 
		left join ".$dbname.".sdm_5jenisijin b on a.idjenis=b.idjenis  where karyawanid='".$karyawanid."' and periodecuti='".$periode."' and a.statuspersetujuan_cancel!='1' and a.statuspersetujuan !='2' order by darijam asc";
		$res=fetchdata($str);
		foreach ($res as $key => $val){
			if($val['statuspersetujuan']=='1' && $val['statuspotongan']!='0' ){
				$sdhambil2[substr($val['darijam'],0,4)]+=$val['jumlahhari'];
				$jlhhariambillast[substr($val['darijam'],0,4)] = $val['jumlahhari'];
			}
		}

		//Get Personalia by notransaksi : 
		$sdhambil=0;
		$str="select a.notransaksi,a.jumlahhari,a.stpersetujuan4,a.statuspersetujuan_cancel,b.idjenis,b.statuspotongan from ".$dbname.".sdm_ijin a
				left join ".$dbname.".sdm_5jenisijin b on a.idjenis=b.idjenis 
				where karyawanid='".$karyawanid."' and periodecuti='".$periode."' and a.statuspersetujuan_cancel = '0' order by notransaksi asc";
		$res=fetchdata($str);
		foreach ($res as $key => $val){
			if($val['stpersetujuan4']=='1' && ($val['statuspotongan']!='0')){
				$sdhambil+=$val['jumlahhari'];
			}
		}

		$sisaprdsblmnya=$sisaprdsblm-$sdhambil;
		// exit('warning:'.$periode);

		$periodenext = $periode+1;
		$sisaprdsblm = $hakcuti;
		$sisaprdsblm = $sisaprdsblm - $sdhambil;
		if (substr($jmDr,0,4) != $periode) {//jika tanggal pengajuan tidak sesuai dengan periode cuti yang dipilih
			if($sdhambil2[$periode] == 12){//jika sisa cuti di periode yang dipilih sudah kosong
				$sisaprdsblm = 0 ;
				$sisa = $sisaprdsblm;
			}else if($sdhambil2[$periode] > 6 ){// apabila jatah cuti periode yang dipilih di tahun yang sama dengan periode melebihi 6
				$sisaprdsblm = $hakcuti - $sdhambil2[$periode];
				$sisa = $sisaprdsblm;
			}else{
				$sisaprdsblm = 6;
				$sisa = $sisaprdsblm;
			}

			$sisaprdsblm = $sisaprdsblm - $sdhambil2[$periodenext];
			$sisa = $sisaprdsblm;
		}else{
			$sisaprdsblm = $sisaprdsblm - $sdhambil2[$periode];
			$sisa = $sisaprdsblm;
		}
		
		##################

	}else{
		$str="select sisa from ".$dbname.".sdm_cutiht where karyawanid=".$_SESSION['standard']['userid']." 
		   and periodecuti=".$periode;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())
		{
			$sisa=$bar->sisa;
		}
	}
	
	$str="select sum(jumlahhari) as jumlahhari from ".$dbname.".sdm_ijin where karyawanid=".$_SESSION['standard']['userid']." and periodecuti='".$periode."' and stpersetujuanhrd='1' and jumlahhari > 0";
	$res=fetchData($str);
	$sisa = $sisa - $res[0]['jumlahhari'];
	if ($sisa == 0 && $jnsIjin=='CUTI08') {
		if ($periode != '' || !empty($periode)) {
			echo $periode."A";
		}
	}

}
else if ($jnsIjin=='CUTI18')
{
	$notrx=str_replace('-', '', $tglijin).$_SESSION['standard']['userid'];
	$str="select (jumlahharidayoff-diambil) as sisa, akandiambil,notransaksicuti from ".$dbname.".sdm_dayoff_dt_vw where karyawanid='".$_SESSION['standard']['userid']."' 
		   and tanggaldayoff <='".tanggalsystem($tglAwal)."' and tanggalberlakusampai >='".tanggalsystem($tglAwal)."' and status='1'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch())
	{
		if($bar->notransaksicuti==$notrx)
		{
			$sisa+=$bar->sisa;
		}
		else
		{
			$sisa+=($bar->sisa-$bar->akandiambil);
		}
	}	
}
else
{
	$sisa='';
}
//ambil cuti ybs
if($sisa=='')
    $sisa=0;

echo $sisa;
?>