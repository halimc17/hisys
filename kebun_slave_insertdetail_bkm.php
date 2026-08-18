<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$param = $_POST;
if(count($param)==0){$param = $_GET;} 

if($_SESSION['language']=='EN'){
    $optKegiatan=makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan1');
}else{
	$optKegiatan=makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
}


try {
	$owlPDO->beginTransaction();

    // Cek dulu datanya ada atau gak kebun_prestasi_detail 
    $cekPres = selectQuery($dbname,"kebun_prestasi_detail","*","notransaksi='".$param['notransaksi']."'");
    $rcPres = fetchData($cekPres);
    $countPres = count($rcPres);
    // Jika ada di delete dulu datanya
    if ($countPres > 0) {
        $delPres = deleteQuery($dbname,"kebun_prestasi_detail","notransaksi='".$param['notransaksi']."'");
        $owlPDO->exec($delPres);
    }

    // Cek dulu datanya ada atau gak kebun_pakaimaterial_detail
    $cekMatr = selectQuery($dbname,"kebun_pakaimaterial_detail","*","notransaksi='".$param['notransaksi']."'");
    $rcMatr = fetchData($cekMatr);
    $countMatr = count($rcMatr);
    // Jika ada di delete dulu datanya
    if ($countMatr > 0) {
        $delMatr = deleteQuery($dbname,"kebun_pakaimaterial_detail","notransaksi='".$param['notransaksi']."'");
        $owlPDO->exec($delMatr);
    }

    // Cek dulu datanya ada atau gak kebun_kehadiran_detail
    $cekHadir = selectQuery($dbname,"kebun_kehadiran_detail","*","notransaksi='".$param['notransaksi']."'");
    $rcHadir = fetchData($cekHadir);
    $countHadir = count($rcHadir);
    // Jika ada di delete dulu datanya
    if ($countHadir > 0) {
        $delHadir = deleteQuery($dbname,"kebun_kehadiran_detail","notransaksi='".$param['notransaksi']."'");
        $owlPDO->exec($delHadir);
    }

	if($param['blok']!=''){
		$wh.=" and b.kodeorg='".$param['blok']."'";
	}
	if($param['kegiatan']!=''){
		$wh.=" and b.kodekegiatan='".$param['kegiatan']."'";
	}

    $hasilkerjax = $hkkaryx = $nikkaryx = $indukorgx = $kegiatanx = $notransx = $upahpremix = $umrx = $blokkecilx = $nourutx = $tahuntanamx = array();
	$hasilkerjadt = $stblokdt = $hkkarydt = $upahpremidt = $umrdt = array();

    $dtLoop = "SELECT a.notransaksi, a.nobkm, a.nikpemel, a.kodekegiatan, a.kodeorg, a.hasilkerja, a.jumlahhk,a.nourut, b.umr, a.upahpremi, c.tanggal
            FROM $dbname.kebun_prestasi a LEFT JOIN $dbname.kebun_kehadiran b on a.notransaksi = b.notransaksi and a.nikpemel=b.nik and a.nourut=b.nourut
			LEFT JOIN $dbname.kebun_aktifitas c on a.notransaksi=c.notransaksi 
            WHERE a.notransaksi='".$param['notransaksi']."' ".$wh."";
    $reslopp = fetchData($dtLoop);
    foreach ($reslopp as $val) {
        $indukorgx[$val['kodeorg']] = $val['kodeorg'];
        $kegiatanx[$val['kodeorg']][$val['kodekegiatan']] = $val['kodekegiatan'];
        $nikkaryx[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']] = $val['nikpemel'];
        $hasilkerjax[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']] = $val['hasilkerja'];
        $hkkaryx[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']] = $val['jumlahhk'];
        $notransx[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']] = $val['notransaksi'];
        $nobkmx[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']] = $val['nobkm'];
        $upahpremix[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']] = $val['upahpremi'];
        $umrx[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']] = $val['umr'];
		$nourutx[$notransx[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']]] = 0;
		// $nourutx[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']] = $val['nourut'];

		$tgl=tanggalnormal($val['tanggal']);
		$tgl2=$val['tanggal'];
		$periode = substr($val['tanggal'],0,7);
    }
	
		$tahunbulan=substr($param['notransaksi'], 0,6);
		$kdorgxx=substr($param['notransaksi'], 9,4);
		$strbloktahunan = "SELECT * FROM setup_blok_tahunan
		where tahun = '".$tahunbulan."' and kodeorg like '%".$kdorgxx."%' ";
		$resbloktahunan = fetchdata($strbloktahunan);

		$dbaseblok='setup_blok';
		$whereblok='';
		$whereblok2='';
		if(count($resbloktahunan) > 0) {	
			$dbaseblok='setup_blok_tahunan';
			$whereblok=" and b.tahun = '".$tahunbulan."'";
			$whereblok=" and b.tahun = '".$tahunbulan."'";
		}

	$dtBlok = "SELECT a.notransaksi, a.nobkm, a.nikpemel, a.kodekegiatan, a.kodeorg, b.kodeorg as blokkecil, b.tahuntanam,
				SUM(b.luasareaproduktif + b.luasareanonproduktif) as luasareaproduktif, b.lc, b.luasbloking, b.statusblok
				FROM $dbname.kebun_prestasi a LEFT JOIN ".$dbname.".setup_kegiatan c ON c.kodekegiatan=a.kodekegiatan
				LEFT JOIN ".$dbname.".".$dbaseblok." b ON a.kodeorg = b.indukblok and IF (c.kelompok = 'PNN', 'TM', c.kelompok) = b.statusblok
				WHERE a.notransaksi = '".$param['notransaksi']."' and b.statusblok IN (SELECT kelompok FROM $dbname.setup_kegiatan) ".$whereblok." 
				group by a.kodekegiatan,a.nikpemel,b.kodeorg
				HAVING SUM(b.luasareaproduktif + b.luasareanonproduktif + b.lc + b.luasbloking) > 0";
	$resBlok = fetchData($dtBlok);
	foreach ($resBlok as $val) {
		$blokkecilx[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']][$val['blokkecil']] .= $val['blokkecil'];
		if ($val['luasareaproduktif'] == 0) {
			if ($val['lc'] == 0) {
				if ($val['luasbloking'] > 0) {
					$hasilkerjadt[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']][$val['blokkecil']] = $val['luasbloking'];
				} else {
					$hasilkerjadt[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']][$val['blokkecil']] = 0;
				}
			} else {
				$hasilkerjadt[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']][$val['blokkecil']] = $val['lc'];
			}
		} else {
			$hasilkerjadt[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']][$val['blokkecil']] = $val['luasareaproduktif'];
		}
		
		$tahuntanamx[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']][$val['blokkecil']] = $val['tahuntanam'];
		$stblokdt[$val['kodeorg']][$val['kodekegiatan']][$val['blokkecil']] = $val['statusblok'];
	}

	$jumlahhk = 0;
	$noCekPnn = 0;
	$noCekMtrl=0;
	$errCekPnn="";
	$errCekMtrl="";
	$warnHK = "";
    foreach ($indukorgx as $kdorg) {
        foreach ($kegiatanx[$kdorg] as $kdkeg) {
			foreach ($nikkaryx[$kdorg][$kdkeg] as $nik) {
				// Cek Apakah jumlah HK melebihi 1 di dalam satu hari dengan karyawan tersebut
				$sCekPres = "select a.notransaksi,jhk from ".$dbname.".kebun_kehadiran a
				left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
				where nik='".$nik."' and tanggal='".$tgl2."'";
				$resPres = fetchData($sCekPres);
				foreach ($resPres as $pres) {
					$jumlahhk = $pres['jhk'];
					$warnHK = "No Transaksi => ".$pres['notransaksi']." => ".$pres['jhk']." HK<br>";
				}
	
				// Jika HK nya Lebih Dari 1 maka berikan validasi
				if((floatval($jumlahhk)) > 1){
					throw new PDOException("Jumlah HK karyawan lebih dari 1, HK yang sudah tersimpan sebesar = ".$jumlahhk." HK<br><br> ".$warnHK."");
				}
	
				//  Cek Perawatan
				//  Jika karyawan ada pekerjaan panen, maka HK tidak boleh diinput
				$qAbs = selectQuery($dbname,'kebun_prestasi_vs_hk','SUM(hasilkerja) as jjg',
				"karyawanid='".$nik."' and tanggal='".$tgl2."'");
				$resAbs = fetchData($qAbs);
				$cekPanen = $resAbs[0]['jjg'];
				
				// Jika Jjg ada dan ada HK di bkm rawat maka munculkan validasi
				if(floatval($cekPanen) > 0 and $hkkaryx[$kdorg][$kdkeg][$nik] > 0) {
					$noCekPnn++;
					$errCekPnn .= "".$noCekPnn.". ".getKary($nik,'nik')." - ".getNamaKaryawan($nik)."<br>";
				}
				
				foreach ($blokkecilx[$kdorg][$kdkeg][$nik] as $blkcl) {
					// $totalxx = $umrx[$kdorg][$kdkeg][$nik] + $upahpremix[$kdorg][$kdkeg][$nik];
					$totalhasilkerjadt[$kdorg][$nik][$kdkeg] += $hasilkerjadt[$kdorg][$kdkeg][$nik][$blkcl];

					$jmlhblok[$kdorg][$kdkeg][$nik] = count($blokkecilx[$kdorg][$kdkeg][$nik]);
					#=== cek apakah di setup ada materialnya ===
					# Ambil data dari  kebun_pakaimaterial
					$queryM = selectQuery($dbname,'kebun_pakaimaterial',"*","notransaksi='".$param['notransaksi']."' and kodekegiatan='".$kegiatanx[$kdorg][$kdkeg]."' and kodeorg='".$indukorgx[$kdorg]."'");
					$dataM = fetchData($queryM);
					
					$queryK = selectQuery($dbname,'setup_kegiatannorma',"*","kodekegiatan='".$kegiatanx[$kdorg][$kdkeg]."' and kelompok='".$stblokdt[$kdorg][$kdkeg][$blkcl]."'");
					// exit("Warning: ".$queryK);
					$dataK = fetchData($queryK);
					$c="";
					$title="";
					if(empty($dataM) and !empty($dataK)){
						$noCekMtrl++;
						$errCekMtrl .= "".$noCekMtrl.". ".$kegiatanx[$kdorg][$kdkeg]." (".$optKegiatan[$kegiatanx[$kdorg][$kdkeg]]."), blok ".$blkcl." (".getBlok($blkcl,'tahuntanam').")<br>";
					}
				}
            }
        }
    }

	if ($noCekPnn > 0) {
		throw new PDOException("List Karyawan:<br>".$errCekPnn."<br>Sudah terdaftar di kegiatan panen, silahkan kosongkan Jumlah HK untuk melanjutkan.");
	}
	
	if ($noCekMtrl > 0) {
		throw new PDOException("List Kegiatan:<br>".$errCekMtrl."<br>harus menggunakan material.");
	}

	$urutblok = array();
	$sisa = array();
	$sisahasil = array();
	$sisapremi = array();
	
    $no = $thk = $tumr = $tpremi = 0;
    foreach ($indukorgx as $kdorg) {
        foreach ($kegiatanx[$kdorg] as $kdkeg) {
			foreach ($nikkaryx[$kdorg][$kdkeg] as $nik) {
				foreach ($blokkecilx[$kdorg][$kdkeg][$nik] as $blkcl) {
					$luasdiproporsi[$kdorg][$kdkeg][$nik] = $hasilkerjadt[$kdorg][$kdkeg][$nik][$blkcl] / $totalhasilkerjadt[$kdorg][$nik][$kdkeg];
					
					@$urutblok[$kdorg][$kdkeg][$nik]++;
					if ($urutblok[$kdorg][$kdkeg][$nik] == $jmlhblok[$kdorg][$kdkeg][$nik]) {
						$hkproporsi[$kdorg][$kdkeg][$nik][$blkcl] 			= $hkkaryx[$kdorg][$kdkeg][$nik] - $sisa[$kdorg][$nik][$kdkeg];
						$hasilkerjaproporsi[$kdorg][$kdkeg][$nik][$blkcl] 	= $hasilkerjax[$kdorg][$kdkeg][$nik] - $sisahasil[$kdorg][$nik][$kdkeg];
						$premiproporsi[$kdorg][$kdkeg][$nik][$blkcl]		= $upahpremix[$kdorg][$kdkeg][$nik] - $sisapremi[$kdorg][$nik][$kdkeg];
					} else {
						$hkproporsi[$kdorg][$kdkeg][$nik][$blkcl]			= floor($luasdiproporsi[$kdorg][$kdkeg][$nik] * $hkkaryx[$kdorg][$kdkeg][$nik]*100)/100;
						$hasilkerjaproporsi[$kdorg][$kdkeg][$nik][$blkcl] 	= floor($luasdiproporsi[$kdorg][$kdkeg][$nik] * $hasilkerjax[$kdorg][$kdkeg][$nik]*100)/100;
						$premiproporsi[$kdorg][$kdkeg][$nik][$blkcl]		= floor($luasdiproporsi[$kdorg][$kdkeg][$nik] * $upahpremix[$kdorg][$kdkeg][$nik]*100)/100;											
					}
					
					$sisa[$kdorg][$nik][$kdkeg]	+= $hkproporsi[$kdorg][$kdkeg][$nik][$blkcl]; 
					// if($nik=='0000000838' and $kdkeg=='621060101' and $blkcl=='PPPE02D18F'){
					// 	echo $kdorg.' : '.$blkcl.' : '.$sisa[$kdorg][$nik][$kdkeg].' dengan HK TOTAL :.'.$hkkaryx[$kdorg][$kdkeg][$nik].'  blok ke : '.$urutblok[$kdorg][$kdkeg][$nik].' total sisa : '.$sisa[$kdorg][$nik][$kdkeg].'<br>';
					// }
					$sisahasil[$kdorg][$nik][$kdkeg]	+= $hasilkerjaproporsi[$kdorg][$kdkeg][$nik][$blkcl]; 
					$sisapremi[$kdorg][$nik][$kdkeg]	+= $premiproporsi[$kdorg][$kdkeg][$nik][$blkcl];
					
					// echo "<pre>";
					// print_r($hkproporsi);
					// echo "</pre>";

					$gajipokokkary[$nik] = getUpahKary($periode, $nik);
					$umrproporsi[$kdorg][$kdkeg][$nik][$blkcl] = $gajipokokkary[$nik] * $hkproporsi[$kdorg][$kdkeg][$nik][$blkcl];
					
					$totalxx = $umrproporsi[$kdorg][$kdkeg][$nik][$blkcl] + $premiproporsi[$kdorg][$kdkeg][$nik][$blkcl];

					if ($nourutx[$notransx[$kdorg][$kdkeg][$nik]] == 0 || $nourutx[$notransx[$kdorg][$kdkeg][$nik]] == "") {
						$nourutx[$notransx[$kdorg][$kdkeg][$nik]] = 1;
					} else {
						$nourutx[$notransx[$kdorg][$kdkeg][$nik]] += 1;
					}
					
					/* ================================================ INSERT KEBUN_PRESTASI_DETAIL ================================================ */
					$dtPres = array(
						'notransaksi'	=> $notransx[$kdorg][$kdkeg][$nik],
						'nobkm'			=> $nobkmx[$kdorg][$kdkeg][$nik],
						'nourut'		=> $nourutx[$notransx[$kdorg][$kdkeg][$nik]],
						'nikpemel'		=> $nikkaryx[$kdorg][$kdkeg][$nik],
						'kodekegiatan'	=> $kdkeg,
						'indukblok'		=> $kdorg,
						'kodeorg'		=> $blokkecilx[$kdorg][$kdkeg][$nik][$blkcl],
						'hasilkerja'	=> $hasilkerjaproporsi[$kdorg][$kdkeg][$nik][$blkcl],
						'jumlahhk'		=> $hkproporsi[$kdorg][$kdkeg][$nik][$blkcl],
						'kodesegment'	=> "0000000001",
						"flag"			=> "0",
					);
					
					$colsPres = array();
					foreach($dtPres as $key=>$row) {
						$colsPres[] = $key;
					}
                    // Execution To kebun_prestasi_detail
	                $qInsPres = insertQuery($dbname,"kebun_prestasi_detail",$dtPres,$colsPres);
	                $owlPDO->exec($qInsPres);
					
					/* ================================================ INSERT KEBUN_KEHADIRAN_DETAIL ================================================ */
					$dtHadir = array(
						'notransaksi'	=> $notransx[$kdorg][$kdkeg][$nik],
						'nik'			=> $nikkaryx[$kdorg][$kdkeg][$nik],
						'nourut'		=> $nourutx[$notransx[$kdorg][$kdkeg][$nik]],
						'jhk'			=> $hkproporsi[$kdorg][$kdkeg][$nik][$blkcl],
						'umr'			=> $umrproporsi[$kdorg][$kdkeg][$nik][$blkcl],
						'insentif'		=> $premiproporsi[$kdorg][$kdkeg][$nik][$blkcl],
						'hasilkerja'	=> $hasilkerjaproporsi[$kdorg][$kdkeg][$nik][$blkcl],
					);
					
					$colsHadir = array();
					foreach($dtHadir as $key=>$row) {
							$colsHadir[] = $key;
					}

                    // Execution To kebun_kehadiran_detail
	                $qInsHadir = insertQuery($dbname,"kebun_kehadiran_detail",$dtHadir,$colsHadir);
	                $owlPDO->exec($qInsHadir);
					
				}
            }
        }
    }
	

	$datamat=$kdgdng=$jlhmat=$jlhmatha=$datakdmat=$kegmat=$kdorgmat=$brgmat=array();
	$blokkecilx = $tahuntanamx = $hasilkerjadt2 = array();

		$str = "select * from ".$dbname.".kebun_pakaimaterial where 1=1 and notransaksi='".$param['notransaksi']."' order by kodekegiatan, kodeorg";
		$res=fetchdata($str);
		foreach($res as $bar){
			$kegmat[$bar['kodekegiatan']] = $bar['kodekegiatan'];
			$kdorgmat[$bar['kodekegiatan']][$bar['kodeorg']] = $bar['kodeorg'];
			$brgmat[$bar['kodekegiatan']][$bar['kodeorg']][$bar['kodebarang']] = $bar['kodebarang'];

			$datakdmat[$bar['kodebarang']]=$bar['kodebarang'];
			$datamat[$bar['kodekegiatan']][$bar['kodeorg']][$bar['kodebarang']]=$bar['kodebarang'];
			$kdgdng[$bar['kodekegiatan']][$bar['kodeorg']][$bar['kodebarang']]=$bar['kodegudang'];
			@$jlhmat[$bar['kodekegiatan']][$bar['kodeorg']][$bar['kodebarang']]+=$bar['kwantitas'];
			@$jlhmatha[$bar['kodekegiatan']][$bar['kodeorg']][$bar['kodebarang']]+=$bar['kwantitasha'];
			$notrans[$bar['kodekegiatan']][$bar['kodeorg']][$bar['kodebarang']]=$bar['notransaksi'];
		}

		if(count($datamat)==0){
			$tab.="<tr class=rowcontent><td colspan=11 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}

		$dtBlok = "SELECT a.notransaksi, a.kodekegiatan, a.kodebarang, a.kodeorg, a.kwantitas, a.kodegudang, 
				b.kodeorg as blokkecil, b.tahuntanam, SUM(b.luasareaproduktif + b.luasareanonproduktif) as luasareaproduktif, 
				b.lc, b.luasbloking,b.statusblok
				FROM $dbname.kebun_pakaimaterial a LEFT JOIN ".$dbname.".setup_kegiatan c ON c.kodekegiatan=a.kodekegiatan
				LEFT JOIN ".$dbname.".".$dbaseblok." b ON a.kodeorg = b.indukblok and c.kelompok = b.statusblok
				WHERE a.notransaksi = '".$param['notransaksi']."' and b.statusblok IN (SELECT kelompok FROM $dbname.setup_kegiatan) ".$whereblok." 
				group by a.kodekegiatan,a.kodebarang,b.kodeorg
				HAVING SUM(b.luasareaproduktif + b.luasareanonproduktif + b.lc + b.luasbloking) > 0";
		$resBlok = fetchData($dtBlok);
		foreach ($resBlok as $val) {
			$blokkecilx[$val['kodekegiatan']][$val['kodeorg']][$val['kodebarang']][$val['blokkecil']] .= $val['blokkecil'];
			$tahuntanamx[$val['kodekegiatan']][$val['kodeorg']][$val['kodebarang']][$val['blokkecil']] = $val['tahuntanam'];
			if ($val['luasareaproduktif'] == 0) {
				if ($val['lc'] == 0) {
					if ($val['luasbloking'] > 0) {
						$hasilkerjadt2[$val['kodekegiatan']][$val['kodeorg']][$val['kodebarang']][$val['blokkecil']] = $val['luasbloking'];
					} else {
						$hasilkerjadt2[$val['kodekegiatan']][$val['kodeorg']][$val['kodebarang']][$val['blokkecil']] = 0;
					}
				} else {
					$hasilkerjadt2[$val['kodekegiatan']][$val['kodeorg']][$val['kodebarang']][$val['blokkecil']] = $val['lc'];
				}
			} else {
				$hasilkerjadt2[$val['kodekegiatan']][$val['kodeorg']][$val['kodebarang']][$val['blokkecil']] = $val['luasareaproduktif'];
			}
		}

		foreach ($kegmat as $kdkeg) {
			foreach ($kdorgmat[$kdkeg] as $kdorg) {
				foreach ($brgmat[$kdkeg][$kdorg] as $kdbrg) {
					foreach ($blokkecilx[$kdkeg][$kdorg][$kdbrg] as $blk) {
						$totalhasilkerjadt2[$kdkeg][$kdorg][$kdbrg] += $hasilkerjadt2[$kdkeg][$kdorg][$kdbrg][$blk];

						$jmlhblok[$kdkeg][$kdorg][$kdbrg] = count($blokkecilx[$kdkeg][$kdorg][$kdbrg]);
					}
				}
			}
		}
		
		$urutblokm 	= array(); 
		$sisam		= array();
		$sisaha		= array();
		
		$no=0;
		foreach ($kegmat as $kdkeg) {
			foreach ($kdorgmat[$kdkeg] as $kdorg) {
				foreach ($brgmat[$kdkeg][$kdorg] as $kdbrg) {
					foreach ($blokkecilx[$kdkeg][$kdorg][$kdbrg] as $blk) {
						$xproporsi[$kdkeg][$kdorg][$kdbrg][$blk] = $hasilkerjadt2[$kdkeg][$kdorg][$kdbrg][$blk] / $totalhasilkerjadt2[$kdkeg][$kdorg][$kdbrg];

						$urutblokm[$kdkeg][$kdorg][$kdbrg]++;
						if (@$urutblokm[$kdkeg][$kdorg][$kdbrg] == $jmlhblok[$kdkeg][$kdorg][$kdbrg]) {
							// $xproporsi[$kdkeg][$kdorg][$kdbrg][$blk] = $oldproporsi[$kdkeg][$kdorg][$kdbrg][$blk];
							$jmlbrgproporsi[$kdkeg][$kdorg][$kdbrg][$blk]	= $jlhmat[$kdkeg][$kdorg][$kdbrg] - $sisam[$kdkeg][$kdorg][$kdbrg];
							$jmlhaproporsi[$kdkeg][$kdorg][$kdbrg][$blk]	= $jlhmatha[$kdkeg][$kdorg][$kdbrg] - $sisaha[$kdkeg][$kdorg][$kdbrg];
							// $jmlbrgproporsi[$kdkeg][$kdorg][$kdbrg][$blk]	= round($jlhmat[$kdkeg][$kdorg][$kdbrg] - $sisam[$kdkeg][$kdorg][$kdbrg],2);
							// $jmlhaproporsi[$kdkeg][$kdorg][$kdbrg][$blk]	= round($jlhmatha[$kdkeg][$kdorg][$kdbrg] - $sisaha[$kdkeg][$kdorg][$kdbrg],2);
						} else {
							// $jmlbrgproporsi[$kdkeg][$kdorg][$kdbrg][$blk]	= floor($xproporsi[$kdkeg][$kdorg][$kdbrg][$blk] * $jlhmat[$kdkeg][$kdorg][$kdbrg] * 100) / 100;
							// $jmlhaproporsi[$kdkeg][$kdorg][$kdbrg][$blk]	= floor($xproporsi[$kdkeg][$kdorg][$kdbrg][$blk] * $jlhmatha[$kdkeg][$kdorg][$kdbrg] * 100) / 100;
							
							$jmlbrgproporsi[$kdkeg][$kdorg][$kdbrg][$blk]	= $xproporsi[$kdkeg][$kdorg][$kdbrg][$blk] * $jlhmat[$kdkeg][$kdorg][$kdbrg];
							$jmlhaproporsi[$kdkeg][$kdorg][$kdbrg][$blk]	= $xproporsi[$kdkeg][$kdorg][$kdbrg][$blk] * $jlhmatha[$kdkeg][$kdorg][$kdbrg];
						}
						
						$sisam[$kdkeg][$kdorg][$kdbrg] 	+= $xproporsi[$kdkeg][$kdorg][$kdbrg][$blk] * $jlhmat[$kdkeg][$kdorg][$kdbrg];
						$sisaha[$kdkeg][$kdorg][$kdbrg] += $xproporsi[$kdkeg][$kdorg][$kdbrg][$blk] * $jlhmatha[$kdkeg][$kdorg][$kdbrg];
						$no+=1;
						
						/* ================================================ INSERT KEBUN_PAKAIMATERIAL_DETAIL ================================================ */
						
						$dtMatr = array(
							'notransaksi'	=> $notrans[$kdkeg][$kdorg][$kdbrg],
							'indukblok'		=> $kdorg,
							'kodekegiatan'	=> $kdkeg,
							'kodeorg'		=> $blokkecilx[$kdkeg][$kdorg][$kdbrg][$blk],
							'kodebarang'	=> $kdbrg,
							'kwantitas'		=> $jmlbrgproporsi[$kdkeg][$kdorg][$kdbrg][$blk],
							'kwantitasha'	=> $jmlhaproporsi[$kdkeg][$kdorg][$kdbrg][$blk],
							'kodegudang'	=> $kdgdng[$kdkeg][$kdorg][$kdbrg],
						);
		
						$colsMatr = array();
						foreach($dtMatr as $key=>$row) {
								$colsMatr[] = $key;
						}

                        // Execution To kebun_pakaimaterial_detail
                        $qInsMatr = insertQuery($dbname,"kebun_pakaimaterial_detail",$dtMatr,$colsMatr);
                        $owlPDO->exec($qInsMatr);
						
					}
				}
			}
		}

	#=========================== End Execution ===============================
	$owlPDO->commit();
} catch (PDOException $e) {
	$owlPDO->rollback();
	echo "Error, " . addslashes($e->getMessage());
	die();
}
?>