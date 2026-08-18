<?php

session_start();
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$tanggalkerja = tanggalsystem(checkPostGet('tanggalkerja', ''));
$notrans = checkPostGet('notrans', '');
$alamatcuti = checkPostGet('alamatcuti', '');
$pengganti = checkPostGet('pengganti', '');
$hometrip = checkPostGet('hometrip', '');
$nohp = checkPostGet('nohp', '');
$tglberangkat = tanggalsystem(checkPostGet('tglberangkat', ''));
$rutekeberangkatan = checkPostGet('rutekeberangkatan', '');
$tglpulang = tanggalsystem(checkPostGet('tglpulang', ''));
$rutekepulangan = checkPostGet('rutekepulangan', '');
$proses = checkPostGet('proses', '');
$tglijin = tanggalsystem(checkPostGet('tglijin', ''));
$jnsIjin = checkPostGet('jnsIjin', '');
$jamDr = checkPostGet('jamDr', '');
$jamSmp = checkPostGet('jamSmp', '');
$keperluan = checkPostGet('keperluan', '');
$ket = checkPostGet('ket', '');
$atasan = checkPostGet('atasan', '');
$atasan2 = checkPostGet('atasan2', '');
$tglAwal = explode("-", checkPostGet('tglAwal', '00-00-0000'));
$tglAwalrel = explode("-", checkPostGet('tglAwalreal', '00-00-0000'));
@$tgl1 = $tglAwal[2] . "-" . $tglAwal[1] . "-" . $tglAwal[0];
@$tgl1real = $tglAwalrel[2] . "-" . $tglAwalrel[1] . "-" . $tglAwalrel[0];
$tglEnd = explode("-", checkPostGet('tglEnd', '00-00-0000'));
$tglEndrel = explode("-", checkPostGet('tglEndreal', '00-00-0000'));
@$tgl2 = $tglEnd[2] . "-" . $tglEnd[1] . "-" . $tglEnd[0];
@$tgl2real = $tglEndrel[2] . "-" . $tglEndrel[1] . "-" . $tglEndrel[0];
$jamDr1 = $tgl1 . " " . $jamDr;
$jamSmp1 = $tgl2 . " " . $jamSmp;
$jamDr1real = $tgl1real . " " . $jamDr;
$jamSmp1real = $tgl2real . " " . $jamSmp;
$arrNmkary = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$arrKeputusan = array("0" => $_SESSION['lang']['diajukan'], "1" => $_SESSION['lang']['disetujui'], "2" => $_SESSION['lang']['ditolak'], "9" =>'Proses Pengajuan');
$where = " tanggal='" . $tglijin . "' and karyawanid='" . $_SESSION['standard']['userid'] . "'";
$atsSblm = checkPostGet('atsSblm', '');
$atsSblm2 = checkPostGet('atsSblm2', '');
$namafile = checkPostGet('namafile', '');
$hk = checkPostGet('jumlahhk', '');
$hrd = checkPostGet('hrd', '');
$karyawanid = checkPostGet('karyawanid', '');
$tanggal = checkPostGet('tanggal', '');
$periodec = checkPostGet('periodec', '');
$tglreal = checkPostGet('tglreal', '');
$tglAwalreal = checkPostGet('tglAwalreal', '');
$tglEndreal = checkPostGet('tglEndreal', '');
$jam1real = checkPostGet('jam1real', '');
$mnt1real = checkPostGet('mnt1real', '');
$jam2real = checkPostGet('jam2real', '');
$mnt2real = checkPostGet('mnt2real', '');
$per['persetujuan1']=checkPostGet('persetujuan1', '');
$per['persetujuan2']=checkPostGet('persetujuan2', '');
$per['persetujuan3']=checkPostGet('persetujuan3', '');
$per['persetujuan4']=checkPostGet('persetujuan4', '');

$alasanbatalcuti = checkPostGet('alasanbatalcuti', '');
$alasanbatalcuti = replaceEnter($alasanbatalcuti," ");


$jumlahlevel = checkPostGet('jumlahlevel', '');

$jenispersetujuan="IJS";

