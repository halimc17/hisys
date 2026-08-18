<?php

session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$tanggalkerja = tanggalsystem(checkPostGet('tanggalkerja', ''));
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
$tgl1 = $tglAwal[2] . "-" . $tglAwal[1] . "-" . $tglAwal[0];
$tglEnd = explode("-", checkPostGet('tglEnd', '00-00-0000'));
$tgl2 = $tglEnd[2] . "-" . $tglEnd[1] . "-" . $tglEnd[0];
$jamDr1 = $tgl1 . " " . $jamDr;
$jamSmp1 = $tgl2 . " " . $jamSmp;
$arrNmkary = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$arrKeputusan = array("0" => $_SESSION['lang']['diajukan'], "1" => $_SESSION['lang']['disetujui'], "2" => $_SESSION['lang']['ditolak']);
$where = " tanggal='" . $tglijin . "' and karyawanid='" . $_SESSION['standard']['userid'] . "'";
$atsSblm = checkPostGet('atsSblm', '');
$atsSblm2 = checkPostGet('atsSblm2', '');
$hk = checkPostGet('jumlahhk', '');
$hrd = checkPostGet('hrd', '');
$periodec = checkPostGet('periodec', '');

if($proses == 'update' or $proses == 'insert'){
	if($jnsIjin == 'CUTI'){

		$strf = "select sisa from " . $dbname . ".sdm_cutiht where karyawanid=" . $_SESSION['standard']['userid'] . " 
                    and periodecuti=" . $periodec;
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
		$strfx = "select max(periodecuti) as periodecuti from " . $dbname . ".sdm_cutiht 
					where karyawanid=" . $_SESSION['standard']['userid'];
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
				$str = "select sisa from " . $dbname . ".sdm_cutiht where karyawanid=" . $bar1->karyawanid . " 
												   and periodecuti>" . ($periodec - 2) . " order by periodecuti desc limit 1";
				$resx=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$resx->setFetchMode(PDO::FETCH_OBJ);
				$sisalalu = 0;
				while ($barx = $resx->fetch()) {
					$sisalalu = $barx->sisa;
				}
				#periksa apakah sudah ada pada periode yang sama
				$str = "select * from " . $dbname . ".sdm_cutiht where karyawanid=" . $bar1->karyawanid . " 
												   and periodecuti=" . $periodec . " order by periodecuti desc limit 1";
				$resy=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$numrows=owlBaris($resy);
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
					#maka insert periode baru
					$str = "insert into " . $dbname . ".sdm_cutiht(kodeorg, karyawanid, periodecuti, keterangan, dari, sampai, hakcuti, diambil, sisa)
													   values('" . $bar1->lokasitugas . "'," . $bar1->karyawanid . "," . $periodec . ",''," . $dari . "," . $sampai . "," . $hakcuti . ",0," . $saldo . ")";
					try{
						$owlPDO->exec($str); 
					}catch (PDOException $e){
						
					}
				}
			}
		}

		function getRangeTanggal($tglAwal, $tglAkhir) {
			$jlh = strtotime($tglAkhir) - strtotime($tglAwal);
			$jlhHari = $jlh / (3600 * 24);
			return $jlhHari + 1;
		}

		if (getRangeTanggal($_POST['tglAwal'], $_POST['tglEnd']) <= 0) {
			exit("Gagal : Periksa kembali periode tanggal cuti. Tanggal Awal lebih besar dari tanggal sampai.");
		}

		// if (getRangeTanggal($_POST['tglAwal'], $_POST['tglEnd']) != $hk) {
			// exit("Gagal : Periksa kembali periode tanggal cuti, tidak sesuai dengan jumlah HK yang diambil.");
		// }

		$strf = "select sisa from " . $dbname . ".sdm_cutiht where karyawanid=" . $_SESSION['standard']['userid'] . " 
						   and periodecuti=" . $periodec;
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
	
	if(($jnsIjin=='PULANGAWALPOTONGGAJI' || $jnsIjin=='TERLAMBATPOTONGGAJI' || $jnsIjin=='CUTIPOTONGGAJI')){
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

		function getRangeTanggal($tglAwal, $tglAkhir) {
			$jlh = strtotime($tglAkhir) - strtotime($tglAwal);
			$jlhHari = $jlh / (3600 * 24);
			return $jlhHari + 1;
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

		if ($hk > $sisa) {
			exit("Gagal : Jumlah HK(Hari) melebihi jumlah sisa cuti untuk periode " . $periodec . ".");
		}
	}
}


