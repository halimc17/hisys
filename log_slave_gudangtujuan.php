<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$param=$_POST;
if(empty($param['gudangId'])){
	$param['gudangId'] = "";
}
$kdid=substr($param['gudangId'],0,4);
$whrt="kodeorganisasi='".substr($param['gudangId'],0,4)."'";
$optInduk=  makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', $whrt);

switch($param['proses']){
    case'gudangtujuan':
		
		// ambil tipe unit
		$cek_tipeunit="select tipe,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi='".substr($param['gudangId'],0,4)."' limit 1 ";
		$stq_cek_tipeunit = fetchdata($cek_tipeunit);
		$Tipelokasitugas = $stq_cek_tipeunit[0]['tipe'];
		$kodeorganisasi_x = $stq_cek_tipeunit[0]['kodeorganisasi'];
		$PT_x = substr($stq_cek_tipeunit[0]['kodeorganisasi'],0,3);
		// Ambil tipe gudang
		$cek_tipegudang="select tipe,inti,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$param['gudangId']."' limit 1 ";
		$stq_cek_tipegudang = fetchdata($cek_tipegudang);
		$cekApakahdivisi = $stq_cek_tipegudang[0]['tipe'];
		$inti_plasma = $stq_cek_tipegudang[0]['inti'];

		if($cekApakahdivisi == 'GUDANGTEMP'){
			$kodeorgDivisi = $stq_cek_tipegudang[0]['kodeorganisasi'];
		}

		$optlokasitujuan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		if($Tipelokasitugas == 'HOLDING' ){
			$str="  select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe = 'GUDANG' and (induk in (".getOrgDetail(2).")) and induk!='".$kodeorganisasi_x."' order by kodeorganisasi";
		}else if($Tipelokasitugas == 'KANWIL' ){
			$str="  select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe = 'GUDANG' and (induk in (".getOrgDetail(2).")) and induk!='".$kodeorganisasi_x."' order by kodeorganisasi";
		}else if($Tipelokasitugas == 'BULKING' ){
			$str="  select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe = 'GUDANG' and (induk in (".getOrgDetail(2).")) and induk!='".$kodeorganisasi_x."' order by kodeorganisasi";
		}else{
			$gudangdivisi='';
			$gudangxxx=makeOption($dbname,'kebun_5gudangtransaksi','afdeling,kodegudang',"status='1'");
			if($kodeorgDivisi!='' and $gudangxxx[$kodeorgDivisi]!=''){
				$gudangdivisi=" and kodeorganisasi like '".$kodeorganisasi_x."%'";
			}else{
				if($kodeorganisasi_x=='SDKM'){			
					$tambahksbw=" or kodeorganisasi = 'KSBW52' ";
				}
			}
			// sri rahayu grup: tambah KSBW... SDKM mau mutasi ke KSBW tidak bisa karena beda regional
			$gudangdivisi.=" and kodeorganisasi not like '".$kodeorganisasi_x."%'";
			// $str="  select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe like 'GUDANG%' and (induk in(select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."') ".$tambahksbw." ) ".$gudangdivisi." order by kodeorganisasi";
			$str="  select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe like 'GUDANG%' ".$gudangdivisi." order by kodeorganisasi";
		}
		// $optlokasitujuan.="<optgroup label='".$PT_x." - ".getNamaOrg($PT_x)."'>";

		$unitDetailAkses = orgDetailuser($_SESSION['standard']['username'],'2');
		$gudang_detailAkses=" (".$unitDetailAkses.") ";
		if($cekApakahdivisi == 'GUDANGTEMP'){
			if($inti_plasma == 0){
				$sql="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe = 'GUDANG' and left(kodeorganisasi,4) IN ".$gudang_detailAkses."  order by kodeorganisasi";
			}else{
				$sql="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe = 'GUDANG' and kodeorganisasi like '".$kodeorganisasi_x."%' order by kodeorganisasi";
			}
		}else{
			// create array 
			// Hapus tanda kutip tunggal
			$stringData = str_replace("'", "", $unitDetailAkses);

			// Ubah menjadi array
			$arrayData = explode(',', $stringData);

			// kodeorganisasi
			// Array untuk menampung klausa-klausa LIKE
			$conditions_kodeorganisasi = [];

			// Loop melalui setiap nilai dan buat klausa LIKE
			foreach ($arrayData as $value) {
				$conditions_kodeorganisasi[] = "kodeorganisasi LIKE '{$value}%'";
			}

			// Gabungkan semua klausa LIKE dengan 'OR'
			$whereClause_kodeorganisasi =  "AND (\n    " . implode(" OR\n    ", $conditions_kodeorganisasi) . "\n)";
			// AKHIR kodeorganisasi

			// kodeorganisasi
			// Array untuk menampung klausa-klausa LIKE
			$conditions_kodegudang = [];

			// Loop melalui setiap nilai dan buat klausa LIKE
			foreach ($arrayData as $value) {
				$conditions_kodegudang[] = "kodeorganisasi LIKE '{$value}%'";
			}

			// Gabungkan semua klausa LIKE dengan 'OR'
			$whereClause_kodegudang =  "AND (\n    " . implode(" OR\n    ", $conditions_kodegudang) . "\n)";
			// AKHIR kodeorganisasi


			if(count($unitDetailAkses) > 0){
				$sql="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe like 'GUDANG%' ".$whereClause_kodegudang."  order by kodeorganisasi";
			}else{
				$sql="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe like 'GUDANG%' and kodeorganisasi like '".$kodeorganisasi_x."%' order by kodeorganisasi";
			}
		}
		$stq = fetchdata($sql);
		$n='';
		foreach($stq as $val){

			$d=substr($val['kodeorganisasi'],0,4);

			if($d!==$n && $n!==""){			
				$optlokasitujuan.="</optgroup>";
			}

			if($d!=$n){	
				$optlokasitujuan.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
			}

			$optlokasitujuan.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";

			$n=$d;

			if($d!=$n){		
				$optlokasitujuan.="</optgroup>";
			}
		}

		// $optlokasitujuan.="</optgroup>";
		
		// echo $str;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$optsloc="<option value=''></option>";
		while($bar=$res->fetch()){
			// $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".substr($bar->kodeorganisasi,0,4)."'");
			// $d=$induk[substr($bar->kodeorganisasi,0,4)];
			// if($d!=$n){			
			// 	$optlokasitujuan.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
			// }
			// if(substr($bar->kodeorganisasi,0,4)==$kodeorganisasi_x){		
			// 	$optlokasitujuan.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
			// }elseif($tipeg[$bar->kodeorganisasi]!=''){
			// 	$optlokasitujuan.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
			// }
			
			// $n=$d;
			// if($d!=$n){			
			// 	$optlokasitujuan.="</optgroup>";
			// }
		}
		echo $optlokasitujuan;
    break;
   
}