if($proses == 'update' or $proses == 'insert'){

	if($jnsIjin == 'CUTI08'){

		$tglGetCuti = tanggalsystem(checkPostGet('tglAwal',''));
		$cekValTanggal = (strtotime($tglGetCuti) - strtotime($tglijin))/(60*60*24);
		/*if($cekValTanggal < 30){
			exit("Gagal : Tanggal cuti harus lebih besar 30 hari dari tanggal pengajuan cuti..");
		}*/
		$strf = "select sisa,hakcuti,diambil from " . $dbname . ".sdm_cutiht where karyawanid=" . $_SESSION['standard']['userid'] . " 
                    and periodecuti=" . $periodec;
		$res=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$barf = $res->fetch();
		$sisa = '';
		$hakcuticek = 0;
		$diambilcek = 0;
		$hakcutiprdmax = 0;
		$sisathnlalu = 0;
		$sisa = $barf->sisa;
		$hakcuticek = $barf->hakcuti;
		$diambilcek = $barf->diambil;

		if ($sisa == '')
			$sisa = 0;

		//Hak cuti thn lalu max HK adalah 6 bila sisa hak cuti > 6 & periode thn sblmnya
		$thnskrg = date('Y');
		if ($sisa > 6 && $periodec < $thnskrg) {
			$hakcutiprdmax = 6;
		}else{
			$hakcutiprdmax = $sisa;
		}
		$periodec1 =$periodec + 1;
		$strf = selectQuery($dbname, "sdm_cutidt", "sum(jumlahcuti) as sisadt", "karyawanid='".$_SESSION['standard']['userid']."' AND left(daritanggal,6) = '".$periodec1."' AND periodecuti = '".$periodec."'");
		$res=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$barf = $res->fetch();
		$diambilcekdt = 0;
		$diambilcekdt = $barf->sisadt;

		//$hk adalah jml pengajuan dari s/d tgl
		$totalhkinputdt = $diambilcekdt + $hk;

		//validasi hk >6
		if ($periodec < $thnskrg) {
			#################
			$karyawanid = $_SESSION['standard']['userid'];
			$hariini = date("Y-m-d");
			$str1="select a.*,b.namakaryawan,b.tanggalmasuk, b.nik
				from ".$dbname.".sdm_cutiht a
				left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
				where 1=1
				and a.periodecuti='".$periodec."' 
				and a.karyawanid = '".$karyawanid."'
				and (tanggalkeluar='0000-00-00' or tanggalkeluar>='".$hariini."') order by b.namakaryawan"; 
			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_OBJ);
			$res1->setFetchMode(PDO::FETCH_ASSOC);
				$bar = $res1->fetch();
				$diambil=$bar['diambil'];
				$hakcuti=$bar['hakcuti'];
				$sisa=$bar['sisa'];

			//Get Personalia by notransaksi : 
			$sdhambil=0;
			$str="select a.notransaksi,a.jumlahhari,a.statuspersetujuan,b.idjenis,b.statuspotongan from ".$dbname.".sdm_ijin a 
			left join ".$dbname.".sdm_5jenisijin b on a.idjenis=b.idjenis  where karyawanid='".$karyawanid."' and periodecuti='".$periodec."' and a.statuspersetujuan_cancel!='1' and a.statuspersetujuan !='2' order by notransaksi asc";
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
			left join ".$dbname.".sdm_5jenisijin b on a.idjenis=b.idjenis  where karyawanid='".$karyawanid."' and periodecuti='".$periodec."' and a.statuspersetujuan_cancel!='1' and a.statuspersetujuan !='2' order by darijam asc";
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
					where karyawanid='".$karyawanid."' and periodecuti='".$periodec."' and a.statuspersetujuan_cancel = '0' order by notransaksi asc";
			$res=fetchdata($str);
			foreach ($res as $key => $val){
				if($val['stpersetujuan4']=='1' && ($val['statuspotongan']!='0')){
					$sdhambil+=$val['jumlahhari'];
				}
			}

			$sisaprdsblmnya=$sisaprdsblm-$sdhambil;

			$periodenext = $periodec+1;
			$sisaprdsblm = $hakcuti;
			$sisaprdsblm = $sisaprdsblm - $sdhambil;
			if (substr($jmDr,0,4) != $periodec) {//jika tanggal pengajuan tidak sesuai dengan periode cuti yang dipilih
				if($sdhambil2[$periodec] == 12){//jika sisa cuti di periode yang dipilih sudah kosong
					$sisaprdsblm = 0 ;
				}else if($sdhambil2[$periodec] > 6 ){// apabila jatah cuti periode yang dipilih di tahun yang sama dengan periode melebihi 6
					$sisaprdsblm = $hakcuti - $sdhambil2[$periodec];
				}else{
					$sisaprdsblm = 6;
				}

				$sisaprdsblm = $sisaprdsblm - $sdhambil2[$periodenext];

			}else{
				$sisaprdsblm = $sisaprdsblm - $sdhambil2[$periodec];

			}
			##################
			if($hk > $sisaprdsblm){
			exit("warning : Cuti periode ".$periodec." yang dapat diambil tersisa : ".$sisaprdsblm." HK. Jumlah cuti yang bisa diambil dari periode tahun sebelumnya adalah maksimal 6 HK. Mohon input sesuai jumlah cuti yang tersisa.");
			}
		}
		

		//=============================      
		#ambil periode cuti terakhir
		$lastp = '';
		$strfx = "select max(periodecuti) as periodecuti from " . $dbname . ".sdm_cutiht 
					where karyawanid=" . $_SESSION['standard']['userid'];
		$resx=$owlPDO->query($strfx) or die(print " Gagal: ".PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_OBJ);
		while ($barx = $resx->fetch()) {
			$lastp = $barx->periodecuti;
		}

		//VALIDASI POIN 1 
		//b.Ambil cuti periode sblmnya hanya diterima sebelum bulan july
		// $thnskrg = date('Y');
		// periode cuti 2021, cuti 2021-12-24, diajukan 2022-01-03 akan kena trap kalo pake $thnskrg = date('Y');
		$thnskrg = $tglAwal[2];
		if ($periodec < $thnskrg && $tglAwal[1] > 6 && $tglEnd[1] > 6) {
			exit("Warning: Sisa jatah cuti tahun " . $periodec . " hanya dapat diteruskan/dibawa sampai akhir bulan Juni ".($periodec+1).".");
		}
		
		if ($periodec < $thnskrg && $totalhkinputdt > 6) {
			exit("Warning: Jumlah HK(Hari) Yang Bisa Diambil Maksimal 6 HK di Periode Tahun ".$periodec."");
		}

		#periksa apakah HRD tidak lupa setting saldo awal cuti
		$tahunmulaiCuti = substr($_SESSION['empl']['signdate'], 0, 4) + 1;
		$tanggalAwalCuti = $tahunmulaiCuti . substr($_SESSION['empl']['signdate'], 4, 6);


		//periksa apakah mengajukan cuti sebelum periode cuti berjalan
		$zz = substr($tgl1, 0, 4);

		if (($lastp < $zz and $lastp != '') or ( $lastp == '' and date_format(date_create($tanggalAwalCuti),'Y-m-d')) < date_format(date_create(substr($tgl1, 0, 10)),'Y-m-d')) {

			//insert cuti baru dan ubah sisa   
			#ambil tanggal masuk
			$str1 = "select karyawanid,namakaryawan,tanggalmasuk,lokasitugas,tipekaryawan from " . $dbname . ".datakaryawan
							where  karyawanid='" . $_SESSION['standard']['userid'] . "'";

			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_OBJ);
			while ($bar1 = $res1->fetch()) {
				//=================================
				//default
				$x = readTextFile('config/jumlahcuti.lst');
				if (intval($x) > 0)
					$hakcuti = $x;
				else
					$hakcuti = 12;  //default
					
				#jika bukan orang HO maka dapat 
				if ($bar1->tipekaryawan == 0 and substr($bar1->lokasitugas, 2, 2) != 'HO')
					$hakcuti = 12;
				else if ($bar1->tipekaryawan != 0 and substr($bar1->lokasitugas, 2, 2) != 'HO')
					$hakcuti = 12;
				$sisa = $hakcuti;

				//lanjut jika tahun pertama
				if (substr($bar1->tanggalmasuk, 0, 4) >= $zz) {
					continue; //tidak melakukan apa apa, karena belum berhak dapat cuti
				}

				//=================================
				$tgl = substr(str_replace("-", "", $bar1->tanggalmasuk), 4, 4);
				$dari = mktime(0, 0, 0, substr($tgl, 0, 2), substr($tgl, 2, 2), $zz);
				$dari = date('Ymd', $dari);
				$sampai = mktime(0, 0, 0, substr($tgl, 0, 2), substr($tgl, 2, 2), $zz + 1);
				$sampai = date('Ymd', $sampai);

				#jika periode masuk masih belum 1tahun maka 0
				$d = substr(str_replace("-", "", $bar1->tanggalmasuk), 0, 4);

				#ambil sisa cuti YBS
				$str = "select sisa from " . $dbname . ".sdm_cutiht where karyawanid=" . $bar1->karyawanid . " 
												   and periodecuti>" . ($periodec - 2) . " order by periodecuti desc limit 1";
				// echo $str;
				// exit('error');		

				$resx=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$resx->setFetchMode(PDO::FETCH_OBJ);
				$sisalalu = 0;
				while ($barx = $resx->fetch()) {
					$sisalalu = $barx->sisa;
				}
				#periksa apakah sudah ada pada periode yang sama
				$str = "select * from " . $dbname . ".sdm_cutiht where karyawanid=" . $bar1->karyawanid . " 
												   and periodecuti=" . $periodec . " order by periodecuti desc limit 1";

				// echo $str;
				// exit('error');	

				$resy=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$numrows=owlBaris($resy);
				// print_r($sisalalu);
				// echo "msk";
				// exit('error');
				if ($numrows > 0) {
					#berarti  saldo saat ini adalah sisalalu
					#$saldo=$sisalalu;
					#tidak ada perubahan
				} else {
					$saldo = $hakcuti;
					#==========================periksa apakah sudah ada pengambilan cuti sebelum ada header (sebelum cuti baru muncul)
					$strx = "select sum(jumlahcuti) as diambil from " . $dbname . ".sdm_cutidt
																where karyawanid=" . $bar1->karyawanid . "
																 and  daritanggal >=" . $dari . " and daritanggal<=" . $sampai;
					// echo $strx;
					// exit('warning');
					$diambil = 0; #sudah diambil diambil tahun ini
					$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
					$resx->setFetchMode(PDO::FETCH_OBJ);
					while ($barx = $resx->fetch()) {
						$diambil = $barx->diambil;
						if ($diambil == '')
							$diambil = 0;
					}
					$saldo = $saldo - $diambil;

					// echo $diambil;
					// exit('warning');	
					// echo $saldo;
					// exit('warning');
					$sisa = $saldo;
					#================================================================
					#maka insert periode baru
					$str = "insert into " . $dbname . ".sdm_cutiht(kodeorg, karyawanid, periodecuti, keterangan, dari, sampai, hakcuti, diambil, sisa)
													   values('" . $bar1->lokasitugas . "'," . $bar1->karyawanid . "," . $periodec . ",''," . $dari . "," . $sampai . "," . $hakcuti . ",0," . $saldo . ")";
					try{
						var_dump($owlPDO->exec($str)); 
					}catch (PDOException $e){
						// echo $e->getMessage();
					}
				}
			}
		}

		// function getRangeTanggal($tglAwal, $tglAkhir) {
		// 	$jlh = strtotime($tglAkhir) - strtotime($tglAwal);
		// 	$jlhHari = $jlh / (3600 * 24);
		// 	return $jlhHari + 1;
		// }

		if (getRangeTanggal($_POST['tglAwal'], $_POST['tglEnd']) <= 0) {
			exit("Gagal : Periksa kembali periode tanggal cuti. Tanggal Awal lebih besar dari tanggal sampai.");
		}

		// if (getRangeTanggal($_POST['tglAwal'], $_POST['tglEnd']) != $hk) {
			// exit("Gagal : Periksa kembali periode tanggal cuti, tidak sesuai dengan jumlah HK yang diambil.");
		// }
		$strf = "select sisa from " . $dbname . ".sdm_cutiht where karyawanid=" . $_SESSION['standard']['userid'] . " 
						   and periodecuti=" . $periodec;

		// echo $strf;
		// exit('error');
		$res=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);

		$sisa = 0;
		while ($barf = $res->fetch()) {
			$sisa = $barf->sisa;
		}
		if ($sisa == '')
			$sisa = 0;

		if ($hk > $sisa) {
			exit("Gagal : Jumlah HK(Hari) melebihi jumlah sisa cuti untuk periode " . $periodec . ".");
		}
	}
	
	if(($jnsIjin=='CUTI09' || $jnsIjin=='CUTI06' || $jnsIjin=='CUTI05')){
		$strf = "select sisa from " . $dbname . ".sdm_5cutilainht where karyawanid=" . $_SESSION['standard']['userid'] . " 
                    and periodecuti=".$periodec." and jeniscuti='".$jnsIjin."'";
		$res=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$sisa = '';
		while ($barf = $res->fetch()) {
			$sisa = $barf->sisa;
		}
		if ($sisa == '')
			$sisa = 0;

		//=============================      
		#ambil periode cuti terakhir
		$lastp = '';
		$strfx = "select max(periodecuti) as periodecuti from " . $dbname . ".sdm_5cutilainht 
					where karyawanid=".$_SESSION['standard']['userid']." and jeniscuti='".$jnsIjin."'";
		$resx=$owlPDO->query($strfx) or die(print " Gagal: ".PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_OBJ);
		while ($barx = $resx->fetch()) {
			$lastp = $barx->periodecuti;
		}
		#periksa apakah HRD tidak lupa setting saldo awal cuti
		$tahunmulaiCuti = substr($_SESSION['empl']['signdate'], 0, 4) + 1;
		$tanggalAwalCuti = $tahunmulaiCuti . substr($_SESSION['empl']['signdate'], 4, 6);

		//periksa apakah mengajukan cuti sebelum periode cuti berjalan
		$zz = substr($tgl1, 0, 4);
		if (($lastp < $zz and $lastp != '') or ( $lastp == '' and $tanggalAwalCuti < substr($tgl1, 0, 10))) {
			//insert cuti baru dan ubah sisa   

			#ambil tanggal masuk
			$str1 = "select karyawanid,namakaryawan,tanggalmasuk,lokasitugas from " . $dbname . ".datakaryawan
							where  karyawanid='" . $_SESSION['standard']['userid'] . "'";
			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_OBJ);
			while ($bar1 = $res1->fetch()) {
				//=================================
				//default
				$x = readTextFile('config/jumlahcuti.lst');
				if (intval($x) > 0)
					$hakcuti = $x;
				else
					$hakcuti = 12;  //default
	#jika bukan orang HO maka dapat 
				if ($bar1->tipekaryawan == 0 and substr($bar1->lokasitugas, 2, 2) != 'HO')
					$hakcuti = 12;
				else if ($bar1->tipekaryawan != 0 and substr($bar1->lokasitugas, 2, 2) != 'HO')
					$hakcuti = 12;
				$sisa = $hakcuti;

				//lanjut jika tahun pertama
				if (substr($bar1->tanggalmasuk, 0, 4) >= $zz) {
					continue; //tidak melakukan apa apa, karena belum berhak dapat cuti
				}

				//=================================
				$tgl = substr(str_replace("-", "", $bar1->tanggalmasuk), 4, 4);
				$dari = mktime(0, 0, 0, substr($tgl, 0, 2), substr($tgl, 2, 2), $zz);
				$dari = date('Ymd', $dari);
				$sampai = mktime(0, 0, 0, substr($tgl, 0, 2), substr($tgl, 2, 2), $zz + 1);
				$sampai = date('Ymd', $sampai);
				#jika periode masuk masih belum 1tahun maka 0
				$d = substr(str_replace("-", "", $bar1->tanggalmasuk), 0, 4);
				#ambil sisa cuti YBS
				$str = "select sisa from " . $dbname . ".sdm_5cutilainht where karyawanid=" . $bar1->karyawanid . " 
												   and periodecuti>" . ($periodec - 2) . " and jeniscuti='".$jnsIjin."' order by periodecuti desc limit 1";
				$resx=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$resx->setFetchMode(PDO::FETCH_OBJ);
				$sisalalu = 0;
				while ($barx = $resx->fetch()) {
					$sisalalu = $barx->sisa;
				}


				#periksa apakah sudah ada pada periode yang sama
				$str = "select * from " . $dbname . ".sdm_5cutilainht where karyawanid=" . $bar1->karyawanid . " 
												   and periodecuti=" . $periodec . " and jeniscuti='".$jnsIjin."' order by periodecuti desc limit 1";
				$resy=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$numrows=owlBaris($resy);
				if ($numrows > 0) {
					#berarti  saldo saat ini adalah sisalalu
					#$saldo=$sisalalu;
					#tidak ada perubahan
				} else {
					$saldo = $hakcuti;
					#==========================periksa apakah sudah ada pengambilan cuti sebelum ada header (sebelum cuti baru muncul)
					$strx = "select sum(jumlahcuti) as diambil from " . $dbname . ".sdm_5cutilaindt
																where karyawanid=" . $bar1->karyawanid . "
																 and  daritanggal >=" . $dari . " and daritanggal<=" . $sampai;
					$diambil = 0; #sudah diambil diambil tahun ini
					$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
					$resx->setFetchMode(PDO::FETCH_OBJ);
					while ($barx = $resx->fetch()) {
						$diambil = $barx->diambil;
						if ($diambil == '')
							$diambil = 0;
					}
					$saldo = $saldo - $diambil;
					$sisa = $saldo;
					#================================================================
					#maka insert periode baru and jeniscuti='".$jnsIjin."'
					$str = "insert into " . $dbname . ".sdm_5cutilainht(kodeorg, karyawanid, periodecuti, dari, sampai, hakcuti, diambil, sisa,jeniscuti)
													   values('" . $bar1->lokasitugas . "'," . $bar1->karyawanid . "," . $periodec . "," . $dari . "," . $sampai . "," . $hakcuti . ",0," . $saldo . ",'".$jnsIjin."')";
					try{
						$owlPDO->exec($str); 
					}catch (PDOException $e){
						
					}
				}
			}
		}

		

		if (getRangeTanggal($_POST['tglAwal'], $_POST['tglEnd']) <= 0) {
			exit("Gagal : Periksa kembali periode tanggal cuti. Tanggal Awal lebih besar dari tanggal sampai.");
		}

	
	
			
		// if (getRangeTanggal($_POST['tglAwal'], $_POST['tglEnd']) != $hk) {
			// exit("Gagal : Periksa kembali periode tanggal cuti, tidak sesuai dengan jumlah HK yang diambil.");
		// }

		$strf = "select sisa from " . $dbname . ".sdm_5cutilainht where karyawanid=" . $_SESSION['standard']['userid'] . " 
						   and periodecuti=" . $periodec;
		$res=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);

		$sisa = 0;
		while ($barf = $res->fetch()) {
			$sisa = $barf->sisa;
		}
		if ($sisa == '')
			$sisa = 0;

		// if ($hk > $sisa) {
		// 	exit("Gagal : Jumlah HK(Hari) melebihi jumlah sisa cuti untuk periode " . $periodec . ".");
		// }
	}



	// if($jnsIjin != 'CUTI08'){
	// 	//Get HK hanya echo
	// 	$daritgl=$tglAwal[2].'-'.$tglAwal[1].'-'.$tglAwal[0];
	// 	$sampaitgl=$tglEnd[2].'-'.$tglEnd[1].'-'.$tglEnd[0];

	// 	$jarakwaktu=strtotime($sampaitgl)-strtotime($daritgl);
	// 	$jarakwaktu =  $jarakwaktu/60/60/24;
	// 	$jmlhk = $jarakwaktu;
	// 	//Get Jenis Cuti
	// 	$optjnsIjin = makeOption($dbname,'sdm_5jenisijin','idjenis,jenisijin');
	// 	// print_r($optjnsIjin);

	// 		echo "Jenis Cuti ".$optjnsIjin[$jnsIjin]." dengan Jumlah HK Diambil : ".$jmlhk." hari. Tidak memotong hak cuti tahunan.";				
	// }

}

//Get HK hanya echo
$daritgl=$tglAwal[2].'-'.$tglAwal[1].'-'.$tglAwal[0];
$sampaitgl=$tglEnd[2].'-'.$tglEnd[1].'-'.$tglEnd[0];

//Get Jenis Cuti
$optjnsIjin = makeOption($dbname,'sdm_5jenisijin','idjenis,jenisijin');

function getRangeTanggal($tglAwal, $tglAkhir) {
			$jlh = strtotime($tglAkhir) - strtotime($tglAwal);
			$jlhHari = $jlh / (3600 * 24);
			return $jlhHari + 1;
		}