switch ($proses) {

    case'insert':
		if (($tglijin == '') || ($tanggalkerja=='') || ($alamatcuti=='') || ($pengganti=='') || ($jnsIjin == '') || ($jamDr1 == '') || ($jamSmp1 == '') || ($keperluan == '') || ($atasan == '') || ($atasan2 == '')) 
		{
            echo"warning:Please Complete The Form";
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
			
			#COUNT HOMETRIP
			$str="select count(karyawanid) as counttrip from ".$dbname.".sdm_ijin where karyawanid='".$_SESSION['standard']['userid']."' and periodecuti = '".$periodec."' and hometrip='1'";
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
				echo"warning : Quta Hometrip sudah melebihi batas. (".$jatahtrip." kali)";
				exit();
			}
		}

        #periksa apakah periode yang diambil sudah lewar 1.5 tahun
        if ($jnsIjin == 'CUTI') {
            #periksa apakah sudah boleh cuti:
            // $tahunmulaiCuti = substr($_SESSION['empl']['signdate'], 0, 4) + 1;
            // $tanggalAwalCuti = $tahunmulaiCuti . substr($_SESSION['empl']['signdate'], 4, 6);
            #kebijakan baru karyawan boleh cuti ketika sudah lewat 3 bulan dari masuk kerja
			$sKaryawan = "select tanggalmasuk from " . $dbname . ".datakaryawan
			where  karyawanid='" . $_SESSION['standard']['userid'] . "'";
			$rKaryawan=$owlPDO->query($sKaryawan);
			$rowKaryawan = $rKaryawan->fetch();
			$tanggalMasukEmpl = $rowKaryawan['tanggalmasuk'];
			$tanggalBolehCutiEmpl = date('Y-m-d', strtotime($tanggalMasukEmpl. ' + 3 month'));
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

            $dudu = mktime(0, 0, 0, $bulanAkh + 6, $tanggalAkh, $tahunAkh);
            $akhirBanget = date('Y-m-d', $dudu);
            if (substr($jamSmp1, 0, 10) > $akhirBanget) {
                #keluarkan disini jika sudah lebih dari 1.5 tahun
                exit("Error: Maaf, Cuti atas masa bakti tahun " . $periodec . " berakhir pada " . date('d-m-Y', $dudu));
            }
        }

        #==== end satu setengah tahun

		
		if ($jnsIjin=='PULANGAWALPOTONGGAJI' || $jnsIjin=='TERLAMBATPOTONGGAJI' || $jnsIjin=='CUTIPOTONGGAJI'){
			#periksa apakah sudah boleh cuti:
            // $tahunmulaiCuti = substr($_SESSION['empl']['signdate'], 0, 4) + 1;
            // $tanggalAwalCuti = $tahunmulaiCuti . substr($_SESSION['empl']['signdate'], 4, 6);
            #kebijakan baru karyawan boleh cuti ketika sudah lewat 3 bulan dari masuk kerja
			$sKaryawan = "select tanggalmasuk from " . $dbname . ".datakaryawan
			where  karyawanid='" . $_SESSION['standard']['userid'] . "'";
			$rKaryawan=$owlPDO->query($sKaryawan);
			$rowKaryawan = $rKaryawan->fetch();
			$tanggalMasukEmpl = $rowKaryawan['tanggalmasuk'];
			$tanggalBolehCutiEmpl = date('Y-m-d', strtotime($tanggalMasukEmpl. ' + 3 month'));
			if (substr($jamDr1, 0, 10) < $tanggalBolehCutiEmpl) {
				exit("Error: Anda belum memiliki hak cuti, hak cuti akan muncul pada tanggal: " . $tanggalBolehCutiEmpl);
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
                exit("Error: Maaf, Cuti atas masa bakti tahun " . $periodec . " berakhir pada " . date('d-m-Y', $dudu));
            }
		}
        $wktu = "0000-00-00 00:00:00";
        $sCek = "select tanggal from " . $dbname . ".sdm_ijin where  " . $where . ""; //echo "warning:".$sCek;
		$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		$qCek->setFetchMode(PDO::FETCH_OBJ);
		$rCek=owlBaris($qCek);
        if ($rCek < 1) {
			exit("tester2");
            if ($atasan != '') {
                $wktu = date("Y-m-d H:i:s");
            }
			
			if($hometrip==true)
			{
				$hmtrp = '1';
			}
			else
			{
				$hmtrp = '0';
			}
			
            $sIns = "insert into " . $dbname . ".sdm_ijin (karyawanid, tanggal, keperluan, keterangan, persetujuan1, 
									persetujuan4,waktupengajuan, darijam, sampaijam, idjenis,hrd,periodecuti,jumlahhari,
									alamatcuti,tanggalkerja,pengganti,nohp,hometrip,tanggalberangkat,rutekeberangkatan,tglpulang,rutekepulangan) 
                        values ('" . $_SESSION['standard']['userid'] . "','" . $tglijin . "','" . $keperluan . "','" . $ket . "',
								'" . $atasan . "','" . $atasan2 . "','" . $wktu . "','" . $jamDr1 . "','" . $jamSmp1 . "',
								'" . $jnsIjin . "'," . $hrd . "," . $periodec . "," . $hk . ",
								'" . $alamatcuti . "','" . $tanggalkerja . "','" . $pengganti . "','".$nohp."','".$hmtrp."','".$tglberangkat."','".$rutekeberangkatan."','".$tglpulang."','".$rutekepulangan."')";
			try{
				$owlPDO->exec($sIns); 
				
				if ($atasan != '') {
                    #send an email to incharge person
                    $to = getUserEmail($atasan);
                    $namakaryawan = getNamaKaryawan($_SESSION['standard']['userid']);
                    $subject = "[Notifikasi]Persetujuan Ijin Keluar Kantor a/n " . $namakaryawan;
                    $body = "<html>
                                                     <head>
                                                     <body>
                                                       <dd>Dengan Hormat,</dd><br>
                                                       <br>
                                                       Pada hari ini, tanggal " . date('d-m-Y') . " karyawan a/n  " . $namakaryawan . " mengajukan Ijin/" . $jnsIjin . " (" . $keperluan . ")
                                                       kepada bapak/ibu. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
                                                       <br>
                                                       <br>
                                                       Note: Sisa cuti ybs periode " . $periodec . ":" . $sisa . " Hari
                                                       <br>
                                                       <br>
                                                       Regards,<br>
                                                       Owl-Plantation System.
                                                     </body>
                                                     </head>
                                                   </html>
                                                   ";
                    $kirim = kirimEmail($to, '', $subject, $body); #this has return but disobeying;
                    #117 
                    #print_r($_SESSION['empl']['regional']);
                    ##send email ke roa jika cuti
                   
                }
				
				if ($atasan2 != '') {
                    #send an email to incharge person
                    $to = getUserEmail($atasan2);
                    $namakaryawan = getNamaKaryawan($_SESSION['standard']['userid']);
                    $subject = "[Notifikasi]Persetujuan Ijin Keluar Kantor a/n " . $namakaryawan;
                    $body = "<html>
                                                     <head>
                                                     <body>
                                                       <dd>Dengan Hormat,</dd><br>
                                                       <br>
                                                       Pada hari ini, tanggal " . date('d-m-Y') . " karyawan a/n  " . $namakaryawan . " mengajukan Ijin/" . $jnsIjin . " (" . $keperluan . ")
                                                       kepada bapak/ibu. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
                                                       <br>
                                                       <br>
                                                       Note: Sisa cuti ybs periode " . $periodec . ":" . $sisa . " Hari
                                                       <br>
                                                       <br>
                                                       Regards,<br>
                                                       Owl-Plantation System.
                                                     </body>
                                                     </head>
                                                   </html>
                                                   ";
                    $kirim = kirimEmail($to, '', $subject, $body); #this has return but disobeying;
                    #117 
                    #print_r($_SESSION['empl']['regional']);
                    ##send email ke roa jika cuti
                   
                }
			}catch (PDOException $e){
				echo "DB Error : " . $e->getMessage();
				die();
			}
        } else {
            exit("Error:Data Pada Tanggal " . $_POST['tglijin'] . " Sudah ada");
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

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".sdm_ijin where karyawanid='" . $_SESSION['standard']['userid'] . "'  order by `tanggal` desc";
		$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }

        $slvhc = "select * from " . $dbname . ".sdm_ijin where karyawanid='" . $_SESSION['standard']['userid'] . "'   order by `tanggal` desc limit " . $offset . "," . $limit . " ";
		$qlvhc=$owlPDO->query($slvhc) or die(print " Gagal: ".PDOException::getMessage());
		$qlvhc->setFetchMode(PDO::FETCH_ASSOC);
        $user_online = $_SESSION['standard']['userid'];
        while ($rlvhc = $qlvhc->fetch()) {
            $no+=1;
            $nmAkun = makeOption($dbname, 'sdm_5jenisijin', 'idjenis,jenisijin');

            echo"
                <tr class=rowcontent>
                <td>" . $no . "</td>
                <td>" . tanggalnormal($rlvhc['tanggal']) . "</td>
                <td>" . $rlvhc['keperluan'] . "</td>

                <td align=left>" . (isset($nmAkun[$rlvhc['idjenis']]) ? $nmAkun[$rlvhc['idjenis']] : '') . "</td>
                <td style='text-align:center'>".($rlvhc['hometrip']=='1'?'Ya':'Tidak')."</td>
                <td>" . $arrNmkary[$rlvhc['persetujuan1']] . "</td>
                <td>" . $arrKeputusan[$rlvhc['stpersetujuan1']] . "</td>
				<td>" . $arrNmkary[$rlvhc['persetujuan4']] . "</td>
                <td>" . $arrKeputusan[$rlvhc['stpersetujuan4']] . "</td>
                <td>" . tanggalnormald($rlvhc['darijam']) . "</td>
                <td>" . tanggalnormald($rlvhc['sampaijam']) . "</td>";
            if ($rlvhc['stpersetujuan1'] == 0 and $rlvhc['stpersetujuan4'] == 0 and empty($rlvhc['stpersetujuanrd'])) {
                echo"<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $rlvhc['keperluan'] . "','" . tanggalnormal($rlvhc['tanggal']) . "',
				'" . $rlvhc['idjenis'] . "','" . $rlvhc['persetujuan1'] . "','" . $rlvhc['stpersetujuan1'] . "','" . $rlvhc['persetujuan4'] . "',
				'" . $rlvhc['stpersetujuan4'] . "','" . $rlvhc['darijam'] . "','" . $rlvhc['sampaijam'] . "','" . $rlvhc['hrd'] . "',
				'" . $rlvhc['jumlahhari'] . "','" . $rlvhc['periodecuti'] . "','" . $rlvhc['keterangan'] . "','" . $rlvhc['alamatcuti'] . "',
				'" . tanggalnormal($rlvhc['tanggalkerja']) . "','" . $rlvhc['pengganti'] . "','".$bar['nohp']."','".$bar['hometrip']."','".tanggalnormal($bar['tanggalberangkat'])."','".$bar['rutekeberangkatan']."','".tanggalnormal($bar['tglpulang'])."','".$bar['rutekepulangan']."');\">
                    <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('" . tanggalnormal($rlvhc['tanggal']) . "');\" >
					<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"previewPdf('" . tanggalnormal($rlvhc['tanggal']) . "','" . $rlvhc['karyawanid'] . "',event)\"></td>";
                //<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_ijin','".$rlvhc['tanggal'].",".$rlvhc['karyawanid']."','','sdm_slave_ijin_meninggalkan_kantor',event)\"></td>";
            } else {
                echo "<td><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"previewPdf('" . tanggalnormal($rlvhc['tanggal']) . "','" . $rlvhc['karyawanid'] . "',event)\"></td>";
                // echo"<td> <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_ijin','".$rlvhc['tanggal'].",".$rlvhc['karyawanid']."','','sdm_slave_ijin_meninggalkan_kantor',event)\"></td>";
            }//end if updateby
        }//end while
        echo"
                </tr><tr class=rowheader><td colspan=11 align=center>
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
        $sket = "select distinct stpersetujuan1 from " . $dbname . ".sdm_ijin where " . $where . "";
		$qKet=$owlPDO->query($sket) or die(print " Gagal: ".PDOException::getMessage());
		$qKet->setFetchMode(PDO::FETCH_ASSOC);
        $rKet = $qKet->fetch();
        if ($rKet['stpersetujuan1'] == 0) {
            $sDel = "delete from " . $dbname . ".sdm_ijin where " . $where . "";
            
			try{
				$owlPDO->exec($sDel); 
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
			
        if (($jnsIjin == '') || ($jamDr == '') || ($jamSmp == '') || ($keperluan == '') || ($atasan == '') || ($atasan2 == '') || $tanggalkerja=='' || $alamatcuti=='' || $pengganti=='') {
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
		
	
		
		// if($atasan==$atasan2){
			// echo"warning:Penyetuju 1 dann Penyetuju 2 harus berbeda.";
            // exit();
		// }
        $sket = "select distinct stpersetujuan1 from " . $dbname . ".sdm_ijin where " . $where . "";
		$qKet=$owlPDO->query($sket) or die(print " Gagal: ".PDOException::getMessage());
		$qKet->setFetchMode(PDO::FETCH_ASSOC);
        $rKet = $qKet->fetch();
        if ($rKet['stpersetujuan1'] == 0) {
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
				
				if ($atsSblm != $atasan) {
                    #send an email to incharge person
                    $to = getUserEmail($atasan);
                    $namakaryawan = getNamaKaryawan($_SESSION['standard']['userid']);
                    $subject = "[Notifikasi]Persetujuan Ijin Keluar Kantor a/n " . $namakaryawan;
                    $body = "<html>
                                                     <head>
                                                     <body>
                                                       <dd>Dengan Hormat,</dd><br>
                                                       <br>
                                                       Pada hari ini, tanggal " . date('d-m-Y') . " karyawan a/n  " . $namakaryawan . " mengajukan Ijin/" . $jnsIjin . " (" . $keperluan . ")
                                                       kepada bapak/ibu. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
                                                       <br>
                                                       <br>
                                                       Note: Sisa cuti ybs periode " . $periodec . ":" . (isset($sisa) ? $sisa : 0) . " Hari
                                                       <br>
                                                       <br>
                                                       Regards,<br>
                                                       Owl-Plantation System.
                                                     </body>
                                                     </head>
                                                   </html>
                                                   ";
                    $kirim = kirimEmail($to, '', $subject, $body); #this has return but disobeying;
				}
					
				if ($atsSblm2 != $atasan2) {
					#send an email to incharge person
                    $to = getUserEmail($atasan2);
                    $namakaryawan = getNamaKaryawan($_SESSION['standard']['userid']);
                    $subject = "[Notifikasi]Persetujuan Ijin Keluar Kantor a/n " . $namakaryawan;
                    $body = "<html>
                                                     <head>
                                                     <body>
                                                       <dd>Dengan Hormat,</dd><br>
                                                       <br>
                                                       Pada hari ini, tanggal " . date('d-m-Y') . " karyawan a/n  " . $namakaryawan . " mengajukan Ijin/" . $jnsIjin . " (" . $keperluan . ")
                                                       kepada bapak/ibu. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
                                                       <br>
                                                       <br>
                                                       Note: Sisa cuti ybs periode " . $periodec . ":" . (isset($sisa) ? $sisa : 0) . " Hari
                                                       <br>
                                                       <br>
                                                       Regards,<br>
                                                       Owl-Plantation System.
                                                     </body>
                                                     </head>
                                                   </html>
                                                   ";
                    $kirim = kirimEmail($to, '', $subject, $body); #this has return but disobeying;
                }
			}catch (PDOException $e){
				
			}
        } else {
            exit("Error:Sudah ada keputusan");
        }
        if ($atsSblm != $atasan) {
            $to = getUserEmail($atsSblm);
            $namakaryawan = getNamaKaryawan($_SESSION['standard']['userid']);
            $subject = "[Notifikasi]Pembatalan Persetujuan Ijin Keluar Kantor a/n " . $namakaryawan;
            $body = "<html>
                                                     <head>
                                                     <body>
                                                       <dd>Dengan Hormat,</dd><br>
                                                       <br>
                                                       Pada hari ini, tanggal " . date('d-m-Y') . " karyawan a/n  " . $namakaryawan . " mengajukan Ijin/" . $jnsIjin . " (" . $keperluan . ")
                                                       kepada bapak/ibu. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
                                                       <br>
                                                       <br>
                                                       Note: Sisa cuti ybs periode " . $periodec . ":" . $sisa . " Hari
                                                       <br>
                                                       <br>
                                                       Regards,<br>
                                                       Owl-Plantation System.
                                                     </body>
                                                     </head>
                                                   </html>
                                                   ";
            $kirim = kirimEmail($to, '', $subject, $body); #this has return but disobeying;
        }
		
		if ($atsSblm2 != $atasan2) {
            $to = getUserEmail($atsSblm2);
            $namakaryawan = getNamaKaryawan($_SESSION['standard']['userid']);
            $subject = "[Notifikasi]Pembatalan Persetujuan Ijin Keluar Kantor a/n " . $namakaryawan;
            $body = "<html>
                                                     <head>
                                                     <body>
                                                       <dd>Dengan Hormat,</dd><br>
                                                       <br>
                                                       Pada hari ini, tanggal " . date('d-m-Y') . " karyawan a/n  " . $namakaryawan . " mengajukan Ijin/" . $jnsIjin . " (" . $keperluan . ")
                                                       kepada bapak/ibu. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
                                                       <br>
                                                       <br>
                                                       Note: Sisa cuti ybs periode " . $periodec . ":" . $sisa . " Hari
                                                       <br>
                                                       <br>
                                                       Regards,<br>
                                                       Owl-Plantation System.
                                                     </body>
                                                     </head>
                                                   </html>
                                                   ";
            $kirim = kirimEmail($to, '', $subject, $body); #this has return but disobeying;
        }
        break;
    default:
        break;
}
?>