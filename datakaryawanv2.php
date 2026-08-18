<?php
if(!isset($dbname)){
	require_once('../dzconfig/connection.php');
	require_once('../__dz_validation.php');    
	include('../dzlib/function.php');
	include('../dzlib/panelset.php');
	include('../dzlib/panelset_graph.php');    
	include('../dzlib/table.php');
}
$apa = "";
if(isset($_GET['apa'])){
	$apa 	= $_GET['apa']; // mode Report
}
$tilok = "";
if(isset($_GET['tilok'])){
	$tilok 	= $_GET['tilok']; // tipe Lokasi
}
$tikar = "";
if(isset($_GET['tikar'])){
	$tikar 	= $_GET['tikar']; // tipe karyawan
}

$hariini=date('Y-m-d');
$kemarin=date('Y-m-d', strtotime("-1 day"));
if(isset($_GET['tanggal']) and $_GET['tanggal']!=''){
	$hariini=$_GET['tanggal'];
	$kemarin=date('Y-m-d', strtotime($hariini." -1 day"));  
}
$blnini=date('Y-m');
$bulanlalu=date('Y-m', strtotime($hariini." -1 month"));
$thnini=date('Y');
$thnlalu=date('Y', strtotime("-1 year"));
$lastupdate=date('H:i');
$selectDate = "";
// data tanggal/tahun/periode	
if(isset($_GET['data']) and $_GET['data'] != ""){
	$datatanggal = trim($_GET['data']);
	$selectDateFrom = " = '".$datatanggal."' ";
	if(strlen($datatanggal) == 4){
		$selectDateFormat = "%Y";
	}elseif(strlen($datatanggal) == 7){
		$selectDateFormat = "%Y-%m";
	}elseif(strlen($datatanggal) == 10){
		$selectDateFormat = "%Y-%m-%d";
	}
	$selectDate = $datatanggal;
}else{
	$selectDateFrom = " >= '".$thnlalu."' ";
	$selectDateFormat = "%Y";
	
}
	
$str="select kodeorganisasi, namaorganisasi, induk, tipe,subregional from ".$dbname.".organisasi a
left join ".$dbname.".bgt_regional_assignment b on a.kodeorganisasi=b.kodeunit
where 1=1 order by subregional, induk, tipe, kodeorganisasi";
$res=mysqli_query($conn,$str);
while($bar=mysqli_fetch_object($res)){
	$kamusinduk[$bar->kodeorganisasi]=$bar->induk;
	$kamustipe[$bar->kodeorganisasi]=$bar->tipe;
	$listkodeorg[$bar->kodeorganisasi]=$bar->namaorganisasi;
	$listkodeorgbyreg[$bar->subregional][$bar->kodeorganisasi]=$bar->namaorganisasi;
	if(strlen($bar->kodeorganisasi)<=6 and substr($bar->kodeorganisasi,0,4)==$_GET['unit']){
		$listdivisi[$bar->kodeorganisasi]=$bar->namaorganisasi;
	}
}

$str="select * from ".$dbname.".bgt_regional_assignment";
$res=mysqli_query($conn,$str);
while($bar=mysqli_fetch_object($res)){
	$optregional[$bar->kodeunit]=$bar->subregional;
	$listreg[$bar->subregional][]=$bar->kodeunit;
}

$str="select * from ".$dbname.".setup_blok where statusblok in ('TM','TBM','TB')";
$res=mysqli_query($conn,$str);
while($bar=mysqli_fetch_object($res)){
	if($bar->statusblok=='TM'){		
		$luas[substr($bar->kodeorg,0,4)]['tm']+=$bar->luasareaproduktif;
		$luas[substr($bar->kodeorg,0,6)]['tm']+=$bar->luasareaproduktif;
	}
	if($bar->statusblok=='TBM' and $bar->statusblok=='TB'){
		$luas[substr($bar->kodeorg,0,4)]['tbm']+=$bar->luasareaproduktif;		
		$luas[substr($bar->kodeorg,0,6)]['tbm']+=$bar->luasareaproduktif;		
	}
}

