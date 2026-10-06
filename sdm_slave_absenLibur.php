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
$preview =checkPostGet('preview','');

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
		$daftar=array();
		if($preview=='1'){
			$namaKary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
		}
		foreach($res as $bar){

			## DElETE DULU
			$str1="select * from ".$dbname.".sdm_absensidt where tanggal='".$t."' and kodeorg='".$subbag."' and karyawanid='".$bar['karyawanid']."' and absensi in ('L', 'LN', 'MG')";
			$res1=fetchdata($str1);
			$diganti=(count($res1)>0);
			if($diganti){	
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
				$daftar[]=array('karyawanid'=>$bar['karyawanid'],'status'=>($diganti?'DIGANTI':'BARU'),'absensi'=>$jnlibur,'hk'=>$hk);
			}else{
				$daftar[]=array('karyawanid'=>$bar['karyawanid'],'status'=>'DILEWATI','absensi'=>$res[0]['absensi'],'hk'=>$res[0]['hk']);
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
		
		#preview: semua proses di atas dijalankan apa adanya, lalu dibatalkan (rollback) dan hanya daftarnya yang ditampilkan
		if($preview=='1'){
			$owlPDO->rollback();
			$jmlBaru=0;$jmlGanti=0;$jmlLewat=0;
			$baris='';
			$urut=0;
			foreach($daftar as $d){
				$urut++;
				if($d['status']=='BARU'){$jmlBaru++;}
				elseif($d['status']=='DIGANTI'){$jmlGanti++;}
				else{$jmlLewat++;}
				$baris.="<tr class=rowcontent>
					<td align=center>".$urut."</td>
					<td>".strtoupper($namaKary[$d['karyawanid']])."</td>
					<td align=center>".$d['absensi']."</td>
					<td align=right>".(float)$d['hk']."</td>
					<td align=center>".$d['status']."</td>
					</tr>";
			}
			$jmlSimpan=$jmlBaru+$jmlGanti;
			echo"<div style='padding:6px 8px;font-size:12px;'>
				Tanggal <b>".$_POST['tgllibur']."</b>, unit <b>".$subbag."</b>. Akan disimpan: <b>".$jmlSimpan."</b> karyawan
				(baru ".$jmlBaru.", diganti ".$jmlGanti."). Dilewati karena sudah punya absensi lain: <b>".$jmlLewat."</b>.
				</div>
				<div style='max-height:320px;overflow:auto;'>
				<table class=sortable border=0 cellspacing=1 cellpadding=3 style='width:100%'>
				<thead><tr class=rowheader>
				<th align=center>No.</th><th align=center>Nama Karyawan</th><th align=center>Absensi</th><th align=center>HK</th><th align=center>Status</th>
				</tr></thead>
				<tbody>".($baris!=''?$baris:"<tr class=rowcontent><td colspan=5 align=center>Tidak ada karyawan yang sesuai</td></tr>")."</tbody>
				</table></div>
				<div style='padding:8px;text-align:center;'>".($jmlSimpan>0?"<button class=mybutton onclick=prosesSimpanHariLibur()>Simpan</button> ":"")."<button class=mybutton onclick=closeDialog()>Batal</button></div>";
			break;
		}

		#execute
		$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
		
		echo $no;
	break;
}

?>