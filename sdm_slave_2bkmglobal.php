<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$proses=checkPostGet('proses','');
$type=checkPostGet('type','');

$unit=checkPostGet('unit','');
$afdeling=checkPostGet('afdeling','');
$tglawal=checkPostGet('tglawal','');
$tglakhir=checkPostGet('tglakhir','');
$nobkm=checkPostGet('nobkm','');


$namaOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

$ptselect = makeOption($dbname,'organisasi','kodeorganisasi,inisialisasiorganisasi',"kodeorganisasi='" .$unit ."'");




switch($proses){
	case'getafdeling':
		if($unit==''){
			if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
				$optAfd.="<option value=''>".$_SESSION['lang']['all']."</option>";
				$whr2 = " and 1=1";
			}else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
				$optAfd.="<option value=''>".$_SESSION['lang']['all']."</option>";
				$whr2 = " and induk in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and tipe='KEBUN')";
				$whr2 = " and 1=1";
			}else{
				$optAfd.="<option value=''>".$_SESSION['lang']['all']."</option>";
				$whr2 = " and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
			}
			//GET AFDELING
			$str = "select * from ".$dbname.".organisasi where tipe = 'AFDELING' ".$whr2." order by namaorganisasi asc";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optAfd.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			} 
		}else{
			//GET AFDELING
			$optAfd.="<option value=''>".$_SESSION['lang']['all']."</option>";
			$str = "select * from ".$dbname.".organisasi where tipe = 'AFDELING' and induk='".$unit."' order by namaorganisasi asc";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optAfd.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			} 
		}
		
		echo $optAfd;
	break;
	case'preview':
		if($type=='html'){
			$border = 0;
		}else{
			$border = 1;
		}
	
		$tab.="<table cellspacing=1 cellpadding=5 border='".$border."' class=sortable>
			<thead class=rowheader>
			<tr>
				<th style='text-align:center'>No.</th>
				<th style='text-align:center'>No. BKM</th>
				<th style='text-align:center'>".$_SESSION['lang']['notransaksi']."</th>
				<th style='text-align:center'>".$_SESSION['lang']['tanggal']."</th>
				<th style='text-align:center'>".$_SESSION['lang']['kodekegiatan']."</th>    
				<th style='text-align:center'>".$_SESSION['lang']['namakegiatan']."</th>
				<th style='text-align:center'>".$_SESSION['lang']['satuan']."</th>
				<th style='text-align:center'>".$_SESSION['lang']['blok']."</th>
				<th style='text-align:center'>".$_SESSION['lang']['nik2']."</th>
				<th style='text-align:center'>".$_SESSION['lang']['namakaryawan']."</th>
				<th style='text-align:center'>Upah</th>
				<th style='text-align:center'>".$_SESSION['lang']['premi']."</th>
				<th style='text-align:center'>".$_SESSION['lang']['prestasi']."</th>
				<th style='text-align:center'>".$_SESSION['lang']['materialcode']."</th>
				<th style='text-align:center'>".$_SESSION['lang']['materialname']."</th>
				<th style='text-align:center'>".$_SESSION['lang']['satuan']."</th>
				<th style='text-align:center'>".$_SESSION['lang']['jumlah']."</th>
			</tr>
			</thead>
			<tbody>";
			
		if($unit=='')
		{
			if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
			{
				if($afdeling=='')
				{
					$whr = '';
				}
				else
				{
					$whr = "and b.kodeorg like '".$afdeling."%'";
				}
			}
			else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL')
			{
				if($afdeling=='')
				{
					$whr = "and left(b.kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and tipe='KEBUN')";
					$whr = '';
				}
				else
				{
					$whr = "and b.kodeorg like '".$afdeling."%'";
				}
			}
			else
			{
				$whr = "and b.kodeorg like '".$afdeling."%'";
			}
		}
		else
		{
			if($afdeling=='')
			{
				$whr = "and left(b.kodeorg,4) = '".$unit."'";
			}
			else
			{
				$whr = "and b.kodeorg like '".$afdeling."%'";
			}
		}
		
		//GET KARYAWAN PEMELIHARAAN
		$str = "select a.*, b.nobkm from ".$dbname.".kebun_kehadiran_vw a 
		left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
		where a.tanggal between ".tanggalsystem($tglawal)." and ".tanggalsystem($tglakhir)."";
		// exit('warning: '.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$listkaryawan[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar['karyawanid']] = $bar['karyawanid'];
			$listupah[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar['karyawanid']] += $bar['umr'];
			$listhk[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar['karyawanid']] += $bar['umr'];
			$listpremi[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar['karyawanid']] = $bar['insentif'];
			$hasilkerja[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar['karyawanid']] = $bar['hasilkerja'];
		}

		//GET KARYAWAN PEMELIHARAAN
		if($ptselect[$unit] == 'PPP'){
			$str = "select a.*, b.nobkm, b.notransaksi as bnotransaksi from ".$dbname.".kebun_3premipemanen a 
            left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.noreferensi 
			where a.tanggalpanen between ".tanggalsystem($tglawal)." and ".tanggalsystem($tglakhir)."";
			// exit('warning: '.$str);
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
                // if(empty($bar['kodekegiatan'])) $bar['kodekegiatan'] = '611010101';
				$listkaryawan[$bar['nobkm']][$bar['bnotransaksi']][$bar['kodekegiatan']][$bar['blok']][$bar['karyawanid']] = $bar['karyawanid'];
				$listupah[$bar['nobkm']][$bar['bnotransaksi']][$bar['kodekegiatan']][$bar['blok']][$bar['karyawanid']] += ($bar['rphkbuahbesar']+$bar['rphkbuahkecil']-$bar['rphkbuahbesarpot']-$bar['rphkbuahkecilpot']);
				$listhk[$bar['nobkm']][$bar['bnotransaksi']][$bar['kodekegiatan']][$bar['blok']][$bar['karyawanid']] += ($bar['rphkbuahbesar']+$bar['rphkbuahkecil']-$bar['rphkbuahbesarpot']-$bar['rphkbuahkecilpot']);
				// $listpremi[$bar['nobkm']][$bar['bnotransaksi']][$bar['kodekegiatan']][$bar['blok']][$bar['karyawanid']] = ($bar['rplbbuahbesar']+$bar['rplbbuahkecil']);
				// $hasilkerja[$bar['nobkm']][$bar['bnotransaksi']][$bar['kodekegiatan']][$bar['blok']][$bar['karyawanid']] = ($bar['hkbuahbesar']+$bar['hkbuahkecil']);
			}
		}else{
			$str = "select a.*, b.nobkm, b.notransaksi as bnotransaksi from ".$dbname.".kebun_3premipemanen_v2 a 
            left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.noreferensi 
			where a.tanggalpanen between ".tanggalsystem($tglawal)." and ".tanggalsystem($tglakhir)."";
			// exit('warning: '.$str);
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
                // if(empty($bar['kodekegiatan'])) $bar['kodekegiatan'] = '611010101';
				$listkaryawan[$bar['nobkm']][$bar['bnotransaksi']][$bar['kodekegiatan']][$bar['indukblok']][$bar['karyawanid']] = $bar['karyawanid'];
				$listupah[$bar['nobkm']][$bar['bnotransaksi']][$bar['kodekegiatan']][$bar['indukblok']][$bar['karyawanid']] += ($bar['upah']+$bar['upahbro']-$bar['potupah']);
				$listhk[$bar['nobkm']][$bar['bnotransaksi']][$bar['kodekegiatan']][$bar['indukblok']][$bar['karyawanid']] += ($bar['upah']+$bar['upahbro']-$bar['potupah']);
				// $listpremi[$bar['nobkm']][$bar['bnotransaksi']][$bar['kodekegiatan']][$bar['indukblok']][$bar['karyawanid']] = ($bar['premilb']+$bar['premikehadiran']+$bar['premikesulitan']);
				// $hasilkerja[$bar['nobkm']][$bar['bnotransaksi']][$bar['kodekegiatan']][$bar['indukblok']][$bar['karyawanid']] = ($bar['hk']);
			}
		}

		
		$str = "select b.hasilkerja, a.nobkm, a.notransaksi, a.tanggal, b.kodekegiatan, b.kodeorg, b.nik, b.upahpremi, b.upahpremilebihbasis from ".$dbname.".kebun_aktifitas a 
				left join ".$dbname.".kebun_prestasi b on a.notransaksi = b.notransaksi
				where (a.nobkm like '%".trim($nobkm)."%' or a.notransaksi like '%".trim($nobkm)."%') and (a.tanggal between ".tanggalsystem($tglawal)." and ".tanggalsystem($tglakhir).") and b.kodeorg!='' ".$whr." order by a.tanggal asc, a.nobkm asc, a.tipetransaksi asc";
		// exit('warning: '.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$countNik=0;
		while($bar=$res->fetch()){
			$jenistrans[$bar['notransaksi']]=array('tipetransaksi'=>$bar['tipetransaksi'],'tipe'=>$bar['tipe']);
			
			//GET MATERIAL
			$str2 = "select * from ".$dbname.".kebun_pakaimaterial where notransaksi = '".$bar['notransaksi']."' and kodekegiatan='".$bar['kodekegiatan']."' and kodeorg='".$bar['kodeorg']."'";
			$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_ASSOC);
			while($bar2=$res2->fetch()){
				$listmat[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar2['kodebarang']] = $bar2['kodebarang'];
				$listmatqty[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar2['kodebarang']] = $bar2['kwantitas'];
			}
			
			$listbkm[$bar['nobkm']]['nobkm'] = $bar['nobkm'];
			$listtrk[$bar['nobkm']][$bar['notransaksi']]=$bar['tanggal'];
			$listkegiatan[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']] = $bar['kodekegiatan'];
			$listblok[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']] = $bar['kodeorg'];
						
			if($bar['nik']!='-'){
				$listkaryawan[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar['nik']] = $bar['nik'];
				if(!isset($listhk[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar['nik']])) {
					$listhk[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar['nik']] = 0;
				}
				if(!isset($listpremi[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar['nik']])) {
					$listpremi[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar['nik']] = ($bar['upahpremi'] + $bar['upahpremilebihbasis']);
				}
				if(!isset($hasilkerja[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar['nik']])) {
					$hasilkerja[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar['nik']] = $bar['hasilkerja'];
				}
			}
		}	
			
		if(isset($listbkm))
		foreach($listbkm as $key=>$val){
			
			if(isset($listtrk[$key]))
			foreach($listtrk[$key] as $trk=>$tanggal){
				$arrplus[$key]+=1;
			
				if(isset($listkegiatan[$key][$trk]))
				foreach($listkegiatan[$key][$trk] as $kegiatan){
					
					if(isset($listblok[$key][$trk][$kegiatan]))
					foreach($listblok[$key][$trk][$kegiatan] as $blok){
						$jlhkaryawan=0;
						$jlhmaterial=0;
						
						if(isset($listkaryawan[$key][$trk][$kegiatan][$blok]))
						foreach($listkaryawan[$key][$trk][$kegiatan][$blok] as $karyawanid){
							$listkaryawan2[$key][$trk][$kegiatan][$blok][] = $karyawanid;
						}
						
						if(isset($listmat[$key][$trk][$kegiatan][$blok]))
						foreach($listmat[$key][$trk][$kegiatan][$blok] as $material){
							$listmaterial2[$key][$trk][$kegiatan][$blok][] = $material;
						}
						
						$jlhkaryawan=@count($listkaryawan[$key][$trk][$kegiatan][$blok]);
						$jlhmaterial=@count($listmat[$key][$trk][$kegiatan][$blok]);
					}
				}
			}
		}
		
		$no=0;
	if(isset($listbkm)){
		$prevKey = null;
		$prevTrk = null;
		$prevKegiatan = null;
		$prevBlok = null;

		foreach($listbkm as $key=>$val){
			$no++;
			
			$no1 = 0;
			foreach($listtrk[$key] as $trk=>$tanggal){
				$no1++;
				
				if(isset($listkegiatan[$key][$trk]))
				$no2 = 0;
				foreach($listkegiatan[$key][$trk] as $kegiatan){
					$no2++;
					$kodekegiatan = ($kegiatan == '' || $kegiatan == 0 ? '611010101' : $kegiatan);
					$optNamaKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$kodekegiatan."'");
					$optSatKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan',"kodekegiatan='".$kodekegiatan."'");
					
					if(isset($listblok[$key][$trk][$kegiatan]))
					$no3 = 0;
					foreach($listblok[$key][$trk][$kegiatan] as $blok){
						$no3++;

						// Proses material dan karyawan
						$jlhmtr = @count($listmaterial2[$key][$trk][$kegiatan][$blok]);
						$jlhkry = @count($listkaryawan2[$key][$trk][$kegiatan][$blok]);
						
						// Kondisi untuk materi atau karyawan
						if($jlhmtr > $jlhkry){
							if(isset($listmaterial2[$key][$trk][$kegiatan][$blok]))
							$no4 = 0;
							foreach($listmaterial2[$key][$trk][$kegiatan][$blok] as $keyx=>$valx){
								$no4++;
								$karyawanid = $listkaryawan2[$key][$trk][$kegiatan][$blok][$keyx];
								$hk = $listhk[$key][$trk][$kegiatan][$blok][$karyawanid];
								$premi = $listpremi[$key][$trk][$kegiatan][$blok][$karyawanid];
								$prestasi = $hasilkerja[$key][$trk][$kegiatan][$blok][$karyawanid];
								$kodebarang = $valx;
								$jlhqty = $listmatqty[$key][$trk][$kegiatan][$blok][$kodebarang];
								$optNik = makeOption($dbname,'datakaryawan','karyawanid,nik',"karyawanid='".$karyawanid."'");
								$optNama = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$karyawanid."'");
								$optNamaBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kodebarang."'");
								$optSatuanBarang = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$kodebarang."'");

								$tab .= "<tr class='rowcontent'>";
								// Cek apakah $key (No BKM) sama dengan sebelumnya
								if($key !== $prevKey) {
									$tab .= "<td style='text-align:right;vertical-align:top'>".$no."</td>";
									$tab .= "<td style='vertical-align:top'>".$key."</td>";
								} else {
									$tab .= "<td style='text-align:right;vertical-align:top'></td>";
									$tab .= "<td style='vertical-align:top'></td>";
								}

								// $click = " onclick=showupload('".$trk."')";
								// Cek apakah $trk (Nomor Transaksi) sama dengan sebelumnya
								if($trk !== $prevTrk) {
									$tab .= "<td style='vertical-align:top;cursor:pointer;color:blue;' ".$click.">".$trk."</td>";
									$tab .= "<td style='text-align:center;width:65px;vertical-align:top'>".tanggalnormal($tanggal)."</td>";
								} else {
									$tab .= "<td style='vertical-align:top'></td>";
									$tab .= "<td style='vertical-align:top'></td>";
								}

								// Cek apakah Kegiatan sama dengan sebelumnya
								if($kodekegiatan !== $prevKegiatan) {
									$tab .= "<td style='vertical-align:top'>".$kodekegiatan."</td>";					
									$tab .= "<td style='vertical-align:top'>".$optNamaKegiatan[$kodekegiatan]."</td>";
									$tab .= "<td style='vertical-align:top'>".$optSatKegiatan[$kodekegiatan]."</td>";
								} else {
									$tab .= "<td style='vertical-align:top'></td>";
									$tab .= "<td style='vertical-align:top'></td>";
									$tab .= "<td style='vertical-align:top'></td>";
								}

								// Cek apakah Blok sama dengan sebelumnya
								if($blok !== $prevBlok) {
									$tab .= "<td style='vertical-align:top'>".getNamaOrg($blok)."</td>";
								} else {
									$tab .= "<td style='vertical-align:top'></td>";
								}
								$tab .= "<td style='vertical-align:top'>".$optNik[$karyawanid]."</td>";
								$tab .= "<td style='vertical-align:top'>".$optNama[$karyawanid]."</td>";
								$tab .= "<td style='vertical-align:top;text-align:right'>".($hk == '' ? '' : number_format($hk))."</td>";
								$tab .= "<td style='vertical-align:top;text-align:right'>".($premi == '' ? '' : number_format($premi))."</td>";
								$tab .= "<td style='vertical-align:top;text-align:right'>".($prestasi == '' ? '' : number_format($prestasi,2))."</td>";
								$tab .= "<td style='vertical-align:top'>".$kodebarang."</td>";
								$tab .= "<td style='vertical-align:top'>".$optNamaBarang[$kodebarang]."</td>";
								$tab .= "<td style='vertical-align:top'>".$optSatuanBarang[$kodebarang]."</td>";
								$tab .= "<td style='vertical-align:top;text-align:right'>".($jlhqty == '' ? '' : hidezerodecimal($jlhqty,5))."</td>";
								
								$totalpremi[$key][$trk] += $premi;
								$totalhk[$key][$trk] += $hk;
								$totalprestasi[$key][$trk] += $prestasi;

								$totalhkperkeg[$key][$trk][$kegiatan] += $hk;
								$totalpremiperkeg[$key][$trk][$kegiatan] += $premi;
								$totalprestasiperkeg[$key][$trk][$kegiatan] += $prestasi;
								
								// Update nilai $prevKey, $prevTrk, $prevKegiatan, $prevBlok
								$prevKey = $key;
								$prevTrk = $trk;
								$prevKegiatan = $kodekegiatan;
								$prevBlok = $blok;	
							}
						}else{
							if(isset($listkaryawan2[$key][$trk][$kegiatan][$blok]))
							$no4 = 0;
							foreach($listkaryawan2[$key][$trk][$kegiatan][$blok] as $keyx=>$valx){
								$no4++;
								$karyawanid = $valx;
								$hk = $listhk[$key][$trk][$kegiatan][$blok][$karyawanid];
								$premi = $listpremi[$key][$trk][$kegiatan][$blok][$karyawanid];
								$prestasi = $hasilkerja[$key][$trk][$kegiatan][$blok][$karyawanid];
								$kodebarang = $listmaterial2[$key][$trk][$kegiatan][$blok][$keyx];
								$jlhqty = $listmatqty[$key][$trk][$kegiatan][$blok][$kodebarang];
								$optNik = makeOption($dbname,'datakaryawan','karyawanid,nik',"karyawanid='".$karyawanid."'");
								$optNama = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$karyawanid."'");
								$optNamaBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kodebarang."'");
								$optSatuanBarang = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$kodebarang."'");

								$tab .= "<tr class='rowcontent'>";
								// Cek apakah $key (No BKM) sama dengan sebelumnya
								if($key !== $prevKey) {
									$tab .= "<td style='text-align:right;vertical-align:top'>".$no."</td>";
									$tab .= "<td style='vertical-align:top'>".$key."</td>";
								} else {
									$tab .= "<td style='text-align:right;vertical-align:top'></td>";
									$tab .= "<td style='vertical-align:top'></td>";
								}

								$click = " onclick=showupload('".$trk."')";
								// Cek apakah $trk (Nomor Transaksi) sama dengan sebelumnya
								if($trk !== $prevTrk) {
									$tab .= "<td style='vertical-align:top;cursor:pointer;color:blue;' ".$click.">".$trk."</td>";
									$tab .= "<td style='text-align:center;width:65px;vertical-align:top'>".tanggalnormal($tanggal)."</td>";
								} else {
									$tab .= "<td style='vertical-align:top'></td>";
									$tab .= "<td style='vertical-align:top'></td>";
								}

								// Cek apakah Kegiatan sama dengan sebelumnya
								if($kodekegiatan !== $prevKegiatan) {
									$tab .= "<td style='vertical-align:top'>".$kodekegiatan."</td>";					
									$tab .= "<td style='vertical-align:top'>".$optNamaKegiatan[$kodekegiatan]."</td>";
									$tab .= "<td style='vertical-align:top'>".$optSatKegiatan[$kodekegiatan]."</td>";
								} else {
									$tab .= "<td style='vertical-align:top'></td>";
									$tab .= "<td style='vertical-align:top'></td>";
									$tab .= "<td style='vertical-align:top'></td>";
								}

								// Cek apakah Blok sama dengan sebelumnya
								if($blok !== $prevBlok) {
									$tab .= "<td style='vertical-align:top'>".getNamaOrg($blok)."</td>";
								} else {
									$tab .= "<td style='vertical-align:top'></td>";
								}
								$tab .= "<td style='vertical-align:top'>".$optNik[$karyawanid]."</td>";
								$tab .= "<td style='vertical-align:top'>".$optNama[$karyawanid]."</td>";
								$tab .= "<td style='vertical-align:top;text-align:right'>".($hk == '' ? '' : number_format($hk))."</td>";
								$tab .= "<td style='vertical-align:top;text-align:right'>".($premi == '' ? '' : number_format($premi))."</td>";
								$tab .= "<td style='vertical-align:top;text-align:right'>".($prestasi == '' ? '' : number_format($prestasi,2))."</td>";
								$tab .= "<td style='vertical-align:top'>".$kodebarang."</td>";
								$tab .= "<td style='vertical-align:top'>".$optNamaBarang[$kodebarang]."</td>";
								$tab .= "<td style='vertical-align:top'>".$optSatuanBarang[$kodebarang]."</td>";
								$tab .= "<td style='vertical-align:top;text-align:right'>".($jlhqty == '' ? '' : hidezerodecimal($jlhqty,5))."</td>";
								
								$totalpremi[$key][$trk] += $premi;
								$totalhk[$key][$trk] += $hk;
								$totalprestasi[$key][$trk] += $prestasi;

								$totalhkperkeg[$key][$trk][$kegiatan] += $hk;
								$totalpremiperkeg[$key][$trk][$kegiatan] += $premi;
								$totalprestasiperkeg[$key][$trk][$kegiatan] += $prestasi;

								// Update nilai $prevKey, $prevTrk, $prevKegiatan, $prevBlok
								$prevKey = $key;
								$prevTrk = $trk;
								$prevKegiatan = $kodekegiatan;
								$prevBlok = $blok;	
							}
						}
					}
					$tab .= "<tr class='rowcontent' style='background-color:#d6d6d6;'>";
					$tab .= "<td colspan=10 style='vertical-align:top;font-weight:bold;color:#f76b07;'>Total 1. (Upah+Premi) Kegiatan ".$kodekegiatan." (".getNamaKeg($kodekegiatan).")</td>";
					$tab .= "<td style='vertical-align:top;text-align:right;font-weight:bold;color:#f76b07;'>".number_format($totalhkperkeg[$key][$trk][$kegiatan])."</td>";
					$tab .= "<td style='vertical-align:top;text-align:right;font-weight:bold;color:#f76b07;'>".number_format($totalpremiperkeg[$key][$trk][$kegiatan])."</td>";
					$tab .= "<td style='vertical-align:top;text-align:right;font-weight:bold;color:#f76b07;'>".number_format($totalprestasiperkeg[$key][$trk][$kegiatan],2)."</td>";
					$tab .= "<td colspan=4 style='vertical-align:top;text-align:right;font-weight:bold;color:#f76b07;'>".number_format(($totalhkperkeg[$key][$trk][$kegiatan]+$totalpremiperkeg[$key][$trk][$kegiatan]),2)."</td>";
					$tab .= "</tr>";

					$tab .= "<tr class='rowcontent' style='background-color:#d6d6d6;'>";
					$tab .= "<td colspan=10 style='vertical-align:top;font-weight:bold;color:#f76b07;'>Total 2. Point 1 Dibagi Prestasi Kegiatan ".$kodekegiatan." (".getNamaKeg($kodekegiatan).")</td>";
					$tab .= "<td style='vertical-align:top;text-align:right;font-weight:bold;color:#f76b07;'>".number_format($totalhkperkeg[$key][$trk][$kegiatan])."</td>";
					$tab .= "<td style='vertical-align:top;text-align:right;font-weight:bold;color:#f76b07;'>".number_format($totalpremiperkeg[$key][$trk][$kegiatan])."</td>";
					$tab .= "<td style='vertical-align:top;text-align:right;font-weight:bold;color:#f76b07;'>".number_format($totalprestasiperkeg[$key][$trk][$kegiatan],2)."</td>";
					$tab .= "<td colspan=4 style='vertical-align:top;text-align:right;font-weight:bold;color:#f76b07;'>".number_format((($totalhkperkeg[$key][$trk][$kegiatan]+$totalpremiperkeg[$key][$trk][$kegiatan])/$totalprestasiperkeg[$key][$trk][$kegiatan]),2)."</td>";
					$tab .= "</tr>";
				}

				$tab .= "<tr class='rowcontent' style='background-color:#b0b0b0;'>";
				$tab .= "<td colspan=10 style='vertical-align:top;font-weight:bold'>Total ".$trk."</td>";
				$tab .= "<td style='vertical-align:top;text-align:right;font-weight:bold'>".number_format($totalhk[$key][$trk])."</td>";
				$tab .= "<td style='vertical-align:top;text-align:right;font-weight:bold'>".number_format($totalpremi[$key][$trk])."</td>";
				$tab .= "<td style='vertical-align:top;text-align:right;font-weight:bold'>".number_format($totalprestasi[$key][$trk],2)."</td>";
				$tab .= "<td style='vertical-align:top'></td>";
				$tab .= "<td style='vertical-align:top'></td>";
				$tab .= "<td style='vertical-align:top'></td>";
				$tab .= "<td style='vertical-align:top'></td>";
				$tab .= "</tr>";
			}
		}
	}
		
		$tab.="</tbody>
		</table>";
			
		if($type=='html')
		{
			echo $tab;
		}
		else
		{
			$tab.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
			$nop_="REPORT_BKM_".date('d-m-Y');
			
			if(strlen($tab)>0)
			{
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/'.$file);
						}
					}	
					closedir($handle);
				}
				$handle=fopen("tempExcel/".$nop_.".xls",'w');
				if(!fwrite($handle,$tab))
				{
					echo "<script language=javascript1.2>
					parent.window.alert('Can't convert to excel format');
					</script>";
					exit;
				}
				else
				{
					echo "<script language=javascript1.2>
					window.location='tempExcel/".$nop_.".xls';
					</script>";
				}
			}
		}
	break;
}
?>