if($apa!='modalkarydetail'){
	$str="select id, tipe  from ".$dbname.".sdm_5tipekaryawan where aktif ='1'";
	$res=mysqli_query($conn,$str);
	while($bar=mysqli_fetch_object($res)){
		$kamustipekary[$bar->id]=$bar->tipe;
	}

	#### QRY Version 2
	$karyawanActive=array();
	$karyawanSTAFF=array();
	$datakaryawanBulanan=array();
	$filterDateDef = array();
	$loopDataTanggal = array($blnini,$bulanlalu,$thnini,$thnlalu);

	$arrjabatan['pnn'] = array('59');
	$arrjabatan['rwt'] = array('61');


	$str = "select * from ".$dbname.".datakaryawan";
	$res = mysqli_query($conn,$str);
	while($bar=mysqli_fetch_assoc($res)){
		$opttipekary[$bar['karyawanid']]=$bar['tipekaryawan'];
		$optlokasitugas[$bar['karyawanid']]=$bar['lokasitugas'];
		$optsubbagian[$bar['karyawanid']]=$bar['subbagian'];
		$detailkary[$bar['karyawanid']]=array(
			'nama'=>$bar['namakaryawan'],
			'tanggallahir'=>$bar['tanggallahir'],
			'nik'=>$bar['nik'],
			'lokasitugas'=>$bar['lokasitugas'],
			'subbagian'=>$bar['subbagian'],
			'tipekaryawan'=>$bar['tipekaryawan'],
			'kodejabatan'=>$bar['kodejabatan'],
			'jeniskelamin'=>$bar['jeniskelamin']
		);
	}	

	$str = "select distinct nik, substr(tanggal,1,7) as periode from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where tanggal between '".$bulanlalu."-01' and '".$hariini."' and tipetransaksi='PNN'";
	$res = mysqli_query($conn,$str);
	while($bar=mysqli_fetch_assoc($res)){
		if($bar['periode']==$bulanlalu){
			$pemanenbl[$bar['nik']] = $bar['nik'];
			$listkaryaktif['pnn']['bl'][$bar['nik']]=$bar['nik'];
		}else{
			$pemanen[$bar['nik']] = $bar['nik'];
			$listkaryaktif['pnn']['bi'][$bar['nik']]=$bar['nik'];
		}
	}
	
	
	$str = "select distinct nikpemel as nik, substr(tanggal,1,7) as periode from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where tanggal between '".$bulanlalu."-01' and '".$hariini."' and tipetransaksi!='PNN'";
	$res = mysqli_query($conn,$str);
	while($bar=mysqli_fetch_assoc($res)){
		if($bar['periode']==$bulanlalu){
			if(empty($pemanenbl[$bar['nik']])){				
				$perawatanbl[$bar['nik']] = $bar['nik'];
				$listkaryaktif['rwt']['bl'][$bar['nik']]=$bar['nik'];
			}
		}else{
			if(empty($pemanen[$bar['nik']])){
				$perawatan[$bar['nik']] = $bar['nik'];
				$listkaryaktif['rwt']['bi'][$bar['nik']]=$bar['nik'];
			}
		}
	}


	
	
	#jangan digabung di atas jadi double angkanya
	$str = "select distinct nikpemel, nik, substr(tanggal,1,7) as periode, tanggal from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where tanggal between '".$bulanlalu."-01' and '".$hariini."'";
	$res = mysqli_query($conn,$str);
	while($bar=mysqli_fetch_assoc($res)){
		if($bar['nikpemel']!=''){		
			$hadir[$bar['nikpemel']][$bar['tanggal']]=$bar['tanggal'];
		}else{
			$hadir[$bar['nik']][$bar['tanggal']]=$bar['tanggal'];
		}
	}

	# DATAKARYAWAN
	$str = "select distinct karyawanid, nik, namakaryawan, lokasitugas, tanggalmasuk,tanggalkeluar,CAST(tipekaryawan AS SIGNED) as tipekaryawan,kodejabatan,jeniskelamin,subbagian,tanggallahir,DATE_FORMAT(tanggalmasuk, '%Y-%m') as periode from ".$dbname.".datakaryawan where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".$hariini."') order by lokasitugas, subbagian, namakaryawan";
	$res = mysqli_query($conn,$str);
	while($bar=mysqli_fetch_assoc($res)){
		if($bar['subbagian']==''){$bar['subbagian']=$bar['lokasitugas'];}
		if(in_array($bar['kodejabatan'],$arrjabatan['pnn'])){
			$karytersedia['pnn'][$bar['karyawanid']]=$bar['karyawanid'];
			$tersediabyjab[$bar['kodejabatan']][$bar['tipekaryawan']][$bar['karyawanid']]=$bar['karyawanid'];
		}else{
			if(in_array($bar['kodejabatan'],$arrjabatan['rwt'])){
				$karytersedia['rwt'][$bar['karyawanid']]=$bar['karyawanid'];
				$tersediabyjab[$bar['kodejabatan']][$bar['tipekaryawan']][$bar['karyawanid']]=$bar['karyawanid'];
			}else{
				$karytersedia['oth'][$bar['karyawanid']]=$bar['karyawanid'];
				$tersediabyjab[$bar['kodejabatan']][$bar['tipekaryawan']][$bar['karyawanid']]=$bar['karyawanid'];
				if($bar['tipekaryawan']=='0'){
					$listkaryaktif['oth']['bi'][$bar['karyawanid']]=$bar['karyawanid']; #staff
				}
			}
		}
		$listjabatan[$bar['kodejabatan']]=$bar['kodejabatan'];
		$listtipekary[$bar['tipekaryawan']]=$bar['tipekaryawan'];
	}

	$akhirbllalu = date('Y-m-d', strtotime($blnini."-01 -1 day"));
	$str = "select distinct karyawanid, nik, namakaryawan, lokasitugas, tanggalmasuk,tanggalkeluar,CAST(tipekaryawan AS SIGNED) as tipekaryawan,kodejabatan,jeniskelamin,levelpendidikan,tanggallahir,DATE_FORMAT(tanggalmasuk, '%Y-%m') as periode from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and periodegaji ='".$bulanlalu."' and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".$akhirbllalu."') and tipekaryawan='0'  order by namakaryawan, lokasitugas, subbagian";
	$res = mysqli_query($conn,$str);
	while($bar=mysqli_fetch_assoc($res)){
		$listkaryaktif['oth']['bl'][$bar['karyawanid']]=$bar['karyawanid'];
	}

	// echo"<pre>";
	// print_r($tersedia);
	// echo"</pre>";


	// $str = "select distinct karyawanid, tipekaryawan from ".$dbname.".sdm_gaji_vw where periodegaji ='".$bulanlalu."' and sumber!='UPLOAD'";
	// $res = mysqli_query($conn,$str);
	// while($bar=mysqli_fetch_assoc($res)){
		// if(!empty($pemanenbl[$bar['karyawanid']])){
			// $listkaryaktif['pnn']['bl'][$bar['karyawanid']]=$bar['karyawanid'];
		// }elseif(!empty($perawatanbl[$bar['karyawanid']])){
			// $listkaryaktif['rwt']['bl'][$bar['karyawanid']]=$bar['karyawanid'];
		// }else{
			// $listkaryaktif['oth']['bl'][$bar['karyawanid']]=$bar['karyawanid'];
		// }
	// }


	$str = "select nikmandor, nikmandor1, nikasisten, keranimuat, substr(tanggal,1,7) as periode, tanggal from ".$dbname.".kebun_aktifitas where tanggal between '".$bulanlalu."-01' and '".$hariini."'";
	$res = mysqli_query($conn,$str);
	while($bar=mysqli_fetch_assoc($res)){
		if($bar['periode']==$bulanlalu){
			if($opttipekary[$bar['nikmandor']]!='0' and empty($pemanenbl[$bar['nikmandor']]) and empty($perawatanbl[$bar['nikmandor']]) and $bar['nikmandor']!=''){
				$pengawasbl[$bar['nikmandor']]=$bar['nikmandor'];
			}
			if($opttipekary[$bar['nikmandor1']]!='0' and empty($pemanenbl[$bar['nikmandor1']]) and empty($perawatanbl[$bar['nikmandor1']]) and $bar['nikmandor1']!=''){
				$pengawasbl[$bar['nikmandor1']]=$bar['nikmandor1'];
			}
			if($opttipekary[$bar['nikasisten']]!='0' and empty($pemanenbl[$bar['nikasisten']]) and empty($perawatanbl[$bar['nikasisten']]) and $bar['nikasisten']!=''){
				$pengawasbl[$bar['nikasisten']]=$bar['nikasisten'];
			}
			if($opttipekary[$bar['keranimuat']]!='0' and empty($pemanenbl[$bar['keranimuat']]) and empty($perawatanbl[$bar['keranimuat']]) and $bar['keranimuat']!=''){
				$pengawasbl[$bar['keranimuat']]=$bar['keranimuat'];
			}
		}else{
			if($opttipekary[$bar['nikmandor']]!='0' and empty($pemanen[$bar['nikmandor']]) and empty($perawatan[$bar['nikmandor']]) and $bar['nikmandor']!=''){
				$pengawas[$bar['nikmandor']]=$bar['nikmandor'];
			}
			if($opttipekary[$bar['nikmandor1']]!='0' and empty($pemanen[$bar['nikmandor1']]) and empty($perawatan[$bar['nikmandor1']]) and $bar['nikmandor1']!=''){
				$pengawas[$bar['nikmandor1']]=$bar['nikmandor1'];
			}
			if($opttipekary[$bar['nikasisten']]!='0' and empty($pemanen[$bar['nikasisten']]) and empty($perawatan[$bar['nikasisten']]) and $bar['nikasisten']!=''){
				$pengawas[$bar['nikasisten']]=$bar['nikasisten'];
			}
			if($opttipekary[$bar['keranimuat']]!='0' and empty($pemanen[$bar['keranimuat']]) and empty($perawatan[$bar['keranimuat']]) and $bar['keranimuat']!=''){
				$pengawas[$bar['keranimuat']]=$bar['keranimuat'];
			}
		}
		$hadir[$bar['nikmandor']][$bar['tanggal']]=$bar['tanggal'];
		$hadir[$bar['nikmandor1']][$bar['tanggal']]=$bar['tanggal'];
		$hadir[$bar['nikasisten']][$bar['tanggal']]=$bar['tanggal'];
		$hadir[$bar['keranimuat']][$bar['tanggal']]=$bar['tanggal'];
	}

	foreach ($pengawas as $key => $value) {
		$listkaryaktif['oth']['bi'][$key]=$key;
	}
	foreach ($pengawasbl as $key => $value) {
		$listkaryaktif['oth']['bl'][$key]=$key;
	}


	$str = "select distinct karyawanid, substr(tanggal,1,7) as periode from ".$dbname.".sdm_absensidt where tanggal between '".$bulanlalu."-01' and '".$hariini."'";
	$res = mysqli_query($conn,$str);
	while($bar=mysqli_fetch_assoc($res)){
		if($bar['periode']==$bulanlalu){
			if(empty($pemanenbl[$bar['karyawanid']]) and empty($perawatanbl[$bar['karyawanid']]) and empty($pengawasbl[$bar['karyawanid']])){
				$listkaryaktif['oth']['bl'][$bar['karyawanid']]=$bar['karyawanid'];
				$sdmabsensibl[$bar['karyawanid']]=$bar['karyawanid'];
			}
		}else{			
			if(empty($pemanen[$bar['karyawanid']]) and empty($perawatan[$bar['karyawanid']]) and empty($pengawas[$bar['karyawanid']])){
				$listkaryaktif['oth']['bi'][$bar['karyawanid']]=$bar['karyawanid'];
				$sdmabsensi[$bar['karyawanid']]=$bar['karyawanid'];
			}
		}
	}

	# jangan di simpan diatas jadi double
	$str = "select distinct karyawanid, substr(tanggal,1,7) as periode, tanggal from ".$dbname.".sdm_absensidt where tanggal between '".$bulanlalu."-01' and '".$hariini."'";
	$res = mysqli_query($conn,$str);
	while($bar=mysqli_fetch_assoc($res)){
		$hadir[$bar['karyawanid']][$bar['tanggal']]=$bar['tanggal'];
	}

	$str = "select distinct idkaryawan as karyawanid, substr(tanggal,1,7) as periode from ".$dbname.".vhc_runhk_vw where tanggal between '".$bulanlalu."-01' and '".$hariini."'";
	$res = mysqli_query($conn,$str);
	while($bar=mysqli_fetch_assoc($res)){
		if($bar['periode']==$bulanlalu){
			if(empty($pemanenbl[$bar['karyawanid']]) and empty($perawatanbl[$bar['karyawanid']]) and empty($pengawasbl[$bar['karyawanid']]) and empty($sdmabsensibl[$bar['karyawanid']])){
				$listkaryaktif['oth']['bl'][$bar['karyawanid']]=$bar['karyawanid'];
			}
		}else{			
			if(empty($pemanen[$bar['karyawanid']]) and empty($perawatan[$bar['karyawanid']]) and empty($pengawas[$bar['karyawanid']]) and empty($sdmabsensi[$bar['karyawanid']])){
				$listkaryaktif['oth']['bi'][$bar['karyawanid']]=$bar['karyawanid'];
			}
		}
	}

	# jangan di simpan diatas jadi double
	$str = "select distinct idkaryawan as karyawanid, substr(tanggal,1,7) as periode, tanggal from ".$dbname.".vhc_runhk_vw where tanggal between '".$bulanlalu."-01' and '".$hariini."'";
	$res = mysqli_query($conn,$str);
	while($bar=mysqli_fetch_assoc($res)){
		$hadir[$bar['karyawanid']][$bar['tanggal']]=$bar['tanggal'];
	}

	#khusus staff
	$str = "select distinct karyawan as karyawanid, substr(scan_date,1,7) as periode, substr(scan_date,1,10) as tanggal from ".$dbname.".att_log a left join ".$dbname.".att_pegawai b on a.sn=b.sn and a.pin=b.pin where substr(scan_date,1,10) between '".$bulanlalu."-01' and '".$hariini."'";
	$res = mysqli_query($conn,$str);
	while($bar=mysqli_fetch_assoc($res)){
		if($opttipekary[$bar['karyawanid']]=='0'){		
			$hadir[$bar['karyawanid']][$bar['tanggal']]=$bar['tanggal'];
			$staffabsen[$bar['karyawanid']]=$bar['karyawanid'];
		}
	}
	$str = "select distinct karyawanid, substr(tanggalabsen,1,7) as periode, tanggalabsen as tanggal from ".$dbname.".upload_absensi where tanggalabsen between '".$bulanlalu."-01' and '".$hariini."'";
	$res = mysqli_query($conn,$str);
	while($bar=mysqli_fetch_assoc($res)){
		if($opttipekary[$bar['karyawanid']]=='0'){		
			$hadir[$bar['karyawanid']][$bar['tanggal']]=$bar['tanggal'];
			$staffabsen[$bar['karyawanid']]=$bar['karyawanid'];
		}
	}
	


	foreach($hadir as $kary => $val){
		foreach($val as $tgl){
			if(substr($tgl,0,7)==$bulanlalu){
				$kehadiran[$kary]['bl']++;
			}
				
			if(substr($tgl,0,7)==$blnini){	
				$kehadiran[$kary]['bi']++;
			}
		}
		$listkary[$kary]=$kary;
	}
	
	
	// echo"<pre>";
	// print_r($hadir);
	// echo"</pre>";

	foreach($karytersedia as $jenis => $val1){
		foreach($val1 as $kary){
			$listkarytersedia[$jenis][$opttipekary[$kary]]++;
			$totaltersedia[$jenis]++;
			
			$listkarytersedia['ksp'][$opttipekary[$kary]]++;
			$totaltersedia['ksp']++;
			
			#perunit
			$datadetail[$jenis][$opttipekary[$kary]][$optlokasitugas[$kary]]['x']['sedia']++;
			$listregion[$jenis][$opttipekary[$kary]][$optregional[$optlokasitugas[$kary]]]['x']['sedia']=$optregional[$optlokasitugas[$kary]];
			
			
			#perdivisi
			if($optsubbagian[$kary]==''){
				$optsubbagian[$kary]=$optlokasitugas[$kary];
			}
			$datadetaildiv[$jenis][$opttipekary[$kary]][$optsubbagian[$kary]]['x']['sedia']++;
			
			if($_GET['region']!=''){
				if($jenis==$_GET['jenis'] and $opttipekary[$kary]==$_GET['tipekar'] and in_array($optlokasitugas[$kary],$listreg[$_GET['region']])){
					$detailperkary[$kary]=$kary;
					$detperkary[$kary][$bibl]=$kehadiran[$kary][$bibl];
				}
			}else{	
				if(strlen($_GET['unit'])==4 and $_GET['subbagian']==''){
					if($jenis==$_GET['jenis'] and $opttipekary[$kary]==$_GET['tipekar'] and $optlokasitugas[$kary]==$_GET['unit'] and $optsubbagian[$kary]==$_GET['unit']){				
						$detailperkary[$kary]=$kary;
						$karyterdata[$kary]=$kary;
					}
				}elseif(strlen($_GET['unit'])==4 and $_GET['subbagian']=='%'){
					if($jenis==$_GET['jenis'] and $opttipekary[$kary]==$_GET['tipekar'] and $optlokasitugas[$kary]==$_GET['unit']){				
						$detailperkary[$kary]=$kary;
						$karyterdata[$kary]=$kary;
					}
				}elseif($_GET['unit']=='%'){
					if($jenis==$_GET['jenis'] and $opttipekary[$kary]==$_GET['tipekar']){				
						$detailperkary[$kary]=$kary;
						$karyterdata[$kary]=$kary;
					}
				}else{
					if($jenis==$_GET['jenis'] and $opttipekary[$kary]==$_GET['tipekar'] and $optsubbagian[$kary]==$_GET['unit']){				
						$detailperkary[$kary]=$kary;
						$karyterdata[$kary]=$kary;
					}
				}
			}
		}
	}

	foreach($listkaryaktif as $jenis => $val1){
		foreach($val1 as $bibl => $val2){
			foreach($val2 as $kary){
				if($optsubbagian[$kary]==''){
					$optsubbagian[$kary]=$optlokasitugas[$kary];
				}
				if($bibl=='bl'){
					if($jenis=='pnn'){
						$listbyjabt['59'][$kary]=$kary;
					}elseif($jenis=='rwt'){
						$listbyjabt['61'][$kary]=$kary;
					}else{
						$listbyjabt[$detailkary[$kary]['kodejabatan']][$kary]=$kary;
					}
				}
				
				if($jenis=='pnn' and $bibl=='bl'){
					$xxxx[$kary]=$kary;
				}
				
				#perunit
				$datadetail[$jenis][$opttipekary[$kary]][$optlokasitugas[$kary]][$bibl]['ttl']++;
				$listregion[$jenis][$opttipekary[$kary]][$optregional[$optlokasitugas[$kary]]]['x']['sedia']=$optregional[$optlokasitugas[$kary]];
				
				#perdivisi
				$datadetaildiv[$jenis][$opttipekary[$kary]][$optsubbagian[$kary]][$bibl]['ttl']++;
				
				if($_GET['region']!=''){
					if($jenis==$_GET['jenis'] and $opttipekary[$kary]==$_GET['tipekar'] and in_array($optlokasitugas[$kary],$listreg[$_GET['region']])){
						$detailperkary[$kary]=$kary;
						$detperkary[$kary][$bibl]=$kehadiran[$kary][$bibl];
					}
				}else{					
					if(strlen($_GET['unit'])==4 and $_GET['subbagian']==''){
						if($jenis==$_GET['jenis'] and $opttipekary[$kary]==$_GET['tipekar'] and $optlokasitugas[$kary]==$_GET['unit'] and $optsubbagian[$kary]==''){				
							$detailperkary[$kary]=$kary;
							$detperkary[$kary][$bibl]=$kehadiran[$kary][$bibl];
						}			
					}elseif(strlen($_GET['unit'])==4 and $_GET['subbagian']=='%'){
						if($jenis==$_GET['jenis'] and $opttipekary[$kary]==$_GET['tipekar'] and $optlokasitugas[$kary]==$_GET['unit']){				
							$detailperkary[$kary]=$kary;
							$detperkary[$kary][$bibl]=$kehadiran[$kary][$bibl];
						}
					}elseif($_GET['unit']=='%'){
						if($jenis==$_GET['jenis'] and $opttipekary[$kary]==$_GET['tipekar']){				
							$detailperkary[$kary]=$kary;
							$detperkary[$kary][$bibl]=$kehadiran[$kary][$bibl];
						}
					}else{
						if($jenis==$_GET['jenis'] and $opttipekary[$kary]==$_GET['tipekar'] and $optsubbagian[$kary]==$_GET['unit']){				
							$detailperkary[$kary]=$kary;
							$detperkary[$kary][$bibl]=$kehadiran[$kary][$bibl];
						}
					}
				}
				
				if($kehadiran[$kary][$bibl]>=21){
					$karyaktif[$jenis][$opttipekary[$kary]][$bibl]['ttl']++;
					$karyaktif['ksp'][$opttipekary[$kary]][$bibl]['ttl']++;
					
					$ttlkaryaktif[$jenis][$bibl]['ttl']++;
					$ttlkaryaktif['ksp'][$bibl]['ttl']++;
					
					
					$hadirplus[$jenis][$opttipekary[$kary]][$bibl]['30']++;
					$hadirplus['ksp'][$opttipekary[$kary]][$bibl]['30']++;
					
					$ttlhadirplus[$jenis][$bibl]['30']++;
					$ttlhadirplus['ksp'][$bibl]['30']++;
					
					#perunit
					$datadetail[$jenis][$opttipekary[$kary]][$optlokasitugas[$kary]][$bibl]['30']++;
					$listregion[$jenis][$opttipekary[$kary]][$optregional[$optlokasitugas[$kary]]]['x']['sedia']=$optregional[$optlokasitugas[$kary]];
					
					#perdivisi
					$datadetaildiv[$jenis][$opttipekary[$kary]][$optsubbagian[$kary]][$bibl]['30']++;
					
				}elseif($kehadiran[$kary][$bibl]>=16 and $kehadiran[$kary][$bibl]<=20){
					$karyaktif[$jenis][$opttipekary[$kary]][$bibl]['ttl']++;
					$karyaktif['ksp'][$opttipekary[$kary]][$bibl]['ttl']++;
					
					$ttlkaryaktif[$jenis][$bibl]['ttl']++;
					$ttlkaryaktif['ksp'][$bibl]['ttl']++;
					
					
					$hadirplus[$jenis][$opttipekary[$kary]][$bibl]['20']++;
					$hadirplus['ksp'][$opttipekary[$kary]][$bibl]['20']++;
					
					$ttlhadirplus[$jenis][$bibl]['20']++;
					$ttlhadirplus['ksp'][$bibl]['20']++;
					
					#perunit
					$datadetail[$jenis][$opttipekary[$kary]][$optlokasitugas[$kary]][$bibl]['20']++;
					$listregion[$jenis][$opttipekary[$kary]][$optregional[$optlokasitugas[$kary]]]['x']['sedia']=$optregional[$optlokasitugas[$kary]];
					
					#perdivisi
					$datadetaildiv[$jenis][$opttipekary[$kary]][$optsubbagian[$kary]][$bibl]['20']++;
					
				}elseif($kehadiran[$kary][$bibl]<=15){
					if($kehadiran[$kary][$bibl]>0){						
						$karyaktif[$jenis][$opttipekary[$kary]][$bibl]['ttl']++;
						$karyaktif['ksp'][$opttipekary[$kary]][$bibl]['ttl']++;
						
						$ttlkaryaktif[$jenis][$bibl]['ttl']++;
						$ttlkaryaktif['ksp'][$bibl]['ttl']++;
					}
					
					$hadirplus[$jenis][$opttipekary[$kary]][$bibl]['15']++;
					$hadirplus['ksp'][$opttipekary[$kary]][$bibl]['15']++;
					
					$ttlhadirplus[$jenis][$bibl]['15']++;
					$ttlhadirplus['ksp'][$bibl]['15']++;
					
					#perunit
					$datadetail[$jenis][$opttipekary[$kary]][$optlokasitugas[$kary]][$bibl]['15']++;
					$listregion[$jenis][$opttipekary[$kary]][$optregional[$optlokasitugas[$kary]]]['x']['sedia']=$optregional[$optlokasitugas[$kary]];
					
					#perdivisi
					$datadetaildiv[$jenis][$opttipekary[$kary]][$optsubbagian[$kary]][$bibl]['15']++;
				}
				// if($opttipekary[$kary]=='1'){					
					// $no++;
					// echo $no."|".$jenis."|".$bibl."|".$kary."|".$kehadiran[$kary][$bibl]."<br>";
				// }
			}
		}
	}

	// echo"<pre>";
	// // // echo count($daftarkary['rwt']['bl']);
	// print_r($listkaryaktif);
	// // // print_r($staffabsen);
	// echo"</pre>";

	// echo"<pre>";
// print_r($listregion);
// print_r($datadetail);
// echo"</pre>";

} #tutup if apa	

