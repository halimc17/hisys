<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$jnlibur =checkPostGet('jnlibur','');
$tipekary=checkPostGet('tipekary','');
$kodeorg =checkPostGet('kodeorg','');
$tgllibur=tanggalsystemn(checkPostGet('tgllibur',''));
$proses  =checkPostGet('proses','');
$divisi  =checkPostGet('divisi','');

switch($proses){
	case'getdivisi':
		$optorg="<option value=''>UMUM - KANTOR / UMUM</option>";
		$str="select * from ".$dbname.".organisasi where length(kodeorganisasi)=6 and induk = '".$kodeorg."' and kodeorganisasi in (select distinct subbagian from ".$dbname.".datakaryawan) order by namaorganisasi asc ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optorg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		}
		echo $optorg;
	break;
	case'simpan':
		try {
		$owlPDO->beginTransaction();
		
		if($jnlibur=='' or $kodeorg=='' or $tgllibur=='--'){
			throw new PDOException("Kodeorganisasi, Tipe karyawan, tanggal dan kehadiran wajib diisi.");
		}
		
		#periksa jl libur, jika Minggu (M) maka periksa tgllibur
		$t=$tgllibur;
		$hari=date('D',  strtotime($t));
		$hmhb=0;
		$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal='".$t."' and (kebun='GLOBAL' or kebun='".$kodeorg."')";
		$roworg=fetchdata($strorg);

		if(@$roworg[0]['keterangan']=='libur'){
			$hmhb+=1;
		} else if (($hari=='Sun' and @$roworg[0]['keterangan']=='') or @$roworg[0]['keterangan']=='libur'){
			$hmhb+=1;
		}
		if($hari=='Sun' and $jnlibur!='MG'){
			throw new PDOException('Tanggal '.$_POST['tgllibur']." adalah hari minggu, kode absensi salah.");
		}else if($jnlibur=='MG' and $hari!='Sun'){
			throw new PDOException('Tanggal '.$_POST['tgllibur']." bukan hari minggu, kode absensi salah.");  
		}
		if($hmhb==0 and $jnlibur!=''){
			throw new PDOException('Tanggal '.$_POST['tgllibur']." bukan hari libur, kode absensi salah.");  
		}
		
		$wh="and jenisgaji='B'";

		#ambil periode gaji
		$str="select periode from ".$dbname.".sdm_5periodegaji where '".$t."'<=tanggalsampai and   '".$t."'>=tanggalmulai ".$wh." and kodeorg='".$kodeorg."'";

	
		$res=fetchdata($str);
		foreach($res as $bar){
			$periode=$bar['periode'];
		}
		
		if($periode==''){
			throw new PDOException("Payroll period required");
		}

		if($tipekary != ''){
			$tipekaryawan = "and tipekaryawan = '".$tipekary."'";
		}else{
			$tipekaryawan = '';
		}

		if($divisi==''){
			$subbag= $kodeorg;
			$whr   = " and subbagian=''";
		}else{
			$subbag= $divisi;
			$whr   = " and subbagian='".$divisi."'";
		}
		
		
		$str="select distinct karyawanid from ".$dbname.".datakaryawan where 1=1 ".$tipekaryawan." and lokasitugas='".$kodeorg."' and tipekaryawan !='4' ".$whr." 
		and (tanggalkeluar > '".$t."' or tanggalkeluar = '0000-00-00')";
		$res=fetchdata($str);
		$jlhkary=count($res);
		$no="";
		foreach($res as $bar){

			## DElETE DULU
			$str1="select * from ".$dbname.".sdm_absensidt where tanggal='".$t."' and kodeorg='".$subbag."' and karyawanid='".$bar['karyawanid']."' and absensi in ('L', 'LN', 'MG')";
			$res1=fetchdata($str1);
			if(count($res1)>0){	
				$delDet = deleteQuery($dbname, "sdm_absensidt","tanggal='".$t."' and kodeorg='".$subbag."' and karyawanid='".$bar['karyawanid']."' and absensi in ('L', 'LN', 'MG') ");
				$owlPDO->exec($delDet);
			}

			$str="select * from ".$dbname.".sdm_absensidt where tanggal='".$t."' and kodeorg='".$subbag."' and karyawanid='".$bar['karyawanid']."'";
			$res=fetchdata($str);
			if(count($res)==0){				
				$no++;
				
				$opttipekaryawan = makeOption($dbname,'datakaryawan','karyawanid,tipekaryawan');
				if($opttipekaryawan[$bar['karyawanid']] == 0){
					$noakun = '7110101';// -> AKUN GAJI STAFF
				}else{
					$noakun = '7110201'; // -> AKUN GAJI NONSTAFF
				}

				## Jabatan  ke biaya keamanan
				$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi = 'HR' and kodeparameter = 'JABSECUR'";
				$res=fetchdata($str);
				$jabatanSecur = $res[0]['nilai'];

				$newArrayJab = array();
				$str="select *  from ".$dbname.".sdm_5jabatan where kodejabatan in (".$jabatanSecur.")";
				$res=fetchdata($str);
				foreach($res as $val){
					$newArrayJab[$val['kodejabatan']] = $val['kodejabatan'];
				}

				if(in_array(getKary($bar['karyawanid'],'kodejabatan'),$newArrayJab)){
					$noakun = '7120400'; // -> Akun biaya kaeamanan
				}

				## Jabatan ke biayan ke prasarana umum
				$str1="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi = 'HR' and kodeparameter = 'JABPRASARA'";
				$res1=fetchdata($str1);
				$jabatanPrasarana = $res1[0]['nilai'];

				$newArrayJabPra = array();
				$str="select *  from ".$dbname.".sdm_5jabatan where kodejabatan in (".$jabatanPrasarana.")";
				$res=fetchdata($str);
				foreach($res as $val){
					$newArrayJabPra[$val['kodejabatan']] = $val['kodejabatan'];
				}

				if(in_array(getKary($bar['karyawanid'],'kodejabatan'),$newArrayJabPra)){
					$noakun = '7140912'; // -> Akun biaya Prasarana
				}

				$str="select nilaihk from ".$dbname.".sdm_5absensi where kodeabsen = '".$jnlibur."' ";
				$res=fetchdata($str);
				$hk = $res[0]['nilaihk'];

				if($opttipekaryawan[$bar['karyawanid']] == 0){
					$umr = 0; ## STAFF
				}else{
					$umr = getUpahKary($periode,$bar['karyawanid']) * $hk; ## NON STAFF
				}

				$data = [
                    "kodeorg" 	 => $subbag,
                    "tanggal" 	 => $t,
                    "absensi" 	 => $jnlibur,
                    "karyawanid" => $bar['karyawanid'],
                    "jam" 		 => "00:00:00",
                    "jamPlg" 	 => "00:00:00",
                    "catu" 		 => 0,
                    "noakun" 	 => $noakun,
                    "hk" 		 => $hk,
                    "umr" 		 => $umr,
                    "penjelasan" => "Created By System",
                ];

                $cols = array_keys($data);
                $query = insertQuery($dbname, "sdm_absensidt", $data, $cols);
				$owlPDO->exec($query);
			}
		}
		if($jlhkary>0){			
			$str="select * from ".$dbname.".sdm_absensiht where tanggal='".$t."' and kodeorg='".$subbag."' and periode='".$periode."'";
			$res=fetchdata($str);
			if(count($res)==0){
				$query="INSERT INTO ".$dbname.".`sdm_absensiht` (`tanggal`, `kodeorg`, `periode`,`updateby`)
				VALUES ('".$t."', '".$subbag."', '".$periode."','".$_SESSION['standard']['userid']."')";
				$owlPDO->exec($query);
			}
		}
		
		#execute
		$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
		
		echo $no;
	break;
}

?>