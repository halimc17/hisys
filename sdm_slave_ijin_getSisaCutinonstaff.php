<?php
error_reporting(0);
require_once('master_validation.php');
include_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');


$jnsIjin		=$_POST['jnsIjin'];
$idjenis		=$_POST['idjenis'];
$statuspotongan	=$_POST['statuspotongan'];
$periode		=$_POST['periodec'];
$tglAwal		=$_POST['tglAwal'];
$tglEnd			=$_POST['tglEnd'];
$pros			=$_POST['pros'];
$tglijin 		= tanggalsystem($_POST['tglijin']);

if($periode==''){
	$periode=0;
}
$karyawanid=$_POST['karyawanid'];
$sisa='';
if ($idjenis=='CUTI18')
{
	if($periode!='' and $karyawanid!=''){
		if($pros=='insert')
		{

			$noTrans ="IJNS/".str_replace('-','',$tglijin)."/";
			$qTrans = selectQuery($dbname,'sdm_ijinnonstaff','notransaksi',
																"notransaksi like '".$noTrans."%'","notransaksi desc",true,1,1);
			$resTrans = fetchData($qTrans);
			if(empty($resTrans)) {
					$notransaksi = $noTrans."001";
			} else {
					$tmpTrans = substr($resTrans[0]['notransaksi'],14,3);
					$tmpTrans++;
					$notransaksi = $noTrans.str_pad($tmpTrans,3,'0',STR_PAD_LEFT);
			}
		}
		else
		{
			$snotrans = "select distinct notransaksi from " . $dbname . ".sdm_ijinnonstaff where tanggal='" . $tglijin . "' and karyawanid='" . $karyawanid . "'";
			$qnotrans=$owlPDO->query($snotrans) or die(print " Gagal: ".PDOException::getMessage());
			$qnotrans->setFetchMode(PDO::FETCH_ASSOC);
	        $rnotrans = $qnotrans->fetch();
	        $notransaksi = $rnotrans['notransaksi'];
		}

		$str="select (jumlahharidayoff-diambil) as sisa, akandiambil,notransaksicuti from ".$dbname.".sdm_dayoff_dt_vw where karyawanid='".$karyawanid."' 
			   and tanggaldayoff <'".tanggalsystem($tglAwal)."' and tanggalberlakusampai >='".tanggalsystem($tglAwal)."'  and status='1'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())
		{
			if($bar->notransaksicuti==$notransaksi)
			{
				$sisa+=$bar->sisa;
			}
			else
			{
				$sisa+=($bar->sisa-$bar->akandiambil);

			}
		}
	}
}
else
{
	if($periode!='' and $karyawanid!=''){
		// otomatis hitung ulang cuti. formula dari laporan ijin/cuti
		// in(0,1,2,3,6,7,8,9,12)
		$str1="select * from ".$dbname.".sdm_cutiht where karyawanid=".$karyawanid." 
			   and periodecuti=".$periode;
		$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_OBJ);
		$numrows=owlBaris($res1);
		
		#idjenis yang memotong hakcuti
		$potonganijin = makeOption($dbname,"sdm_5jenisijin","idjenis,idjenis","statuspotongan = 1");
		$imp_pot = implode("','",array_values($potonganijin));

		## CEK STAFF
		$str="select SUM(jumlahhari) as jumlahhari,karyawanid from ".$dbname.".sdm_ijin where periodecuti='".$periode."' and idjenis IN ('$imp_pot') and statuspersetujuan = '1' and statuspersetujuan_cancel not in ('1', '2', '9') and karyawanid ='".$karyawanid."' ";
		$res=fetchdata($str);
		foreach($res as $val){
			$ambil+=$val['jumlahhari'];
		}
		
		## CEK NON STAFF
		$str="select SUM(jumlahhari) as jumlahhari,karyawanid from ".$dbname.".sdm_ijinnonstaff where periodecuti='".$periode."' and idjenis IN ('$imp_pot') and stpersetujuan4 = '1' and statuspersetujuan_cancel not in ('1', '2', '9') and karyawanid ='".$karyawanid."' ";
		$res=fetchdata($str);
		foreach($res as $val){
			@$ambil+=$val['jumlahhari'];
		}

		while($bar1=$res1->fetch()){
			if($periode == '2020'){ // kalo 2020 ga diudpate
				$sisax = $bar1->sisa; 
				$ambil = $bar1->diambil;
			}else{
				$sisax = ($bar1->hakcuti - $ambil);
			}
		}	

		$supdate = "update ".$dbname.".sdm_cutiht set diambil='".$ambil."', sisa='".$sisax."' where karyawanid='".$karyawanid."' and periodecuti='".$periode."'";
    	try{
		$owlPDO->exec($supdate); 
		}catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
			die();
		}

		$str="select sisa from ".$dbname.".sdm_cutiht where karyawanid=".$karyawanid." 
			   and periodecuti=".$periode;
		$resx=fetchdata($str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch()){
			$sisa=$bar->sisa;
		}
		if(count($resx)>0 && $sisa == 0 && $idjenis == "CUTI08") {
			echo $periode."A";
		}
	}

}

if($sisa=='')
    $sisa=0;
echo $sisa;
?>