# TIPEKARYAWAN
$tipekaryawan = array();
$str="select id, tipe from ".$dbname.".sdm_5tipekaryawan";
$res=mysqli_query($conn,$str);
while($bar=mysqli_fetch_assoc($res)){
	$tipekaryawan[$bar['id']] = $bar['tipe'];
}
# JABATAN
$jabatan = array();
$str="select kodejabatan, namajabatan from ".$dbname.".sdm_5jabatan order by namajabatan";
$res=mysqli_query($conn,$str);
while($bar=mysqli_fetch_assoc($res)){
	$jabatan[$bar['kodejabatan']] = $bar['namajabatan'];
}

ksort($listtipekary);


$jeniskary=['pnn'=>'Pemanen','rwt'=>'Perawatan','oth'=>'Lain - Lain','ksp'=>'KSP Agro'];
switch($apa){
	case'modalkarydetail':
		
		$str = "select *, substr(tanggal,1,7) as periode, a.kodeorg as kodeorg from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where tanggal between '".$bulanlalu."-01' and '".$hariini."' and nik='".$_GET['karyawanid']."' and tipetransaksi='PNN' order by tanggal";
		$res = mysqli_query($conn,$str);
		while($bar=mysqli_fetch_assoc($res)){
			$bar['kodekegiatan']='611010202';
			if($bar['jurnal']==1){
				if($bar['upahpremi']+$bar['upahpremilebihbasis']+$bar['upahpremilebihbasis2']+$bar['premibasis']+$bar['premibasis2']+$bar['premibrondol']+$bar['upahkerja']>0){
					$kegiatan[$bar['tanggal']][]=array(
						'keg'=>$bar['kodekegiatan'],
						'blok'=>$bar['kodeorg'],
						'notr'=>$bar['notransaksi'],
						'post'=>$bar['jurnal'],
						'jenis'=>'BKM Panen',
						'upah'=>$bar['upahkerja']-$bar['upahpenalty'],
						'premi'=>$bar['upahpremi']+$bar['upahpremilebihbasis']+$bar['upahpremilebihbasis2']+$bar['premibasis']+$bar['premibasis2']+$bar['premibrondol'],
						'lembur'=>''
					);
				}	
			}else{	
				$kegiatan[$bar['tanggal']][]=array(
					'keg'=>$bar['kodekegiatan'],
					'blok'=>$bar['kodeorg'],
					'notr'=>$bar['notransaksi'],
					'post'=>$bar['jurnal'],
					'jenis'=>'BKM Panen',
					'upah'=>$bar['upahkerja']-$bar['upahpenalty'],
					'premi'=>$bar['upahpremi']+$bar['upahpremilebihbasis']+$bar['upahpremilebihbasis2']+$bar['premibasis']+$bar['premibasis2']+$bar['premibrondol'],
					'lembur'=>''
				);			
			}
		}
		
		$str = "select tanggal,kodekegiatan,a.kodeorg,a.notransaksi,jurnal,c.umr,c.insentif, substr(tanggal,1,7) as periode from ".$dbname.".kebun_prestasi a 
		left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
		left join ".$dbname.".kebun_kehadiran c on a.notransaksi=c.notransaksi and a.nikpemel=c.nik  and a.nourut=c.nourut 
		where tanggal between '".$bulanlalu."-01' and '".$hariini."' and a.nikpemel='".$_GET['karyawanid']."' and tipetransaksi!='PNN'  order by tanggal";
		$res = mysqli_query($conn,$str);
		while($bar=mysqli_fetch_assoc($res)){
			$kegiatan[$bar['tanggal']][]=array(
				'keg'=>$bar['kodekegiatan'],
				'blok'=>$bar['kodeorg'],
				'notr'=>$bar['notransaksi'],
				'post'=>$bar['jurnal'],
				'jenis'=>'BKM Perawatan',
				'upah'=>$bar['umr'],
				'premi'=>$bar['insentif'],
				'lembur'=>''
			);			
		}
		
		$str = "select *, substr(a.tanggal,1,7) as periode from ".$dbname.".vhc_runhk a 
		left join ".$dbname.".vhc_runht b on a.notransaksi=b.notransaksi 
		where a.tanggal between '".$bulanlalu."-01' and '".$hariini."' and a.idkaryawan='".$_GET['karyawanid']."' order by a.tanggal";
		$res = mysqli_query($conn,$str);
		while($bar=mysqli_fetch_assoc($res)){
			if($bar['upah']+$bar['premi']>0){				
				$kegiatan[$bar['tanggal']][]=array(
					'keg'   =>$bar['kodevhc'],
					'blok'  =>$bar['kodeorg'],
					'notr'  =>$bar['notransaksi'],
					'post'  =>$bar['posting'],
					'jenis' =>'Driver/Operator/Helper',
					'upah'  =>$bar['upah'],
					'premi' =>$bar['premi'],
					'lembur'=>''
				);			
			}
		}
		
		$str="select * from ".$dbname.".sdm_5absensi";
		$res=mysqli_query($conn,$str);
		while($bar=mysqli_fetch_assoc($res)){
			$namaabs[$bar['kodeabsen']] = $bar['keterangan'];
		}
		$str = "select *, substr(a.tanggal,1,7) as periode from ".$dbname.".sdm_absensidt_vw a
		where a.tanggal between '".$bulanlalu."-01' and '".$hariini."' and karyawanid='".$_GET['karyawanid']."' order by a.tanggal";
		$res = mysqli_query($conn,$str);
		while($bar=mysqli_fetch_assoc($res)){
			$kegiatan[$bar['tanggal']][]=array(
				'keg'   =>$bar['noakun'],
				'blok'  =>$bar['subbagian'],
				'notr'  =>$bar['penjelasan'],
				'post'  =>'1',
				'jenis' =>'SDM Absensi <br>('.$namaabs[$bar['absensi']].')',
				'upah'  =>$bar['umr'],
				'premi' =>$bar['premi']+$bar['insentif'],
				'lembur'=>''
			);			
		}
		
		$str = "select *, substr(a.tanggal,1,7) as periode from ".$dbname.".sdm_lemburdt a
		where a.tanggal between '".$bulanlalu."-01' and '".$hariini."' and karyawanid='".$_GET['karyawanid']."' order by a.tanggal";
		$res = mysqli_query($conn,$str);
		while($bar=mysqli_fetch_assoc($res)){
			$kegiatan[$bar['tanggal']][]=array(
				'keg'   =>$bar['noakun'],
				'blok'  =>getKary($bar['lokasitugas']),
				'notr'  =>$bar['ket'],
				'post'  =>'1',
				'jenis' =>'Lembur',
				'upah'  =>'',
				'premi' =>'',
				'lembur'=>$bar['uangkelebihanjam']
			);			
		}
		

		
		$str="select * from ".$dbname.".setup_kegiatan";
		$res=mysqli_query($conn,$str);
		while($bar=mysqli_fetch_assoc($res)){
			$namakeg[$bar['kodekegiatan']] = $bar['namakegiatan'];
		}
		
		$str="select * from ".$dbname.".vhc_5master";
		$res=mysqli_query($conn,$str);
		while($bar=mysqli_fetch_assoc($res)){
			$namakeg[$bar['kodevhc']] = $bar['detailvhc']." ".$bar['nopol'];
		}
		
		$str="select * from ".$dbname.".keu_5akun";
		$res=mysqli_query($conn,$str);
		while($bar=mysqli_fetch_assoc($res)){
			$namakeg[$bar['noakun']] = $bar['namaakun'];
		}
		
		$nama = "Nama : ".getKary($_GET['karyawanid']);
		$nama .= ", Jabatan : ".$jabatan[getKary($_GET['karyawanid'],'kodejabatan')];
		$nama .= ", (".$tipekaryawan[getKary($_GET['karyawanid'],'tipekaryawan')].")";
		?>
		<h5 class="font-extra-bold text-primary-2"><?php echo $nama ?></h5>				
		<div class="table-responsive">
			<table id="detkary" class="table table-striped table-bordered table-hover">
				<thead>
					<tr class="warning" title="click untuk mengurutkan">
						<th style='vertical-align:middle;'>No</th>
						<th style='vertical-align:middle;'>Periode</th>
						<th style='vertical-align:middle;'>Tanggal</th>
						<th style='vertical-align:middle;'>Hari</th>
						<th style='vertical-align:middle;'>Notransaksi</th>
						<th style='vertical-align:middle;'>Kegiatan</th>
						<th style='vertical-align:middle;'>Lokasi</th>
						<th style='vertical-align:middle;'>Sumber</th>
						<th style='vertical-align:middle;'>Posting</th>
						<th style='vertical-align:middle;'>Upah</th>
						<th style='vertical-align:middle;'>Premi</th>
						<th style='vertical-align:middle;'>Lembur</th>
						<th style='vertical-align:middle;'>Total</th>
					</tr>
				</thead>
				<tbody>
					<?php
						$arrpost=['1'=>'Posted','0'=>'Not Post'];
						$no=0;
						$range = rangeTanggal($bulanlalu."-01",date('Y-m-d'));
						$bulanr = month_inbetween($bulanlalu,date('Y-m'));
						
						foreach($range as $tanggal){
							$d=substr($tanggal,0,7);
							if($d!=$n){			
								$no=0;
								echo"<tr style=background-color:cyan;font-weight:bold;>";	
								echo"<td></td>";
								echo"<td></td>";
								echo"<td></td>";
								echo"<td></td>";
								echo"<td></td>";
								echo"<td align=center>".$d."</td>";
								echo"<td></td>";
								echo"<td></td>";
								echo"<td></td>";
								echo"<td></td>";
								echo"<td></td>";
								echo"<td></td>";
								echo"<td></td>";
								echo"</tr>";	
							}
							foreach($kegiatan[$tanggal] as $key => $val){								
								echo"<tr>";									
								$t=$tanggal;
								if($t!=$e){									
									$no++;
									echo"<td align=center>".$no."</td>";
									echo"<td align=center>".substr($tanggal,0,7)."</td>";
									echo"<td align=center nowrap>".$tanggal."</td>";
								}else{
									echo"<td align=center></td>";
									echo"<td align=center></td>";
									echo"<td align=center></td>";
								}
								
								$harilibur = getjenisharikerja(getKary($kary,'lokasitugas'),$tanggal);
								$hari = hari($tanggal);
								if($harilibur=='LIBUR'){
									echo"<td style=color:red;>".$hari."</td>";
								}else{
									echo"<td>".$hari."</td>";
								}
								
								$u=$val['notr'];
								if($u!=$i){
									echo"<td>".$val['notr']."</td>";
								}else{
									echo"<td align=center></td>";
								}
								if($namakeg[$val['keg']]!=''){									
									echo"<td>".$namakeg[$val['keg']]."</td>";
								}else{
									echo"<td>".$val['keg']."</td>";
								}
								echo"<td align=center>".$listkodeorg[$val['blok']]."</td>
								<td align=left>".$val['jenis']."</td>";
								if($val['post']=='0'){
									echo"<td align=center style=color:red;>".$arrpost[$val['post']]."</td>";
								}else{
									echo"<td align=center>".$arrpost[$val['post']]."</td>";
								}
								echo"<td align=right>".dznumber_format($val['upah'])."</td>
								<td align=right>".dznumber_format($val['premi'])."</td>
								<td align=right>".dznumber_format($val['lembur'])."</td>
								<td align=right>".dznumber_format($val['upah']+$val['premi']+$val['lembur'])."</td>
								</tr>";
								$e=$t;
								$i=$u;
								
								$subupah[$d]['u']+=$val['upah'];
								$subupah[$d]['p']+=$val['premi'];
								$subupah[$d]['l']+=$val['lembur'];
							}
							$n=$d;
						}
						
						echo"<tr style=background-color:#808080;font-weight:bold;color:white;>";
						echo"<td></td>";
						echo"<td></td>";
						echo"<td></td>";
						echo"<td></td>";
						echo"<td></td>";
						echo"<td align=center>REKAPITULASI</td>";
						echo"<td></td>";
						echo"<td></td>";
						echo"<td></td>";
						echo"<td></td>";
						echo"<td></td>";
						echo"<td></td>";
						echo"<td></td>";
						echo"</tr>";
						foreach($bulanr as $bulan){
							echo"<tr style=background-color:cyan;>";	
							echo"<td></td>";
							echo"<td></td>";
							echo"<td></td>";
							echo"<td></td>";
							echo"<td>PERIODE ".$bulan."</td>";
							echo"<td></td>";
							echo"<td></td>";
							echo"<td></td>";
							echo"<td></td>";
							echo"<td align=right>".dznumber_format($subupah[$bulan]['u'])."</td>";
							echo"<td align=right>".dznumber_format($subupah[$bulan]['p'])."</td>";
							echo"<td align=right>".dznumber_format($subupah[$bulan]['l'])."</td>";
							echo"<td align=right>".dznumber_format($subupah[$bulan]['u']+$subupah[$bulan]['p']+$subupah[$bulan]['l'])."</td>";
							echo"</tr>";
						}
						
					?>
				</tbody>
			</table>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" title="Print" onclick=exportTableToExcel('detkary')>Excel</button>
			<button type="button" class="btn btn-primary" onclick="window.close();">Close</button>
		</div>
		
		<?php	
	break;
	case'perkary':
		?>
		<div class="table-responsive" >
			<table id="daftartransaksi" class="table table-striped table-bordered table-hover">
				<thead>
				<tr class="warning" title="Click untuk mengurutkan">
					<th style='vertical-align:middle;' rowspan=2>No</th>
					<th style='vertical-align:middle;' rowspan=2>Nama Karyawan</th>
					<th style='vertical-align:middle;' rowspan=2>NIP</th>
					<th style='vertical-align:middle;' rowspan=2>Unit</th>
					<th style='vertical-align:middle;' rowspan=2>Divisi</th>
					<th style='vertical-align:middle;' rowspan=2>Tipe Karyawan</th>
					<th style='vertical-align:middle;' rowspan=2>Jabatan/Pekerjaan</th>
					<th style='vertical-align:middle;' rowspan=2>Jenis Kelamin</th>
					<th style='vertical-align:middle;' rowspan=2>Usia (Thn)</th>
					<th style='vertical-align:middle;' rowspan=2>Terdata</th>
					<th colspan=3>Bulan Lalu - <?php echo date('M',strtotime('-1 month')) ?><br><small>(Hadir)</small></th>
					<th colspan=3>Bulan Ini - <?php echo date('M') ?><br><small>(Hadir)</small></th>
					
				</tr>
				<tr class="warning">
					<th>&le; 15 Hari</th>
					<th>16 - 20 Hari</th>
					<th>&ge; 21 Hari</th>
					<th>&le; 15 Hari</th>
					<th>16 - 20 Hari</th>
					<th>&ge; 21 Hari</th>
				</tr>
			</thead>
			<tbody>
		
				<?php
					$no=0;
					foreach($detailperkary as $kary){
						$no+=1;
						$link = " class='show-modal' style='cursor:pointer' onclick=\"modalkarydetail('index_datakaryawanv2.html?apa=modalkarydetail&karyawanid=".$kary."')\"";	
					echo'<tr '.$link.'>
							<td align=center>'.$no.'</td>
							<td nowrap>'.$detailkary[$kary]['nama'].'</td>
							<td nowrap>'.$detailkary[$kary]['nik'].'</td>
							<td nowrap>'.$detailkary[$kary]['lokasitugas'].'</td>
							<td nowrap>'.$listkodeorg[$detailkary[$kary]['subbagian']].'</td>
							<td nowrap>'.$tipekaryawan[$detailkary[$kary]['tipekaryawan']].'</td>
							<td nowrap>'.$jabatan[$detailkary[$kary]['kodejabatan']].'</td>
							<td align=center>'.$detailkary[$kary]['jeniskelamin'].'</td>
							<td align=center>'.hitumur($detailkary[$kary]['tanggallahir']).'</td>';
							if($karyterdata[$kary]!=''){
								echo'<td align=center>&#x2713;</td>';
								$jumlah['x']['terdata']['count']+=1;
							}else{
								echo'<td align=center></td>';
							}
							if($detperkary[$kary]['bl']>=21){
								echo'<td align=center></td>';								
								echo'<td align=center></td>';								
								echo'<td align=center>'.$detperkary[$kary]['bl'].'</td>';								
								$jumlah['bl']['30']['count']+=1;
								$jumlah['bl']['30']['sum']+=$detperkary[$kary]['bl'];
							}elseif($detperkary[$kary]['bl']>=16 and $detperkary[$kary]['bl']<=20){
								echo'<td align=center></td>';								
								echo'<td align=center>'.$detperkary[$kary]['bl'].'</td>';								
								echo'<td align=center></td>';								
								$jumlah['bl']['20']['count']+=1;
								$jumlah['bl']['20']['sum']+=$detperkary[$kary]['bl'];
							}else{
								echo'<td align=center>'.$detperkary[$kary]['bl'].'</td>';	
								echo'<td align=center></td>';
								echo'<td align=center></td>';
								if($detperkary[$kary]['bl']>0){									
									$jumlah['bl']['15']['count']+=1;
								}
								$jumlah['bl']['15']['sum']+=$detperkary[$kary]['bl'];
							}
							if($detperkary[$kary]['bi']>=21){
								echo'<td align=center></td>';								
								echo'<td align=center></td>';								
								echo'<td align=center>'.$detperkary[$kary]['bi'].'</td>';	
								$jumlah['bi']['30']['count']+=1;
								$jumlah['bi']['30']['sum']+=$detperkary[$kary]['bi'];
							}elseif($detperkary[$kary]['bl']>=16 and $detperkary[$kary]['bl']<=20){	
								echo'<td align=center></td>';								
								echo'<td align=center>'.$detperkary[$kary]['bi'].'</td>';	
								echo'<td align=center></td>';								
								$jumlah['bi']['20']['count']+=1;
								$jumlah['bi']['20']['sum']+=$detperkary[$kary]['bi'];
							}else{
								echo'<td align=center>'.$detperkary[$kary]['bi'].'</td>';	
								echo'<td align=center></td>';
								echo'<td align=center></td>';
								if($detperkary[$kary]['bi']>0){	
									$jumlah['bi']['15']['count']+=1;
								}								
								$jumlah['bi']['15']['sum']+=$detperkary[$kary]['bi'];								
							}
						echo'</tr>';
					}
					?>
					</tbody>
					</tfoot>
					<?php
					echo'<tr style=background-color:cyan>
						<td align=center colspan=9>Jumlah (Count)</td>
						<td align=center>'.$jumlah['x']['terdata']['count'].'</td>
						<td align=center>'.$jumlah['bl']['15']['count'].'</td>
						<td align=center>'.$jumlah['bl']['20']['count'].'</td>
						<td align=center>'.$jumlah['bl']['30']['count'].'</td>
						<td align=center>'.$jumlah['bi']['15']['count'].'</td>
						<td align=center>'.$jumlah['bi']['20']['count'].'</td>
						<td align=center>'.$jumlah['bi']['30']['count'].'</td>
						';
					echo'</tr>';
					
				?>
				
				</tfoot>
			</table>
		</div> 
		<?php	
	break;
	case'perdiv':
		?>
		<div class="table-responsive">
			<table cellpadding="0" cellspacing="0" class="table table-bordered table-striped" data-print='true' id="perdiv">
				<thead>
					<tr>
						<th style='vertical-align:middle;' rowspan=4>Kode Organisasi</th>
						<th style='vertical-align:middle;' rowspan=4>Luas (Ha)</th>
						<th style='vertical-align:middle;' rowspan=4>Terdata<br>(TK)</th>
						<th style='vertical-align:middle;' colspan=16>Bekerja (TK)</th>
					</tr>
					<tr>
						<th colspan=8>Bulan Lalu - <?php echo date('M',strtotime('-1 month')) ?> (Hadir)</th>
						<th colspan=8>Bulan Ini - <?php echo date('M') ?> (Hadir)</th>
					</tr>
					<tr>
						<th colspan=2>&le; 15 Hari</th>
						<th colspan=2>16 - 20 Hari</th>
						<th colspan=2>&ge; 21 Hari</th>
						<th colspan=2>Sub Total</th>
						<th colspan=2>&le; 15 Hari</th>
						<th colspan=2>16 - 20 Hari</th>
						<th colspan=2>&ge; 21 Hari</th>
						<th colspan=2>Sub Total</th>
					</tr>
					<tr>
						<th>TK</th>
						<th>%</th>
						<th>TK</th>
						<th>%</th>
						<th>TK</th>
						<th>%</th>
						<th>TK</th>
						<th>%</th>
						<th>TK</th>
						<th>%</th>
						<th>TK</th>
						<th>%</th>
						<th>TK</th>
						<th>%</th>
						<th>TK</th>
						<th>%</th>
					</tr>
				</thead>
				<tbody>
				<?php 
					
					foreach($listdivisi as $kodeorg => $namaorg){
						if(!empty($datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['x']['sedia'])){
							$link = " class='show-modal' style='cursor:pointer' onclick=\"modalkary('index_datakaryawanv2.html?apa=perkary&jenis=".$_GET['jenis']."&tipekar=".$_GET['tipekar']."&unit=".$kodeorg."&subbagian=".(strlen($kodeorg)<=4?"":$kodeorg)."&region=')\"";

							if($_GET['jenis']=='pnn' and strlen($kodeorg)==6){
								$luasblok[$kodeorg]=$luas[$kodeorg]['tm'];
							}elseif(strlen($kodeorg)==6){
								$luasblok[$kodeorg]=$luas[$kodeorg]['tbm']+$luas[$kodeorg]['tb']+$luas[$kodeorg]['tm'];
							}
							
							echo'<tr><td>'.$kodeorg.' - '.$namaorg.'</td>';
							echo'<td '.$link.' align="right">'.dznumber_format(@$luasblok[$kodeorg],2).'</td>';
							echo'<td '.$link.' align="right">'.dznumber_format(@$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['x']['sedia']).'</td>';
							
							$persen['15']=$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['15']/$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['ttl']*100;
							$persen['20']=$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['20']/$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['ttl']*100;
							$persen['30']=$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['30']/$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['ttl']*100;
							$persen['sedia']=$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['ttl']/$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['x']['sedia']*100;
							
							echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['15']).'</td>';
							echo'<td '.$link.' title="Persen terhadap subtotal bulan lalu" align="right">'.dznumber_format(@$persen['15'],2).'</td>';
							echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['20']).'</td>';
							echo'<td '.$link.' title="Persen terhadap subtotal bulan lalu" align="right">'.dznumber_format(@$persen['20'],2).'</td>';
							echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['30']).'</td>';
							echo'<td '.$link.' title="Persen terhadap subtotal bulan lalu" align="right">'.dznumber_format(@$persen['30'],2).'</td>';
							echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['ttl']).'</td>';
							echo'<td '.$link.' title="Persen terhadap total TK terdata" align="right">'.dznumber_format(@$persen['sedia'],2).'</td>';
							
							$persen['15']=$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['15']/$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['ttl']*100;
							$persen['20']=$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['20']/$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['ttl']*100;
							$persen['30']=$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['30']/$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['ttl']*100;
							$persen['sedia']=$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['ttl']/$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['x']['sedia']*100;
							
							echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['15']).'</td>';
							echo'<td '.$link.' title="Persen terhadap subtotal bulan lalu" align="right">'.dznumber_format(@$persen['15'],2).'</td>';
							echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['20']).'</td>';
							echo'<td '.$link.' title="Persen terhadap subtotal bulan lalu" align="right">'.dznumber_format(@$persen['20'],2).'</td>';
							echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['30']).'</td>';
							echo'<td '.$link.' title="Persen terhadap subtotal bulan lalu" align="right">'.dznumber_format(@$persen['30'],2).'</td>';
							echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['ttl']).'</td>';
							echo'<td '.$link.' title="Persen terhadap total TK terdata" align="right">'.dznumber_format(@$persen['sedia'],2).'</td>';
							echo'</tr>';
							
							$total['x']['ha']+=$luasblok[$kodeorg];
							$total['x']['sedia']+=$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['x']['sedia'];
							$total['bl']['15']+=$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['15'];
							$total['bl']['20']+=$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['20'];
							$total['bl']['30']+=$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['30'];
							$total['bi']['15']+=$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['15'];
							$total['bi']['20']+=$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['20'];
							$total['bi']['30']+=$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['30'];
							$total['bi']['ttl']+=$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['ttl'];
							$total['bl']['ttl']+=$datadetaildiv[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['ttl'];
						}
					}
				?>
				</tbody>
				<tfoot>
					<tr>
						<td>Total</td>
						<?php
							$link = " class='show-modal' style='cursor:pointer' onclick=\"modalkary('index_datakaryawanv2.html?apa=perkary&jenis=".$_GET['jenis']."&tipekar=".$_GET['tipekar']."&unit=".substr($kodeorg,0,4)."&subbagian=%&region=')\"";
							
							$persen['15']=$total['bl']['15']/$total['bl']['ttl']*100;
							$persen['20']=$total['bl']['20']/$total['bl']['ttl']*100;
							$persen['30']=$total['bl']['30']/$total['bl']['ttl']*100;
							$persen['sedia']=$total['bl']['ttl']/$total['x']['sedia']*100;
							
									
							echo'<td '.$link.' align="right">'.dznumber_format(@$total['x']['ha'],2).'</td>';
							echo'<td '.$link.' align="right">'.dznumber_format(@$total['x']['sedia']).'</td>';
							echo'<td '.$link.' align="right">'.dznumber_format(@$total['bl']['15']).'</td>';
							echo'<td align="right">'.dznumber_format(@$persen['15'],2).'</td>';
							echo'<td '.$link.' align="right">'.dznumber_format(@$total['bl']['20']).'</td>';
							echo'<td align="right">'.dznumber_format(@$persen['20'],2).'</td>';
							echo'<td '.$link.' align="right">'.dznumber_format(@$total['bl']['30']).'</td>';
							echo'<td align="right">'.dznumber_format(@$persen['30'],2).'</td>';
							echo'<td '.$link.' align="right">'.dznumber_format(@$total['bl']['ttl']).'</td>';
							echo'<td align="right">'.dznumber_format(@$persen['sedia'],2).'</td>';
							
							$persen['15']=$total['bi']['15']/$total['bi']['ttl']*100;
							$persen['20']=$total['bi']['20']/$total['bi']['ttl']*100;
							$persen['30']=$total['bi']['30']/$total['bi']['ttl']*100;
							$persen['sedia']=$total['bi']['ttl']/$total['x']['sedia']*100;
							
							echo'<td '.$link.' align="right">'.dznumber_format(@$total['bi']['15']).'</td>';
							echo'<td align="right">'.dznumber_format(@$persen['15'],2).'</td>';
							echo'<td '.$link.' align="right">'.dznumber_format(@$total['bi']['20']).'</td>';
							echo'<td align="right">'.dznumber_format(@$persen['20'],2).'</td>';
							echo'<td '.$link.' align="right">'.dznumber_format(@$total['bi']['30']).'</td>';
							echo'<td align="right">'.dznumber_format(@$persen['30'],2).'</td>';
							echo'<td '.$link.' align="right">'.dznumber_format(@$total['bi']['ttl']).'</td>';
							echo'<td align="right">'.dznumber_format(@$persen['sedia'],2).'</td>';
						?>
					</tr>
				</tfoot>
			</table>
		</div>	 
	<?php
	break;
	case'perunit':
		?>
		<div class="table-responsive">
			<table cellpadding="0" cellspacing="0" class="table table-bordered" data-print='true' id='tableperunit'>
				<thead>
					<tr>
						<th style='vertical-align:middle;' rowspan=4>Kode Organisasi</th>
						<th style='vertical-align:middle;' rowspan=4>Luas (Ha)</th>
						<th style='vertical-align:middle;' rowspan=4>Terdata<br>(TK)</th>
						<th style='vertical-align:middle;' colspan=16>Bekerja (TK)</th>
					</tr>
					<tr>
						<th colspan=8>Bulan Lalu - <?php echo date('M',strtotime('-1 month')) ?> (Hadir)</th>
						<th colspan=8>Bulan Ini - <?php echo date('M') ?> (Hadir)</th>
					</tr>
					<tr>
						<th colspan=2>&le; 15 Hari</th>
						<th colspan=2>16 - 20 Hari</th>
						<th colspan=2>&ge; 21 Hari</th>
						<th colspan=2>Sub Total</th>
						<th colspan=2>&le; 15 Hari</th>
						<th colspan=2>16 - 20 Hari</th>
						<th colspan=2>&ge; 21 Hari</th>
						<th colspan=2>Sub Total</th>
					</tr>
					<tr>
						<th>TK</th>
						<th>%</th>
						<th>TK</th>
						<th>%</th>
						<th>TK</th>
						<th>%</th>
						<th>TK</th>
						<th>%</th>
						<th>TK</th>
						<th>%</th>
						<th>TK</th>
						<th>%</th>
						<th>TK</th>
						<th>%</th>
						<th>TK</th>
						<th>%</th>
					</tr>
				</thead>
				<tbody>
				<?php 
					foreach($listkodeorgbyreg as $regional => $val1){
						if($regional!=''){							
							//echo'<tr style=background-color:#A3E4D7><td>REGION '.$regional.'</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
							foreach($val1 as $kodeorg => $namaorg){							
								if($datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['x']['sedia']!=''){
									$link = " class='show-modal' style='cursor:pointer' onclick=\"modaldivisi('".$_GET['jenis']."','".$_GET['tipekar']."','".$kodeorg."','perdiv','".$tipekaryawan[$tipekary].", ".$namajenis."')\"";
									echo'<tr><td>'.$kodeorg.' - '.$namaorg.'</td>';	
									
									if($_GET['jenis']=='pnn'){
										$luasblok[$kodeorg]=$luas[$kodeorg]['tm'];
									}else{
										$luasblok[$kodeorg]=$luas[$kodeorg]['tbm']+$luas[$kodeorg]['tb']+$luas[$kodeorg]['tm'];
									}
									
									echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$luasblok[$kodeorg],2).'</td>';
									echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['x']['sedia']).'</td>';
									
									$persen['15']=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['15']/$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['ttl']*100;
									$persen['20']=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['20']/$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['ttl']*100;
									$persen['30']=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['30']/$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['ttl']*100;
									$persen['sedia']=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['ttl']/$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['x']['sedia']*100;
									
									echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['15']).'</td>';
									echo'<td '.$link.' title="Persen terhadap subtotal bulan lalu" align="right">'.dznumber_format(@$persen['15'],2).'</td>';
									echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['20']).'</td>';
									echo'<td '.$link.' title="Persen terhadap subtotal bulan lalu" align="right">'.dznumber_format(@$persen['20'],2).'</td>';
									echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['30']).'</td>';
									echo'<td '.$link.' title="Persen terhadap subtotal bulan lalu" align="right">'.dznumber_format(@$persen['30'],2).'</td>';
									echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['ttl']).'</td>';
									echo'<td '.$link.' title="Persen terhadap total TK terdata" align="right">'.dznumber_format(@$persen['sedia'],2).'</td>';
									
									$persen['15']=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['15']/$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['ttl']*100;
									$persen['20']=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['20']/$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['ttl']*100;
									$persen['30']=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['30']/$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['ttl']*100;
									$persen['sedia']=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['ttl']/$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['x']['sedia']*100;
									
									echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['15']).'</td>';
									echo'<td '.$link.' title="Persen terhadap subtotal bulan lalu" align="right">'.dznumber_format(@$persen['15'],2).'</td>';
									echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['20']).'</td>';
									echo'<td '.$link.' title="Persen terhadap subtotal bulan lalu" align="right">'.dznumber_format(@$persen['20'],2).'</td>';
									echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['30']).'</td>';
									echo'<td '.$link.' title="Persen terhadap subtotal bulan lalu" align="right">'.dznumber_format(@$persen['30'],2).'</td>';
									echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['ttl']).'</td>';
									echo'<td '.$link.' title="Persen terhadap subtotal bulan lalu" align="right">'.dznumber_format(@$persen['sedia'],2).'</td>';
									echo'</tr>';
									
									$total['x']['luas']+=$luasblok[$kodeorg];
									$total['x']['sedia']+=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['x']['sedia'];
									$total['bl']['15']+=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['15'];
									$total['bl']['20']+=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['20'];
									$total['bl']['30']+=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['30'];
									$total['bi']['15']+=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['15'];
									$total['bi']['20']+=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['20'];
									$total['bi']['30']+=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['30'];
									$total['bi']['ttl']+=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['ttl'];
									$total['bl']['ttl']+=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['ttl'];
									
									$streg[$regional]['x']['ha']+=$luasblok[$kodeorg];
									$streg[$regional]['x']['sedia']+=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['x']['sedia'];
									$streg[$regional]['bl']['15']+=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['15'];
									$streg[$regional]['bl']['20']+=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['20'];
									$streg[$regional]['bl']['30']+=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['30'];
									$streg[$regional]['bi']['15']+=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['15'];
									$streg[$regional]['bi']['20']+=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['20'];
									$streg[$regional]['bi']['30']+=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['30'];
									$streg[$regional]['bi']['ttl']+=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bi']['ttl'];
									$streg[$regional]['bl']['ttl']+=$datadetail[$_GET['jenis']][$_GET['tipekar']][$kodeorg]['bl']['ttl'];
									
								}
							}
							if($streg[$regional]['x']['sedia']>0){								
								$link = " class='show-modal' style='cursor:pointer;background-color:#D1F2EB;' onclick=\"modalkary('index_datakaryawanv2.html?apa=perkary&jenis=".$_GET['jenis']."&tipekar=".$_GET['tipekar']."&unit=%&subbagian=%&region=".$regional."')\"";
								echo'<tr '.$link.'>
									<td>Total REGION '.$regional.'</td>';
									echo'<td align="right">'.dznumber_format(@$streg[$regional]['x']['ha'],2).'</td>';
									echo'<td align="right">'.dznumber_format(@$streg[$regional]['x']['sedia']).'</td>';
									$persen['15']=$streg[$regional]['bl']['15']/$streg[$regional]['bl']['ttl']*100;
									$persen['20']=$streg[$regional]['bl']['20']/$streg[$regional]['bl']['ttl']*100;
									$persen['30']=$streg[$regional]['bl']['30']/$streg[$regional]['bl']['ttl']*100;
									$persen['sedia']=$streg[$regional]['bl']['ttl']/$streg[$regional]['x']['sedia']*100;
									
									echo'<td align="right">'.dznumber_format(@$streg[$regional]['bl']['15']).'</td>';
									echo'<td align="right">'.dznumber_format(@$persen['15'],2).'</td>';
									echo'<td align="right">'.dznumber_format(@$streg[$regional]['bl']['20']).'</td>';
									echo'<td align="right">'.dznumber_format(@$persen['20'],2).'</td>';
									echo'<td align="right">'.dznumber_format(@$streg[$regional]['bl']['30']).'</td>';
									echo'<td align="right">'.dznumber_format(@$persen['30'],2).'</td>';
									echo'<td align="right">'.dznumber_format(@$streg[$regional]['bl']['ttl']).'</td>';
									echo'<td align="right">'.dznumber_format(@$persen['sedia'],2).'</td>';

									$persen['15']=$streg[$regional]['bi']['15']/$streg[$regional]['bi']['ttl']*100;
									$persen['20']=$streg[$regional]['bi']['20']/$streg[$regional]['bi']['ttl']*100;
									$persen['30']=$streg[$regional]['bi']['30']/$streg[$regional]['bi']['ttl']*100;
									$persen['sedia']=$streg[$regional]['bi']['ttl']/$streg[$regional]['x']['sedia']*100;
									
									echo'<td align="right">'.dznumber_format(@$streg[$regional]['bi']['15']).'</td>';
									echo'<td align="right">'.dznumber_format(@$persen['15'],2).'</td>';
									echo'<td align="right">'.dznumber_format(@$streg[$regional]['bi']['20']).'</td>';
									echo'<td align="right">'.dznumber_format(@$persen['20'],2).'</td>';
									echo'<td align="right">'.dznumber_format(@$streg[$regional]['bi']['30']).'</td>';
									echo'<td align="right">'.dznumber_format(@$persen['30'],2).'</td>';
									echo'<td align="right">'.dznumber_format(@$streg[$regional]['bi']['ttl']).'</td>';
									echo'<td align="right">'.dznumber_format(@$persen['sedia'],2).'</td>';
								echo'</tr>';
							}
						}
					}
				?>
				</tbody>
				<tfoot>
					<tr  style="background-color:Gray;font-weight:bold;color:Navy">
						<td>Grand Total</td>
						<?php 
							$link = " class='show-modal' style='cursor:pointer' onclick=\"modalkary('index_datakaryawanv2.html?apa=perkary&jenis=".$_GET['jenis']."&tipekar=".$_GET['tipekar']."&unit=%&subbagian=%&region=')\"";
							
							echo'<td '.$link.' align="right">'.dznumber_format(@$total['x']['luas'],2).'</td>';
							echo'<td '.$link.' align="right">'.dznumber_format(@$total['x']['sedia']).'</td>';
							$persen['15']=$total['bl']['15']/$total['bl']['ttl']*100;
							$persen['20']=$total['bl']['20']/$total['bl']['ttl']*100;
							$persen['30']=$total['bl']['30']/$total['bl']['ttl']*100;
							$persen['sedia']=$total['bl']['ttl']/$total['x']['sedia']*100;
							
							echo'<td '.$link.' align="right">'.dznumber_format(@$total['bl']['15']).'</td>';
							echo'<td align="right">'.dznumber_format(@$persen['15'],2).'</td>';
							echo'<td '.$link.' align="right">'.dznumber_format(@$total['bl']['20']).'</td>';
							echo'<td align="right">'.dznumber_format(@$persen['20'],2).'</td>';
							echo'<td '.$link.' align="right">'.dznumber_format(@$total['bl']['30']).'</td>';
							echo'<td align="right">'.dznumber_format(@$persen['30'],2).'</td>';
							echo'<td '.$link.' align="right">'.dznumber_format(@$total['bl']['ttl']).'</td>';
							echo'<td align="right">'.dznumber_format(@$persen['sedia'],2).'</td>';
							
							$persen['15']=$total['bi']['15']/$total['bi']['ttl']*100;
							$persen['20']=$total['bi']['20']/$total['bi']['ttl']*100;
							$persen['30']=$total['bi']['30']/$total['bi']['ttl']*100;
							$persen['sedia']=$total['bi']['ttl']/$total['x']['sedia']*100;
							echo'<td '.$link.' align="right">'.dznumber_format(@$total['bi']['15']).'</td>';
							echo'<td align="right">'.dznumber_format(@$persen['15'],2).'</td>';
							echo'<td '.$link.' align="right">'.dznumber_format(@$total['bi']['20']).'</td>';
							echo'<td align="right">'.dznumber_format(@$persen['20'],2).'</td>';
							echo'<td '.$link.' align="right">'.dznumber_format(@$total['bi']['30']).'</td>';
							echo'<td align="right">'.dznumber_format(@$persen['30'],2).'</td>';
							echo'<td '.$link.' align="right">'.dznumber_format(@$total['bi']['ttl']).'</td>';
							echo'<td align="right">'.dznumber_format(@$persen['sedia'],2).'</td>';
						?>
					</tr>
				</tfoot>
			</table>
		</div>	 
	<?php
	break;
	
	default:
		if(!empty($listkarytersedia))foreach($jeniskary as $jenis => $namajenis){ 
			?>
			 <div class="col-lg-6">
                <div class="hpanel" id="bodydatakaryawan">
                    <div class="panel-heading">
                        <div class="panel-tools">
                            <a class="showhide"><i class="fa fa-expand"></i></a>
                        </div>
                        <h4 class="font-extra-bold text-primary-2"><?php echo $namajenis; ?></h4>
                        <span><small>Klik pada angka untuk detail</small></span>   
						<!--<button type="button" class="fa fa-print print" title="Print <?php echo $namajenis; ?>" appname="print" print-area="#bodydatakaryawan"> </button>
						<button type="button" class="fa fa-file-excel-o print" title="Excel <?= $namajenis ?>" appname="excel" print-area="#bodydatakaryawan"> </button>
						-->							
                    </div>
                    <div class="panel-body">
						<div class="table-responsive">
                            <table cellpadding="0" cellspacing="0" class="table table-bordered table-striped" data-print='true'>
                                <thead>
                                    <tr>
										<th style='vertical-align:middle;' rowspan=3>Tipe Kary</th>
										<th style='vertical-align:middle;' rowspan=3>Terdata<br>(TK)</th>
										<th style='vertical-align:middle;' colspan=8>Bekerja (TK)</th>
									</tr>
									<tr>
										<th colspan=4>Bulan Lalu - <?php echo date('M',strtotime('-1 month')) ?> (Hadir)</th>
										<th colspan=4>Bulan Ini - <?php echo date('M') ?> (Hadir)</th>
									</tr>
									<tr>
										<th>&le; 15 Hari</th>
										<th>16 - 20 Hari</th>
										<th>&ge; 21 Hari</th>
										<th>Sub Total</th>
										<th>&le; 15 Hari</th>
										<th>16 - 20 Hari</th>
										<th>&ge; 21 Hari</th>
										<th>Sub Total</th>
									</tr>
                                </thead>
                                <tbody>
								<?php 
									foreach($listtipekary as $tipekary){
										echo'<tr><td>'.$tipekaryawan[$tipekary].'</td>';	
										$link = "";
										$link = " class='show-modal' style='cursor:pointer' onclick=\"today_modal('".$jenis."','".$tipekary."','sedia','perunit','".$tipekaryawan[$tipekary].", ".$namajenis."')\"";
										echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$listkarytersedia[$jenis][$tipekary]).'</td>';
										
										$link = " class='col-2' style='cursor:pointer' onclick=\"today_modal('".$jenis."','".$tipekary."','bl','perunit','".$tipekaryawan[$tipekary].", ".$namajenis."')\"";
										echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$hadirplus[$jenis][$tipekary]['bl']['15']).'</td>';
										echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$hadirplus[$jenis][$tipekary]['bl']['20']).'</td>';
										echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$hadirplus[$jenis][$tipekary]['bl']['30']).'</td>';
										echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$karyaktif[$jenis][$tipekary]['bl']['ttl']).'</td>';
										
										$link = " class='show-modal' style='cursor:pointer' onclick=\"today_modal('".$jenis."','".$tipekary."','bi','perunit','".$tipekaryawan[$tipekary].", ".$namajenis."')\"";
										echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$hadirplus[$jenis][$tipekary]['bi']['15']).'</td>';
										echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$hadirplus[$jenis][$tipekary]['bi']['20']).'</td>';
										echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$hadirplus[$jenis][$tipekary]['bi']['30']).'</td>';
										echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$karyaktif[$jenis][$tipekary]['bi']['ttl']).'</td>';
										echo'</tr>';
									} 
								?>
                                </tbody>
                                <tfoot>
									<tr>
										<td>Total</td>
										<?php 
											echo'<td align="right">'.dznumber_format(@$totaltersedia[$jenis]).'</td>';
											echo'<td align="right">'.dznumber_format(@$ttlhadirplus[$jenis]['bl']['15']).'</td>';
											echo'<td align="right">'.dznumber_format(@$ttlhadirplus[$jenis]['bl']['20']).'</td>';
											echo'<td align="right">'.dznumber_format(@$ttlhadirplus[$jenis]['bl']['30']).'</td>';
											echo'<td align="right">'.dznumber_format(@$ttlkaryaktif[$jenis]['bl']['ttl']).'</td>';
											echo'<td align="right">'.dznumber_format(@$ttlhadirplus[$jenis]['bi']['15']).'</td>';
											echo'<td align="right">'.dznumber_format(@$ttlhadirplus[$jenis]['bi']['20']).'</td>';
											echo'<td align="right">'.dznumber_format(@$ttlhadirplus[$jenis]['bi']['30']).'</td>';
											echo'<td align="right">'.dznumber_format(@$ttlkaryaktif[$jenis]['bi']['ttl']).'</td>';
										?>
									</tr>
								</tfoot>
                            </table>
                        </div>
					</div>
				</div>
			</div>
		<?php }
		
		#per jabatan
		?>
		<!--
		 <div class="col-lg-6">
			<div class="hpanel" id="perjabatan">
				<div class="panel-heading">
					<div class="panel-tools">
						<a class="showhide"><i class="fa fa-expand"></i></a>
					</div>
					<h4 class="font-extra-bold text-primary-2">Rekap Jabatan Kary BHL</h4>
					<span><small>Klik pada angka untuk detail</small></span>
				</div>
				<div class="panel-body">
					<div class="table-responsive">
						<table cellpadding="0" cellspacing="0" class="table table-bordered table-striped" data-print='true'>
							<thead>
								<tr>	
									<th style='vertical-align:middle;' rowspan=2>Jabatan</th>
									<th colspan=<?= (count($listtipekary)+1) ?>>Terdata</th>
									<th colspan=<?= (count($listtipekary)+1) ?>>Bekerja</th>
								</tr>
								<tr>
									<?php
									foreach($listtipekary as $tipekary){	 
										echo'<th style=vertical-align:middle;>'.$tipekaryawan[$tipekary].'</th>';	
									}
									echo'<th style=vertical-align:middle;>TOTAL</th>';
									foreach($listtipekary as $tipekary){	 
										echo'<th style=vertical-align:middle;>'.$tipekaryawan[$tipekary].'</th>';	
									}
									echo'<th style=vertical-align:middle;>TOTAL</th>';									
									?>
								</tr>
							</thead>
							<tbody>
							<?php 
								
								foreach($tersediabyjab as $kodejab => $val1){
									foreach($val1 as $tipekary => $val2){
										foreach($val2 as $karyid){
											$sediabyjab[$kodejab][$tipekary]++;
											
											if(!empty($kehadiran[$karyid]['bi'])){
												$hadirbyjab[$kodejab][$tipekary]++;
											}
										}
									}
								}
								
								foreach($jabatan as $kodejab => $namajab){
									if(!empty($listjabatan[$kodejab])){
										echo'<tr><td>'.$namajab.'</td>';
										foreach($listtipekary as $tipekary){	 
											echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$sediabyjab[$kodejab][$tipekary]).'</td>';
										}
										foreach($listtipekary as $tipekary){	 
											echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$hadirbyjab[$kodejab][$tipekary]).'</td>';
										}
										
									}
								}
								// foreach($listtipekary as $tipekary){
									// echo'<tr><td>'.$tipekaryawan[$tipekary].'</td>';	
									// $link = "";
									// $link = " class='show-modal' style='cursor:pointer' onclick=\"today_modal('".$jenis."','".$tipekary."','sedia','perunit','".$tipekaryawan[$tipekary].", ".$namajenis."')\"";
									// echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$listkarytersedia[$jenis][$tipekary]).'</td>';
									
									// $link = " class='col-2' style='cursor:pointer' onclick=\"today_modal('".$jenis."','".$tipekary."','bl','perunit','".$tipekaryawan[$tipekary].", ".$namajenis."')\"";
									// echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$hadirplus[$jenis][$tipekary]['bl']['20']).'</td>';
									// echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$hadirplus[$jenis][$tipekary]['bl']['30']).'</td>';
									// echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$karyaktif[$jenis][$tipekary]['bl']['ttl']).'</td>';
									
									// $link = " class='show-modal' style='cursor:pointer' onclick=\"today_modal('".$jenis."','".$tipekary."','bi','perunit','".$tipekaryawan[$tipekary].", ".$namajenis."')\"";
									// echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$hadirplus[$jenis][$tipekary]['bi']['15']).'</td>';
									// echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$hadirplus[$jenis][$tipekary]['bi']['20']).'</td>';
									// echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$hadirplus[$jenis][$tipekary]['bi']['30']).'</td>';
									// echo'<td '.$link.' title="Details" align="right">'.dznumber_format(@$karyaktif[$jenis][$tipekary]['bi']['ttl']).'</td>';
									// echo'</tr>';
								// } 
							?>
							</tbody>
							<tfoot>
								<tr>
									<td>Total</td>
									<?php 
										// echo'<td align="right">'.dznumber_format(@$totaltersedia[$jenis]).'</td>';
										// echo'<td align="right">'.dznumber_format(@$ttlhadirplus[$jenis]['bl']['15']).'</td>';
										// echo'<td align="right">'.dznumber_format(@$ttlhadirplus[$jenis]['bl']['20']).'</td>';
										// echo'<td align="right">'.dznumber_format(@$ttlhadirplus[$jenis]['bl']['30']).'</td>';
										// echo'<td align="right">'.dznumber_format(@$ttlkaryaktif[$jenis]['bl']['ttl']).'</td>';
										// echo'<td align="right">'.dznumber_format(@$ttlhadirplus[$jenis]['bi']['15']).'</td>';
										// echo'<td align="right">'.dznumber_format(@$ttlhadirplus[$jenis]['bi']['20']).'</td>';
										// echo'<td align="right">'.dznumber_format(@$ttlhadirplus[$jenis]['bi']['30']).'</td>';
										// echo'<td align="right">'.dznumber_format(@$ttlkaryaktif[$jenis]['bi']['ttl']).'</td>';
									?>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
			</div>
		</div>-->
	<?php 	
	break;
}
  
