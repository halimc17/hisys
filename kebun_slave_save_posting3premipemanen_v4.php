<?php
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$notransaksi    = checkPostGet('notransaksi','');
$prd            = checkPostGet('prd','');
$unit           = checkPostGet('unit','');
$tanggalpanen   = checkPostGet('tanggalpanen','');

try {
    $owlPDO->beginTransaction();
    
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
    $tabel = 'datakaryawan';
    $whhist = '';
    if($jumlahkaryhist > 0) {
        $whhist = "and b.version_type='B' and b.periodegaji='".$prd."' ";
        $whhistx = "and version_type='B' and periodegaji='".$prd."' ";
        $tabel = 'datakaryawan_hist';
    }
	
	#========================= delete jurnal ===============================
	$str="delete from ".$dbname.".keu_jurnalht where noreferensi='".$notransaksi."'";
	$owlPDO->exec($str);

	#=========================== Nomor Jurnal ==============================
	#PNN01 untuk UPAH , PNN02 untuk PREMI HANYA AMBIL AKUN DEBETNYA SAJA, PNN03 untuk Kutip Brondolan
	$tglEntry = date('Ymd');
	$tgl2=tglakhir($prd.'-01');

	$kodeJurnal = 'PNN01';
	$queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet',"kodeaplikasi='PNN' and jurnalid='".$kodeJurnal."'");
	$resParam = fetchData($queryParam);

	$queryParam2 = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet',"kodeaplikasi='PNN' and jurnalid='PNN02'");
	$resParam2 = fetchData($queryParam2);

	$queryParam3 = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet',"kodeaplikasi='PNN' and jurnalid='PNN03'");
	$resParam3 = fetchData($queryParam3);
	#======================== End Nomor Jurnal =============================


	## INSERT KEBUN AKTIFITAS, KEBUN PRESTASI, KEBUN PRESTASI DETAIL
	$str="select tanggalpanen,kodeorg,divisi from ".$dbname.".kebun_3premipemanen_v2 where notransaksi ='".$notransaksi."' group by tanggalpanen,divisi";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$dataheader='';
	$datanoperx=array();
	$noxz=0;
	while($bar=$res->fetch()){

		# Generate No Transaksi
		$tanggal = str_replace('-','',$bar['tanggalpanen']);
		$fWhere = "tanggal='".$tanggal."' and kodeorg='".$bar['kodeorg']."' and tipetransaksi='PNN'";
		$fQuery = selectQuery($dbname,'kebun_aktifitas','notransaksi',$fWhere);
		$tmpNo = fetchData($fQuery);
		if(count($tmpNo)==0) {
			$notranbkn = $tanggal."/".$bar['kodeorg']."/PNN/001";
		} else {
			$noxz++;
			# Get Max No Urut
			$maxNo = 1;
			foreach($tmpNo as $row) {
			$tmpRow = explode('/',$row['notransaksi']);
			$noUrut = (int)$tmpRow[3];
			if($noUrut>$maxNo)
				$maxNo = $noUrut;
			}
			$currNo = addZero($maxNo+$noxz,3);
			$notranbkn = $tanggal."/".$bar['kodeorg']."/PNN/".$currNo."";
			// echo $notranbkn.'<br>';
		}
        
		$datanoperx[$bar['tanggalpanen']][$bar['divisi']]=$notranbkn;

		if($dataheader!=''){
			$dataheader.=",";
		}
			
			## Header Kebun Aktifitas (`notransaksi`,`tipetransaksi`,`tanggal`,`kodeorg`,`divisi`,`jurnal`,`noreferensi`,`updateby`)
			$dataheader .= "(
				'".$notranbkn."',
				'PNN',
				'".$bar['tanggalpanen']."',
				'".$bar['kodeorg']."',
				'".$bar['divisi']."',
				'1',
				'".$notransaksi."',
				'".$_SESSION['standard']['userid']."'
			)";

		#======================== Mulai Nomor Jurnal =============================

		# Get Journal Counter
		$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodeorg='".getindukPT($bar['kodeorg'])."' and kodekelompok='".$kodeJurnal."' and kodeunit='".$bar['kodeorg']."' and periode='".$prd."'");
		$tmpKonter = fetchData($queryJ);
		
		# Validasi kelompok jurnal
		if(count($tmpKonter) <= 0){
			exit("Warning : Kelompok jurnal ".$kodeJurnal." tidak ada untuk unit : ".$bar['kodeorg']." dan periode : ".$prd." ");
		}

		$konter[$bar['kodeorg']] = addZero($tmpKonter[0]['nokounter']+1,4);

		# Validasi jika nomor konter sudah 1000
		if($konter[$bar['kodeorg']]>9999){
			throw new PDOException("Nomor transaksi Jurnal sudah melebihi batas, silahkan reset nomor melalui menu Keuangan - Setup - Kelompok Jurnal, Kode Org = ".getindukPT($bar['kodeorg']).", Kode Kelompok = PNN01");
		}

		# Transform No Jurnal dari No Transaksi
		$tmpNoJurnal = explode('/',$notransaksi);
		$tmpKodeOrg = $bar['kodeorg'];
		$nojurnal[$bar['kodeorg']] = $tmpNoJurnal[0]."/".$tmpKodeOrg."/".$kodeJurnal."/".$konter[$bar['kodeorg']];

		# Get Journal Counter
		$queryJ2 = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodeorg='".getindukPT($bar['kodeorg'])."' and kodekelompok='PNN03' and kodeunit='".$bar['kodeorg']."' and periode='".$prd."'");
		$tmpKonter2 = fetchData($queryJ2);

		# Validasi kelompok jurnal
		if(count($tmpKonter2 ) <= 0){
			exit("Warning : Kelompok jurnal PPN03 tidak ada untuk unit : ".$bar['kodeorg']." dan periode : ".$prd." ");
		}

		$konter2[$bar['kodeorg']] = addZero($tmpKonter2[0]['nokounter']+1,4);

		# Validasi jika nomor konter sudah 1000
		if($konter2[$bar['kodeorg']]>9999){
			throw new PDOException("Nomor transaksi Jurnal sudah melebihi batas, silahkan reset nomor melalui menu Keuangan - Setup - Kelompok Jurnal, Kode Org = ".getindukPT($bar['kodeorg']).", Kode Kelompok = PNN03");
		}

		# Transform No Jurnal dari No Transaksi
		$tmpNoJurnal2 = explode('/',$notransaksi);
		$tmpKodeOrg2 = $bar['kodeorg'];
		$nojurnal2[$bar['kodeorg']] = $tmpNoJurnal2[0]."/".$tmpKodeOrg2."/PNN03/".$konter2[$bar['kodeorg']];

		#======================== End Nomor Jurnal =============================
	}

	$penampungNojurnal = '';
	foreach($nojurnal as $jurnal ){
		if($penampungNojurnal == ''){
			$penampungNojurnal.= $jurnal;
		}else{
			$penampungNojurnal .= ",".$jurnal;
		}
	}

	$penampungNojurnal2 = '';
	foreach($nojurnal2 as $jurnal ){
		if($penampungNojurnal2 == ''){
			$penampungNojurnal2.= $jurnal;
		}else{
			$penampungNojurnal2 .= ",".$jurnal;
		}
	}

	$listDataBlokBesar= array();
	$listDataBlokKecil= array();

	## INPUT KEBUN PRESTASI BLOK BESAR DAB INPUT KEBUN PRESTASI DETAIL BLOK KECil
	$str="select a.*, b.namakaryawan,b.nik from ".$dbname.".kebun_3premipemanen_v2 a left join ".$tabel." b on a.karyawanid=b.karyawanid where a.notransaksi ='".$notransaksi."' ".$whhist." order by a.tahuntanam asc, b.namakaryawan asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){

		## BLOK BESAR
		$listDataBlokBesar[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']] = $bar['indukblok'];
		$total_JG[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']] += $bar['jjg'];
		$total_KG[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']] += $bar['kg'];
		$total_HK[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']] += $bar['hk'];
		$total_UP[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']] += $bar['upah'];
		$total_PT[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']] += $bar['potupah'];
		$total_PL[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']] += $bar['premilb'];
		$total_PR[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']] += ($bar['premikehadiran'] + $bar['premikesulitan']);
		$total_DN[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']] += $bar['denda'];
		$total_HA[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']] += $bar['hapanen'];
		$total_BR[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']] += $bar['brondol'];
		$total_UR[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']] += $bar['upahbro'];

		$nospb[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']]     = $bar['nospb'];

		## BLOK KECIL
		$listDataBlokKecil[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']][$bar['blokkecil']] = $bar['blokkecil'];
		$total_JG_X[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']][$bar['blokkecil']] += $bar['jjg'];
		$total_KG_X[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']][$bar['blokkecil']] += $bar['kg'];
		$total_HK_X[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']][$bar['blokkecil']] += $bar['hk'];
		$total_UP_X[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']][$bar['blokkecil']] += $bar['upah'];
		$total_PT_X[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']][$bar['blokkecil']] += $bar['potupah'];
		$total_PL_X[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']][$bar['blokkecil']] += $bar['premilb'];
		$total_PR_X[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']][$bar['blokkecil']] += ($bar['premikehadiran'] + $bar['premikesulitan']);
		$total_DN_X[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']][$bar['blokkecil']] += $bar['denda'];
		$total_HA_X[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']][$bar['blokkecil']] += $bar['hapanen'];
		$total_BR_X[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']][$bar['blokkecil']] += $bar['brondol'];
		$total_UR_X[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']][$bar['blokkecil']] += $bar['upahbro'];

		$nospb_X[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']][$bar['blokkecil']] = $bar['nospb'];
		$tahuntanam_X[$datanoperx[$bar['tanggalpanen']][$bar['divisi']]][$bar['tanggalpanen']][$bar['divisi']][$bar['karyawanid']][$bar['indukblok']][$bar['blokkecil']] = $bar['tahuntanam'];
	}

	$rupiahHT=array();
	$rupiahHT2=array();
	$totaldapatbersih=array();

	$datadetail1 = '';
	foreach($listDataBlokBesar as $notrans =>$arr1){
		foreach($arr1 as $tglpnn =>$arr2){
			foreach($arr2 as $div =>$arr3){
				foreach($arr3 as $karid =>$arr4){
					foreach($arr4 as $blokbesar =>$value){
					
						if($datadetail1 != ''){
							$datadetail1.=",";
						}

						// (`notransaksi`,`noreferensi`,`nik`,`kodeorg`,`hasilkerja`,`hasilkerjakg`,`jumlahhk`,`upahkerja`,`upahpenalty`,`upahpremilebihbasis`,`upahpremi`,`rupiahpenalty`,`luaspanen`,`brondolan`,`premibrondol`,`nospb`,`updateby`)

						$datadetail1 .= "(
							'".$notrans."',
							'".$notransaksi."',
							'".$karid."',
							'".$blokbesar."',
							'".$total_JG[$notrans][$tglpnn][$div][$karid][$blokbesar]."',
							'".$total_KG[$notrans][$tglpnn][$div][$karid][$blokbesar]."',
							'".$total_HK[$notrans][$tglpnn][$div][$karid][$blokbesar]."',
							'".$total_UP[$notrans][$tglpnn][$div][$karid][$blokbesar]."',
							'".$total_PT[$notrans][$tglpnn][$div][$karid][$blokbesar]."',
							'".$total_PL[$notrans][$tglpnn][$div][$karid][$blokbesar]."',
							'".$total_PR[$notrans][$tglpnn][$div][$karid][$blokbesar]."',
							'".$total_DN[$notrans][$tglpnn][$div][$karid][$blokbesar]."',
							'".$total_HA[$notrans][$tglpnn][$div][$karid][$blokbesar]."',
							'".$total_BR[$notrans][$tglpnn][$div][$karid][$blokbesar]."',
							'".$total_UR[$notrans][$tglpnn][$div][$karid][$blokbesar]."',
							'".$nospb[$notrans][$tglpnn][$div][$karid][$blokbesar]."',
							'".$_SESSION['standard']['userid']."'
						)";

						$rupiahHT[substr($div,0,4)] +=($total_UP[$notrans][$tglpnn][$div][$karid][$blokbesar] +  $total_PL[$notrans][$tglpnn][$div][$karid][$blokbesar] +$total_PR[$notrans][$tglpnn][$div][$karid][$blokbesar] )-($total_PT[$notrans][$tglpnn][$div][$karid][$blokbesar] + $total_DN[$notrans][$tglpnn][$div][$karid][$blokbesar]);

						

						$rupiahHT2[substr($div,0,4)] +=$total_UR[$notrans][$tglpnn][$div][$karid][$blokbesar];
						$totaldapatbersih[substr($div,0,4)] =$rupiahHT[substr($div,0,4)]+$rupiahHT2[substr($div,0,4)];

						$kodeOrganisasi[substr($div,0,4)] = substr($div,0,4);
					}
				}
			}
		}
	}

	$datajurnal1=array();
	$datajurnal2=array();

	$datadetail2 = '';
	foreach($listDataBlokKecil as $notrans =>$arr1){
		foreach($arr1 as $tglpnn =>$arr2){
			foreach($arr2 as $div =>$arr3){
				foreach($arr3 as $karid =>$arr4){
					foreach($arr4 as $blokbesar =>$arr5){
						foreach($arr5 as $blokkecil =>$value){

							// (`notransaksi`,`noreferensi`,`nik`,`kodeorg`,`tahuntanam`,`hasilkerja`,`hasilkerjakg`,`jumlahhk`,`upahkerja`,`upahpenalty`,`upahpremilebihbasis`,`upahpremi`,`rupiahpenalty`,`luaspanen`,`brondolan`,`premibrondol`,`nospb`,`updateby`)

							if($datadetail2!=''){
							 	$datadetail2.=",";
							}

							$datadetail2 .= "(
								'".$notrans."',
								'".$notransaksi."',
								'".$karid."',
								'".$blokkecil."',
								'".$tahuntanam_X[$notrans][$tglpnn][$div][$karid][$blokbesar][$blokkecil]."',
								'".$total_JG_X[$notrans][$tglpnn][$div][$karid][$blokbesar][$blokkecil]."',
								'".$total_KG_X[$notrans][$tglpnn][$div][$karid][$blokbesar][$blokkecil]."',
								'".$total_HK_X[$notrans][$tglpnn][$div][$karid][$blokbesar][$blokkecil]."',
								'".$total_UP_X[$notrans][$tglpnn][$div][$karid][$blokbesar][$blokkecil]."',
								'".$total_PT_X[$notrans][$tglpnn][$div][$karid][$blokbesar][$blokkecil]."',
								'".$total_PL_X[$notrans][$tglpnn][$div][$karid][$blokbesar][$blokkecil]."',
								'".$total_PR_X[$notrans][$tglpnn][$div][$karid][$blokbesar][$blokkecil]."',
								'".$total_DN_X[$notrans][$tglpnn][$div][$karid][$blokbesar][$blokkecil]."',
								'".$total_HA_X[$notrans][$tglpnn][$div][$karid][$blokbesar][$blokkecil]."',
								'".$total_BR_X[$notrans][$tglpnn][$div][$karid][$blokbesar][$blokkecil]."',
								'".$total_UR_X[$notrans][$tglpnn][$div][$karid][$blokbesar][$blokkecil]."',
								'".$nospb_X[$notrans][$tglpnn][$div][$karid][$blokbesar][$blokkecil]."',
								'".$_SESSION['standard']['userid']."'
							)";

							## Upah, Premi, Denda
							$datajurnal1[substr($div,0,4)][$nojurnal[substr($div,0,4)]][$karid][$blokkecil]['upah']+=$total_UP_X[$notrans][$tglpnn][$div][$karid][$blokbesar][$blokkecil];
							$datajurnal1[substr($div,0,4)][$nojurnal[substr($div,0,4)]][$karid][$blokkecil]['upahpot']+=$total_PT_X[$notrans][$tglpnn][$div][$karid][$blokbesar][$blokkecil];
							$datajurnal1[substr($div,0,4)][$nojurnal[substr($div,0,4)]][$karid][$blokkecil]['premi']+=$total_PL_X[$notrans][$tglpnn][$div][$karid][$blokbesar][$blokkecil] + $total_PR_X[$notrans][$tglpnn][$div][$karid][$blokbesar][$blokkecil] ;
							$datajurnal1[substr($div,0,4)][$nojurnal[substr($div,0,4)]][$karid][$blokkecil]['denda']+=$total_DN_X[$notrans][$tglpnn][$div][$karid][$blokbesar][$blokkecil];

							## Brondolan
							$datajurnal2[substr($div,0,4)][$nojurnal2[substr($div,0,4)]][$karid][$blokkecil]['premibrondol']+=$total_UR_X[$notrans][$tglpnn][$div][$karid][$blokbesar][$blokkecil];
							
							$totaldapatbersihJurnal[substr($div,0,4)] += ($datajurnal1[substr($div,0,4)][$nojurnal[substr($div,0,4)]][$karid][$blokkecil]['upah'] + $datajurnal1[substr($div,0,4)][$nojurnal[substr($div,0,4)]][$karid][$blokkecil]['premi'] + $datajurnal2[substr($div,0,4)][$nojurnal2[substr($div,0,4)]][$karid][$blokkecil]['premibrondol']) - ($datajurnal1[substr($div,0,4)][$nojurnal[substr($div,0,4)]][$karid][$blokkecil]['upahpot'] + $datajurnal1[substr($div,0,4)][$nojurnal[substr($div,0,4)]][$karid][$blokkecil]['denda'] );
						}
					}
				}
			}
		}
	}

	$indukunit=makeOption($dbname,'organisasi','kodeorganisasi,induk',"tipe='KEBUN'");
	$noxjur=array();
	$JurnalM=array();

	## HT NYA PNN01
	foreach($rupiahHT as $unitx =>$value){
		$str="insert into ".$dbname.".keu_jurnalht
		(`nojurnal`,`kodejurnal`,`tanggal`,`tanggalentry`,`posting`,`totaldebet`,`totalkredit`,`amountkoreksi`,
		`noreferensi`,`autojurnal`,`matauang`,`kurs`,`revisi`)values
		('".$nojurnal[$unitx]."','".$kodeJurnal."','".$tgl2."','".$tglEntry."','0','".$value."','".$value."','0','".$notransaksi."','1','IDR','1','0')";
		$owlPDO->exec($str);
	}

	

	$no=0;
	$totalxxx=0;
	foreach ($datajurnal1 as $kodeorg => $key1x) {
		foreach ($key1x as $nojurnalx => $key1) {
			foreach ($key1 as $karyd => $key2) {
				foreach ($key2 as $blokkecil => $val) {
					
					$nmkary=makeOption($dbname,$tabel,'karyawanid,namakaryawan',"karyawanid='".$karyd."'");
					$lokasitugasx=makeOption($dbname,$tabel,'karyawanid,lokasitugas',"karyawanid='".$karyd."' ".$whhistx."");
					$statusblok=makeOption($dbname,'setup_blok','kodeorg,statusblok',"kodeorg='".$blokkecil."'");

					$akundebet=$resParam[0]['noakundebet'];
					$akundebet2=$resParam2[0]['noakundebet'];

					if($statusblok[$blokkecil]!='TM'){
						$akundebet='1261004';
						$akundebet2='1261004';
					}

					# insert debet => keu_jurnaldt
					## Upah
					if($val['upah']>0){
						$no+=1;
						$str="insert into ".$dbname.".keu_jurnaldt
							(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
							`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
							`kodeblok`,`revisi`,`kodesegment`)
							values ('".$nojurnalx."','".$tgl2."','".$no."','".$akundebet."',
							'Potong Buah : ".$nmkary[$karyd]."','".($val['upah'])."','IDR','1','".$kodeorg."',
							'".$resParam[0]['noakundebet']."01','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
						$owlPDO->exec($str);
					}

					##  Premi
					if($val['premi']>0){
						$no+=1;
						$str="insert into ".$dbname.".keu_jurnaldt
							(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
							`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
							`kodeblok`,`revisi`,`kodesegment`)
							values ('".$nojurnalx."','".$tgl2."','".$no."','".$akundebet2."',
							'Premi Panen : ".$nmkary[$karyd]."','".$val['premi']."','IDR','1','".$kodeorg."',
							'".$resParam2[0]['noakundebet']."01','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
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
							values ('".$nojurnalx."','".$tgl2."','".$no."','".$aknHtg[$kodeorg]."',
							'Potong Buah dan Premi Panen : ".$nmkary[$karyd]."','".$rupiahKr*(-1)."','IDR','1','".$kodeorg."',
							'','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
						$owlPDO->exec($str);

						# Get Journal Counter
						$queryJxzz = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
							"kodeorg='".$indukunit[$lokasitugasx[$karyd]]."' and kodekelompok='M' and kodeunit='".$lokasitugasx[$karyd]."' and periode='".$prd."'");
						$tmpKonterxzz = fetchData($queryJxzz);
						$konterxzz = addZero($tmpKonterxzz[0]['nokounter']+$noxjur[$lokasitugasx[$karyd]],4);

						# Validasi kelompok jurnal
						if(count($konterxzz ) <= 0){
							exit("Warning : Kelompok jurnal M tidak ada untuk unit : ".$lokasitugasx[$karyd]." dan periode : ".$prd." ");
						}

						# Validasi jika nomor konter sudah 1000
						if($konterxzz>9999){
							throw new PDOException("Nomor transaksi Jurnal sudah melebihi batas, silahkan reset nomor melalui menu Keuangan - Setup - Kelompok Jurnal, Kode Org = ".$_SESSION['org']['kodeorganisasi'].", Kode Kelompok = M");
						}

						# Transform No Jurnal dari No Transaksi
						$tmpNoJurnalxzz = explode('/',$notransaksi);

						$tmpKodeOrgxzz = $lokasitugasx[$karyd];
						$nojurnalxzz = $tmpNoJurnalxzz[0]."/".$tmpKodeOrgxzz."/M/".$konterxzz;


						## START CEK KONTER
								# TEMPNOJURNAL
								$TempJurnal = $tmpNoJurnalxzz[0]."/".$tmpKodeOrgxzz."/M/" ;

								## CEK COUNTER TERAKHIR JURNAL HT
								$strCounter5 = "
									SELECT 
										MAX(CAST(RIGHT(nojurnal, 4) AS UNSIGNED)) AS last_counter
									FROM ".$dbname.".keu_jurnalht
									WHERE nojurnal LIKE '%".$TempJurnal."%'
									AND kodejurnal = 'M' ";
								$resCounter5      = fetchdata($strCounter5);
								$counterTerakhir5 = (int) ($resCounter5[0]['last_counter'] ?? 0);

								if ($konterxzz < $counterTerakhir5) {
									$konterxzz = $counterTerakhir5+1;

									#= update counter jurnal
									$str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konterxzz . "' where kodeunit='" . $lokasitugasx[$karyd] . "' and kodekelompok='M' and periode='" . $prd. "' ";
									$owlPDO->exec($str);

									$nojurnalxzz = $tmpNoJurnalxzz[0]."/".$tmpKodeOrgxzz."/M/".$konterxzz;
								}
	
						## END CEK KONTER
						$JurnalM[$lokasitugasx[$karyd]] = $konterxzz+1;



						$str="insert into ".$dbname.".keu_jurnalht
							(`nojurnal`,`kodejurnal`,`tanggal`,`tanggalentry`,`posting`,`totaldebet`,`totalkredit`,`amountkoreksi`,
							`noreferensi`,`autojurnal`,`matauang`,`kurs`,`revisi`)values
							('".$nojurnalxzz."','M','".$tgl2."','".$tglEntry."','0','".$rupiahKr."','".$rupiahKr."','0','".$notransaksi."','1','IDR','1','0')";
						$owlPDO->exec($str);

						$noxzx+=1;
						$str="insert into ".$dbname.".keu_jurnaldt
							(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
							`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
							`kodeblok`,`revisi`,`kodesegment`)
							values ('".$nojurnalxzz."','".$tgl2."','".$noxzx."','".$aknPt[$lokasitugasx[$karyd]]."',
							'Potong Buah dan Premi Panen : ".$nmkary[$karyd]."','".$rupiahKr."','IDR','1','".$lokasitugasx[$karyd]."',
							'','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
						$owlPDO->exec($str);

						$noxzx+=1;
						$str="insert into ".$dbname.".keu_jurnaldt
							(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
							`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
							`kodeblok`,`revisi`,`kodesegment`)
							values ('".$nojurnalxzz."','".$tgl2."','".$noxzx."','".$resParam[0]['noakunkredit']."',
							'Potong Buah dan Premi Panen : ".$nmkary[$karyd]."','".$rupiahKr*(-1)."','IDR','1','".$lokasitugasx[$karyd]."',
							'','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
						$owlPDO->exec($str);
					
						if(($val['upahpot']+($val['denda']*-1))>0){
							$no+=1;
							# insert debet => keu_jurnaldt
							$str="insert into ".$dbname.".keu_jurnaldt
								(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
								`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
								`kodeblok`,`revisi`,`kodesegment`)
								values ('".$nojurnalx."','".$tgl2."','".$no."','".$aknHtg[$kodeorg]."',
								'DENDA Potong Buah : ".$nmkary[$karyd]."','".($val['upahpot']+($val['denda']*-1))."','IDR','1','".$kodeorg."',
								'','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
							$owlPDO->exec($str);

							$noxzx+=1;
							$str="insert into ".$dbname.".keu_jurnaldt
								(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
								`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
								`kodeblok`,`revisi`,`kodesegment`)
								values ('".$nojurnalxzz."','".$tgl2."','".$noxzx."','".$aknPt[$lokasitugasx[$karyd]]."',
								'DENDA Potong Buah : ".$nmkary[$karyd]."','".($val['upahpot']+($val['denda']*-1))*(-1)."','IDR','1','".$lokasitugasx[$karyd]."',
								'','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
							$owlPDO->exec($str);

							$noxzx+=1;
							$str="insert into ".$dbname.".keu_jurnaldt
								(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
								`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
								`kodeblok`,`revisi`,`kodesegment`)
								values ('".$nojurnalxzz."','".$tgl2."','".$noxzx."','".$resParam[0]['noakunkredit']."',
								'DENDA Potong Buah : ".$nmkary[$karyd]."','".($val['upahpot']+($val['denda']*-1))."','IDR','1','".$lokasitugasx[$karyd]."',
								'','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
							$owlPDO->exec($str);
							
							if($val['upahpot']>0){
								$no+=1;
								$rupiahKr=$val['upahpot'];
								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnalx."','".$tgl2."','".$no."','".$akundebet."',
									'DENDA Potong Buah : ".$nmkary[$karyd]."','".$rupiahKr*(-1)."','IDR','1','".$kodeorg."',
									'".$resParam[0]['noakundebet']."01','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
								$owlPDO->exec($str);
							}

							if(($val['denda']*-1)>0){
								$no+=1;
								$rupiahKr=($val['denda']*-1);
								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnalx."','".$tgl2."','".$no."','".$akundebet2."',
									'DENDA Potong Buah : ".$nmkary[$karyd]."','".$rupiahKr*(-1)."','IDR','1','".$kodeorg."',
									'".$resParam2[0]['noakundebet']."01','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
								$owlPDO->exec($str);

							}

						}

						$noxjur[$lokasitugasx[$karyd]]+=1;

					}else{

						$str="insert into ".$dbname.".keu_jurnaldt
							(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
							`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
							`kodeblok`,`revisi`,`kodesegment`)
							values ('".$nojurnalx."','".$tgl2."','".$no."','".$resParam[0]['noakunkredit']."',
							'Potong Buah dan Premi Panen : ".$nmkary[$karyd]."','".$rupiahKr*(-1)."','IDR','1','".$kodeorg."',
							'','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
						$owlPDO->exec($str);

						if(($val['upahpot']+($val['denda']*-1))>0){
							$no+=1;
							# insert debet => keu_jurnaldt
							$str="insert into ".$dbname.".keu_jurnaldt
								(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
								`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
								`kodeblok`,`revisi`,`kodesegment`)
								values ('".$nojurnalx."','".$tgl2."','".$no."','".$resParam[0]['noakunkredit']."',
								'DENDA Potong Buah : ".$nmkary[$karyd]."','".($val['upahpot']+($val['denda']*-1))."','IDR','1','".$kodeorg."',
								'','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
							$owlPDO->exec($str);
							
							if($val['upahpot']>0){
								$no+=1;
								$rupiahKr=$val['upahpot'];
								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnalx."','".$tgl2."','".$no."','".$akundebet."',
									'DENDA Potong Buah : ".$nmkary[$karyd]."','".$rupiahKr*(-1)."','IDR','1','".$kodeorg."',
									'".$resParam[0]['noakundebet']."01','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
								$owlPDO->exec($str);

							}

							if(($val['denda']*-1)>0){
								$no+=1;
								$rupiahKr=($val['denda']*-1);
								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnalx."','".$tgl2."','".$no."','".$akundebet2."',
									'DENDA Potong Buah : ".$nmkary[$karyd]."','".$rupiahKr*(-1)."','IDR','1','".$kodeorg."',
									'".$resParam2[0]['noakundebet']."01','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
								$owlPDO->exec($str);

							}
						}
					}
				}
			}
		}
	}
	

	$adaDatanya = 0;
	foreach($rupiahHT2 as $unitx =>$value){
		if($value > 0 ){
			$str="insert into ".$dbname.".keu_jurnalht
			(`nojurnal`,`kodejurnal`,`tanggal`,`tanggalentry`,`posting`,`totaldebet`,`totalkredit`,`amountkoreksi`,
			`noreferensi`,`autojurnal`,`matauang`,`kurs`,`revisi`)values
			('".$nojurnal2[$unitx]."','PNN03','".$tgl2."','".$tglEntry."','0','".$value."','".$value."','0','".$notransaksi."','1','IDR','1','0')";
			$owlPDO->exec($str);

			$adaDatanya = 1;
		}
	}
	
	if($adaDatanya == 1){
		$nox=0;
		foreach ($datajurnal2 as $kodeorg => $key1x) {
			foreach ($key1x as $nojurnal2x => $key1) {
				foreach ($key1 as $karyd => $key2) {
					foreach ($key2 as $blokkecil => $val) {
						$noxzx=0;
						
						$nmkary=makeOption($dbname,$tabel,'karyawanid,namakaryawan',"karyawanid='".$karyd."'");
						$lokasitugasx=makeOption($dbname,$tabel,'karyawanid,lokasitugas',"karyawanid='".$karyd."' ".$whhistx."");
						$statusblok=makeOption($dbname,'setup_blok','kodeorg,statusblok',"kodeorg='".$blokkecil."'");
						
						$akundebet3=$resParam3[0]['noakundebet'];
						if($statusblok[$blokkecil]!='TM'){
							$akundebet3='1261004';
						}
						
						if($lokasitugasx[$karyd]!=$kodeorg){
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
							
							
							# Get Journal Counter
							$queryJxzz = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
							"kodeorg='".$indukunit[$lokasitugasx[$karyd]]."' and kodekelompok='M' and kodeunit='".$lokasitugasx[$karyd]."' and periode='".$prd."'");
							$tmpKonterxzz = fetchData($queryJxzz);
							$konterxzz = addZero($tmpKonterxzz[0]['nokounter']+$noxjur[$lokasitugasx[$karyd]],4);

							# Validasi kelompok jurnal
							if(count($konterxzz ) <= 0){
								exit("Warning : Kelompok jurnal M tidak ada untuk unit : ".$lokasitugasx[$karyd]." dan periode : ".$prd." ");
							}

							//echo $konterxzz.'<br>';
							
							# Validasi jika nomor konter sudah 1000
							if($konterxzz>9999){
								throw new PDOException("Nomor transaksi Jurnal sudah melebihi batas, silahkan reset nomor melalui menu Keuangan - Setup - Kelompok Jurnal, Kode Org = ".$_SESSION['org']['kodeorganisasi'].", Kode Kelompok = M");
							}
							
							# Transform No Jurnal dari No Transaksi
							$tmpNoJurnalxzz = explode('/',$notransaksi);
							
							$tmpKodeOrgxzz = $lokasitugasx[$karyd];
							$nojurnalxzz = $tmpNoJurnalxzz[0]."/".$tmpKodeOrgxzz."/M/".$konterxzz;	
							
							
							

						## START CEK KONTER
								# TEMPNOJURNAL
								$TempJurnal = $tmpNoJurnalxzz[0]."/".$tmpKodeOrgxzz."/M/" ;

								## CEK COUNTER TERAKHIR JURNAL HT
								$strCounter5 = "
									SELECT 
										MAX(CAST(RIGHT(nojurnal, 4) AS UNSIGNED)) AS last_counter
									FROM ".$dbname.".keu_jurnalht
									WHERE nojurnal LIKE '%".$TempJurnal."%'
									AND kodejurnal = 'M' ";
								$resCounter5      = fetchdata($strCounter5);
								$counterTerakhir5 = (int) ($resCounter5[0]['last_counter'] ?? 0);

								if ($konterxzz < $counterTerakhir5) {
									$konterxzz = $counterTerakhir5+1;

									#= update counter jurnal
									$str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konterxzz . "' where kodeunit='" . $lokasitugasx[$karyd] . "' and kodekelompok='M' and periode='" . $prd. "' ";
									$owlPDO->exec($str);

									$nojurnalxzz = $tmpNoJurnalxzz[0]."/".$tmpKodeOrgxzz."/M/".$konterxzz;
								}
	
						## END CEK KONTER
							
							$JurnalM[$lokasitugasx[$karyd]] = $konterxzz+1;

							if($val['premibrondol']>0){
								$str="insert into ".$dbname.".keu_jurnalht
								(`nojurnal`,`kodejurnal`,`tanggal`,`tanggalentry`,`posting`,`totaldebet`,`totalkredit`,`amountkoreksi`,
								`noreferensi`,`autojurnal`,`matauang`,`kurs`,`revisi`)values
								('".$nojurnalxzz."','M','".$tgl2."','".$tglEntry."','0','".$val['premibrondol']."','".$val['premibrondol']."','0','".$notransaksi."','1','IDR','1','0')";
								$owlPDO->exec($str);

								$noxzx+=1;
								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnalxzz."','".$tgl2."','".$noxzx."','".$aknPt[$lokasitugasx[$karyd]]."',
									'Kutip Brondolan : ".$nmkary[$karyd]."','".($val['premibrondol'])."','IDR','1','".$lokasitugasx[$karyd]."',
									'','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
								$owlPDO->exec($str);

								$noxzx+=1;
								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnalxzz."','".$tgl2."','".$noxzx."','".$resParam[0]['noakunkredit']."',
									'Kutip Brondolan : ".$nmkary[$karyd]."','".($val['premibrondol'])*(-1)."','IDR','1','".$lokasitugasx[$karyd]."',
									'','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
								$owlPDO->exec($str);


								$nox+=1;
								$nmkary=makeOption($dbname,$tabel,'karyawanid,namakaryawan',"karyawanid='".$karyd."'");
								# insert debet => keu_jurnaldt
								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnal2x."','".$tgl2."','".$nox."','".$akundebet3."',
									'Kutip Brondolan : ".$nmkary[$karyd]."','".$val['premibrondol']."','IDR','1','".$kodeorg."',
									'".$resParam3[0]['noakundebet']."01','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
								$owlPDO->exec($str);
								//$totalxxx+=$val['premibrondol'];
								
								$nox+=1;
								# insert kredit => keu_jurnaldt
								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnal2x."','".$tgl2."','".$nox."','".$aknHtg[$kodeorg]."',
									'Kutip Brondolan : ".$nmkary[$karyd]."','".$val['premibrondol']*(-1)."','IDR','1','".$kodeorg."',
									'".$resParam3[0]['noakundebet']."01','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
								$owlPDO->exec($str);

								$noxjur[$lokasitugasx[$karyd]]+=1;
							}
						}else{

							if($val['premibrondol']>0){
								$nox+=1;
								$nmkary=makeOption($dbname,$tabel,'karyawanid,namakaryawan',"karyawanid='".$karyd."'");
								# insert debet => keu_jurnaldt
								$str="insert into ".$dbname.".keu_jurnaldt
								(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
								`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
								`kodeblok`,`revisi`,`kodesegment`)
								values ('".$nojurnal2x."','".$tgl2."','".$nox."','".$akundebet3."',
								'Kutip Brondolan : ".$nmkary[$karyd]."','".$val['premibrondol']."','IDR','1','".$kodeorg."',
								'".$resParam3[0]['noakundebet']."01','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
								$owlPDO->exec($str);
								//$totalxxx+=$val['premibrondol'];
								
								$nox+=1;
								# insert kredit => keu_jurnaldt
								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnal2x."','".$tgl2."','".$nox."','".$resParam3[0]['noakunkredit']."',
									'Kutip Brondolan : ".$nmkary[$karyd]."','".$val['premibrondol']*(-1)."','IDR','1','".$kodeorg."',
									'".$resParam3[0]['noakundebet']."01','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
								$owlPDO->exec($str);
							}
						}
					}
				}
			}
		}
	}
	

	#========================= delete kebun_aktifitas ===============================
	$str="delete from ".$dbname.".kebun_aktifitas where noreferensi='".$notransaksi."'";
	$owlPDO->exec($str);
	#========================= delete kebun_aktifitas ===============================

	#========================= insert kebun_prestasi ===============================
	$str="insert into ".$dbname.".kebun_aktifitas
				(`notransaksi`,`tipetransaksi`,`tanggal`,`kodeorg`,`divisi`,`jurnal`,`noreferensi`,`updateby`)
			values ".$dataheader.";";
	$owlPDO->exec($str);

	$str="insert into ".$dbname.".kebun_prestasi
	(`notransaksi`,`noreferensi`,`nik`,`kodeorg`,`hasilkerja`,`hasilkerjakg`,`jumlahhk`,`upahkerja`,`upahpenalty`,`upahpremilebihbasis`,`upahpremi`,`rupiahpenalty`,`luaspanen`,`brondolan`,`premibrondol`,`nospb`,`updateby`)
	values ".$datadetail1.";";
	$owlPDO->exec($str);

	$str="insert into ".$dbname.".kebun_prestasi_detail
		(`notransaksi`,`noreferensi`,`nik`,`kodeorg`,`tahuntanam`,`hasilkerja`,`hasilkerjakg`,`jumlahhk`,`upahkerja`,`upahpenalty`,`upahpremilebihbasis`,`upahpremi`,`rupiahpenalty`,`luaspanen`,`brondolan`,`premibrondol`,`nospb`,`updateby`)
	values ".$datadetail2.";";
	$owlPDO->exec($str);

	if (isset($JurnalM[$lokasitugasx[$karyd]])) {
		foreach ($JurnalM as $unitz => $urutxz) {
			$str="update ".$dbname.".keu_5kelompokjurnal set nokounter = '".$urutxz."' where kodeorg='".$indukunit[$unitz]."' and kodekelompok='M' and kodeunit='".$unitz."' and periode='".$prd."'";
			$owlPDO->exec($str);
		}
	}
	#============= End Insert Data ke Table Kebun Aktifitas ===============


	#============= End Insert Data ke Table Kebun Aktifitas ===============
	#======================== Cek data jurnal =============================
	
	foreach($kodeOrganisasi as $unity){

		// $str="select sum(debet-kredit) as rpj from ".$dbname.".keu_jurnaldt_vw where nojurnal in ('".$nojurnal[$unity]."','".$nojurnal2[$unity]."') and noreferensi='".$notransaksi."' and noakun in ('".$resParam[0]['noakundebet']."','".$resParam2[0]['noakundebet']."','".$resParam3[0]['noakundebet']."','1261004')"; 
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// $bar=$res->fetch();
		// $rpj[$unity]=$bar['rpj'];

		$varian=($totaldapatbersihJurnal[$unity]-$totaldapatbersih[$unity]);
		if($varian > 2 or $varian < (-2)){
			throw new PDOException("Nilai Jurnal ".$unity." : ".number_format($rpj[$unity])." tidak sama dengan nilai transaksi : ".number_format($totaldapatbersih[$unity]));
		}			
		
		$str="select sum(upahpremilebihbasis+premibrondol+upahpremi+upahkerja-rupiahpenalty-upahpenalty) as prelb from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where b.noreferensi='".$notransaksi."' and b.kodeorg = '".$unity."'"; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$prelb=$bar['prelb'];
		
		$varianx=($prelb-$totaldapatbersih[$unity]);
		if($varianx > 2 or $varianx < (-2)){
			throw new PDOException("Nilai Kegiatan Panen : ".number_format($prelb)." tidak sama dengan nilai transaksi : ".number_format($totaldapatbersih[$unity]));
		}

		#======================== End data jurnal =============================
	
		#============================= Update ==================================
		# Update Counter
		$str="update ".$dbname.".keu_5kelompokjurnal set nokounter = '".$konter[$unity]."' where kodeorg='".getindukPT($unity)."' and kodekelompok='".$kodeJurnal."' and kodeunit='".$unity."' and periode='".$prd."'";
		$owlPDO->exec($str);
	
		$str="update ".$dbname.".keu_5kelompokjurnal set nokounter = '".$konter2[$unity]."' where kodeorg='".getindukPT($unity)."' and kodekelompok='PNN03' and kodeunit='".$unity."' and periode='".$prd."'";
		$owlPDO->exec($str);
	}

	# Update flag transaksi
	$str="update ".$dbname.".kebun_3premipemanen_v2 set posting='1', jurnal = '".$penampungNojurnal.",".$penampungNojurnal2."', postingby ='".$_SESSION['standard']['userid']."',
		  postingdate='".$tglEntry."' where notransaksi='".$notransaksi."'";
	$owlPDO->exec($str);
	
	#=========================== End Update ===============================
	$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}

?>