switch ($proses) {
    case'insert':

    #periksa lokasi tugas sesuai table datakaryawan atau tidak (user)
	$strValidUser = "select * from ".$dbname.".datakaryawan where karyawanid='".$_SESSION['standard']['userid']."'";
	$dataValidUser = fetchData($strValidUser);

	if($dataValidUser[0]['lokasitugas'] != $_SESSION['empl']['lokasitugas']) {
		exit("Warning : Lokasi tugas anda sekarang ".$_SESSION['empl']['lokasitugas'].", harus pindah lokasi tugas sesuai datakaryawan di ".$dataValidUser[0]['lokasitugas']."");
	}

    			$tglawalcuti = tanggalsystem(checkPostGet('tglAwal',''));
				$tglakhircuti = tanggalsystem(checkPostGet('tglEnd',''));
    try {
	$owlPDO->beginTransaction();

			if (($tglijin == '') || ($tanggalkerja=='') || ($jnsIjin == '') || ($jamDr1 == '') || ($jamSmp1 == '') || ($keperluan == '') || ($nohp == '')) 
			{
				// echo"warning:Please Complete The Form"; //alamat ; $alamatcuti pengganti : $pengganti jenis $jnsIjin jamdr1 $jamDr1 jamsampe $jamSmp1 keperluan $keperluan, tglijin $tglijin tanggalkerja $tanggalkerja";
				throw new PDOException("Please Complete The Form.");
			}
			
			if ($jnsIjin != 'CUTI08') {
				$str = makeOption($dbname, 'sdm_5hakcutijenis', 'jenisijin,hakcuti',"jenisijin = '$jnsIjin'");
				
				$jumlahhari = selisitgl($daritgl,$sampaitgl)+1;
				$n = $jumlahhari;
				$tglcuti='';
				$no="";
				for ($i=0; $i < $n ; $i++) { 

					$whr=" and (kebun='GLOBAL' or kebun='".$_SESSION['empl']['lokasitugas']."')";
					if ($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
						$whr=" and (kebun='GLOBAL' or kebun='HOLDING' or kebun='".$_SESSION['empl']['lokasitugas']."')";
					}

					#cek apakah tanggal termasuk hari libur
					$tglcuti= date("Ymd",strtotime("+".$i." Day",strtotime($daritgl)));	
					$sql="select * from ".$dbname.".sdm_5harilibur where tanggal='".$tglcuti."'".$whr;
					$res=fetchData($sql);
					$jmlhbaris=count($res);
					if ($jmlhbaris>0) {
						$jumlahhari=$jumlahhari-1;
					}
					$no++;	
				}
					
				if (count($str) > 0 && $jumlahhari > $str[$jnsIjin]){			

					
					throw new PDOException("Anda menginput jumlah cuti lebih dari hari yang ditentukan. Jatah cuti ".$optjnsIjin[$jnsIjin]." sebanyak: ".$str[$jnsIjin]." hari. Mohon agar menginput sesuai jatah cuti.");
				}
			}
			// $selisihari = (((strtotime (tanggalsystem(tanggalnormal($jamDr1))) - strtotime ($tglijin)))/(60*60*24));;
			
			// if($selisihari < 30)
			// {
			// 	exit("Warning : Tanggal cuti harus lebih besar 30 hari dari tanggal pengajuan.");
			// }
			
			$counttrip = 0;
			if($hometrip=='true')
			{
				if($tglberangkat==''||$rutekeberangkatan==''||$tglpulang==''||$rutekepulangan=='')
				{
					//echo"warning:Please Complete The Form.";
					throw new PDOException("Please Complete The Form.");
				}
				
				#COUNT HOMETRIP
				$str="select count(karyawanid) as counttrip from ".$dbname.".sdm_ijin where karyawanid='".$_SESSION['standard']['userid']."' and periodecuti = '".$periodec."' and hometrip='1' and stpersetujuanhrd=1";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar = $res->fetch();
				$counttrip = $bar['counttrip'];
				
				##JATAH HOMETRIP
				$str="select * from ".$dbname.".sdm_karyawankeluarga where karyawanid='".$_SESSION['standard']['userid']."'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$countemplasment = 0;
				while($bar = $res->fetch())
				{
					if($bar['emplasment']=='1')
					{
						$countemplasment = $countemplasment + 1;
					}
				}
				if($countemplasment > 0)
				{
					$jatahtrip = 1;
				}
				else
				{
					$jatahtrip = 2;
				}
				
				if($counttrip >= $jatahtrip)
				{
					
					throw new PDOException("Quta Hometrip sudah melebihi batas. (".$jatahtrip." kali.");
				}
			}
			
			// $getPotHK = makeOption($dbname,'sdm_5jenisijin','idjenis,potonganhk',"idjenis='".$jnsIjin."'");
			// $potHk = $getPotHK[$jnsIjin];
			
			// $hk = $potHk * $hk;

			#periksa apakah periode yang diambil sudah lewar 1.5 tahun
			if ($jnsIjin == 'CUTI08') {

				#periksa apakah sudah boleh cuti:
				// $tahunmulaiCuti = substr($_SESSION['empl']['signdate'], 0, 4) + 1;
				// $tanggalAwalCuti = $tahunmulaiCuti . substr($_SESSION['empl']['signdate'], 4, 6);

				#kebijakan baru karyawan boleh cuti ketika sudah lewat 3 bulan dari masuk kerja
				$sKaryawan = "select tanggalmasuk,tanggalpengangkatan, statuskaryawan from " . $dbname . ".datakaryawan
				where  karyawanid='" . $_SESSION['standard']['userid'] . "'";
				$rKaryawan=$owlPDO->query($sKaryawan);
				$rowKaryawan = $rKaryawan->fetch();
				$tanggalMasukEmpl = $rowKaryawan['tanggalmasuk'];
				if ($rowKaryawan['tanggalpengangkatan'] == "" || $rowKaryawan['tanggalpengangkatan'] == "0000-00-00") {
					$tanggalBolehCutiEmpl = date('Y-m-d', strtotime($tanggalMasukEmpl. ' + 3 month'));
					if($rowKaryawan['statuskaryawan']=='Kontrak'){
						$sKaryawan = "select tanggalmasuk,tanggalpengangkatan, statuskaryawan from " . $dbname . ".datakaryawan_hist where  karyawanid='" . $_SESSION['standard']['userid'] . "' and version_type='B' and approval_status='8' and statuskaryawan='Kontrak' and periodegaji<='".substr($tanggalBolehCutiEmpl,0,7)."' order by periodegaji desc";
						$rKaryawan=$owlPDO->query($sKaryawan);
						$rowKaryawan = $rKaryawan->fetch();
						$tanggalMasukEmpl = $rowKaryawan['tanggalmasuk'];
						if ($tanggalMasukEmpl != '') $tanggalBolehCutiEmpl = date('Y-m-d',strtotime($tanggalMasukEmpl . ' + 3 month'));
					}
					
				} else {
					$tanggalBolehCutiEmpl = $rowKaryawan['tanggalpengangkatan'];
				}
				if (substr($jamDr1, 0, 10) < $tanggalBolehCutiEmpl) {
					exit("Error: Anda belum memiliki hak cuti, hak cuti akan muncul pada tanggal: " . $tanggalBolehCutiEmpl);
				}
				$stt = "select sampai from " . $dbname . ".sdm_cutiht where periodecuti='" . $periodec . "' and
							karyawanid=" . $_SESSION['standard']['userid'];
				$rett=$owlPDO->query($stt) or die(print " Gagal: ".PDOException::getMessage());
				$rett->setFetchMode(PDO::FETCH_OBJ);
				while ($batt = $rett->fetch()) {
					$tanggalAkhir = str_replace("-", "", $batt->sampai);
				}
				$tahunAkh = intval(substr($tanggalAkhir, 0, 4));
				$bulanAkh = intval(substr($tanggalAkhir, 4, 2));
				$tanggalAkh = intval(substr($tanggalAkhir, 6, 2));

				$dudu = mktime(0, 0, 0, $bulanAkh + 6, $tanggalAkh, $tahunAkh+1);
				$akhirBanget = date('Y-m-d', $dudu);
				if (substr($jamSmp1, 0, 10) > $akhirBanget) {
					#keluarkan disini jika sudah lebih dari 1.5 tahun
					//exit("Error: Maaf, Cuti atas masa bakti tahun " . $periodec . " berakhir pada " . date('d-m-Y', $dudu));
					throw new PDOException("Error: Maaf, Cuti atas masa bakti tahun " . $periodec . " berakhir pada " . date('d-m-Y', $dudu));
				}

				#periksa apakah yang input ini bukan staff
				$qCektipekary = selectQuery($dbname, "datakaryawan", "tipekaryawan", "karyawanid='".$_SESSION['standard']['userid']."'");
				$resCektipekary = fetchData($qCektipekary);
				$makeOpttipekary = makeOption($dbname, "sdm_5tipekaryawan", 'id,tipe');
				$tipekary = $resCektipekary[0]['tipekaryawan'];
				if ($tipekary != 0) {
					throw new PDOException("Anda tidak bisa menginput cuti staff karena tipe karyawan anda adalah ".$makeOpttipekary[$tipekary]."");
				}
			}

			#==== end satu setengah tahun
			$thun=substr($tglijin,0,4);
			$blan=substr($tglijin,4,2);

			if ($jnsIjin=='CUTI18') {
				
				

				$tanggalarr=array();
				$stt = "select (jumlahharidayoff-(diambil+akandiambil)) as sisa,tanggaldayoff,tanggalberlakusampai,jumlahharidayoff,(diambil+akandiambil) as dipakai,notransaksi from ".$dbname.".sdm_dayoff_dt_vw where karyawanid='".$_SESSION['standard']['userid']."' 
						and tanggaldayoff <'".$tglawalcuti."' and tanggalberlakusampai >='".$tglawalcuti."' and status='1' and (jumlahharidayoff-(diambil+akandiambil)) > 0 order by tanggaldayoff asc";
				$rett=$owlPDO->query($stt) or die(print " Gagal: ".PDOException::getMessage());
				$rett->setFetchMode(PDO::FETCH_OBJ);
				$nox=0;
				while ($batt = $rett->fetch()) {
					$tanggalarr[$nox]['tanggalawal']=$batt->tanggaldayoff;
					$tanggalarr[$nox]['tanggalakhir']=$batt->tanggalberlakusampai;
					$tanggalarr[$nox]['sisa']=$batt->sisa;
					$tanggalarr[$nox]['diambil']=0;
					$tanggalarr[$nox]['notransaksi']=$batt->notransaksi;
					$nox++;
				}

				$notrx=str_replace('-', '', $tglijin).$_SESSION['standard']['userid'];
				$rangetgl = rangeTanggal($tglawalcuti,$tglakhircuti);
				$whr=" and (kebun='GLOBAL' or kebun='".$_SESSION['empl']['lokasitugas']."')";
				if ($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
					$whr=" and (kebun='GLOBAL' or kebun='HOLDING' or kebun='".$_SESSION['empl']['lokasitugas']."')";
				}
				$jlhtglx=0;
				$jlhtglm=0;
				$arrupdate=array();
				foreach($rangetgl as $tglx){
					$str="select * from ".$dbname.".sdm_5harilibur where tanggal='".$tglx."'".$whr;
					$res=fetchData($str);
					$jmlhbaris=count($res);
					if ($jmlhbaris==0) {
					$jlhtglx++;
						for ($i=0; $i < $nox; $i++) { 
							if($tanggalarr[$i]['tanggalawal']<$tglx and $tanggalarr[$i]['tanggalakhir'] >= $tglx and $tanggalarr[$i]['sisa']>0 and $tanggalarr[$i]['diambil']==0)
							{
								$tanggalarr[$i]['diambil']=1;
								$arrupdate[$jlhtglm] = "update  " . $dbname . ".sdm_dayoff_dt set akandiambil='1' , notransaksicuti='".$notrx."' where notransaksi='".$tanggalarr[$i]['notransaksi']."' and tanggaldayoff='".$tanggalarr[$i]['tanggalawal']."' and tanggalberlakusampai='".$tanggalarr[$i]['tanggalakhir']."' and karyawanid='".$_SESSION['standard']['userid']."' ";
								//echo $supdate;
								
								$jlhtglm++;
								$i=$nox;
							}
						}
					}
				}
				
				
				if ($jlhtglx!=$jlhtglm) {
					//exit("Error: Hak Cuti Day Off anda untuk tanggal tertentu sudah habis/tidak mencukupi");
					throw new PDOException(" Hak Cuti Day Off anda untuk tanggal tertentu sudah habis/tidak mencukupi");
				}

				for ($i=0; $i < $jlhtglm; $i++) { 
					$supdate=$arrupdate[$i];
					
					$owlPDO->exec($supdate); 
					
				}


			}
			if ($jnsIjin=='CUTI09' || $jnsIjin=='CUTI06' || $jnsIjin=='CUTI05'){
				#periksa apakah sudah boleh cuti:
				$tahunmulaiCuti = substr($_SESSION['empl']['signdate'], 0, 4) + 1;
				$tanggalAwalCuti = $tahunmulaiCuti . substr($_SESSION['empl']['signdate'], 4, 6);
				if (substr($jamDr1, 0, 10) < $tanggalAwalCuti) {
					throw new PDOException(" Anda belum memiliki hak cuti, hak cuti akan muncul pada tanggal: " . $tanggalAwalCuti);
				}
				$stt = "select sampai from " . $dbname . ".sdm_5cutilainht where periodecuti='" . $periodec . "' and
							karyawanid=" . $_SESSION['standard']['userid'];
				$rett=$owlPDO->query($stt) or die(print " Gagal: ".PDOException::getMessage());
				$rett->setFetchMode(PDO::FETCH_OBJ);
				while ($batt = $rett->fetch()) {
					$tanggalAkhir = str_replace("-", "", $batt->sampai);
				}
				$tahunAkh = intval(substr($tanggalAkhir, 0, 4));
				$bulanAkh = intval(substr($tanggalAkhir, 4, 2));
				$tanggalAkh = intval(substr($tanggalAkhir, 6, 2));

				$dudu = mktime(0, 0, 0, $bulanAkh + 6, $tanggalAkh, $tahunAkh);
				$akhirBanget = date('Y-m-d', $dudu);
				if (substr($jamSmp1, 0, 10) > $akhirBanget) {
					#keluarkan disini jika sudah lebih dari 1.5 tahun
					
					throw new PDOException("Maaf, Cuti atas masa bakti tahun " . $periodec . " berakhir pada " . date('d-m-Y', $dudu));
				}
			}

			$tglcuti='';
			$echodata="";
			$ihari=$hk;
			if ($hk<1) {
				$ihari=1;
			}

			##pengecekan tanggal cuti yang dipilih sudah pernah diajukan atau belum
			for ($i=0; $i < $ihari ; $i++) { 

				$tglcuti= date("Ymd",strtotime("+".$i." Day",strtotime($tgl1)));

				$whrijin = " karyawanid='" . $_SESSION['standard']['userid'] . "' and '".$tglcuti."' between darijam and sampaijam  ";
				$sCek = "select tanggal from " . $dbname . ".sdm_ijin where  " . $whrijin . " and statuspersetujuan_cancel NOT IN ('1','2')";
				$res=fetchData($sCek);
				$jmlhbaris=count($res);
				if ($jmlhbaris>0) {
					$echodata="Tanggal cuti yang dipilih sudah pernah diajukan.";
				}
			}

			if ($echodata!="") {
				throw new PDOException('warning : '.$echodata);
			}

			##tgl
			$tglsdmijin =tanggalsystemn(checkPostGet('tglijin', ''));
			#pengecekan apakah tanggal pengajuan cuti sama
			$sCek = "select tanggal from " . $dbname . ".sdm_ijin where tanggal='" . $tglsdmijin . "' and karyawanid='" . $_SESSION['standard']['userid'] . "'"; //echo "warning:".$sCek;
			$res=fetchData($sCek);
			$rCek=count($res);
			if ($rCek>0) {
				
				throw new PDOException('Pengajuan cuti pada tanggal '.tanggalnormal($tglijin).' sudah ada. harap ajukan cuti ditanggal/hari lain.');
			}

			$wktu = "0000-00-00 00:00:00";
			if ($rCek < 1) {
				if ($atasan != '') {
					$wktu = date("Y-m-d H:i:s");
				}
				
				if($hometrip=='true')
				{
					$hmtrp = '1';
				}
				else
				{
					$hmtrp = '0';
				}

				$notrx=str_replace('-', '', $tglijin).$_SESSION['standard']['userid'];
				
				//hitung HK hanya utk echo & jml hk selain cuti tahunan
				$jarakwaktu=strtotime($sampaitgl)-strtotime($daritgl);
				$jarakwaktu =  $jarakwaktu/60/60/24+1;
				$jmlhk = $jarakwaktu;
				if ($hk == 0 || $hk == '') {
					$hk = $jmlhk;
				}

				$whrx=" and (kebun='GLOBAL' or kebun='".$_SESSION['empl']['lokasitugas']."')";
				if ($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
					$whrx=" and (kebun='GLOBAL' or kebun='HOLDING' or kebun='".$_SESSION['empl']['lokasitugas']."')";
				}

				$rangetglxz = rangeTanggal($tglawalcuti,$tglakhircuti);
				$jmlhk=0;
				foreach($rangetglxz as $tglx){
						$strxz="select * from ".$dbname.".sdm_5harilibur where tanggal='".$tglx."'".$whrx;
						$resxz=fetchData($strxz);
						$jmlhbaris=count($resxz);
						if ($jmlhbaris==0) {
							$jmlhk++;
						}
					}

					// cek jika cuti sebelumnya persetujuan belum disetujui semua belum bisa insert 
					$qPrevCuti = selectQuery($dbname, 'sdm_ijin', "*", "karyawanid='" . $_SESSION['standard']['userid'] . "' order by tanggal desc");
					$resPrevCuti = fetchData($qPrevCuti);
					$prevCuti = $resPrevCuti[0];

					// cek jika saldo cuti tahun ini sudah ada, tidak bisa input cuti tahun lalu
					$qPrevCuti2 = selectQuery($dbname, 'sdm_cutiht', "*", "karyawanid='".$_SESSION['standard']['userid']."' AND periodecuti='".$periodec."'");
					$resPrevCuti2 = fetchData($qPrevCuti2);

					if (count($resPrevCuti) > 0) {
						if ($prevCuti['statuspersetujuan'] == 0) {
							throw new PDOException("Untuk menginput cuti terbaru, cuti sebelumnya harus disetujui");
						} 
					}
					#if (substr($jamDr1,0,4) != date('Y', strtotime("-1 year")) && substr($jamSmp1,0,4) != date('Y', strtotime("-1 year"))) {
						$sIns = "insert into " . $dbname . ".sdm_ijin (notransaksi,karyawanid, tanggal, keperluan, keterangan, persetujuan1, 
						persetujuan4,waktupengajuan, darijam, sampaijam, idjenis,hrd,periodecuti,jumlahhari,
						alamatcuti,tanggalkerja,pengganti,nohp,hometrip,tanggalberangkat,rutekeberangkatan,tglpulang,rutekepulangan) 
						values ('".$notrx."','" . $_SESSION['standard']['userid'] . "','" . $tglijin . "','" . $keperluan . "','" . $ket . "',
						'" . $atasan . "','" . $atasan2 . "','" . $wktu . "','" . $jamDr1 . "','" . $jamSmp1 . "',
						'" . $jnsIjin . "','" . $hrd . "','" . $periodec . "'," . $jmlhk . ",
						'" . $alamatcuti . "','" . $tanggalkerja . "','" . $pengganti . "','".$nohp."','".$hmtrp."','".$tglberangkat."','".$rutekeberangkatan."','".$tglpulang."','".$rutekepulangan."')";

						$owlPDO->exec($sIns); 

						// $str="delete from ".$dbname.".approval where notransaksi='".$notrx."' and jenispersetujuan='".$jenispersetujuan."'";
						// $owlPDO->exec($str);
						
						// $optdepartmen=makeOption($dbname,'datakaryawan','karyawanid,bagian',"karyawanid='".$_SESSION['standard']['userid']."'");
						// $departemen=$optdepartmen[$_SESSION['standard']['userid']];

						// $optgol 	= makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan','aktif=1');
						// $gol=substr($optgol[$_SESSION['empl']['kodegolongan']],0,1);
						
						// $countApp = getCountApproval($jenispersetujuan,$_SESSION['empl']['lokasitugas'],$departemen,$gol);
						
						// for($i=1; $i<=$countApp; $i++){
						// 	$str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenispersetujuan."' and level='".$i."' and kodeunit='".$_SESSION['empl']['lokasitugas']."'";
						// 	// exit("error : $str");
						// 	$res=fetchData($str);
						// 	$tipeapp = $res[0]['tipe'];
						// 	$departemenapp = $res[0]['departemen'];
						// 	$tipekaryawanapp = $res[0]['tipekaryawan'];
						// 	$jabatanapp = $res[0]['jabatan'];
							
						// 	if(count($res) > 0){
						// 		if($tipeapp=='1'){
						// 			if($departemenapp!=''){
						// 				$str="select * from ".$dbname.".datakaryawan where bagian='".$departemenapp."'";
						// 				$res=fetchdata($str);
						// 				foreach($res as $keyx=>$valx){
						// 					$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notrx."','".$jenispersetujuan."','".$i."','".$valx['karyawanid']."','0')";
						// 					$owlPDO->exec($str);
						// 				}
						// 			}
						// 			if($tipekaryawanapp!=''){
						// 				$str="select * from ".$dbname.".datakaryawan where tipekaryawan='".$tipekaryawanapp."'";
						// 				$res=fetchdata($str);
						// 				foreach($res as $keyx=>$valx){
						// 					$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notrx."','".$jenispersetujuan."','".$i."','".$valx['karyawanid']."','0')";
						// 					$owlPDO->exec($str);
						// 				}
						// 			}
						// 			if($jabatanapp!='0'){
						// 				$str="select * from ".$dbname.".datakaryawan where kodejabatan='".$jabatanapp."'";
						// 				$res=fetchdata($str);
						// 				foreach($res as $keyx=>$valx){
						// 					if($per['persetujuan'.$i]!=''){
						// 						$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notrx."','".$jenispersetujuan."','".$i."','".$valx['karyawanid']."','0')";
						// 						$owlPDO->exec($str);
						// 					}
						// 				}
						// 			}
						// 		}else{
						// 			if($per['persetujuan'.$i]!=''){
						// 				$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notrx."','".$jenispersetujuan."','".$i."','".$per['persetujuan'.$i]."','0')";
						// 					$owlPDO->exec($str);
										
						// 			}
						// 		}
						// 	}
						// }
					#} else {
					#	$thnlalu = $periodec;
					#	throw new PDOException("Tidak bisa menginput cuti tahun ".$thnlalu." karena administrasi tahun ini sudah diproses");
					#}
					
					$countImage = count($_SESSION['buktiizin']);
					
					if($countImage>0)
					{
						foreach($_SESSION['buktiizin'] as $key=>$row)
						{
							$filetype = strtolower('.'.substr($row['namafile'],strripos($row['namafile'],'.')+1));
							$str="insert into ".$dbname.".listfile_sdm_ijin (notransaksi,namafile,formaticon,status,createdby,createdtime) values ('".$notrx."','".$row['namafile']."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
							$owlPDO->exec($str);
						}
					}
				
					if($jnsIjin != 'CUTI08'){
						echo "Jenis Cuti ".$optjnsIjin[$jnsIjin]." dengan Jumlah HK Diambil : ".$jmlhk." hari. Tidak memotong hak cuti tahunan.\n\n";
					}

					$str="select * from ".$dbname.".sdm_5hakcutijenis where  jenisijin='".$jnsIjin."'";
					$res=fetchData($str);
					if (count($res)>0) {
						if ($jmlhk > $res[0]['hakcuti']) {	
							throw new PDOException("Jenis Cuti ".$optjnsIjin[$jnsIjin]." tidak boleh melebihi : ".$res[0]['hakcuti']." hari.");
						}
					}		

				
			} else {
				
				throw new PDOException("Error:Data Pada Tanggal " . $_POST['tglijin'] . " Sudah ada");
			}

			#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	break;

	case 'form_ajukan':
		$notransaksi = $notrans;

		$str="select * from ".$dbname.".sdm_ijin where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		$karyawanid = $res[0]['karyawanid'];

		$str="select * from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
		$res=fetchData($str);
		$departemen = $res[0]['bagian'];
		$kodegolongan = $res[0]['kodegolongan'];
		$lokasitugas = $res[0]['lokasitugas'];


		##CEK PER DEPARTEMEN
		$str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$lokasitugas."' and jenispersetujuan='IJS' and departemen='".$departemen."'";
		$res=fetchdata($str);
		$perdepartemen=$res[0]['kodeunit'];
		$where="";
		if($perdepartemen>0){
			$where.=" and departemen='".$departemen."'";
		}else{
			$where.=" and departemen=''";
		}

		$golongan     = $kodegolongan;
		
		##CEK PER GOLONGAN
		$str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$lokasitugas."' and jenispersetujuan='IJS' and golongan='".$golongan."'";
		$res=fetchdata($str);
		$perdepartemen=$res[0]['kodeunit'];
		$where="";
		if($perdepartemen>0){
			$where.=" and golongan='".$golongan."'";
		}else{
			$where.=" and golongan=''";
		}
		
		## APPROVAL DINAMIS SESUAI SETUP##
	
		//$optper=array();
		$optKryx=array();
		$optKrylevel=array();

		$str="select * from ".$dbname.".setup_approval 
				where jenispersetujuan='IJS' and kodeunit='".$lokasitugas."' and karyawaniduser='".$karyawanid."' ".$where." order by level";  
		$res=fetchData($str);
		if(count($res) > 0){
			foreach($res as $key => $bar){
				$whr        =" karyawanid='".$bar['karyawanid']."'";
				$optnama    = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
			   
			   $optKryx[$bar['level']][$bar['karyawanid']]="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
				$optKrylevel[$bar['level']]=$bar['level'];
				
			}
		}else{
			
			$str="select * from ".$dbname.".setup_approval 
			where jenispersetujuan='IJS' and kodeunit='".$lokasitugas."' and karyawaniduser='' ".$where." order by level";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$whr        =" karyawanid='".$bar['karyawanid']."'";
				$optnama    = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
				
				$optKryx[$bar['level']][$bar['karyawanid']]="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
				$optKrylevel[$bar['level']]=$bar['level'];
			}
		}

		$jumlahlevel=count($optKrylevel);    
		$tab.="<input hidden id=jlh value='".$jumlahlevel."'>";
		$tab.="<input hidden id=notransaksi_ajukan value='".$notransaksi."'>";
		if($jumlahlevel>0)
		{
			foreach ($optKrylevel as $key) {
				$optKry='';
				foreach ($optKryx[$key] as $key2 => $val) {
					$optKry.=$val;
				}
					$tab .= "<tr class=rowcontent>
						<td>Approval ke-".$key."</td>
						<td width=5px>:</td>
						<td><select id=kepada".$key." style='width:99%;'>".$optKry."</select></td>     
					</tr>";
				
			}

		}
		else
		{           $jumlahlevel=1;
					$tab .= "<tr class=rowcontent>
						<td>Approval ke-1</td>
						<td width=5px>:</td>
						<td><select id=kepada1 style='width:99%;'></select></td>
					</tr>";
		}
		$tab.="<tr class=rowcontent>
				<td></td>
				<td></td>
				<td><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
			</tr>               
		</table>";
		echo $tab;
	break;

	case 'ajukan' :
		
		$jlh = checkPostGet('jlh','');
		$notransaksi = $notrans;

		$str="select * from ".$dbname.".sdm_ijin where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		$karyawanid = $res[0]['karyawanid'];

		$str="select * from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
		$res=fetchData($str);
		$lokasitugas = $res[0]['lokasitugas'];

		
		for ($i=1; $i <= $jlh ; $i++) { 
			$per['persetujuan'.$i] = checkPostGet('kepada'.$i, '');
			if($per['persetujuan'.$i] == '' or $notransaksi==''){
				exit('Warning : Isikan nama penyetuju.');
			}
		}
		

		$str = "UPDATE " . $dbname . ".sdm_ijin SET statuspersetujuan='9' WHERE notransaksi= '" . $notransaksi . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}

		$jenispersetujuan='IJS';
		for($i=1; $i<=$jlh; $i++){
			$str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenispersetujuan."' and level='".$i."' and kodeunit='".$lokasitugas."'";
			// exit("error : $str");
			$res=fetchData($str);
			$tipeapp = $res[0]['tipe'];
			$departemenapp = $res[0]['departemen'];
			$tipekaryawanapp = $res[0]['tipekaryawan'];
			$jabatanapp = $res[0]['jabatan'];
			
			if(count($res) > 0){
				if($tipeapp=='1'){
					if($departemenapp!=''){
						$str="select * from ".$dbname.".datakaryawan where bagian='".$departemenapp."'";
						$res=fetchdata($str);
						foreach($res as $keyx=>$valx){
							$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenispersetujuan."','".$i."','".$valx['karyawanid']."','0')";
							$owlPDO->exec($str);
						}
					}
					if($tipekaryawanapp!=''){
						$str="select * from ".$dbname.".datakaryawan where tipekaryawan='".$tipekaryawanapp."'";
						$res=fetchdata($str);
						foreach($res as $keyx=>$valx){
							$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenispersetujuan."','".$i."','".$valx['karyawanid']."','0')";
							$owlPDO->exec($str);
						}
					}
					if($jabatanapp!='0'){
						$str="select * from ".$dbname.".datakaryawan where kodejabatan='".$jabatanapp."'";
						$res=fetchdata($str);
						foreach($res as $keyx=>$valx){
							if($per['persetujuan'.$i]!=''){
								$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenispersetujuan."','".$i."','".$valx['karyawanid']."','0')";
								$owlPDO->exec($str);
							}
						}
					}
				}else{
					if($per['persetujuan'.$i]!=''){
						$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenispersetujuan."','".$i."','".$per['persetujuan'.$i]."','0')";
						try
						{
							$owlPDO->exec($str);
						}
						catch (PDOException $e) 
						{
							echo " Gagal," . addslashes($e->getMessage());
						}
					}
				}
			}
		}
	break;


	case'loadData':
        $limit = 10;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;

		$where="";
		if($karyawanid!=''){
			$where=" and karyawanid = '".$karyawanid."'";
		}

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".sdm_ijin where 1=1 ".$where."  order by `tanggal` desc";
		$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
		
		$optpoh = makeOption($dbname,'datakaryawan','karyawanid,lokasitugas',"karyawanid='".$karyawanid."'");
		$poh = $optpoh[$karyawanid];
		
        $slvhc = "select * from " . $dbname . ".sdm_ijin where 1=1 ".$where." order by `tanggal` desc limit " . $offset . "," . $limit . " ";
		$qlvhc=$owlPDO->query($slvhc) or die(print " Gagal: ".PDOException::getMessage());
		$qlvhc->setFetchMode(PDO::FETCH_ASSOC);
        $user_online = $karyawanid;
        while ($rlvhc = $qlvhc->fetch()) {

            $no+=1;
            $nmAkun = makeOption($dbname, 'sdm_5jenisijin', 'idjenis,jenisijin');

            $notrx=str_replace('-', '', $rlvhc['tanggal']).$rlvhc['karyawanid'];
            $str="select * from ".$dbname.".approval where notransaksi='".$notrx."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$per['persetujuan'.$bar['level']]=$bar['karyawanid'];
				$per['stpersetujuan'.$bar['level']]=$bar['status'];
			}

			$stat=array('0' =>$_SESSION['lang']['wait_approve'],'1' =>$_SESSION['lang']['disetujui'],'3' =>$_SESSION['lang']['ditolak'] );

            echo"
                <tr class=rowcontent>
                <td align=center>" . $no . "</td>
                <td align=center>" . $rlvhc['notransaksi'] . "</td>
                <td align=center>" . $arrNmkary[$rlvhc['karyawanid']] . "</td>
                <td align=center>" . tanggalnormal($rlvhc['tanggal']) . "</td>
                <td align=left>" . (isset($nmAkun[$rlvhc['idjenis']]) ? $nmAkun[$rlvhc['idjenis']] : '') . "</td>
				
                <td align=center>" . $rlvhc['periodecuti'] . "</td>

                <td align=center>" . tanggalnormal($rlvhc['darijam']) . "</td>
                <td align=center>" . tanggalnormal($rlvhc['sampaijam']) . "</td>
                <td align=center>" . $rlvhc['jumlahhari'] . "</td>
                <td align=center>" . tanggalnormal($rlvhc['tanggalkerja']) . "</td>
                <td align=left>" . $rlvhc['nohp'] . "</td>
                
				<td align=left>" . $rlvhc['keperluan'] . "</td>
				<td align=left>" . $rlvhc['keterangan'] . "</td>
				<td align=left>" . $rlvhc['alamatcuti'] . "</td>
				<td align=center>" . $arrNmkary[$rlvhc['pengganti']] . "</td>
				
				";
			echo"<td>"; #buka td persetujuan
			echo"<table>";
			$stat=0;
			$countApp = getCountApproval($jenispersetujuan,$_SESSION['empl']['lokasitugas']);
			for($i=1;$i<=$countApp;$i++){
				$strx="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenispersetujuan."' and level='".$i."' and kodeunit='".$_SESSION['empl']['lokasitugas']."'";
				$resx=fetchData($strx);
				$tipeapp = @$resx[0]['tipe'];
				$departemenapp = @$resx[0]['departemen'];
				$tipekaryawanapp = @$resx[0]['tipekaryawan'];
				$jabatanapp = @$resx[0]['jabatan'];
				$level=@$resx[0]['level'];
				
				
				@$arrDetail = detailApprove($i,$notrx,$jenispersetujuan);
				if($tipeapp=='1' && $arrDetail['status']!=''){
					if($arrDetail['status']!='1'){
						if($departemenapp!=''){
							$opttipe = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$departemenapp."'");
							$arrDetail['nama'] = $opttipe[$departemenapp];
						}
						
						if($tipekaryawanapp!=''){
							$opttipe = makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$tipekaryawanapp."'");
							$arrDetail['nama'] = $opttipe[$tipekaryawanapp];
						}
						
						if($jabatanapp!='0'){
							$opttipe = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$jabatanapp."'");
							$arrDetail['nama'] = $opttipe[$jabatanapp];
						}
					}
				}

				if($rlvhc['statuspersetujuan'] == 1){
					$statuscuti = $_SESSION['lang']['disetujui'];
				}
				elseif ($rlvhc['statuspersetujuan'] == 2) {
					$statuscuti = $_SESSION['lang']['ditolak'];
				}
				elseif ($rlvhc['statuspersetujuan'] == 3) {
					$statuscuti = 'Perbaikan';
				}
				elseif ($rlvhc['statuspersetujuan'] == 9) {
					$statuscuti = $_SESSION['lang']['proses']." ".$_SESSION['lang']['approve'];
				}else{
					$statuscuti = $_SESSION['lang']['belumdiajukan'];
				}

				
				
				if($level==1){
					$approve1=$arrDetail['karyawanid'];
				}elseif($level==2){
					$approve2=$arrDetail['karyawanid'];
				}elseif($level==3){
					$approve3=$arrDetail['karyawanid'];
				}
				if($arrDetail['status']==1){
					$stat=1;
				}
			}

			echo"<tr>";
					echo"<td align=left>".$statuscuti."</td>";
				echo"</tr>";
			
			echo"</table>";
			echo"</td>"; #tutup td persetujuan
			
			echo"<td>"; #buka td batalcuti
			echo"<table>";
			// $statbtl=0;
			// $countApp = getCountApproval($jenispersetujuan,$_SESSION['empl']['lokasitugas']);
			// for($i=1;$i<=$countApp;$i++){
				// $strx="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenispersetujuan."' and level='".$i."' and kodeunit='".$_SESSION['empl']['lokasitugas']."'";
				// $resx=fetchData($strx);
				// $tipeapp = @$resx[0]['tipe'];
				// $departemenapp = @$resx[0]['departemen'];
				// $tipekaryawanapp = @$resx[0]['tipekaryawan'];
				// $jabatanapp = @$resx[0]['jabatan'];
				// $level=@$resx[0]['level'];
				
				
				// @$arrDetail = detailApprove($i,$notrx,"IJSC");
				// if($tipeapp=='1' && $arrDetail['status']!=''){
					// if($arrDetail['status']!='1'){
						// if($departemenapp!=''){
							// $opttipe = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$departemenapp."'");
							// $arrDetail['nama'] = $opttipe[$departemenapp];
						// }
						
						// if($tipekaryawanapp!=''){
							// $opttipe = makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$tipekaryawanapp."'");
							// $arrDetail['nama'] = $opttipe[$tipekaryawanapp];
						// }
						
						// if($jabatanapp!='0'){
							// $opttipe = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$jabatanapp."'");
							// $arrDetail['nama'] = $opttipe[$jabatanapp];
						// }
					// }
				// }
			
				
				// if($arrDetail['status']==1){
					// $statbtl=1;
				// }
			// }
			 if ($rlvhc['statuspersetujuan_cancel'] == 1){

			echo "<td  align=center> " . $arrKeputusan[$rlvhc['statuspersetujuan_cancel']] . "</td>";
			 } 
			if ($rlvhc['statuspersetujuan_cancel'] == 9){

			echo "<td  align=center> " . $arrKeputusan[$rlvhc['statuspersetujuan_cancel']] . "</td>";
			 } 
			 if ($rlvhc['statuspersetujuan_cancel'] == 2){

			echo "<td  align=center> " . $arrKeputusan[$rlvhc['statuspersetujuan_cancel']] . "</td>";
			 } 
			
			
			echo"</table>";
			echo"</td>"; #tutup td batalcuti
            if ($rlvhc['statuspersetujuan'] == 0 and $stat==0) {
                echo"<td align=center>
				<img src=images/skyblue/submit.jpg class=zImgBtn title='Ajukan ".$rlvhc['notransaksi']."'   onclick=\"form_ajukan('".$rlvhc['notransaksi']."');\">
				<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $rlvhc['keperluan'] . "','" . tanggalnormal($rlvhc['tanggal']) . "','" . $rlvhc['idjenis'] . "', 
				'" . $approve1. "', '" . $approve2. "','" . $approve3. "','".tanggalnormal($rlvhc['darijam'])."', 
				'".tanggalnormal($rlvhc['sampaijam'])."','" . $rlvhc['jumlahhari'] . "','" . $rlvhc['periodecuti'] . "',
				'" . $rlvhc['keterangan'] . "','" . $rlvhc['alamatcuti'] . "','" . tanggalnormal($rlvhc['tanggalkerja']) . "',
				'" . $rlvhc['pengganti'] . "','".$rlvhc['nohp']."', '".$rlvhc['karyawanid']."');\">
				<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$rlvhc['notransaksi']."', '" . tanggalnormal($rlvhc['tanggal']) . "', '".$rlvhc['karyawanid']."');\" >
				<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"previewPdf('" . tanggalnormal($rlvhc['tanggal']) . "','" . $rlvhc['karyawanid'] . "',event)\">
				</td>";
            }
			
            elseif ($rlvhc['statuspersetujuan'] == 2) {
                echo"<td align=center>
				<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $rlvhc['keperluan'] . "','" . tanggalnormal($rlvhc['tanggal']) . "','" . $rlvhc['idjenis'] . "', 
				'" . $approve1. "', '" . $approve2. "','" . $approve3. "','".tanggalnormal($rlvhc['darijam'])."', 
				'".tanggalnormal($rlvhc['sampaijam'])."','" . $rlvhc['jumlahhari'] . "','" . $rlvhc['periodecuti'] . "',
				'" . $rlvhc['keterangan'] . "','" . $rlvhc['alamatcuti'] . "','" . tanggalnormal($rlvhc['tanggalkerja']) . "',
				'" . $rlvhc['pengganti'] . "','".$rlvhc['nohp']."', '".$karyawanid."');\">
				
				<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$rlvhc['notransaksi']."','" . tanggalnormal($rlvhc['tanggal']) . "', '".$rlvhc['karyawanid']."');\" >
				
				<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"previewPdf('" . tanggalnormal($rlvhc['tanggal']) . "','" . $rlvhc['karyawanid'] . "',event)\">
				
				</td>";
            } else if ($rlvhc['statuspersetujuan'] == 1 and ($rlvhc['statuspersetujuan_cancel']==0) and ($rlvhc['idjenis']='CUTI18')){
				echo "<td  align=center>
					<img class='resicon' src='images/Delete.png' onclick=batalcuti('".$rlvhc['notransaksi']."','".$approve1. "','".$approve2."','".$approve3."') title=\"Batalkan !\">
					
					<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"previewPdf('" . tanggalnormal($rlvhc['tanggal']) . "','" . $rlvhc['karyawanid'] . "',event)\">
					</td>";
			}else{
                echo "<td  align=center><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"previewPdf('" . tanggalnormal($rlvhc['tanggal']) . "','" . $rlvhc['karyawanid'] . "',event)\"></td>";
            }//end if updateby
        }//end while
		
        echo"
			</tr><tr class=rowheader><td colspan=17 align=center>
			" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
			<button class=mybutton onclick=cariBast(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
			<button class=mybutton onclick=cariBast(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
			</td>
			</tr>";
    break;

    case'getKet':
	
        $sket = "select distinct keterangan from " . $dbname . ".sdm_ijin where " . $where . "";
		
		$qKet=$owlPDO->query($sket) or die(print " Gagal: ".PDOException::getMessage());
		$qKet->setFetchMode(PDO::FETCH_ASSOC);
        $rKet = $qKet->fetch();
        echo $rKet['keterangan'];
	break;

    case'deleteData':
        $sket = "select distinct stpersetujuan1, notransaksi from " . $dbname . ".sdm_ijin where " . $where . "";
		$qKet=$owlPDO->query($sket) or die(print " Gagal: ".PDOException::getMessage());
		$qKet->setFetchMode(PDO::FETCH_ASSOC);
        $rKet = $qKet->fetch();
        if ($rKet['stpersetujuan1'] == 0) {
            $sDel = "delete from " . $dbname . ".sdm_ijin where " . $where . "";
            
			try{
				$owlPDO->exec($sDel); 
				
				#update tabel day off nya
				$updatedayoff = "update  " . $dbname . ".sdm_dayoff_dt set notransaksicuti='', akandiambil='0' where notransaksicuti='".$tglijin."".$_SESSION['standard']['userid']."'";
				//echo $updatedayoff;
				try{
					$owlPDO->exec($updatedayoff); 
				}catch (PDOException $e){
					echo "DB Error : " . $e->getMessage();
				}

				$str="delete from ".$dbname.".listfile_sdm_ijin where notransaksi='".$tglijin."".$_SESSION['standard']['userid']."'";
				try{
					$owlPDO->exec($str);
					
					$str="delete from ".$dbname.".approval where notransaksi='".$tglijin."".$_SESSION['standard']['userid']."' and jenispersetujuan='IJS'";
					try{
						$owlPDO->exec($str);
					}catch(PDOException $e){
						echo "DB Error : " . $e->getMessage();
					}
				}catch(PDOException $e){
					echo "DB Error : " . $e->getMessage();
				}
			}catch (PDOException $e){
				echo "DB Error : " . $e->getMessage();
			}
        }
        else {
            exit("Error:Sudah ada keputusan");
        }
        break;

    case'update':

        //=============================	
			
        if (($jnsIjin == '') || ($jamDr == '') || ($jamSmp == '') || ($keperluan == '')  || $tanggalkerja=='' || $nohp=='' ) {
            echo"warning:Please Complete The Formx";
            exit();
        }
		
		$counttrip = 0;
		if($hometrip=='true')
		{
			if($tglberangkat==''||$rutekeberangkatan==''||$tglpulang==''||$rutekepulangan=='')
			{
				echo"warning:Please Complete The Form.";
				exit();
			}
		}
		
		// $getPotHK = makeOption($dbname,'sdm_5jenisijin','idjenis,potonganhk',"idjenis='".$jnsIjin."'");
		// $potHk = $getPotHK[$jnsIjin];
		
		// $hk = $potHk * $hk;

		$tglcuti='';
		$echodata="";

		$ihari=$hk;
		if ($hk<1) {
			$ihari=1;
		}

		##pengecekan tanggal cuti yang dipilih sudah pernah diajukan atau belum
		for ($i=0; $i < $ihari ; $i++) {

			$tglcuti= date("Ymd",strtotime("+".$i." Day",strtotime($tgl1)));

	        $whrijin = " karyawanid='" . $_SESSION['standard']['userid'] . "' and '".$tglcuti."' between darijam and sampaijam  ";
	        $sCek = "select tanggal from " . $dbname . ".sdm_ijin where  " . $whrijin . " and tanggal!='".$tglijin."' and statuspersetujuan_cancel!='1'";
			$res=fetchData($sCek);
			$jmlhbaris=count($res);
			if ($jmlhbaris>0) {
				$echodata="Tanggal cuti yang dipilih sudah pernah diajukan.";
			}
		}

		if ($echodata!="") {
			exit('warning : '.$echodata);
		}
		

		if ($jnsIjin=='CUTI18') {

            $notrx=str_replace('-', '', $tglijin).$_SESSION['standard']['userid'];
			$supdate = "update  " . $dbname . ".sdm_dayoff_dt set akandiambil='0' , notransaksicuti='' where notransaksicuti='".$notrx."' and karyawanid='".$_SESSION['standard']['userid']."' ";
			try{
				$owlPDO->exec($supdate); 
				}catch (PDOException $e){
					echo "DB Error : " . $e->getMessage();
					die();
				}

        	
        	$tglawalcuti = tanggalsystem(checkPostGet('tglAwal',''));
        	$tglakhircuti = tanggalsystem(checkPostGet('tglEnd',''));

        	$tanggalarr=array();
    	 	$stt = "select (jumlahharidayoff-(diambil+akandiambil)) as sisa,tanggaldayoff,tanggalberlakusampai,jumlahharidayoff,(diambil+akandiambil) as dipakai,notransaksi from ".$dbname.".sdm_dayoff_dt_vw where karyawanid='".$_SESSION['standard']['userid']."' 
		   			and tanggaldayoff <'".$tglawalcuti."' and tanggalberlakusampai >='".$tglawalcuti."' and status='1' and (jumlahharidayoff-(diambil+akandiambil)) > 0 order by tanggaldayoff asc";
			$rett=$owlPDO->query($stt) or die(print " Gagal: ".PDOException::getMessage());
			$rett->setFetchMode(PDO::FETCH_OBJ);
			$nox=0;
            while ($batt = $rett->fetch()) {
            	$tanggalarr[$nox]['tanggalawal']=$batt->tanggaldayoff;
            	$tanggalarr[$nox]['tanggalakhir']=$batt->tanggalberlakusampai;
            	$tanggalarr[$nox]['sisa']=$batt->sisa;
            	$tanggalarr[$nox]['diambil']=0;
            	$tanggalarr[$nox]['notransaksi']=$batt->notransaksi;
            	$nox++;
            }

            $rangetgl = rangeTanggal($tglawalcuti,$tglakhircuti);
            $whr=" and (kebun='GLOBAL' or kebun='".$_SESSION['empl']['lokasitugas']."')";
			if ($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
				$whr=" and (kebun='GLOBAL' or kebun='HOLDING' or kebun='".$_SESSION['empl']['lokasitugas']."')";
			}
			$jlhtglx=0;
			$jlhtglm=0;
			$arrupdate=array();
			foreach($rangetgl as $tglx){
				$str="select * from ".$dbname.".sdm_5harilibur where tanggal='".$tglx."'".$whr;
				$res=fetchData($str);
				$jmlhbaris=count($res);
				if ($jmlhbaris==0) {
					$jlhtglx++;
					for ($i=0; $i < $nox; $i++) { 
						if($tanggalarr[$i]['tanggalawal']<$tglx and $tanggalarr[$i]['tanggalakhir'] >= $tglx and $tanggalarr[$i]['sisa']>0 and $tanggalarr[$i]['diambil']==0)
						{
							$tanggalarr[$i]['diambil']=1;
							$arrupdate[$jlhtglm] = "update  " . $dbname . ".sdm_dayoff_dt set akandiambil='1' , notransaksicuti='".$notrx."' where notransaksi='".$tanggalarr[$i]['notransaksi']."' and tanggaldayoff='".$tanggalarr[$i]['tanggalawal']."' and tanggalberlakusampai='".$tanggalarr[$i]['tanggalakhir']."' and karyawanid='".$_SESSION['standard']['userid']."' ";
							//echo $supdate;
							
							$jlhtglm++;
							$i=$nox;
						}
					}
				}
			}
               
               
            if ($jlhtglx!=$jlhtglm) {
            	 exit("Error: Hak Cuti Day Off anda untuk tanggal tertentu sudah habis/tidak mencukupi");
            }

            for ($i=0; $i < $jlhtglm; $i++) { 
            	$supdate=$arrupdate[$i];
            	try{
				$owlPDO->exec($supdate); 
				}catch (PDOException $e){
					echo "DB Error : " . $e->getMessage();
					die();
				}
            }


        }
        else
        {

            $notrx=str_replace('-', '', $tglijin).$_SESSION['standard']['userid'];
        	#update tabel day off nya
				$updatedayoff = "update  " . $dbname . ".sdm_dayoff_dt set notransaksicuti='', akandiambil='0' where notransaksicuti='".$notrx."'";
				//echo $updatedayoff;
				try{
					$owlPDO->exec($updatedayoff); 
				}catch (PDOException $e){
					echo "DB Error : " . $e->getMessage();
				}

        }
		
		// if($atasan==$atasan2){
			// echo"warning:Penyetuju 1 dann Penyetuju 2 harus berbeda.";
            // exit();
		// }
		$notrx=str_replace('-', '', $tglijin).$_SESSION['standard']['userid'];
        $sket = "select status from ".$dbname.".approval where notransaksi='".$notrx."' and level='1' ";
		$qKet=$owlPDO->query($sket) or die(print " Gagal: ".PDOException::getMessage());
		$qKet->setFetchMode(PDO::FETCH_ASSOC);
        $rKet = $qKet->fetch();
        if ($rKet['status'] == 0) {
            //(karyawanid, tanggal, keperluan, keterangan, persetujuan1, waktupengajuan, darijam, sampaijam, jenisijin) 
            //values ('".$_SESSION['standard']['userid']."','".$tglijin."','".$keperluan."','".$ket."','".$atasan."','".$wktu."','".$jamDr."','".$jamSmp."','".$jnsIjin."')
            $sUp = "update  " . $dbname . ".sdm_ijin set keperluan='" . $keperluan . "', keterangan='" . $ket . "', darijam='" . $jamDr1 . "', 
                          sampaijam='" . $jamSmp1 . "',idjenis='" . $jnsIjin . "',
                          hrd='" . $hrd . "',periodecuti='" . $periodec . "',jumlahhari='" . $hk."',
						  alamatcuti='".$alamatcuti."',tanggalkerja='".$tanggalkerja."',pengganti='".$pengganti."'
						  ,nohp='".$nohp."',tanggalberangkat='".$tglberangkat."',rutekeberangkatan='".$rutekeberangkatan."',tglpulang='".$tglpulang."',rutekepulangan='".$rutekepulangan."'";
            if ($atsSblm != $atasan) {
                $wktu = date("Y-m-d H:i:s");
                $sUp.=",persetujuan1='" . $atasan . "',waktupengajuan='" . $wktu . "'";
            }
			if ($atsSblm2 != $atasan2) {
                $wktu = date("Y-m-d H:i:s");
                $sUp.=",persetujuan4='" . $atasan2 . "'";
            }

            $sUp.=" where " . $where . "";
			try{
				$owlPDO->exec($sUp); 
				
				$str="delete from ".$dbname.".approval where notransaksi='".$notrx."' and jenispersetujuan='".$jenispersetujuan."'";
				$owlPDO->exec($str);
				
				$countApp = getCountApproval($jenispersetujuan,$_SESSION['empl']['lokasitugas']);
				for($i=1;$i<=$countApp;$i++){
					$str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenispersetujuan."' and level='".$i."' and kodeunit='".$_SESSION['empl']['lokasitugas']."'";
					// exit("error : $str");
					$res=fetchData($str);
					$tipeapp = $res[0]['tipe'];
					$departemenapp = $res[0]['departemen'];
					$tipekaryawanapp = $res[0]['tipekaryawan'];
					$jabatanapp = $res[0]['jabatan'];
					
					if(count($res) > 0){
						if($tipeapp=='1'){
							if($departemenapp!=''){
								$str="select * from ".$dbname.".datakaryawan where bagian='".$departemenapp."'";
								$res=fetchdata($str);
								foreach($res as $keyx=>$valx){
									$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notrx."','".$jenispersetujuan."','".$i."','".$valx['karyawanid']."','0')";
									$owlPDO->exec($str);
								}
							}
							if($tipekaryawanapp!=''){
								$str="select * from ".$dbname.".datakaryawan where tipekaryawan='".$tipekaryawanapp."'";
								$res=fetchdata($str);
								foreach($res as $keyx=>$valx){
									$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notrx."','".$jenispersetujuan."','".$i."','".$valx['karyawanid']."','0')";
									$owlPDO->exec($str);
								}
							}
							if($jabatanapp!='0'){
								if($per['persetujuan'.$i]!=''){
									$str="select * from ".$dbname.".datakaryawan where kodejabatan='".$jabatanapp."'";
									$res=fetchdata($str);
									foreach($res as $keyx=>$valx){
										$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notrx."','".$jenispersetujuan."','".$i."','".$valx['karyawanid']."','0')";
										$owlPDO->exec($str);
									}
								}
							}
						}else{
							if($per['persetujuan'.$i]!=''){
								$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notrx."','".$jenispersetujuan."','".$i."','".$per['persetujuan'.$i]."','0')";
								try
								{
									$owlPDO->exec($str);
								}
								catch (PDOException $e) 
								{
									echo " Gagal," . addslashes($e->getMessage());
								}
							}
						}
					}
					
					// if($per['persetujuan'.$i]!=''){
						// $qstr="insert into ".$dbname.".approval (`notransaksi`,`jenispersetujuan`,`level`,`karyawanid`) values 
							  // ('".$notrx."','".$jenispersetujuan."','".$i."','".$per['persetujuan'.$i]."')";
						// try{
							// $owlPDO->exec($qstr); 
						// }catch(PDOException $e){
				            // echo " Gagal," . addslashes($e->getMessage());
				        // }
				    // }
				}
				
				$strx="delete from ".$dbname.".listfile_sdm_ijin where notransaksi='".$notrx."'";
				try{
					$owlPDO->exec($strx); 
					
					$countImage = count($_SESSION['buktiizin']);
				
					if($countImage>0)
					{
						foreach($_SESSION['buktiizin'] as $key=>$row)
						{
							$filetype = strtolower('.'.substr($row['namafile'],strripos($row['namafile'],'.')+1));
							$str="insert into ".$dbname.".listfile_sdm_ijin (notransaksi,namafile,formaticon,status,createdby,createdtime) values ('".$notrx."','".$row['namafile']."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
							try
							{
								$owlPDO->exec($str);
							}
							catch (PDOException $e) 
							{
								print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
							}
						}
					}
				}catch(PDOException $e){
					echo " Gagal," . addslashes($e->getMessage());
				}
				
				// if ($atsSblm != $atasan) {
    //                 #send an email to incharge person
    //                 $to = getUserEmail($atasan);
    //                 $namakaryawan = getNamaKaryawan($_SESSION['standard']['userid']);
    //                 $subject = "[Notifikasi]Persetujuan Ijin Keluar Kantor a/n " . $namakaryawan;
    //                 $body = "<html>
    //                                                  <head>
    //                                                  <body>
    //                                                    <dd>Dengan Hormat,</dd><br>
    //                                                    <br>
    //                                                    Pada hari ini, tanggal " . date('d-m-Y') . " karyawan a/n  " . $namakaryawan . " mengajukan Ijin/" . $jnsIjin . " (" . $keperluan . ")
    //                                                    kepada bapak/ibu. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
    //                                                    <br>
    //                                                    <br>
    //                                                    Note: Sisa cuti ybs periode " . $periodec . ":" . (isset($sisa) ? $sisa : 0) . " Hari
    //                                                    <br>
    //                                                    <br>
    //                                                    Regards,<br>
    //                                                    Owl-Plantation System.
    //                                                  </body>
    //                                                  </head>
    //                                                </html>
    //                                                ";
    //                 $kirim = kirimEmail($to, '', $subject, $body); #this has return but disobeying;
				// }
					
				// if ($atsSblm2 != $atasan2) {
				// 	#send an email to incharge person
    //                 $to = getUserEmail($atasan2);
    //                 $namakaryawan = getNamaKaryawan($_SESSION['standard']['userid']);
    //                 $subject = "[Notifikasi]Persetujuan Ijin Keluar Kantor a/n " . $namakaryawan;
    //                 $body = "<html>
    //                                                  <head>
    //                                                  <body>
    //                                                    <dd>Dengan Hormat,</dd><br>
    //                                                    <br>
    //                                                    Pada hari ini, tanggal " . date('d-m-Y') . " karyawan a/n  " . $namakaryawan . " mengajukan Ijin/" . $jnsIjin . " (" . $keperluan . ")
    //                                                    kepada bapak/ibu. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
    //                                                    <br>
    //                                                    <br>
    //                                                    Note: Sisa cuti ybs periode " . $periodec . ":" . (isset($sisa) ? $sisa : 0) . " Hari
    //                                                    <br>
    //                                                    <br>
    //                                                    Regards,<br>
    //                                                    Owl-Plantation System.
    //                                                  </body>
    //                                                  </head>
    //                                                </html>
    //                                                ";
    //                 $kirim = kirimEmail($to, '', $subject, $body); #this has return but disobeying;
    //             }
			}catch (PDOException $e){
				
			}
        } else {
            exit("Error:Sudah ada keputusan");
        }
  //       if ($atsSblm != $atasan) {
  //           $to = getUserEmail($atsSblm);
  //           $namakaryawan = getNamaKaryawan($_SESSION['standard']['userid']);
  //           $subject = "[Notifikasi]Pembatalan Persetujuan Ijin Keluar Kantor a/n " . $namakaryawan;
  //           $body = "<html>
  //                                                    <head>
  //                                                    <body>
  //                                                      <dd>Dengan Hormat,</dd><br>
  //                                                      <br>
  //                                                      Pada hari ini, tanggal " . date('d-m-Y') . " karyawan a/n  " . $namakaryawan . " mengajukan Ijin/" . $jnsIjin . " (" . $keperluan . ")
  //                                                      kepada bapak/ibu. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
  //                                                      <br>
  //                                                      <br>
  //                                                      Note: Sisa cuti ybs periode " . $periodec . ":" . $sisa . " Hari
  //                                                      <br>
  //                                                      <br>
  //                                                      Regards,<br>
  //                                                      Owl-Plantation System.
  //                                                    </body>
  //                                                    </head>
  //                                                  </html>
  //                                                  ";
  //           $kirim = kirimEmail($to, '', $subject, $body); #this has return but disobeying;
  //       }
		
		// if ($atsSblm2 != $atasan2) {
  //           $to = getUserEmail($atsSblm2);
  //           $namakaryawan = getNamaKaryawan($_SESSION['standard']['userid']);
  //           $subject = "[Notifikasi]Pembatalan Persetujuan Ijin Keluar Kantor a/n " . $namakaryawan;
  //           $body = "<html>
  //                                                    <head>
  //                                                    <body>
  //                                                      <dd>Dengan Hormat,</dd><br>
  //                                                      <br>
  //                                                      Pada hari ini, tanggal " . date('d-m-Y') . " karyawan a/n  " . $namakaryawan . " mengajukan Ijin/" . $jnsIjin . " (" . $keperluan . ")
  //                                                      kepada bapak/ibu. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
  //                                                      <br>
  //                                                      <br>
  //                                                      Note: Sisa cuti ybs periode " . $periodec . ":" . $sisa . " Hari
  //                                                      <br>
  //                                                      <br>
  //                                                      Regards,<br>
  //                                                      Owl-Plantation System.
  //                                                    </body>
  //                                                    </head>
  //                                                  </html>
  //                                                  ";
  //           $kirim = kirimEmail($to, '', $subject, $body); #this has return but disobeying;
  //       }
        break;
		
	case'submitfile':
		$tgl = date("YmdHis");
		$data = $_POST;
		if($data['fileupload']!='')
		{
			if($_FILES['file']['error']==0)
			{
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
				$filename = $newfilename."_".$tgl."".$filetype;
				$file_tmpname = $_FILES['file']['tmp_name'];		
				
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx'))
				{
					if($_FILES['file']['size'] <= 500000)
					{
						$newdata = array(
							'namafile'=>$filename
						);
						
						if($_SESSION['buktiizin'] != array())
						{
							foreach($_SESSION['buktiizin'] as $key=>$row)
							{
								if($row['namafile'] == $filename)
								{
									exit("Warning : Item ini sudah pernah diinput sebelumnya.");
								}
							}
							array_push($_SESSION['buktiizin'],$newdata);
						}else{
							array_push($_SESSION['buktiizin'],$newdata);
						}
						move_uploaded_file($file_tmpname,"fileupload/sdm_ijin/$filename");
					}
					else
					{
						exit("warning : Ukuran file upload maksimal 500kb");
					}
				}else{
					exit("Warning : Format file upload harus .jpg, .jpeg, .png, .pdf, .xls, .xlsx, .doc, .docx");
				}
			}
		}
	break;
	
	case 'loadfiles':
		$tab="";
		$no=0;
		foreach($_SESSION['buktiizin'] as $key=>$row)
		{
			$no++;
			$tab.="<tr class='rowcontent'>";
			$tab.="<td style='text-align:right'>".$no."</td>";
			if($row['formaticon']=='.jpeg'||$row['formaticon']=='.jpg')
			{
				$tab.="<td style='text-align:center'>
					<a href='fileupload/sdm_ijin/".$row['namafile']."' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
				</td>";
			}
			elseif($row['formaticon']=='.png')
			{
				$tab.="<td style='text-align:center'>
					<a href='fileupload/sdm_ijin/".$row['namafile']."' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
				</td>";
			}
			elseif($row['formaticon']=='.pdf')
			{
				$tab.="<td style='text-align:center'>
					<a href='fileupload/sdm_ijin/".$row['namafile']."' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
				</td>";
			}
			elseif($row['formaticon']=='.xls'||$row['formaticon']=='.xlsx')
			{
				$tab.="<td style='text-align:center'>
					<a href='fileupload/sdm_ijin/".$row['namafile']."' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
				</td>";
			}
			elseif($row['formaticon']=='.doc'||$row['formaticon']=='.docx')
			{
				$tab.="<td style='text-align:center'>
					<a href='fileupload/sdm_ijin/".$row['namafile']."' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
				</td>";
			}
			else
			{
				$tab.="<td style='text-align:center'>
					<a href='fileupload/sdm_ijin/".$row['namafile']."' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
				</td>";
			}
			$tab.="<td><a href='fileupload/sdm_ijin/".$row['namafile']."' download>".substr($row['namafile'],0,30)."...</a></td>";
			$tab.="<td style='text-align:center'>
				<img title='Delete' class=resicon onclick=\"deletefile('".$row['namafile']."')\" src='images/delete_32.png'/
			</td>";
			$tab.="</tr>";
		}
		
		echo $tab;
	break;
	
	case 'deletefile':
		foreach($_SESSION['buktiizin'] as $key=>$row)
		{
			if($row['namafile'] == $namafile)
			{
				$path = "fileupload/sdm_ijin/".$namafile;
				unlink($path);
				unset($_SESSION['buktiizin'][$key]);
			}
		}
	break;
	
	case 'cancelForm':
		foreach($_SESSION['buktiizin'] as $key=>$row)
		{
			$path = "fileupload/sdm_ijin/".$row['namafile'];
			unlink($path);
			unset($_SESSION['buktiizin'][$key]);
		}
	break;
	
	case 'fillField':
		$_SESSION['buktiizin'] = array();
		$str="select * from ".$dbname.".listfile_sdm_ijin where notransaksi='".$tglijin."".$_SESSION['standard']['userid']."'";
		$res=fetchdata($str);
		foreach($res as $key=>$val){
			$newdata = array(
				'namafile'=>$val['namafile']
			);
			array_push($_SESSION['buktiizin'],$newdata);
		}
	break;

    case'getjumlahcuti':

		$hariawal=$tgl1;
		$hariakhir=$tgl2;
		$dt1 = strtotime($hariawal);
		$dt2 = strtotime($hariakhir);
		$diff = $dt2-$dt1;
		$diff=$diff/86400;

		#$jumlahhari=$diff+1;
		
		$jumlahhari = selisitgl($hariakhir,$hariawal)+1;
		$n=$jumlahhari;
			
		$tglcuti='';
		$no="";
		for ($i=0; $i < $n ; $i++) { 

			$whr=" and (kebun='GLOBAL' or kebun='".$_SESSION['empl']['lokasitugas']."')";
			if ($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
				$whr=" and (kebun='GLOBAL' or kebun='HOLDING' or kebun='".$_SESSION['empl']['lokasitugas']."')";
			}

			#cek apakah tanggal termasuk hari libur
			$tglcuti= date("Ymd",strtotime("+".$i." Day",strtotime($tgl1)));	
			$str="select * from ".$dbname.".sdm_5harilibur where tanggal='".$tglcuti."'".$whr;
			$res=fetchData($str);
			$jmlhbaris=count($res);
			if ($jmlhbaris>0) {
				$jumlahhari=$jumlahhari-1;
			}
			
			$no++;	
		}
		
		#ambil status potongan
		#jika statuspotongan 0, jumlah hari 0 (tidak berpengaruh ke cuti)
		$strcuti="select statuspotongan from ".$dbname.".sdm_5jenisijin where idjenis='".$jnsIjin."'";
		$rescuti=$owlPDO->query($strcuti) or die(print " Gagal: ".PDOException::getMessage());
		$rescuti->setFetchMode(PDO::FETCH_ASSOC);
		$barcuti=$rescuti->fetch();
		$statuspotongan=$barcuti['statuspotongan'];
		
		if ($statuspotongan==0 and $jnsIjin!='CUTI18') {
			$jumlahhari=0;
		}
		
		if($jumlahhari<0){
			$jumlahhari=0;
		}
		
    	echo number_format($jumlahhari);

	break;

	case'getjumlahcutireal':

    	#cek cuti yang hanya boleh perjam
    	$arrjnscutijam=Array(); 
		$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='CT' and kodeparameter='CTJAM'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch(); 
		$arrctjam=explode(',',$bar['nilai']);
		foreach($arrctjam as $key){
			$arrjnscutijam[]=$key;
		}

		if ($tgl1real!='' && $tgl1real!='--' && $tgl2real!='' && $tgl2real!='--') {
			if(in_array($jnsIjin,$arrjnscutijam)){
				if ($tgl1real!=$tgl2real) {
					exit('warning : jenis izin ini tidak boleh lebih dari sehari (hanya izin perjam).');
				}
			
				#ambil jam/HK
				$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='CT' and kodeparameter='CTJAMHK'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch(); 
				$arrctjamhk=explode(',',$bar['nilai']);


				if ($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
					
					$jamhk=$arrctjamhk[0];
					$jamhkdetik=$jamhk*3600;

				}else{

					$jamhk=$arrctjamhk[1];

					$hari=date('D', strtotime($tgl1real));
					if ($hari=='Sat') {
						$jamhk=$arrctjamhk[2];
					}
		            
					$jamhkdetik=$jamhk*3600;
				}

				$hariawal=$jamDr1real;
				$hariakhir=$jamSmp1real;
				$dt1 = strtotime($hariawal);
				$dt2 = strtotime($hariakhir);
				$diff = $dt2-$dt1;
				$selisihjam=$diff/$jamhkdetik;
				$jumlahhari=$selisihjam;

			}else{

				$hariawal=$tgl1real;
				$hariakhir=$tgl2real;
				$dt1 = strtotime($hariawal);
				$dt2 = strtotime($hariakhir);
				$diff = $dt2-$dt1;
				$diff=$diff/86400;
				$jumlahhari=$diff+1;
				//exit('error'.$hariakhir);
				$tglcuti='';
				for ($i=0; $i < $jumlahhari ; $i++) { 

					$whr=" and kebun='GLOBAL' or kebun='".$_SESSION['empl']['lokasitugas']."' ";
					if ($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
						$whr=" and kebun='GLOBAL' or kebun='HOLDING' or kebun='".$_SESSION['empl']['lokasitugas']."' ";
					}

					#cek apakah tanggal termasuk hari libur
					$tglcuti= date("Ymd",strtotime("+".$i." Day",strtotime($tgl1)));	
					$str="select * from ".$dbname.".sdm_5harilibur where tanggal='".$tglcuti."'".$whr;
					$res=fetchData($str);
					$jmlhbaris=count($res);
					if ($jmlhbaris>0) {
						$jumlahhari=$jumlahhari-1;
					}
				}
			}
		}

		#ambil status potongan
		#jika statuspotongan 0, jumlah hari 0 (tidak berpengaruh ke cuti)
		/*$strcuti="select statuspotongan from ".$dbname.".sdm_5jenisijin where idjenis='".$jnsIjin."'";
		$rescuti=$owlPDO->query($strcuti) or die(print " Gagal: ".PDOException::getMessage());
		$rescuti->setFetchMode(PDO::FETCH_ASSOC);
		$barcuti=$rescuti->fetch();
		$statuspotongan=$barcuti['statuspotongan'];

		if ($statuspotongan==0) {
			$jumlahhari=0;
		}*/


    	echo number_format($jumlahhari,2);

	break;
	

	case'formreal':

		/*$jm=$mnt="";
		for($i=0;$i<24;)
		{
			if(strlen($i)<2)
			{
				$i="0".$i;
			}
			$jm.="<option value=".$i.">".$i."</option>";
			$i++;
		}

		for($i=0;$i<60;)
		{
			if(strlen($i)<2)
			{
				$i="0".$i;
			}
			$mnt.="<option value=".$i.">".$i."</option>";
			$i++;
		}*/

		$tgl=substr(tanggalsystem($tanggal),0,4)."-".substr(tanggalsystem($tanggal),4,2)."-".substr(tanggalsystem($tanggal),6,2);
		$darijam=substr(tanggalsystem($jamDr),0,4)."-".substr(tanggalsystem($jamDr),4,2)."-".substr(tanggalsystem($jamDr),6,2);
		$sampaijam=substr(tanggalsystem($jamSmp),0,4)."-".substr(tanggalsystem($jamSmp),4,2)."-".substr(tanggalsystem($jamSmp),6,2);
	
		$str="select * from ".$dbname.".sdm_ijin where 
				  tanggal='".$tgl."' and darijam like '%".$darijam."%' and sampaijam like '%".$sampaijam."%' and karyawanid='".$karyawanid."'";
	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optdata="";
		$rdata=$res->fetch();
			//$optdata.="<option value='".$rdata['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
		
		
		$tab="<fieldset style=width:500px;>
			<legend>Realisasi Cuti</legend>";
		$tab.="<table cellspacing=1 border=0>
			<tr hidden>
				<td>Tanggal Realisasi</td>
				<td>:</td>
				<td><input class=myinputtext style=width:165px type=\"text\" id=\"tglreal\" name=\"fnopp\"  value='".date('d-m-Y')."' disabled></td>
			</tr>
			<tr>
		<td>".$_SESSION['lang']['dari']."  ".$_SESSION['lang']['tanggal']." </td>
		<td>:</td>
		<td>
			<input type='text' class='myinputtext' onchange='getjumlahcutireal()' style='width:100px;' id='tglAwalreal' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style='width:150px;' readonly/>
			
		</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tglcutisampai']."  ".$_SESSION['lang']['tanggal']." & ".$_SESSION['lang']['jam']."</td>
			<td>:</td>
			<td>
				<input type='text' class='myinputtext' onchange='getjumlahcutireal()'  style='width:100px;' id='tglEndreal' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style='width:150px;' readonly/>
				
			</td>
		</tr>
				<td><td><td>
					<button class=mybutton onclick=save_real('".$tgl."') >Submit</button>
					<button class=mybutton onclick=reset_data_setuju()>".$_SESSION['lang']['cancel']."</button>
				</td>
			</tr>
		</table>
		</fieldset>";
		echo $tab;
	break;

	case'savereal':

	$tgl1=substr(tanggalsystemd($tglreal),0,10);
	$tgl2=$tanggal;

	$dt1 = strtotime($tgl2);
	$dt2 = strtotime($tgl1);
	$diff = $dt2-$dt1;
	$diff=$diff/86400;

	if ($diff >30) {
		exit('Warning : Tanggal realisasi sudah lewat 1 bulan dari tangggal pengajuan cuti');
	}

	$tgl=$tanggal;
	$sUp = "update  " . $dbname . ".sdm_ijin set waktupengajuanreal='" . tanggalsystem($tglreal) . "', darijamreal='" . tanggalsystem($tglAwalreal)."',sampaijamreal='" . tanggalsystem($tglEndreal)."',jumlahhari='".$hk."' where tanggal='".$tgl."' and karyawanid=" . $_SESSION['standard']['userid'] . "";
    #exit('error'.$sUp);
    try
	{
	$owlPDO->exec($sUp);
	}
	catch (PDOException $e) 
	{
	echo " Gagal," . addslashes($e->getMessage());
	}
                         
    break;

	case'formbatalcuti':
		$tab="<center>";
		$tab.="<table>
				<tr>
					<td>Alasan Pembatalan Cuti : </td>
				</tr>
				<tr>
					<td><textarea id='alasanbatalcuti'  style='width:360px;'  onkeypress=return tanpa_kutip(event);></textarea></td>
				</tr>
				<tr>
					<td align=center><button class=mybutton onclick=prosesbatalcuti()>".$_SESSION['lang']['save']."</button></td>
				</tr>
				<input hidden id=notrbatal value=".$notrans.">
				<input hidden  id=persetujuan1batal value=".$per['persetujuan1'].">
				<input hidden  id=persetujuan2batal value=".$per['persetujuan2'].">
				<input hidden  id=persetujuan3batal value=".$per['persetujuan3'].">
		</table></fieldset>";
		$tab.="</center>";
		echo $tab;
	break;
	
	case'batalcuti':
		$notrx=$notrans;
		$tglhi = date("Y-m-d");
		$str="select * from ".$dbname.".sdm_ijin where notransaksi='".$notrx."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$rdata=$res->fetch();
		$idjenis = $rdata['idjenis'];
		$tglcuti = tanggalnormal($rdata['darijam']);

		// $str="select * from ".$dbname.".setup_approval where jenispersetujuan='IJSC' and level='1' and kodeunit='BPPE'";
		// $res=fetchData($str);
		// exit("warning".count($res));

		if($tglhi>=tanggalsystemn($tglcuti)){
			$str="delete from ".$dbname.".approval where notransaksi='".$notrx."' and jenispersetujuan='IJSC'";
			$owlPDO->exec($str);
		
			#nama penyetuju sama dengan kode IJS
			# get departemen
			$optdepartmen=makeOption($dbname,'datakaryawan','karyawanid,bagian',"karyawanid='".$_SESSION['standard']['userid']."'");
			$optGolongan=makeOption($dbname,'datakaryawan','karyawanid,kodegolongan',"karyawanid='".$_SESSION['standard']['userid']."'");
			$optNamaGolongan=makeOption($dbname, "sdm_5golongan", "kodegolongan,namagolongan", "aktif='1'");
			$departemen=$optdepartmen[$_SESSION['standard']['userid']];
			$golongan=$optNamaGolongan[$optGolongan[$_SESSION['standard']['userid']]];

			$countApp = getCountApproval('IJSC',$_SESSION['empl']['lokasitugas'], $departemen, substr($golongan, 0,1));

			if($countApp == 0) {
				exit('Warning: Tolong Isi Setup Approval sesuai dengan golongan, jenispersetujuan dan kodeunit anda');
			}
			
			for($i=1;$i<=$countApp;$i++){
				$str="select * from ".$dbname.".setup_approval where jenispersetujuan='IJSC' and level='".$i."' and kodeunit='".$_SESSION['empl']['lokasitugas']."' AND departemen = '".$departemen."' AND golongan='".substr($golongan, 0,1)."' ORDER BY level ASC";
				$res=fetchData($str);
				$tipeapp = $res[0]['tipe'];
				$departemenapp = $res[0]['departemen'];
				$tipekaryawanapp = $res[0]['tipekaryawan'];
				$jabatanapp = $res[0]['jabatan'];
				if(count($res) > 0){
					if($tipeapp=='1'){
						if($departemenapp!=''){
							$str="select * from ".$dbname.".datakaryawan where bagian='".$departemenapp."'";
							$res=fetchdata($str);
							foreach($res as $keyx=>$valx){
								$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notrx."','IJSC','".$i."','".$valx['karyawanid']."','0')";
								$owlPDO->exec($str);
							}
						}
						if($tipekaryawanapp!=''){
							$str="select * from ".$dbname.".datakaryawan where tipekaryawan='".$tipekaryawanapp."'";
							$res=fetchdata($str);
							foreach($res as $keyx=>$valx){
								$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notrx."','IJSC','".$i."','".$valx['karyawanid']."','0')";
								$owlPDO->exec($str);
							}
						}
						if($jabatanapp!='0'){
							if($per['persetujuan'.$i]!=''){
								$str="select * from ".$dbname.".datakaryawan where kodejabatan='".$jabatanapp."'";
								$res=fetchdata($str);
								foreach($res as $keyx=>$valx){
									$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notrx."','IJSC','".$i."','".$valx['karyawanid']."','0')";
									$owlPDO->exec($str);
								}
							}
						}
					}else{
						if($per['persetujuan'.$i]!=''){
							$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notrx."','IJSC','".$i."','".$per['persetujuan'.$i]."','0')";
							try{$owlPDO->exec($str);}catch (PDOException $e) {echo " Gagal," . addslashes($e->getMessage());}
						}
					}
				}
			}

			$str="update ".$dbname.".sdm_ijin set statuspersetujuan_cancel=9, alasanbatal='".$alasanbatalcuti."' where notransaksi='".$notrx."'";
			try{$owlPDO->exec($str);}catch (PDOException $e) {echo " Gagal," . addslashes($e->getMessage());}
			
		} else {
			// cancel cuti ketika tanggal cuti kurang dari tanggal hari ini
			$str="select b.lokasitugas, a.jumlahhari, a.karyawanid, a.periodecuti, a.darijam, a.sampaijam, a.jenisijin, a.idjenis  from ".$dbname.".sdm_ijin a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.notransaksi='".$notrx."'";
			$res=fetchdata($str);
			$kodeorg = $res[0]['lokasitugas'];
			$tanggalnow = date("Y-m-d");
			if ($tanggalnow < substr($res[0]['sampaijam'], 0, 10)) {
				$tgl1 = new DateTime($tanggalnow);
				$tgl2 = new DateTime(substr($res[0]['sampaijam'], 0, 10));
				$difftgl = $tgl2->diff($tgl1);
				$jumlahhari = $difftgl->d + 1;
			} else {
				$jumlahhari=$res[0]['jumlahhari'];
			}
			$karyawancuti=$res[0]['karyawanid'];
			$periodecuti=$res[0]['periodecuti'];
			$daritanggal=substr($res[0]['darijam'],0,10);
			$sampaitanggal=substr($res[0]['sampaijam'],0,10);
			$strspl="update ".$dbname.".sdm_ijin set statuspersetujuan_cancel='1' where notransaksi='".$notrx."' ";
			try{$owlPDO->exec($strspl);
				
				if($idjenis=='CUTI18'){
					$strdyoff="select akandiambil,tanggalberlakusampai,tanggaldayoff,notransaksi,karyawanid from ".$dbname.".sdm_dayoff_dt where notransaksicuti='".$notrx."'";
					$resdyoff=$owlPDO->query($strdyoff) or die(print " Gagal: ".PDOException::getMessage());
					$resdyoff->setFetchMode(PDO::FETCH_OBJ);
					while ($bardyoff = $resdyoff->fetch()) {
						$strupdyoff="update ".$dbname.".sdm_dayoff_dt set diambil='0', notransaksicuti='' where notransaksicuti='".$notrx."' and tanggaldayoff='".$bardyoff->tanggaldayoff."' and tanggalberlakusampai='".$bardyoff->tanggalberlakusampai."' and notransaksi='".$bardyoff->notransaksi."' and karyawanid='".$bardyoff->karyawanid."'";
						$owlPDO->exec($strupdyoff);
					}
				}
					
				#= update sisa hak cuti
				$str="update ".$dbname.".sdm_cutiht set diambil=(diambil-".$jumlahhari."),sisa=(sisa+".$jumlahhari.") 
				where karyawanid='".$karyawancuti."' and periodecuti='".$periodecuti."'";
				try{$owlPDO->exec($str);}catch (PDOException $e){print " Gagal  !: " . $e->getMessage() . "\n";die();}
				
				#= delete dt
				$str="delete from ".$dbname.".sdm_cutidt where karyawanid='".$karyawancuti."' and kodeorg='".$kodeorg."' and periodecuti='".$periodecuti."' and daritanggal='".$daritanggal."' and sampaitanggal='".$sampaitanggal."'";
				try{$owlPDO->exec($str);}catch (PDOException $e){print " Gagal  !: " . $e->getMessage() . "\n";die();}

			}
			catch (PDOException $e){print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}
		
	break;
	
}
?>