function month_inbetween($month1, $month2){

    if($month1>$month2) {
        exit("Error:Month 1 > Month 2");
    }

	$day = 60*60*24;
	$date1=$month1.'-01';
	$date2=$month2.'-01';
    $date1 = strtotime($date1);
    $date2 = strtotime($date2);

    $days_diff = round(($date2 - $date1)/$day); // Unix time difference devided by 1 day to get total days in between

    $dates_array = array();
    $dates_array[substr(date('Y-m-d',$date1),0,7)] = substr(date('Y-m-d',$date1),0,7);

    for($x = 1; $x < $days_diff; $x++){
        $dates_array[substr(date('Y-m-d',($date1+($day*$x))),0,7)] = substr(date('Y-m-d',($date1+($day*$x))),0,7);
    }

    $dates_array[substr(date('Y-m-d',$date2),0,7)] = substr(date('Y-m-d',$date2),0,7);
    if($date1==$date2){
        $dates_array = array();
        $dates_array[substr(date('Y-m-d',$date1),0,7)] = substr(date('Y-m-d',$date1),0,7);        
    }
    return $dates_array;
	
}  

function hitumur($tanggal,$ext=""){
	$birthDate = $tanggal; // 2021-01-31
	$birthDate = explode("-", $birthDate);
	$age = (date("md", date("U", mktime(0, 0, 0, $birthDate[1], $birthDate[2], $birthDate[0]))) > date("md")
	? ((date("Y") - $birthDate[0]) - 1)
	: (date("Y") - $birthDate[0]));
	if($age>120)$age='';
	$res=$extRes = "";
	if($ext != ""){
		$extRes = " ".$ext;  
	}
	if($age != ""){
		$res = $age.$extRes;
	}
	return $res;
}

