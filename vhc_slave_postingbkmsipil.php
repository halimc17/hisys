<?php
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$notransaksi    = checkPostGet('notransaksi','');


try {
    $owlPDO->beginTransaction();

    ## Ambil Data yang penting
    $str="select * from ".$dbname.".vhc_spl_aktifitas where notransaksi = '".$notransaksi."'";
    $res = fetchdata($str);

    $unit = $res[0]['kodeorg'];
    $prd = substr($res[0]['tanggal'],0,7);
    $tgl2 = $res[0]['tanggal'];
    
    #========================= Validasi Data ===========================
    #1. Cek Prd Akuntansi
    $str="select * from ".$dbname.".setup_periodeakuntansi where periode = '".$prd."' and kodeorg='".$unit."' and tutupbuku='1'";
    $res = fetchData($str);
    if(count($res)>0){
        throw new PDOException("Periode Akuntansi Sudah di Tutup.");
    }

    #2. Cek periode vs sesion prd
    if($_SESSION['org']['period']['tahun']."-".$_SESSION['org']['period']['bulan']>$prd){
        throw new PDOException("Periode diluar periode aktif !\nPeriode Aktif => ".$_SESSION['org']['period']['tahun']."-".$_SESSION['org']['period']['bulan']."\nPeriode Transaksi => ".$prd."");
    }

    #3. Cek Prd Gaji
    $str="select * from ".$dbname.".sdm_5periodegaji where periode = '".$prd."' and kodeorg='".$unit."'  and sudahproses='1'";
    $res = fetchData($str);
    if(count($res)>0){
        throw new PDOException("Periode Gaji sudah di Tutup.");
    }

    #5. Cek apakah sudah pernah di posting sebelumnnya
    $str="select * from ".$dbname.".keu_jurnalht where	noreferensi='".$notransaksi."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $row=$res->rowCount();
    if($row>0){
        throw new PDOException("Transaksi sudah pernah di posting sebelumnnya.");
    }

    #========================= End Validasi Data ===========================

    # Cek Datakaryawan History
    $jumlahkaryhist=0;
    $str = "select count(karyawanid) as jlh from ".$dbname.".datakaryawan_hist where 5=5 and version_type='B' and lokasitugas='".$unit."' and periodegaji='".$prd."' "; 
    $res = fetchdata($str);
    $jumlahkaryhist=$res[0]['jlh'];
    
    if($jumlahkaryhist > 0) {
        $whhist = "and b.version_type='B' and b.periodegaji='".$prd."' ";
		$whhistb = "and version_type='B' and periodegaji='".$prd."' ";
        $tabel = 'datakaryawan_hist';
    }else{
		$tabel = 'datakaryawan';
    	$whhistb = '';
	}

	// exit('warning:'.$tabel);
	
	#========================= delete jurnal ===============================
	$str="delete from ".$dbname.".keu_jurnalht where noreferensi='".$notransaksi."'";
	$owlPDO->exec($str);
	

	#=========================== Nomor Jurnal ==============================
	#SIPL1 untuk UPAH , SIPL2 untuk Premi
	$tglEntry = date('Ymd');

	$kodeJurnal = 'SIPL1';
	$queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet',"kodeaplikasi='SIPL' and jurnalid='".$kodeJurnal."'");
	$resParam = fetchData($queryParam);

	$queryParam2 = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet',"kodeaplikasi='SIPL' and jurnalid='SIPL2'");
	$resParam2 = fetchData($queryParam2);

	#======================== End Nomor Jurnal =============================


	#======================== Mulai Nomor Jurnal =============================
    # Get Journal Counter
    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodeorg='".getindukPT($unit)."' and kodekelompok='".$kodeJurnal."' and kodeunit='".$unit."' and periode='".$prd."'");
    $tmpKonter = fetchData($queryJ);
    $konter = addZero($tmpKonter[0]['nokounter']+1,3);

    # Validasi jika nomor konter sudah 1000
    if($konter>999){
        throw new PDOException("Nomor transaksi Jurnal sudah melebihi batas, silahkan reset nomor melalui menu Keuangan - Setup - Kelompok Jurnal, Kode Org = ".getindukPT($unit).", Kode Kelompok = PNN01");
    }

    # Transform No Jurnal dari No Transaksi
    $tmpNoJurnal = explode('/',$notransaksi);
    $tmpKodeOrg = $unit;
    $nojurnal = $tmpNoJurnal[0]."/".$tmpKodeOrg."/".$kodeJurnal."/".$konter;

	#======================== End Nomor Jurnal =============================
	

	
	$listDataBlokBesar= array();
	$listDataBlokKecil= array();

	$totalHK = array(); ## HK
	$totalPR = array(); ## PREMI
	$totalUP = array();	## UPAH
	$totalHJ = array();	## HASIL KERJA

	$rupiahHT=0;


	## CEK SPL PRESTASI
	$str="select a.*, b.namakaryawan from ".$dbname.".vhc_spl_kehadiran_vw a left join ".$tabel." b on a.nik=b.karyawanid where a.notransaksi ='".$notransaksi."' ".$whhist." order by b.namakaryawan asc";
	// exit('warning:'.$str);
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		## BLOK BESAR
		$listDataBlokBesar[$bar['notransaksi']][$bar['kodeorg']][$bar['tanggal']][$bar['nik']][$bar['kodekegiatan']][$bar['alokasi']] = $bar['alokasi'];
		$noBKM[$bar['notransaksi']][$bar['kodeorg']][$bar['tanggal']][$bar['nik']][$bar['kodekegiatan']][$bar['alokasi']] = $bar['nobkm'];

		$totalHK[$bar['notransaksi']][$bar['kodeorg']][$bar['tanggal']][$bar['nik']][$bar['kodekegiatan']][$bar['alokasi']] += $bar['jhk'];
		$totalPR[$bar['notransaksi']][$bar['kodeorg']][$bar['tanggal']][$bar['nik']][$bar['kodekegiatan']][$bar['alokasi']] += $bar['premi'];
		$totalUP[$bar['notransaksi']][$bar['kodeorg']][$bar['tanggal']][$bar['nik']][$bar['kodekegiatan']][$bar['alokasi']] += $bar['umr'];
		$totalHJ[$bar['notransaksi']][$bar['kodeorg']][$bar['tanggal']][$bar['nik']][$bar['kodekegiatan']][$bar['alokasi']]+= $bar['hasilkerja'];


		$rupiahHT += $totalUP[$bar['notransaksi']][$bar['kodeorg']][$bar['tanggal']][$bar['nik']][$bar['kodekegiatan']][$bar['alokasi']] + $totalPR[$bar['notransaksi']][$bar['kodeorg']][$bar['tanggal']][$bar['nik']][$bar['kodekegiatan']][$bar['alokasi']];
	}
	
	$dbaseblok='setup_blok';
	$whereblok='';
	
	$strbloktahunan = "select * from setup_blok_tahunan where tahun = '".$prd."' and kodeorg like '%".$unit."%' ";
	$resbloktahunan = fetchdata($strbloktahunan);
	if(count($resbloktahunan) > 0) {	
		$dbaseblok='setup_blok_tahunan';
		$whereblok=" and b.tahun = '".$tahunbulan."'";
	}


	$luasBlokKecil  = array();
	$luasBlokBesar  = array();

	$str="select b.kodeorg,b.indukblok,b.luasareaproduktif,a.nik,a.kodekegiatan,a.alokasi from ".$dbname.".vhc_spl_kehadiran_vw a left join ".$dbaseblok." b on a.alokasi = b.indukblok where a.notransaksi ='".$notransaksi."' ".$whereblok." order by b.indukblok,b.tahuntanam asc";
	$res = fetchData($str);
	foreach ($res as $bar) {

		if($bar['indukblok'] == ''){
			$bar['indukblok'] = $bar['alokasi'];
		}

		$listDataBlokKecil[$bar['nik']][$bar['kodekegiatan']][$bar['indukblok']][$bar['kodeorg']] = $bar['kodeorg'];
		$luasBlokBesar[$bar['nik']][$bar['kodekegiatan']][$bar['indukblok']] += round($bar['luasareaproduktif'],2);
		$luasBlokKecil[$bar['nik']][$bar['kodekegiatan']][$bar['indukblok']][$bar['kodeorg']] += round($bar['luasareaproduktif'],2);
	}


	## Menghitung Persentase
	$persentaseBlok = array(); 
	foreach($listDataBlokKecil as $karidXX => $arr1){
		foreach($arr1 as $kegiatanXX => $arr2){
			foreach($arr2 as $blokbesarXX => $arr3){
				foreach($arr3 as $blokkecilXX => $val){
					if(($luasBlokKecil[$karidXX][$kegiatanXX][$blokbesarXX][$blokkecilXX] == '' || $luasBlokKecil[$karidXX][$kegiatanXX][$blokbesarXX][$blokkecilXX] == 0) and ($luasBlokBesar[$karidXX][$kegiatanXX][$blokbesarXX] == '' || $luasBlokBesar[$karidXX][$kegiatanXX][$blokbesarXX] == 0)){
						$persentaseBlok[$karidXX][$kegiatanXX][$blokkecilXX] = 1;				
					}else{
						$persentaseBlok[$karidXX][$kegiatanXX][$blokkecilXX] = fixnan($luasBlokKecil[$karidXX][$kegiatanXX][$blokbesarXX][$blokkecilXX] / $luasBlokBesar[$karidXX][$kegiatanXX][$blokbesarXX]);				
					}
				}
			}
		}
	}

	$listData = array();
	$datajurnal1=array();

	$hkPRO = array(); ## HK PROPORSI
	$prPRO = array(); ## PREMI PROPORSI
	$upPRO = array(); ## UPAH PROPORSI
	$hjPRO = array(); ## HASIL KERJA PROPORSI

	$datadetail1 = '';
	$nox =0;

	$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
	
	foreach($listDataBlokBesar as $notrans =>$arr1){
		foreach($arr1 as $kodeorgx =>$arr2){
			foreach($arr2 as $tanggalx =>$arr3){
				foreach($arr3 as $karidx =>$arr4){
					foreach($arr4 as $kegiatanx =>$arr5){
						foreach($arr5 as $blokbesarx =>$valx){

							foreach($listDataBlokKecil[$karidx][$kegiatanx][$blokbesarx] as $blokkecilx => $value){
								$nox ++;

								$listData[$notrans][$kodeorgx][$tanggalx][$karidx][$kegiatanx][$blokbesarx][$blokkecilx] = $blokkecilx;

								$hkPRO[$notrans][$kodeorgx][$tanggalx][$karidx][$kegiatanx][$blokbesarx][$blokkecilx] =  round($totalHK[$notrans][$kodeorgx][$tanggalx][$karidx][$kegiatanx][$blokbesarx]* $persentaseBlok[$karidx][$kegiatanx][$blokkecilx],2);

								$prPRO[$notrans][$kodeorgx][$tanggalx][$karidx][$kegiatanx][$blokbesarx][$blokkecilx] =  round($totalPR[$notrans][$kodeorgx][$tanggalx][$karidx][$kegiatanx][$blokbesarx]* $persentaseBlok[$karidx][$kegiatanx][$blokkecilx],2);

								$upPRO[$notrans][$kodeorgx][$tanggalx][$karidx][$kegiatanx][$blokbesarx][$blokkecilx] =  round($totalUP[$notrans][$kodeorgx][$tanggalx][$karidx][$kegiatanx][$blokbesarx]* $persentaseBlok[$karidx][$kegiatanx][$blokkecilx],2);

								$hjPRO[$notrans][$kodeorgx][$tanggalx][$karidx][$kegiatanx][$blokbesarx][$blokkecilx] =  round($totalHJ[$notrans][$kodeorgx][$tanggalx][$karidx][$kegiatanx][$blokbesarx]* $persentaseBlok[$karidx][$kegiatanx][$blokkecilx],2);


								/* ================================================ INSERT VHC_SPL_KEHADIRAN_DETAIL ================================================ */
								// (`notransaksi`,`nobkm`,`nourut`,`indukblok`,`alokasi`,`nik`,`kodekegiatan`,`total_hasilkerja`,`total_hk`,`total_upah`,`total_premi`,`rupiahpenalty`,`kodesegment`)

								if($datadetail1 != ''){
									$datadetail1.=",";
								}

									$datadetail1 .= "(
										'".$notrans."',
										'".$noBKM[$notrans][$kodeorgx][$tanggalx][$karidx][$kegiatanx][$blokbesarx]."',
										'".$nox."',
										'".$blokbesarx."',
										'".$blokkecilx."',
										'".$karidx."',
										'".$kegiatanx."',
										'".$hjPRO[$notrans][$kodeorgx][$tanggalx][$karidx][$kegiatanx][$blokbesarx][$blokkecilx]."',
										'".$hkPRO[$notrans][$kodeorgx][$tanggalx][$karidx][$kegiatanx][$blokbesarx][$blokkecilx]."',
										'".$upPRO[$notrans][$kodeorgx][$tanggalx][$karidx][$kegiatanx][$blokbesarx][$blokkecilx]."',
										'".$prPRO[$notrans][$kodeorgx][$tanggalx][$karidx][$kegiatanx][$blokbesarx][$blokkecilx]."',
										'".$defSegment."'
									)";

								/* ================================================ END INSERT VHC_SPL_KEHADIRAN_DETAIL ================================================ */

								$datajurnal1[$nojurnal][$tanggalx][$karidx][$kegiatanx][$blokbesarx][$blokkecilx]['upah']+=$upPRO[$notrans][$kodeorgx][$tanggalx][$karidx][$kegiatanx][$blokbesarx][$blokkecilx];
								$datajurnal1[$nojurnal][$tanggalx][$karidx][$kegiatanx][$blokbesarx][$blokkecilx]['premi']+=$prPRO[$notrans][$kodeorgx][$tanggalx][$karidx][$kegiatanx][$blokbesarx][$blokkecilx];

							} 
						}
					}
				}
			}
		}
	}

	$indukunit=makeOption($dbname,'organisasi','kodeorganisasi,induk',"tipe='KEBUN'");
	$noxjur=array();
	$JurnalM=array();

	## HT NYA SIPIL 
	$str="insert into ".$dbname.".keu_jurnalht
	(`nojurnal`,`kodejurnal`,`tanggal`,`tanggalentry`,`posting`,`totaldebet`,`totalkredit`,`amountkoreksi`,
	`noreferensi`,`autojurnal`,`matauang`,`kurs`,`revisi`)values
	('".$nojurnal."','".$kodeJurnal."','".$tgl2."','".$tglEntry."','0','".$rupiahHT."','".$rupiahHT."','0','".$notransaksi."','1','IDR','1','0')";
	$owlPDO->exec($str);

	$no=0;
	$totalxxx=0;
	foreach ($datajurnal1 as $nojurnalx => $key1) {
		foreach ($key1 as $tanggalx => $key2) {
			foreach ($key2 as $karyd => $key3) {
				foreach ($key3 as $kegiatanx => $key4) {
					foreach ($key4 as $blokbesar => $key5) {
						foreach ($key5 as $blokkecil => $val) {

							$kodeorg = $unit;
											
							$nmkary=makeOption($dbname,$tabel,'karyawanid,namakaryawan',"karyawanid='".$karyd."'");
							$lokasitugasx=makeOption($dbname,$tabel,'karyawanid,lokasitugas',"karyawanid='".$karyd."' ".$whhistb."");
							$statusblok=makeOption($dbname,'setup_blok','indukblok,statusblok',"indukblok='".$blokbesar."'");

							## Ketika selain dia di blok
							if($statusblok[$blokbesar] == ''){
								$kodeAsset = $blokbesar;
							}else{
								$kodeAsset = "";
							}

							$akundebet= substr($kegiatanx,0,7);
							$akundebet2=substr($kegiatanx,0,7);


							# insert debet => keu_jurnaldt
							## Upah
							if($val['upah']>0){
								$no+=1;
								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnalx."','".$tanggalx."','".$no."','".$akundebet."',
									'Upah Kegiatan Sipil : ".$nmkary[$karyd]."','".($val['upah'])."','IDR','1','".$kodeorg."',
									'".$kegiatanx."','".$kodeAsset."','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','".$defSegment."')";
								$owlPDO->exec($str);
							}

							##  Premi
							if($val['premi']>0){
								$no+=1;
								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnalx."','".$tanggalx."','".$no."','".$akundebet2."',
									'Premi Kegiatan Sipil : ".$nmkary[$karyd]."','".$val['premi']."','IDR','1','".$kodeorg."',
									'".$kegiatanx."','".$kodeAsset."','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','".$defSegment."')";
								$owlPDO->exec($str);
							}

							$no+=1;
							$rupiahKr=($val['upah']+$val['premi']);


							# insert kredit => keu_jurnaldt
							if($lokasitugasx[$karyd]!=$kodeorg){

								$noxzx=0;
								if(!isset($noxjur[$lokasitugasx[$karyd]])){
									$noxjur[$lokasitugasx[$karyd]]=1;
								}
								if($indukunit[$lokasitugasx[$karyd]]!=$indukunit[$kodeorg]){
									$jenis="inter";
								}else if($indukunit[$lokasitugasx[$karyd]]==$indukunit[$kodeorg]){
									$jenis="intra";    
								}
								
								$aknPt=makeOption($dbname,'keu_5caco','kodeorg,akunpiutang',"kodeorg='".$lokasitugasx[$karyd]."' and jenis='".$jenis."'");
								$aknHtg=makeOption($dbname,'keu_5caco','kodeorg,akunhutang',"kodeorg='".$kodeorg."' and jenis='".$jenis."'"); 


								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnalx."','".$tanggalx."','".$no."','".$aknHtg[$kodeorg]."',
									'Upah dan Premi Kegiatan Sipil (".$jenis.") : ".$nmkary[$karyd]."','".$rupiahKr*(-1)."','IDR','1','".$kodeorg."',
									'','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','".$defSegment."')";
								$owlPDO->exec($str);

								# Get Journal Counter
								$queryJxzz = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
									"kodeorg='".$indukunit[$lokasitugasx[$karyd]]."' and kodekelompok='M' and kodeunit='".$lokasitugasx[$karyd]."' and periode='".$prd."'");
								$tmpKonterxzz = fetchData($queryJxzz);
								$konterxzz = addZero($tmpKonterxzz[0]['nokounter']+$noxjur[$lokasitugasx[$karyd]],3);


								# Validasi jika nomor konter sudah 1000
								if($konterxzz>999){
									throw new PDOException("Nomor transaksi Jurnal sudah melebihi batas, silahkan reset nomor melalui menu Keuangan - Setup - Kelompok Jurnal, Kode Org = ".$_SESSION['org']['kodeorganisasi'].", Kode Kelompok = M");
								}

								$JurnalM[$lokasitugasx[$karyd]] = $tmpKonterxzz[0]['nokounter']+1;


								# Transform No Jurnal dari No Transaksi
								$tmpNoJurnalxzz = explode('/',$notransaksi);

								$tmpKodeOrgxzz = $lokasitugasx[$karyd];
								$nojurnalxzz = $tmpNoJurnalxzz[0]."/".$tmpKodeOrgxzz."/M/".$konterxzz;

								$str="insert into ".$dbname.".keu_jurnalht
									(`nojurnal`,`kodejurnal`,`tanggal`,`tanggalentry`,`posting`,`totaldebet`,`totalkredit`,`amountkoreksi`,
									`noreferensi`,`autojurnal`,`matauang`,`kurs`,`revisi`)values
									('".$nojurnalxzz."','M','".$tanggalx."','".$tglEntry."','0','".$rupiahKr."','".$rupiahKr."','0','".$notransaksi."','1','IDR','1','0')";
								$owlPDO->exec($str);

								$noxzx+=1;
								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnalxzz."','".$tanggalx."','".$noxzx."','".$aknPt[$lokasitugasx[$karyd]]."',
									'Upah dan Premi Kegiatan Sipil (".$jenis.") : ".$nmkary[$karyd]."','".$rupiahKr."','IDR','1','".$lokasitugasx[$karyd]."',
									'','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','".$defSegment."')";
								$owlPDO->exec($str);

								$noxzx+=1;
								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnalxzz."','".$tanggalx."','".$noxzx."','".$resParam[0]['noakunkredit']."',
									'Upah dan Premi Kegiatan Sipil (".$jenis.") : ".$nmkary[$karyd]."','".$rupiahKr*(-1)."','IDR','1','".$lokasitugasx[$karyd]."',
									'','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','".$defSegment."')";
								$owlPDO->exec($str);
							

								$noxjur[$lokasitugasx[$karyd]]+=1;

							}else{

								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnalx."','".$tanggalx."','".$no."','".$resParam[0]['noakunkredit']."',
									'Upah dan Premi Kegiatan Sipil : ".$nmkary[$karyd]."','".$rupiahKr*(-1)."','IDR','1','".$kodeorg."',
									'','','','".$karyd."','','','".$notransaksi."','','','','','0','".$defSegment."')";
								$owlPDO->exec($str);

							}
						}
					}
				}
			}
		}
	}

	#========================= insert vhc_spl_prestasi_detail ===============================
	// (`notransaksi`,`nobkm`,`nourut`,`indukblok`,`alokasi`,`nik`,`kodekegiatan`,`total_hasilkerja`,`total_hk`,`total_upah`,`total_premi`,`kodesegment`)

	$str="insert into ".$dbname.".vhc_spl_prestasi_detail
		(`notransaksi`,`nobkm`,`nourut`,`indukblok`,`alokasi`,`nik`,`kodekegiatan`,`total_hasilkerja`,`total_hk`,`total_upah`,`total_premi`,`kodesegment`)
	values ".$datadetail1.";";
	$owlPDO->exec($str);

	if (isset($JurnalM)) {
		foreach ($JurnalM as $unitz => $urutxz) {
			$str="update ".$dbname.".keu_5kelompokjurnal set nokounter = '".$urutxz."' where kodeorg='".$indukunit[$unitz]."' and kodekelompok='M' and kodeunit='".$unitz."' and periode='".$prd."'";
			$owlPDO->exec($str);
		}
	}
	#============= End Insert Data ke Table Kebun Aktifitas ===============


	#============= End Insert Data ke Table Kebun Aktifitas ===============
	#======================== Cek data jurnal =============================
	
	$str="select sum(debet) as rpj from ".$dbname.".keu_jurnaldt_vw where  noreferensi ='".$notransaksi."' and kodejurnal not in ('M')"; 
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	$rpj=$bar['rpj'];
	
	$varian=($rpj-$rupiahHT);
	if($varian > 2 or $varian < (-2)){
		throw new PDOException("Nilai Jurnal ".$unit." : ".number_format($rpj)." tidak sama dengan nilai transaksi : ".number_format($rupiahHT));
	}			

	#======================== End data jurnal =============================

	#============================= Update ==================================
	# Update Counter
	$str="update ".$dbname.".keu_5kelompokjurnal set nokounter = '".$konter."' where kodeorg='".getindukPT($unit)."' and kodekelompok='".$kodeJurnal."' and kodeunit='".$unit."' and periode='".$prd."'";
	$owlPDO->exec($str);

	# Update flag transaksi
	$str="update ".$dbname.".vhc_spl_aktifitas set jurnal ='1' where notransaksi='".$notransaksi."'";
	$owlPDO->exec($str);
	
	#=========================== End Update ===============================
	$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}

?>