function rangeTanggal($date1, $date2){

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

function tglakhir($tgl){
	#menbuat tanggal terakhir di periode parameter kiriman
	#$tgl format : 2015-12-25;
	$tglakhir = date('Y-m-t', strtotime($tgl));
	return $tglakhir;
}

function getjenisharikerja($kodeorg,$tanggal){
	global $dbname;
	global $conn;
	
	$day = date('D', strtotime($tanggal));
	$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal='".$tanggal."' and (kebun='GLOBAL' or kebun='".$kodeorg."')";
	$res=mysqli_query($conn,$strorg);
	while($roworg=mysqli_fetch_assoc($res)){
		if(@$roworg['keterangan']=='libur'){
			$jenispremi='LIBUR';
		} else if (($day=='Sun' and @$roworg['keterangan']=='') or @$roworg['keterangan']=='libur'){
			$jenispremi='LIBUR';
		} else if($day=='Fri'){
			$jenispremi='JUMAT';
		} else if(@$roworg['keterangan']=='masuk'){
			$jenispremi='KERJA';
		}else{
			$jenispremi='KERJA';
		}
	}
	return $jenispremi;
}	
function getKary($karyawanid,$kolom='namakaryawan'){
	global $dbname;
    global $conn;
    
	$hasil='';
    $str="select ".$kolom." from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
	$res=mysqli_query($conn,$str);
	while($bar=mysqli_fetch_assoc($res)){		
		$hasil=$bar[$kolom];
	}
	
	return $hasil;    
}

function hari($tgl,$lang='ID')//$tgl==2009-04-13
{
//return name of days in Indonesia	
	$bln=substr($tgl,5,2);
	$thn=substr($tgl,0,4);
	$tgl=substr($tgl,8,2);
	$ha=date("w", mktime(0, 0, 0, $bln,$tgl,$thn));
	$x=array ("Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu");
	$y=array ("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
	if($lang=='ID')
	   return($x[$ha]);
	else
	   return($y[$ha]);   
}

?>