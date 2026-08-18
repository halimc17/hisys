<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
error_reporting(0);

if(count($_POST)>0){	
	$param = $_POST;
}else{
	$param = $_GET;
}
$proses=checkPostGet('proses','');
$kdOrg=checkPostGet('kdOrg','');
$tgl=tanggalsystem(checkPostGet('tgl',''));
$tgl2    =tanggalsystem(checkPostGet('tgl2',''));
$noSpb=checkPostGet('noSpb','');
$noTrans=checkPostGet('noTrans','');
$intex=checkPostGet('intex','');
$tipe = checkPostGet('tipe','');

$pertgl = tanggalsystemn(checkPostGet('tgl',''));
$pertgl2 = tanggalsystemn(checkPostGet('tgl2',''));

switch($proses){
	//load data
	case'getData':
		unset($_SESSION['temp']['tempNospb']);

		$where1 = "";
		$where2 = "";
		$tahun = "";
		if ($tgl != "" && $tgl2 != "") {
			$where1 .= " and tanggal between '".$tgl."' and '".$tgl2."'";
			//$where2 .= " and nospb like '%".substr($tgl,4,2)."%' ";
			//$where2 .= " and nospb like '%".substr($tgl2,4,2)."%' ";
			$tahun=substr($tgl, 0,4);
			$bulan=substr($tgl, 4,2);

		}


		if ($intex != "") {
			$where1 .= " and tujuan='".$intex."'";
		}

		$whereKodeOrg = " and c.kodeorg='".$kdOrg."' ";
		$where2 .= " and kodeorg='".$kdOrg."' ";
		$whereKodeOrgSPB = " and d.nospb like '%".$kdOrg."%' ";
		$whereKodeOrgTimbangan = " and kodeorg = '".$kdOrg."' ";
		if(strlen($kdOrg)>4){
			// $whereKodeOrg = " and c.nospb like '%".$kdOrg."%' ";
			$whereKodeOrg = " and a.blok like '%".$kdOrg."%' ";
			$whereKodeOrgTimbangan = " and divcode = '".$kdOrg."' ";
			$whereKodeOrgSPB = " and c.blok like '%".$kdOrg."%' ";
		}

			if($intex=='3'){
				$whereKodeOrgTimbangan .= " and millcode = 'EXTM' ";
				$where2 .= " and millcode = 'EXTM' ";
			}

		$nmklaslahan = makeOption($dbname,"setup_kelaslahan","kode,nama");

		$strbloktahunan = "SELECT * FROM setup_blok_tahunan
		where tahun = '".$tahun."".$bulan."' and kodeorg like '%".$kdOrg."%' ";
		//echo $strbloktahunan;

		$resbloktahunan = fetchdata($strbloktahunan);

		$dbaseblok='setup_blok';
		$whereblok='';
		$whereblok2='';
		if(count($resbloktahunan) > 0) {	
			$dbaseblok='setup_blok_tahunan';
			$whereblok=" and b.tahun = '".$tahun."".$bulan."'";
			$whereblok=" and b.tahun = '".$tahun."".$bulan."'";
		}

		$str = "SELECT a.nospb, substr(a.blok,1,6) as divisi, a.blok as indukblok, a.tph as notph, a.sesi as sesi, a.pemanen as pemanen,
				b.kodeorg as blok, b.tahuntanam, b.luasareaproduktif, a.tanggalpanen,
				a.jjg, a.kgwb, a.kgwbnetto, a.bjr, a.brondolan, a.totalkg, a.kgbjr, c.kodeorg, c.tanggal as tglspb, c.posting
				FROM $dbname.kebun_spbdt a JOIN $dbname.".$dbaseblok." b on a.blok = b.indukblok and b.tahuntanam <= (".(intval(date('Y'))-3).") and b.tahuntanam!='0'
				JOIN $dbname.kebun_spbht c on a.nospb = c.nospb
				WHERE 1=1 and ($tahun-b.tahuntanam)>=3 and b.luasareaproduktif>0  ".$where1." ".$whereKodeOrg." ".$whereblok."";
		$res = fetchdata($str);
		$nospbx = $divisix = $blokx = $indukblokx = $thntnmx = $luasblokx = $klaslahanx = $tglpanenx = $tglpanenindk = array();
		$sesix = $notphx = $pemanenx = array();
		$tgltimbang = $tdnblk = $kgbruto = $kgnetto = $bjrblk = $tdnblok = $klsbjrkg = $totjjgspb = $totbrdspb = $hslpnnblok = $totbrdindk = array();
		$totkgbruto = $tottdnblkthn = $totbjrblk = $totaltdnblok = $totklsbjrkg = $totluasblk = $tothslpnn = $brdblk = array();
		$kgbrutobrd = $kgnettobrd = $kgbrutoproporsi = $kgnettoproporsi = $kgbrutobrdproporsi = $kgnettobrdproporsi = array();

		foreach ($res as $val) {
			$nospbx[$val['nospb']] = $val['nospb'];
			if(!isset($jlhblokxx[$val['nospb']])){
				$jlhblokxx[$val['nospb']]=0;
			}

			if(!isset($blokx[$val['nospb']][$val['divisi']][$val['tanggalpanen']][$val['indukblok']][$val['blok']])){
				$jlhblokxx[$val['nospb']]+=1;
			}
			$blokx[$val['nospb']][$val['divisi']][$val['tanggalpanen']][$val['indukblok']][$val['blok']] = $val['blok'];

			$thntnmx[$val['blok']] = $val['tahuntanam'];
			$luasblokx[$val['blok']] = $val['luasareaproduktif'];

			$kdorgb[$val['nospb']] 							= $val['kodeorg'];
			$tanggalspb[$val['nospb']] 						= $val['tglspb'];
			$statusposting[$val['nospb']]					= $val['posting'];
			$totluasblk[$val['nospb']][$val['indukblok']]	+= $val['luasareaproduktif'];
		}
		
		// Get Total Group By Nospb
		$strSum = "SELECT a.nospb,a.jjg,a.brondolan FROM $dbname.kebun_spbdt a
				JOIN $dbname.kebun_spbht c on a.nospb = c.nospb
				WHERE 1=1 ".$where1." ".$whereKodeOrg."";
		$resSum = fetchdata($strSum);
		foreach ($resSum as $val) {
			$totjjgspb[$val['nospb']] += $val['jjg'];
			$totbrdspb[$val['nospb']] += $val['brondolan'];
		}

		$strcek = "SELECT * FROM $dbname.pabrik_timbangan
				WHERE 1=1 ".$where2."";
				//echo $strcek;
		$rescek = fetchdata($strcek);
		$hsltimbang=array();
		foreach ($rescek as $val) {
			$hsltimbang[$val['nospb']]=$val['nospb'];
		}
		

		// Get Total Group By Induk Blok
		$strSum = "SELECT a.nospb,substr(a.blok,1,6) as divisi,a.blok as indukblok,a.tanggalpanen,a.jjg,a.kgwb,a.kgwbnetto,a.bjr,
				a.brondolan,a.totalkg,a.kgbjr,c.kodeorg,c.tanggal as tglspb,c.posting FROM $dbname.kebun_spbdt a
				JOIN $dbname.kebun_spbht c on a.nospb = c.nospb
				WHERE 1=1 ".$where1." ".$whereKodeOrg."";
		$resSum = fetchdata($strSum);
		foreach ($resSum as $val) {
			$jjgindkx[$val['nospb']][$val['divisi']][$val['tanggalpanen']][$val['indukblok']] 	+=$val['jjg'];
			$brdindkx[$val['nospb']][$val['divisi']][$val['tanggalpanen']][$val['indukblok']] 	+= $val['brondolan'];
			$brdindkx2[$val['nospb']] 	+= $val['brondolan'];
		}

		// echo "<pre>";
		// print_r($jjgindkx);
		// echo "</pre>";

		$strbjr = "SELECT a.kodeorg, a.bjr, b.indukblok,a.periode FROM kebun_5bjr a JOIN ".$dbaseblok." b
		on a.kodeorg = b.kodeorg where (a.periode='".substr($pertgl,0,7)."' or a.periode='".periodelalu(substr($pertgl,0,7))."') and b.indukblok in
		(
		SELECT c.blok FROM kebun_spbdt c 
		JOIN kebun_spbht d on c.nospb = d.nospb
		where d.tanggal between '".$pertgl."' and '".$pertgl2."' ".$whereKodeOrgSPB." ".$whereblok."
		and d.tujuan='".$intex."'
		)";
		$resbjr = fetchdata($strbjr);

		if(count($resbjr) > 0) {	
			foreach ($resbjr as $val) {
				$bjrblk[$val['kodeorg']][$val['periode']] = $val['bjr'];
			}
		} else {

			$sql = "SELECT DISTINCT c.blok,c.indukblok FROM kebun_spbdt_detail c 
			JOIN kebun_spbht d on c.nospb = d.nospb
			where d.tanggal between '".$pertgl."' and '".$pertgl2."' ".$whereKodeOrgSPB."
			and d.tujuan='".$intex."'";
			$res = fetchData($sql);


			$tabbjr = "<b>Masih ada List Data, <span style=color:red>BJR Periode ".substr($pertgl,0,7)." belum di Setup!</span></b> <br/><br/>";
			$tabbjr .= "<table border=0 cellpadding=2 cellspacing=1 width=100%>";
				$tabbjr .= "<thead>";
					$tabbjr .= "<tr>";
						$tabbjr .= "<th>Induk Blok</th>";
						$tabbjr .= "<th>Kode Blok</th>";
						$tabbjr .= "<th>Periode</th>";
						$tabbjr .= "<th>Keterangan</th>";
					$tabbjr .= "<tr>";
				$tabbjr .= "</thead>";
				
				$tabbjr .= "<tbody>";
				foreach($res as $val):
					$tabbjr .= "<tr class=rowcontent>";
						$tabbjr .= "<td align=center>".$val['indukblok']."</td>";
						$tabbjr .= "<td align=center>".$val['blok']."</td>";
						$tabbjr .= "<td align=center>".substr($pertgl,0,7)."</td>";
						$tabbjr .= "<td align=center>Data BJR untuk Periode ".substr($pertgl,0,7)." belum di Setup</td>";
					$tabbjr .= "</tr>";
				endforeach;
				$tabbjr .= "</tbody>";
			$tabbjr .= "</table>";
		}


		$arrstatus = array("0" => "Belum Posting", "1" => "Sudah Posting");
		$no = 0;
		$nob = 0;
		$nok = 0;
		$nod = 0;
		$urutBlok=array();
		$sisa = array();
		$sisa2 = array();
		foreach ($blokx as $spb => $key1) {
			$strPabrik = "SELECT * FROM $dbname.pabrik_timbangan where nospb='".$spb."' ".$whereKodeOrgTimbangan."";
			$resPabrik = fetchData($strPabrik);
			foreach ($resPabrik as $dtp) {
				$tgltimbang[$dtp['nospb']]	= substr($dtp['tanggal'],0,10);
				$nospbwb[$dtp['nospb']]		= $dtp['nospb'];
				$notranswb[$dtp['nospb']]	= $dtp['notransaksi'];

				// Perhitungan mendapatkan Kg Bruto
				$kgbruto[$dtp['nospb']]		= ($dtp['beratmasuk']-$dtp['beratkeluar'] - $brdindkx2[$dtp['nospb']]);
				// Perhitungan mendapatkan Kg Netto
				$kgnetto[$dtp['nospb']]		= ($dtp['beratbersih'] - $brdindkx2[$dtp['nospb']]);

				$kgbrutobrd[$dtp['nospb']] = ($kgbruto[$dtp['nospb']] - $brdindkx2[$dtp['nospb']]);
				$kgnettobrd[$dtp['nospb']] = ($kgnetto[$dtp['nospb']] - $brdindkx2[$dtp['nospb']]);

			}

			$periodext= substr($pertgl,0,7);

			foreach ($key1 as $div => $key2) {
				foreach ($key2 as $tglPanen => $key3) {
					foreach ($key3 as $indk => $key4) {
						$nob++;
						foreach ($key4 as $blk => $val) {
									$strkls = "SELECT b.kodeorg,a.kodelahan,a.tahuntanam,a.nilai,a.periode1,a.periode2 FROM $dbname.kebun_5tandanhathn a JOIN $dbname.".$dbaseblok." b
												ON a.kodelahan = b.klasifikasitanah and a.tahuntanam = b.tahuntanam
												WHERE '{$periodext}' between a.periode1 and a.periode2 and indukblok='".$indk."' ".$whereblok."
												group by a.kodelahan, a.tahuntanam";
									$reskls = fetchdata($strkls);
									foreach ($reskls as $kls) {
										$tdnblk[$kls['kodeorg']][$kls['tahuntanam']] = $kls['nilai'];
										$klaslahanx[$blk] = $kls['kodelahan'];
									}
			
									// Perhitungan Tdn/blok Per TPH, Sesi, Nik
									$tdnblok[$spb][$div][$tglPanen][$indk][$blk] = ($luasblokx[$blk] * $tdnblk[$blk][$thntnmx[$blk]]);
									// Total Perhitungan Tdn/blok
									$totaltdnblok[$spb][$div][$tglPanen][$indk] += $tdnblok[$spb][$div][$tglPanen][$indk][$blk];
									$totaltdnblokxz[$spb][$div][$tglPanen][$indk] += $tdnblok[$spb][$div][$tglPanen][$indk][$blk];
									// Perhitungan Kelas Lahan KG Per TPH, Sesi, Nik
									//$klsbjrkg[$spb][$div][$indk][$blk] = round($tdnblok[$spb][$div][$indk][$blk] * $bjrblk[$blk][substr($tglPanen,0,7)][substr($tglPanen,0,7)],2);
									// Perhitungan Total Kelas Lahan KG
									//$totklsbjrkg[$spb] += $klsbjrkg[$spb][$div][$indk][$blk];
									// Perhitungan Total (Kg Bruto - Brondolan)
									//$kgbrutobrd[$spb] = ($kgbruto[$spb] - $brdindkx[$spb][$div][$tglPanen][$indk]);
									// Perhitungan Total (Kg Netto - Brondolan)
									//$kgnettobrd[$spb] = ($kgnetto[$spb] - $brdindkx[$spb][$div][$tglPanen][$indk]);
			
									// $jmlhblok[$spb][$div][$indk] = count($blokx[$spb][$div][$indk]);
									$jmlhblok[$spb][$div][$tglPanen][$indk] += count($blokx[$spb][$div][$tglPanen][$indk][$blk]);
									// $jmlhblok[$spb][$div][$indk] += count($bloktsnx[$spb][$div][$indk][$blk][$tph]);
						}
					}
				}
			}
		}
		

		// echo "<pre>";
		// print_r($jmlhblok);
		// echo "</pre>";	
		foreach ($blokx as $spb => $key1) {
			foreach ($key1 as $div => $key2) {
				foreach ($key2 as $tglPanen => $key3) {
					foreach ($key3 as $indk => $key4) {
						foreach ($key4 as $blk => $val) {
							if($bjrblk[$blk][substr($tglPanen,0,7)]<=0 or $bjrblk[$blk][substr($tglPanen,0,7)]==''){
								if(intval(substr($tglPanen,9,2))<=2){
									$bjrblk[$blk][substr($tglPanen,0,7)]=$bjrblk[$blk][periodelalu(substr($tglPanen,0,7))];

								}
							}
							$urutBlok[$spb][$div][$tglPanen][$indk]++;
							// Jika Urutan Blok sama dengan jumlah blok di induk blok
							if ($urutBlok[$spb][$div][$tglPanen][$indk] == $jmlhblok[$spb][$div][$tglPanen][$indk]) {
								// Hasil Panen (Tdn/Blok) / JJG Per TPH, Sesi, Nik
								$hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] = round($jjgindkx[$spb][$div][$tglPanen][$indk] - $sisa[$spb][$div][$tglPanen][$indk],2);
								$brdblk[$spb][$div][$tglPanen][$indk][$blk] = round($brdindkx[$spb][$div][$tglPanen][$indk] - $sisa2[$spb][$div][$tglPanen][$indk],2);
								$klsbjrkg[$spb][$div][$tglPanen][$indk][$blk]=($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk]*$bjrblk[$blk][substr($tglPanen,0,7)]);
								// Hasil Brondolan Per Blok Kecil, TPH, Sesi, Nik
							} else {
								// Hasil Panen (Tdn/Blok) / JJG
								$hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] = round(fixnan($tdnblok[$spb][$div][$tglPanen][$indk][$blk] / $totaltdnblok[$spb][$div][$tglPanen][$indk]) * $jjgindkx[$spb][$div][$tglPanen][$indk],2);
								$brdblk[$spb][$div][$tglPanen][$indk][$blk] = round(fixnan($tdnblok[$spb][$div][$tglPanen][$indk][$blk] / $totaltdnblok[$spb][$div][$tglPanen][$indk]) * $brdindkx[$spb][$div][$tglPanen][$indk],2);
								$klsbjrkg[$spb][$div][$tglPanen][$indk][$blk]=($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk]*$bjrblk[$blk][substr($tglPanen,0,7)]);
								// Hasil Brondolan Per Blok Kecil
							}
							$totklsbjrkg[$spb]+=$klsbjrkg[$spb][$div][$tglPanen][$indk][$blk];
							$totklsbjrkgx[$spb][$div][$tglPanen][$indk]+=$klsbjrkg[$spb][$div][$tglPanen][$indk][$blk];
							$sisa[$spb][$div][$tglPanen][$indk] 	+= $hslpnnblok[$spb][$div][$tglPanen][$indk][$blk];
							$sisa2[$spb][$div][$tglPanen][$indk] 	+= $brdblk[$spb][$div][$tglPanen][$indk][$blk];
						}
					}
				}
			}
		}

		if($param['tipe']!='excel'){
			$border = "border=0";
			$bgcolor = "";
		}else{
			$border = "border=1";
			$bgcolor = "bgcolor=#8AD5DE";
		}
		if ($nob > 0) {
			$datasalah=0;
			foreach ($nospbx as $spb) {
				if(!isset($hsltimbang[$spb])){
					$datasalah=1;
				}
			}
				if($datasalah==1){
					echo "<span style=color:red> MASIH ADA DATA SPB DI TANGGAL INI YANG BELUM MEMELILIK HASIL TIMBANG</span>";
				}else{
					if($param['tipe']!='excel'){
						$tab.="<a class=mybutton onclick=\"postingbro()\">".$_SESSION['lang']['posting']." All</a>";
					}
				}
			$tab.="<br><br>";
			foreach ($blokx as $spb => $key1) {
				$kgbrutospb=$kgbruto[$spb]+$brdindkx2[$spb];
				$kgnettospb=$kgnetto[$spb]+$brdindkx2[$spb];
				$kgbrutobrdspb=$kgbrutobrd[$spb];
				$kgnettobrdspb=$kgnettobrd[$spb];
				$noblokx=0;
				$no++;
					$tab.="<table cellspacing=1 ".$border." cellpadding=2>";
						$tab.="<thead>";
						$tab.="<tr>
							<th align=center>".$_SESSION['lang']['kebun']."</th>
							<th align=center>".$_SESSION['lang']['pabrik']."</th>
						</tr>";
						$tab.="</thead>";
						$tab.="<tbody>";
							$tab.="<tr class=rowcontent>";
								$tab.="<td>";
									$tab.="
									<table cellspacing=1 cellpadding=5 ".$border." class='sortable'>
									<thead>
									<tr class=rowheader>
									<th>No</th>
									<th>".$_SESSION['lang']['kodeorg']."</th>
									<th>".$_SESSION['lang']['nospb']."</th>
									<th>".$_SESSION['lang']['tglNospb']."</th>
									<th>".$_SESSION['lang']['jjg']."</th>
									<th>".$_SESSION['lang']['brondolan']."</th>
									<th>".$_SESSION['lang']['status']."</th>
									</tr>
									</thead>
									<tbody>";
									$tab.="<tr class=rowcontent>
									<td align=center>".$no."</td>
									<td>".$kdorgb[$spb]."</td>
									<td>".$nospbx[$spb]."</td>
									<td>".$tanggalspb[$spb]."</td>
									<td align=right>".$totjjgspb[$spb]."</td>
									<td align=right>".$totbrdspb[$spb]."</td>
									<td>".$arrstatus[$statusposting[$spb]]."</td>";
									$tab.="</tr>";
									$tab.="</tbody>
									</table>";
								$tab.="</td>";
	
								$tab.="<td>";
									$tab.="
									<table cellspacing=1 ".$border." cellpadding=5 class='sortable'>
									<thead>
									<tr class=rowheader>
										<th align=center>No</th>
										<th>".$_SESSION['lang']['tanggal']."</th>
										<th>".$_SESSION['lang']['notransaksi']."</th>
										<th>".$_SESSION['lang']['nospb']."</th>
										<th>".$_SESSION['lang']['berat']." Bersih</th>
									</tr>
									</thead>
									<tbody>";
									$tab.="<tr class=rowcontent>
										<td align=center>".$no."</td>
										<td>".$tgltimbang[$spb]."</td>
										<td>".$notranswb[$spb]."</td>
										<td>".$nospbwb[$spb]."</td>
										<td align=right>".($kgnetto[$spb]+$brdindkx2[$spb])."</td>";
									$tab.="</tr>";
									$tab.="</tbody></table>";
								$tab.="</td>";
								
							$tab.="</tr>";
	
							$tab.="<tr class=rowcontent>";
								$tab.="<table cellspacing=1 ".$border." cellpadding=5 class='sortable'>";
								$tab.="<thead>";
								$tab.="<tr class=rowheader>";
								$tab.="<th rowspan=2>".$_SESSION['lang']['divisi']."</th>";
								$tab.="<th rowspan=2>".$_SESSION['lang']['tahuntanam']."</th>";
								$tab.="<th rowspan=2>".$_SESSION['lang']['blok']."</th>";
								$tab.="<th>".$_SESSION['lang']['luas']."</th>";
								$tab.="<th colspan=2>".$_SESSION['lang']['tanggal']."</th>";
								$tab.="<th colspan=3>Tandan</th>";
								$tab.="<th rowspan=2>".$_SESSION['lang']['bjr']."</th>";
								$tab.="<th rowspan=2>Brondolan</th>";
								$tab.="<th colspan=5>".$_SESSION['lang']['kg']."</th>";
								$tab.="</tr>";
								
								$tab.="<tr class=rowheader>";
								$tab.="<th>(HA)</th>";
								$tab.="<th>".$_SESSION['lang']['panen']."</th>";
								$tab.="<th>".$_SESSION['lang']['timbangan']."</th>";
								$tab.="<th>Tdn/Pokok/Thn</th>";
								$tab.="<th>Tdn/Pokok/Blok</th>";
								$tab.="<th>Hasil Panen<br>(Tdn/Blok)</th>";
	
								$tab.="<th>Kelas<br>Lahan</th>";
								$tab.="<th>Hasil Timbang<br>(Bruto)</th>";
								$tab.="<th>Hasil Timbang<br>(Bruto - Brondolan)</th>";
								$tab.="<th>Hasil Timbang<br>(Netto)</th>";
								$tab.="<th>Hasil Timbang<br>(Netto - Brondolan)</th>";
								$tab.="</tr>";
								$tab.="</thead>";
								$tab.="<tbody>";
				foreach ($key1 as $div => $key2) {
					foreach ($key2 as $tglPanen => $key3) {
						foreach ($key3 as $indk => $key4) {
							$nod++;
							$tab.="<tr bgcolor=#DAF7A6>";
							$tab.="<td align=left colspan=8 style='font-weight:bold;'>".$indk."</td>";
							$tab.="<td>".round($jjgindkx[$spb][$div][$tglPanen][$indk],2)."</td>";
							$tab.="<td></td>";
							$tab.="<td>".round($brdindkx[$spb][$div][$tglPanen][$indk],2)."</td>";
							$tab.="<td></td>";
							$tab.="<td></td>";
							$tab.="<td></td>";
							$tab.="<td></td>";
							$tab.="<td></td>";
							$tab.="</tr>";
							$Jumlahblokx=count($key4);
							foreach ($key4 as $blk => $val) {
								$nok++;
								$noblokx++;
														
			
								// Total Tdn/Ha/Thn
								$tottdnblkthn[$spb][$tglPanen][$div][$indk] += $tdnblk[$blk][$thntnmx[$blk]];
								// Total BJR per blok kecil
								$totbjrblk[$spb][$tglPanen][$div][$indk] += $bjrblk[$blk][substr($tglPanen,0,7)];
								// Total Hasil Panen (Tdn/Blok) / JJG
								$tothslpnn[$spb][$tglPanen][$div][$indk] += $hslpnnblok[$spb][$div][$tglPanen][$indk][$blk];
								// Total Hasil Brondolan Per Blok Besar
								$totbrdindk[$spb][$tglPanen][$div][$indk] += $brdblk[$spb][$div][$tglPanen][$indk][$blk];
								// Perhitungan Kg Bruto Diproporsi Per TPH, Sesi, Nik
								$kgbrutoproporsi[$spb][$div][$tglPanen][$indk][$blk] = (fixnan($klsbjrkg[$spb][$div][$tglPanen][$indk][$blk] / $totklsbjrkg[$spb]) * $kgbruto[$spb]+$brdblk[$spb][$div][$tglPanen][$indk][$blk]);
								// Perhitungan Kg Netto Diproporsi Per TPH, Sesi, Nik
								$kgnettoproporsi[$spb][$div][$tglPanen][$indk][$blk] = (fixnan($klsbjrkg[$spb][$div][$tglPanen][$indk][$blk] / $totklsbjrkg[$spb]) * $kgnetto[$spb]+$brdblk[$spb][$div][$tglPanen][$indk][$blk]);
								// Perhitungan Kg (Bruto - Brondolan) Diproporsi Per TPH, Sesi, Nik
								$kgbrutobrdproporsi[$spb][$div][$tglPanen][$indk][$blk] = round(fixnan( $kgbrutoproporsi[$spb][$div][$tglPanen][$indk][$blk]) - $brdblk[$spb][$div][$tglPanen][$indk][$blk],2);
								// Perhitungan Kg (Netto - Brondolan) Diproporsi Per TPH, Sesi, Nik
								$kgnettobrdproporsi[$spb][$div][$tglPanen][$indk][$blk] = round(fixnan( $kgnettoproporsi[$spb][$div][$tglPanen][$indk][$blk]) - $brdblk[$spb][$div][$tglPanen][$indk][$blk],2);
								// Perhitungan Total (Kg Bruto - Brondolan) Per Blok Besar
								$kgbrutobrdindk[$spb][$tglPanen][$div][$indk] += round($kgbrutobrdproporsi[$spb][$div][$tglPanen][$indk][$blk],2);
								// Perhitungan Total (Kg Netto - Brondolan) Per Blok Besar
								$kgnettobrdindk[$spb][$tglPanen][$div][$indk] += round($kgnettobrdproporsi[$spb][$div][$tglPanen][$indk][$blk],2);


								/// update untuk blok terakhir
								$kgbrutoproporsi[$spb][$div][$tglPanen][$indk][$blk]=round($kgbrutoproporsi[$spb][$div][$tglPanen][$indk][$blk],2);
								$kgnettoproporsi[$spb][$div][$tglPanen][$indk][$blk]=round($kgnettoproporsi[$spb][$div][$tglPanen][$indk][$blk],2);
								$kgbrutobrdproporsi[$spb][$div][$tglPanen][$indk][$blk]=round($kgbrutobrdproporsi[$spb][$div][$tglPanen][$indk][$blk],2);
								$kgnettobrdproporsi[$spb][$div][$tglPanen][$indk][$blk]=round($kgnettobrdproporsi[$spb][$div][$tglPanen][$indk][$blk],2);
								$kgbrutospb=round($kgbrutospb-round($kgbrutoproporsi[$spb][$div][$tglPanen][$indk][$blk],2),2);
								$kgnettospb=round($kgnettospb-round($kgnettoproporsi[$spb][$div][$tglPanen][$indk][$blk],2),2);
								//$kgbrutobrdspb=round($kgbrutobrdspb-round($kgbrutobrdproporsi[$spb][$div][$tglPanen][$indk][$blk],2),2);
								//$kgnettobrdspb=round($kgnettobrdspb-round($kgnettobrdproporsi[$spb][$div][$tglPanen][$indk][$blk],2),2);
								
								if($jlhblokxx[$spb]==$noblokx){
									// if($spb=='0002/PPPE/08/2024'){
									// 	echo $kgnettospb.'='.$kgnettoproporsi[$spb][$div][$tglPanen][$indk][$blk].'<br>';
									// }
									$kgbrutoproporsi[$spb][$div][$tglPanen][$indk][$blk]+=$kgbrutospb;
									$kgnettoproporsi[$spb][$div][$tglPanen][$indk][$blk]+=$kgnettospb;
									//$kgbrutobrdproporsi[$spb][$div][$tglPanen][$indk][$blk]+=$kgbrutobrdspb;
									//$kgnettobrdproporsi[$spb][$div][$tglPanen][$indk][$blk]+=$kgnettobrdspb;
								}

								// if($spb=='0002/PPPE/08/2024'){
								// 	echo $kgnettoproporsi[$spb][$div][$tglPanen][$indk][$blk].'<br>';
								// }
								// Perhitnungan Total KG BJR Per Blok Besar
								$totalkgbjrindk[$spb][$tglPanen][$div][$indk] += round($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] * $bjrblk[$blk][substr($tglPanen,0,7)],2);
								// Total KG Per Blok Besar
								$totalkgindk[$spb][$tglPanen][$div][$indk]  += round($kgbrutobrdproporsi[$spb][$div][$tglPanen][$indk][$blk] + $brdblk[$spb][$div][$tglPanen][$indk][$blk],2);
								// Total KG Bruto Diproporsi
								$totalkgbrutoproporsi[$spb][$tglPanen][$div][$indk] += $kgbrutoproporsi[$spb][$div][$tglPanen][$indk][$blk];
								// Total KG Netto Diproporsi
								$totalkgnettoroporsi[$spb][$tglPanen][$div][$indk] += $kgnettoproporsi[$spb][$div][$tglPanen][$indk][$blk];
								
								$totallusbloktanggal[$spb][$tglPanen][$div][$indk] += $luasblokx[$blk];
								$tab.="<tr id=row".$nok." class=rowcontent>";
								if($param['tipe']!='excel'){
									$tab.="<td hidden id=nospb_".$nok.">".$spb."</td>";
								}
								$tab.="<td>".getNamaOrg($div)."</td>";
								$tab.="<td>".$thntnmx[$blk]."</td>";
								if($param['tipe']!='excel'){
									$tab.="<td hidden id=indukblok_".$nok.">".$indk."</td>";
								}
								$tab.="<td id=blok_".$nok.">".$blk."</td>";
								$tab.="<td>".$luasblokx[$blk]."</td>";
								$tab.="<td id=tglpanen_".$nok.">".$tglPanen."</td>";
								$tab.="<td>". $tgltimbang[$spb] ."</td>";
								$tab.="<td>". $tdnblk[$blk][$thntnmx[$blk]] ."</td>";
								$tab.="<td>". round($tdnblok[$spb][$div][$tglPanen][$indk][$blk],2) ."</td>";
								$tab.="<td id=jjgblkx_".$nok.">". round($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk],2) ."</td>";
								$tab.="<td id=bjrblk_".$nok.">". round($bjrblk[$blk][substr($tglPanen,0,7)],2) ."</td>";
								if($param['tipe']!='excel'){
									$tab.="<td hidden id=kgbjr_".$nok.">".round(($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] * $bjrblk[$blk][substr($tglPanen,0,7)]),2)."</td>";
								}
								$tab.="<td id=brdblk_".$nok.">". round($brdblk[$spb][$div][$tglPanen][$indk][$blk],2) ."</td>";
								$tab.="<td>".round($klsbjrkg[$spb][$div][$tglPanen][$indk][$blk],2)."</td>";
								$tab.="<td id=kgwbbrutobrd_".$nok.">".round($kgbrutoproporsi[$spb][$div][$tglPanen][$indk][$blk],2)."</td>";
								$tab.="<td>".round($kgbrutobrdproporsi[$spb][$div][$tglPanen][$indk][$blk],2)."</td>";
								$tab.="<td id=kgwbnettobrd_".$nok.">".round($kgnettoproporsi[$spb][$div][$tglPanen][$indk][$blk],2)."</td>";
								$tab.="<td>".round($kgnettobrdproporsi[$spb][$div][$tglPanen][$indk][$blk],2)."</td>";
								if($param['tipe']!='excel'){
									$tab.="<td hidden id=totalkg_".$nok.">".round(($kgbrutobrdproporsi[$spb][$div][$tglPanen][$indk][$blk] + $brdblk[$spb][$div][$tglPanen][$indk][$blk]),2)."</td>";
								}
								$tab.="</tr>";
							}
							$tab.="<tr id=row_".$nod." class=rowheader ".$bgcolor.">";
							if($param['tipe']!='excel'){
								$tab.="<td hidden id=nospbdiv_".$nod.">".$spb."</td>";
								$tab.="<td hidden id=indukblokdiv_".$nod.">".$indk."</td>";
								$tab.="<td hidden id=tglpanendiv_".$nod.">".$tglPanen."</td>";
							}
								$tab.="<td colspan=2>TOTAL ".tanggalnormal($tglPanen)." ".getNamaOrg($div)."</td>";
								$tab.="<td>".substr($blk,0,9)."</td>";
								$tab.="<td>". $totallusbloktanggal[$spb][$tglPanen][$div][$indk] ."</td>";
								$tab.="<td></td>";
								$tab.="<td></td>";
								$tab.="<td>".round($tottdnblkthn[$spb][$tglPanen][$div][$indk],2)."</td>";
								$tab.="<td>".round($totaltdnblok[$spb][$div][$tglPanen][$indk],2)."</td>";
								$tab.="<td>".round($tothslpnn[$spb][$tglPanen][$div][$indk],2)."</td>";
								$tab.="<td>".round($totbjrblk[$spb][$tglPanen][$div][$indk],2)."</td>";
							if($param['tipe']!='excel'){
								$tab.="<td hidden id=totkgbjrindk_".$nod.">".round($totalkgbjrindk[$spb][$tglPanen][$div][$indk],2)."</td>";
								$tab.="<td hidden id=bjrblokbesar_".$nod.">".round(($totalkgbjrindk[$spb][$tglPanen][$div][$indk] / $jjgindkx[$spb][$div][$tglPanen][$indk]),2)."</td>";
							}
								$tab.="<td>".round($totbrdindk[$spb][$tglPanen][$div][$indk],2)."</td>";
								$tab.="<td>".round($totklsbjrkgx[$spb][$div][$tglPanen][$indk],2)."</td>";
								// $tab.="<td>". round($kgbruto[$spb],2) ." xxx</td>";
								$tab.="<td>". round($totalkgbrutoproporsi[$spb][$tglPanen][$div][$indk],2) ."</td>";
								$tab.="<td id=kgwbindk_".$nod.">". round($kgbrutobrdindk[$spb][$tglPanen][$div][$indk],2) ."</td>";
								// $tab.="<td>" . round($kgnetto[$spb],2) . " yyy</td>";
								$tab.="<td>" . round($totalkgnettoroporsi[$spb][$tglPanen][$div][$indk],2) . "</td>";
								$tab.="<td id=kgwbnettoindk_".$nod.">" . round($kgnettobrdindk[$spb][$tglPanen][$div][$indk],2) . "</td>";
							if($param['tipe']!='excel'){
								$tab.="<td hidden id=totalkgindk_".$nod.">".round($totalkgindk[$spb][$div][$indk],2)."</td>";
							}
							$tab.="</tr>";
						}
					}
				}
					$tab.="</tbody>";
								$tab.="</table>";
							$tab.="</tr>";
						$tab.="</tbody>";
					$tab.="</table>";
				$tab.="<br><br>";
			}
			$tab.="<input type=hidden id='rows_dt' value='".$nok."'>";
				$tab.="<input type=hidden id='rows_induk' value='".$nod."'>";
		

			
		
			
			
		} else {
			$tab.="<table cellspacing=1 ".$border." cellpadding=2>";
				$tab.="<thead>";
				$tab.="<tr>
					<th align=center>".$_SESSION['lang']['kebun']."</th>
					<th align=center>".$_SESSION['lang']['pabrik']."</th>
				</tr>";
				$tab.="</thead>";
				$tab.="<tbody>";
					$tab.="<tr class=rowcontent>";
						$tab.="<td>";
							$tab.="
							<table cellspacing=1 cellpadding=5 ".$border." class='sortable'>
							<thead>
							<tr class=rowheader>
							<th>No</th>
							<th>".$_SESSION['lang']['kodeorg']."</th>
							<th>".$_SESSION['lang']['nospb']."</th>
							<th>".$_SESSION['lang']['tglNospb']."</th>
							<th>".$_SESSION['lang']['jjg']."</th>
							<th>".$_SESSION['lang']['brondolan']."</th>
							<th>".$_SESSION['lang']['status']."</th>
							</tr>
							</thead>
							<tbody>";
							$tab.="<tr class=rowcontent>
							<td align=center colspan=7>".$_SESSION['lang']['errdatanotexist']."</td>";
							$tab.="</tr>";
							$tab.="</tbody>
							</table>";
						$tab.="</td>";

						$tab.="<td>";
							$tab.="
							<table cellspacing=1 ".$border." cellpadding=5 class='sortable'>
							<thead>
							<tr class=rowheader>
								<th align=center>No</th>
								<th>".$_SESSION['lang']['tanggal']."</th>
								<th>".$_SESSION['lang']['notransaksi']."</th>
								<th>".$_SESSION['lang']['nospb']."</th>
								<th>".$_SESSION['lang']['berat']."</th>
							</tr>
							</thead>
							<tbody>";
							$tab.="<tr class=rowcontent>
								<td align=center colspan=5>".$_SESSION['lang']['errdatanotexist']."</td>";
							$tab.="</tr>";
							$tab.="</tbody></table>";
						$tab.="</td>";
						
					$tab.="</tr>";
				$tab.="</tbody>";
			$tab.="</table>";
		}

		if($tabbjr != '') {
			$tab = $tabbjr;
		}
		if($param['tipe']!='excel'){
			echo $tab;
		}else{
			$nop_ = "laporan_ambil_kg_timbangan";
			if (strlen($tab) > 0) {
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/' . $file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
				if (!fwrite($handle, $tab)) {
					echo "<script language=javascript1.2>
							parent.window.alert('Can't convert to excel format');
							</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
							window.location='tempExcel/" . $nop_ . ".xls';
							</script>";
				}
				fclose($handle);
			}
		}
	break;
	
	case'postingbro':

		unset($_SESSION['temp']['tempNospb']);

		$where1 = "";
		$tahun = "";
		if ($tgl != "" && $tgl2 != "") {
			$tahun=substr($tgl, 0,4);
			$bulan=substr($tgl, 4,2);
			$where1 .= " and tanggal between '".$tgl."' and '".$tgl2."'";
		}
		if ($intex != "") {
			$where1 .= " and tujuan='".$intex."'";
		}

		#1. Cek Prd Akuntansi
		$strperiodeak="select * from ".$dbname.".setup_periodeakuntansi where periode = '".$tahun.'-'.$bulan."' and kodeorg='".substr($kdOrg,0,4)."'";
		// exit('warning:'.$strperiodeak);
		$resak=$owlPDO->query($strperiodeak) or die(print " Gagal: ".PDOException::getMessage());
		$resak->setFetchMode(PDO::FETCH_ASSOC);
		$barak=$resak->fetch();
		if($barak['tutupbuku']=='1'){
			exit('Error : Periode Akuntansi Sudah di Tutup.');
		}

		#2. Cek Prd Gaji
		$strgaji="select * from ".$dbname.".sdm_5periodegaji where periode = '".$tahun.'-'.$bulan."' and kodeorg='".substr($kdOrg,0,4)."'";
		$resgj=$owlPDO->query($strgaji) or die(print " Gagal: ".PDOException::getMessage());
		$resgj->setFetchMode(PDO::FETCH_ASSOC);
		$bargj=$resgj->fetch();
		if($bargj['sudahproses']=='1'){
			exit('Error : Periode Gaji Sudah di Tutup.');
		}

		$whereKodeOrg = " and c.kodeorg='".$kdOrg."' ";
		$whereKodeOrgSPB = " and d.nospb like '%".$kdOrg."%' ";
		$whereKodeOrgTimbangan = " and kodeorg = '".$kdOrg."' ";
		if(strlen($kdOrg)>4){
			// $whereKodeOrg = " and c.nospb like '%".$kdOrg."%' ";
			$whereKodeOrg = " and a.blok like '%".$kdOrg."%' ";
			$whereKodeOrgTimbangan = " and divcode = '".$kdOrg."' ";
			$whereKodeOrgSPB = " and c.blok like '%".$kdOrg."%' ";
		}

			if($intex=='3'){
				$whereKodeOrgTimbangan .= " and millcode = 'EXTM' ";
				$where2 .= " and millcode = 'EXTM' ";
			}

		$nmklaslahan = makeOption($dbname,"setup_kelaslahan","kode,nama");

		$strbloktahunan = "SELECT * FROM setup_blok_tahunan
		where tahun = '".$tahun."".$bulan."' and kodeorg like '%".$kdOrg."%' ";
		$resbloktahunan = fetchdata($strbloktahunan);

		$dbaseblok='setup_blok';
		$whereblok='';
		$whereblok2='';
		if(count($resbloktahunan) > 0) {	
			$dbaseblok='setup_blok_tahunan';
			$whereblok=" and b.tahun = '".$tahun."".$bulan."'";
			$whereblok=" and b.tahun = '".$tahun."".$bulan."'";
		}

		$str = "SELECT a.nospb, substr(a.blok,1,6) as divisi, a.blok as indukblok, a.tph as notph, a.sesi as sesi, a.pemanen as pemanen,
				b.kodeorg as blok, b.tahuntanam, b.luasareaproduktif, a.tanggalpanen,
				a.jjg, a.kgwb, a.kgwbnetto, a.bjr, a.brondolan, a.totalkg, a.kgbjr, c.kodeorg, c.tanggal as tglspb, c.posting
				FROM $dbname.kebun_spbdt a JOIN $dbname.".$dbaseblok." b on a.blok = b.indukblok and b.tahuntanam <= (".(intval(date('Y'))-3).") and b.tahuntanam!='0'
				JOIN $dbname.kebun_spbht c on a.nospb = c.nospb
				WHERE 1=1 and ($tahun-b.tahuntanam)>=3 ".$where1." ".$whereKodeOrg." ".$whereblok."";
		$res = fetchdata($str);
		$nospbx = $divisix = $blokx = $indukblokx = $thntnmx = $luasblokx = $klaslahanx = $tglpanenx = $tglpanenindk = array();
		$sesix = $notphx = $pemanenx = array();
		$tgltimbang = $tdnblk = $kgbruto = $kgnetto = $bjrblk = $tdnblok = $klsbjrkg = $totjjgspb = $totbrdspb = $hslpnnblok = $totbrdindk = array();
		$totkgbruto = $tottdnblkthn = $totbjrblk = $totaltdnblok = $totklsbjrkg = $totluasblk = $tothslpnn = $brdblk = array();
		$kgbrutobrd = $kgnettobrd = $kgbrutoproporsi = $kgnettoproporsi = $kgbrutobrdproporsi = $kgnettobrdproporsi = array();

		foreach ($res as $val) {
			$nospbx[$val['nospb']] = $val['nospb'];
			if(!isset($jlhblokxx[$val['nospb']])){
				$jlhblokxx[$val['nospb']]=0;
			}

			if(!isset($blokx[$val['nospb']][$val['divisi']][$val['tanggalpanen']][$val['indukblok']][$val['blok']])){
				$jlhblokxx[$val['nospb']]+=1;
			}
			$blokx[$val['nospb']][$val['divisi']][$val['tanggalpanen']][$val['indukblok']][$val['blok']] = $val['blok'];
			$thntnmx[$val['blok']] = $val['tahuntanam'];
			$luasblokx[$val['blok']] = $val['luasareaproduktif'];

			$kdorgb[$val['nospb']] 							= $val['kodeorg'];
			$tanggalspb[$val['nospb']] 						= $val['tglspb'];
			$statusposting[$val['nospb']]					= $val['posting'];
			$totluasblk[$val['nospb']][$val['indukblok']]	+= $val['luasareaproduktif'];
		}
		
		// Get Total Group By Nospb
		$strSum = "SELECT a.nospb,a.jjg,a.brondolan FROM $dbname.kebun_spbdt a
				JOIN $dbname.kebun_spbht c on a.nospb = c.nospb
				WHERE 1=1 ".$where1." ".$whereKodeOrg."";
		$resSum = fetchdata($strSum);
		foreach ($resSum as $val) {
			$totjjgspb[$val['nospb']] += $val['jjg'];
			$totbrdspb[$val['nospb']] += $val['brondolan'];
		}

		// Get Total Group By Induk Blok
		$strSum = "SELECT a.nospb,substr(a.blok,1,6) as divisi,a.blok as indukblok,a.tanggalpanen,a.jjg,a.kgwb,a.kgwbnetto,a.bjr,
				a.brondolan,a.totalkg,a.kgbjr,c.kodeorg,c.tanggal as tglspb,c.posting FROM $dbname.kebun_spbdt a
				JOIN $dbname.kebun_spbht c on a.nospb = c.nospb
				WHERE 1=1 ".$where1." ".$whereKodeOrg."";
		$resSum = fetchdata($strSum);
		foreach ($resSum as $val) {
			$jjgindkx[$val['nospb']][$val['divisi']][$val['tanggalpanen']][$val['indukblok']] 	+=$val['jjg'];
			$brdindkx[$val['nospb']][$val['divisi']][$val['tanggalpanen']][$val['indukblok']] 	+= $val['brondolan'];
			$brdindkx2[$val['nospb']] 	+= $val['brondolan'];

		}

		// echo "<pre>";
		// print_r($jjgindkx);
		// echo "</pre>";

		$strbjr = "SELECT a.kodeorg, a.bjr, b.indukblok,a.periode FROM kebun_5bjr a JOIN ".$dbaseblok." b
		on a.kodeorg = b.kodeorg where (a.periode='".substr($pertgl,0,7)."' or a.periode='".periodelalu(substr($pertgl,0,7))."') and b.indukblok in
		(
		SELECT c.blok FROM kebun_spbdt c 
		JOIN kebun_spbht d on c.nospb = d.nospb
		where d.tanggal between '".$pertgl."' and '".$pertgl2."' ".$whereKodeOrgSPB." ".$whereblok."
		and d.tujuan='".$intex."'
		)";
		$resbjr = fetchdata($strbjr);

		if(count($resbjr) > 0) {	
			foreach ($resbjr as $val) {
				$bjrblk[$val['kodeorg']][$val['periode']] = $val['bjr'];
			}
		} else {

			$sql = "SELECT DISTINCT c.blok,c.indukblok FROM kebun_spbdt_detail c 
			JOIN kebun_spbht d on c.nospb = d.nospb
			where d.tanggal between '".$pertgl."' and '".$pertgl2."' ".$whereKodeOrgSPB."
			and d.tujuan='".$intex."'";
			$res = fetchData($sql);


			$tabbjr = "<b>Masih ada List Data, <span style=color:red>BJR Periode ".substr($pertgl,0,7)." belum di Setup!</span></b> <br/><br/>";
			$tabbjr .= "<table border=0 cellpadding=2 cellspacing=1 width=100%>";
				$tabbjr .= "<thead>";
					$tabbjr .= "<tr>";
						$tabbjr .= "<th>Induk Blok</th>";
						$tabbjr .= "<th>Kode Blok</th>";
						$tabbjr .= "<th>Periode</th>";
						$tabbjr .= "<th>Keterangan</th>";
					$tabbjr .= "<tr>";
				$tabbjr .= "</thead>";
				
				$tabbjr .= "<tbody>";
				foreach($res as $val):
					$tabbjr .= "<tr class=rowcontent>";
						$tabbjr .= "<td align=center>".$val['indukblok']."</td>";
						$tabbjr .= "<td align=center>".$val['blok']."</td>";
						$tabbjr .= "<td align=center>".substr($pertgl,0,7)."</td>";
						$tabbjr .= "<td align=center>Data BJR untuk Periode ".substr($pertgl,0,7)." belum di Setup</td>";
					$tabbjr .= "</tr>";
				endforeach;
				$tabbjr .= "</tbody>";
			$tabbjr .= "</table>";
		}


		$arrstatus = array("0" => "Belum Posting", "1" => "Sudah Posting");
		$no = 0;
		$nob = 0;
		$nok = 0;
		$nod = 0;
		$urutBlok=array();
		$sisa = array();
		$sisa2 = array();
		
		foreach ($blokx as $spb => $key1) {
			$strPabrik = "SELECT * FROM $dbname.pabrik_timbangan where nospb='".$spb."' ".$whereKodeOrgTimbangan."";
			$resPabrik = fetchData($strPabrik);
			foreach ($resPabrik as $dtp) {
				$tgltimbang[$dtp['nospb']]	= substr($dtp['tanggal'],0,10);
				$nospbwb[$dtp['nospb']]		= $dtp['nospb'];
				$notranswb[$dtp['nospb']]	= $dtp['notransaksi'];

				// Perhitungan mendapatkan Kg Bruto
				$kgbruto[$dtp['nospb']]		= ($dtp['beratmasuk']-$dtp['beratkeluar']- $brdindkx2[$dtp['nospb']]);
				// Perhitungan mendapatkan Kg Netto
				$kgnetto[$dtp['nospb']]		= ($dtp['beratbersih']- $brdindkx2[$dtp['nospb']]);

				$kgbrutobrd[$dtp['nospb']] = ($kgbruto[$dtp['nospb']] - $brdindkx2[$dtp['nospb']]);
				$kgnettobrd[$dtp['nospb']] = ($kgnetto[$dtp['nospb']] - $brdindkx2[$dtp['nospb']]);
			}

			$periodext= substr($pertgl,0,7);

			foreach ($key1 as $div => $key2) {
				foreach ($key2 as $tglPanen => $key3) {
					foreach ($key3 as $indk => $key4) {
						$nob++;
						foreach ($key4 as $blk => $val) {

									if($bjrblk[$blk][substr($tglPanen,0,7)]<=0 or $bjrblk[$blk][substr($tglPanen,0,7)]==''){
										if(intval(substr($tglPanen,9,2))<=2){
											$bjrblk[$blk][substr($tglPanen,0,7)]=$bjrblk[$blk][periodelalu(substr($tglPanen,0,7))];

										}
									}
									$strkls = "SELECT b.kodeorg,a.kodelahan,a.tahuntanam,a.nilai,a.periode1,a.periode2 FROM $dbname.kebun_5tandanhathn a JOIN $dbname.".$dbaseblok." b
												ON a.kodelahan = b.klasifikasitanah and a.tahuntanam = b.tahuntanam
												WHERE '{$periodext}' between a.periode1 and a.periode2 and  indukblok='".$indk."' ".$whereblok."
												group by a.kodelahan, a.tahuntanam";
									$reskls = fetchdata($strkls);
									foreach ($reskls as $kls) {
										$tdnblk[$kls['kodeorg']][$kls['tahuntanam']] = $kls['nilai'];
										$klaslahanx[$blk] = $kls['kodelahan'];
									}
			
									// Perhitungan Tdn/blok Per TPH, Sesi, Nik
									$tdnblok[$spb][$div][$tglPanen][$indk][$blk] = ($luasblokx[$blk] * $tdnblk[$blk][$thntnmx[$blk]]);
									// Total Perhitungan Tdn/blok
									$totaltdnblok[$spb][$div][$tglPanen][$indk] += $tdnblok[$spb][$div][$tglPanen][$indk][$blk];
									// Perhitungan Kelas Lahan KG Per TPH, Sesi, Nik
									//$klsbjrkg[$spb][$div][$indk][$blk] = round($tdnblok[$spb][$div][$indk][$blk] * $bjrblk[$blk][substr($tglPanen,0,7)],2);
									// Perhitungan Total Kelas Lahan KG
									//$totklsbjrkg[$spb] += $klsbjrkg[$spb][$div][$indk][$blk];
									// Perhitungan Total (Kg Bruto - Brondolan)
									//$kgbrutobrd[$spb] = ($kgbruto[$spb] - $brdindkx[$spb][$div][$tglPanen][$indk]);
									// Perhitungan Total (Kg Netto - Brondolan)
									//$kgnettobrd[$spb] = ($kgnetto[$spb] - $brdindkx[$spb][$div][$tglPanen][$indk]);
			
									// $jmlhblok[$spb][$div][$indk] = count($blokx[$spb][$div][$indk]);
									$jmlhblok[$spb][$div][$tglPanen][$indk] += count($blokx[$spb][$div][$tglPanen][$indk][$blk]);
									// $jmlhblok[$spb][$div][$indk] += count($bloktsnx[$spb][$div][$indk][$blk][$tph]);
						}
					}
				}
			}
		}
		

		// echo "<pre>";
		// print_r($jmlhblok);
		// echo "</pre>";	
		foreach ($blokx as $spb => $key1) {
			foreach ($key1 as $div => $key2) {
				foreach ($key2 as $tglPanen => $key3) {
					foreach ($key3 as $indk => $key4) {
						foreach ($key4 as $blk => $val) {
							$urutBlok[$spb][$div][$tglPanen][$indk]++;
							// Jika Urutan Blok sama dengan jumlah blok di induk blok
							if ($urutBlok[$spb][$div][$tglPanen][$indk] == $jmlhblok[$spb][$div][$tglPanen][$indk]) {
								// Hasil Panen (Tdn/Blok) / JJG Per TPH, Sesi, Nik
								$hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] = round($jjgindkx[$spb][$div][$tglPanen][$indk] - $sisa[$spb][$div][$tglPanen][$indk],2);
								$brdblk[$spb][$div][$tglPanen][$indk][$blk] = round($brdindkx[$spb][$div][$tglPanen][$indk] - $sisa2[$spb][$div][$tglPanen][$indk],2);
								$klsbjrkg[$spb][$div][$tglPanen][$indk][$blk]=($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk]*$bjrblk[$blk][substr($tglPanen,0,7)]);
								// Hasil Brondolan Per Blok Kecil, TPH, Sesi, Nik
							} else {
								// Hasil Panen (Tdn/Blok) / JJG
								$hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] = round(fixnan($tdnblok[$spb][$div][$tglPanen][$indk][$blk] / $totaltdnblok[$spb][$div][$tglPanen][$indk]) * $jjgindkx[$spb][$div][$tglPanen][$indk],2);
								$brdblk[$spb][$div][$tglPanen][$indk][$blk] = round(fixnan($tdnblok[$spb][$div][$tglPanen][$indk][$blk] / $totaltdnblok[$spb][$div][$tglPanen][$indk]) * $brdindkx[$spb][$div][$tglPanen][$indk],2);
								$klsbjrkg[$spb][$div][$tglPanen][$indk][$blk]=($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk]*$bjrblk[$blk][substr($tglPanen,0,7)]);
								// Hasil Brondolan Per Blok Kecil
							}
							$totklsbjrkg[$spb]+=$klsbjrkg[$spb][$div][$tglPanen][$indk][$blk];
							$totklsbjrkgx[$spb][$div][$tglPanen][$indk]+=$klsbjrkg[$spb][$div][$tglPanen][$indk][$blk];
							$sisa[$spb][$div][$tglPanen][$indk] 	+= $hslpnnblok[$spb][$div][$tglPanen][$indk][$blk];
							$sisa2[$spb][$div][$tglPanen][$indk] 	+= $brdblk[$spb][$div][$tglPanen][$indk][$blk];
						}
					}
				}
			}
		}

		$sdta2='';
		$kgbrutospb = 0;
		$kgnettospb = 0;
		if ($nob > 0) {
			foreach ($blokx as $spb => $key1) {
				$delDet = deleteQuery($dbname, "kebun_spbdt_detail","nospb='".$spb."'");
				$owlPDO->exec($delDet);
				$kgbrutospb=$kgbruto[$spb]+$brdindkx2[$spb];
				$kgnettospb=$kgnetto[$spb]+$brdindkx2[$spb];
				$kgbrutobrdspb=$kgbrutobrd[$spb];
				$kgnettobrdspb=$kgnettobrd[$spb];
				$no++;
				foreach ($key1 as $div => $key2) {
					foreach ($key2 as $tglPanen => $key3) {
						foreach ($key3 as $indk => $key4) {
							$nod++;
							foreach ($key4 as $blk => $val) {
								$nok++;
								$noblokx++;
														
			
														// Total Tdn/Ha/Thn
														$tottdnblkthn[$spb][$div][$indk] += $tdnblk[$blk][$thntnmx[$blk]];
														// Total BJR per blok kecil
														$totbjrblk[$spb][$div][$indk] += $bjrblk[$blk][substr($tglPanen,0,7)];
														// Total Hasil Panen (Tdn/Blok) / JJG
														$tothslpnn[$spb][$div][$indk] += $hslpnnblok[$spb][$div][$tglPanen][$indk][$blk];
														// Total Hasil Brondolan Per Blok Besar
														$totbrdindk[$spb][$div][$indk] += $brdblk[$spb][$div][$tglPanen][$indk][$blk];
														// Perhitungan Kg Bruto Diproporsi Per TPH, Sesi, Nik
														$kgbrutoproporsi[$spb][$div][$tglPanen][$indk][$blk] = (fixnan($klsbjrkg[$spb][$div][$tglPanen][$indk][$blk] / $totklsbjrkg[$spb]) * $kgbruto[$spb])+ $brdblk[$spb][$div][$tglPanen][$indk][$blk];
														// Perhitungan Kg Netto Diproporsi Per TPH, Sesi, Nik
														$kgnettoproporsi[$spb][$div][$tglPanen][$indk][$blk] = (fixnan($klsbjrkg[$spb][$div][$tglPanen][$indk][$blk] / $totklsbjrkg[$spb]) * $kgnetto[$spb])+ $brdblk[$spb][$div][$tglPanen][$indk][$blk];
														// Perhitungan Kg (Bruto - Brondolan) Diproporsi Per TPH, Sesi, Nik
														$kgbrutobrdproporsi[$spb][$div][$tglPanen][$indk][$blk] = round(fixnan( $kgbrutoproporsi[$spb][$div][$tglPanen][$indk][$blk] ) - $brdblk[$spb][$div][$tglPanen][$indk][$blk],2);
														// Perhitungan Kg (Netto - Brondolan) Diproporsi Per TPH, Sesi, Nik
														$kgnettobrdproporsi[$spb][$div][$tglPanen][$indk][$blk] = round(fixnan( $kgnettoproporsi[$spb][$div][$tglPanen][$indk][$blk]) - $brdblk[$spb][$div][$tglPanen][$indk][$blk],2);

														$kgbrutospb=round($kgbrutospb-round($kgbrutoproporsi[$spb][$div][$tglPanen][$indk][$blk],2),2);
														$kgnettospb=round($kgnettospb-round($kgnettoproporsi[$spb][$div][$tglPanen][$indk][$blk],2),2);
														//$kgbrutobrdspb=round($kgbrutobrdspb-round($kgbrutobrdproporsi[$spb][$div][$tglPanen][$indk][$blk],2),2);
														//$kgnettobrdspb=round($kgnettobrdspb-round($kgnettobrdproporsi[$spb][$div][$tglPanen][$indk][$blk],2),2);
														
														if($jlhblokxx[$spb]==$noblokx){
															// if($spb=='0002/PPPE/08/2024'){
															// 	echo $kgnettospb.'='.$kgnettoproporsi[$spb][$div][$tglPanen][$indk][$blk].'<br>';
															// }
															// $kgbrutoproporsi[$spb][$div][$tglPanen][$indk][$blk]+=$kgbrutospb;
															// $kgnettoproporsi[$spb][$div][$tglPanen][$indk][$blk]+=$kgnettospb;
															//$kgbrutobrdproporsi[$spb][$div][$tglPanen][$indk][$blk]+=$kgbrutobrdspb;
															//$kgnettobrdproporsi[$spb][$div][$tglPanen][$indk][$blk]+=$kgnettobrdspb;
														}
														// Perhitungan Total (Kg Bruto - Brondolan) Per Blok Besar
														$kgbrutobrdindk[$spb][$div][$indk] += round($kgbrutobrdproporsi[$spb][$div][$tglPanen][$indk][$blk],2);
														// Perhitungan Total (Kg Netto - Brondolan) Per Blok Besar
														$kgnettobrdindk[$spb][$div][$indk] += round($kgnettobrdproporsi[$spb][$div][$tglPanen][$indk][$blk],2);
														// Perhitnungan Total KG BJR Per Blok Besar
														$totalkgbjrindk[$spb][$div][$indk] += round($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] * $bjrblk[$blk][substr($tglPanen,0,7)],2);
														// Total KG Per Blok Besar
														$totalkgindk[$spb][$div][$indk]  += round($kgbrutobrdproporsi[$spb][$div][$tglPanen][$indk][$blk] + $brdblk[$spb][$div][$tglPanen][$indk][$blk],2);
														// Total KG Bruto Diproporsi
														$totalkgbrutoproporsi[$spb][$div][$indk] += $kgbrutoproporsi[$spb][$div][$tglPanen][$indk][$blk];
														// Total KG Netto Diproporsi
														$totalkgnettoroporsi[$spb][$div][$indk] += $kgnettoproporsi[$spb][$div][$tglPanen][$indk][$blk];
														
														if($sdta2==''){
										        			$sdta2=" ('".$spb."','".$indk."','".$blk."','".$tglPanen."','".round($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk],2)."','".round($kgbrutoproporsi[$spb][$div][$tglPanen][$indk][$blk],2)."','".round($kgnettoproporsi[$spb][$div][$tglPanen][$indk][$blk],2)."','".round($bjrblk[$blk][substr($tglPanen,0,7)],2)."','".round($brdblk[$spb][$div][$tglPanen][$indk][$blk],2)."','".$kgbrutoproporsi[$spb][$div][$tglPanen][$indk][$blk]."','".round(($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] * $bjrblk[$blk][substr($tglPanen,0,7)]),2)."')";
										        		}else{
										        			$sdta2.=" , ('".$spb."','".$indk."','".$blk."','".$tglPanen."','".round($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk],2)."','".round($kgbrutoproporsi[$spb][$div][$tglPanen][$indk][$blk],2)."','".round($kgnettoproporsi[$spb][$div][$tglPanen][$indk][$blk],2)."','".round($bjrblk[$blk][substr($tglPanen,0,7)],2)."','".round($brdblk[$spb][$div][$tglPanen][$indk][$blk],2)."','".$kgbrutoproporsi[$spb][$div][$tglPanen][$indk][$blk]."','".round(($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] * $bjrblk[$blk][substr($tglPanen,0,7)]),2)."')";
										        		}
							}
						}
					}
				}
			}
		}

		

		

		try {
			$owlPDO->beginTransaction();
		
		//echo $tab;

					
		$sdta1="insert into ".$dbname.".kebun_spbdt_detail (nospb,indukblok,blok,tanggalpanen,jjg,kgwb,kgwbnetto,bjr,brondolan,totalkg,kgbjr) values ";
		$sinsert=$sdta1." ".$sdta2.";";
		// echo $sinsert;
		// exit('error');
		$owlPDO->exec($sinsert);
		
		foreach ($nospbx as $spb) {
			$colHt = array(
				"posting" 	=> '1',
				"postingby"	=> $_SESSION['standard']['userid']
			);
			$updH = updateQuery($dbname,"kebun_spbht",$colHt,"nospb='".$spb."'");
			// exit("Warning: ".$updH);
			$owlPDO->exec($updH);
		}
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
			echo 'SUKSES';
	break;

	case'PostingData':
		try {
			$owlPDO->beginTransaction();
			$str = "select distinct tujuan, kodeorg, tanggal, substr(blok,1,6) as divisi  from ".$dbname.".kebun_spbdt a left join ".$dbname.".kebun_spbht b on a.nospb = b.nospb where a.nospb='".$noSpb."'";
			$res = fetchdata($str);
			foreach($res as $val){
				$intex=$val['tujuan'];
				$kodeorg=$val['kodeorg'];
				$tanggal=$val['tanggal'];
				$divisi=$val['divisi'];
			}
			
			if($intex!='3'){
				validasiInput($kodeorg,$divisi,'spbpost',$tanggal,$exit='0');
			}
			
			if($param['intex'] == '3'){
				$whrintex = "and millcode like 'EXT%'";
			}else{
				$whrintex = "and millcode = '".$param['kodeorg']."'";
			}
			$str = "select * from ".$dbname.".pabrik_timbangan where nospb='".$noSpb."' ".$whrintex."";
			$resn = fetchdata($str);
			if(count($resn)==0){
				throw new PDOException("Tiket timbang PKS tidak ada.");
			}
			if(count($resn)>1){
				throw new PDOException("Tiket timbang double.");
			}
			
			if($noTrans==''){
				foreach($resn as $rTimbngn){							
					$noTrans  =$rTimbngn['notransaksi'];
				}			
			}

			$strblk = "SELECT a.nospb,a.blok as indukblok,b.kodeorg FROM $dbname.kebun_spbdt a 
			JOIN $dbname.setup_blok b on a.blok = b.indukblok
			WHERE nospb='".$noSpb."' and b.statusblok='TM'";
			$resblk = fetchdata($strblk);
			foreach ($resblk as $bar) {
				$getspbblk = $bar['kodeorg'];
			}
			// echo "<pre>";
			// print_r($getspbblk);
			// echo "</pre>";
			// exit("Warning ".$strblk);
			
			$sNospb="select nospb,blok,kgbjr,brondolan,jjg,tanggalpanen from ".$dbname.".kebun_spbdt where nospb='".$noSpb."'";
			$res = fetchdata($sNospb);
			$jlhblok = count($res); 
			foreach($res as $rNospb){
				//berat dan total berat
				$sTotal="select sum(kgbjr) as total,sum(brondolan) as totalbrondolan,sum(jjg) as jjg from ".$dbname.".kebun_spbdt where nospb='".$rNospb['nospb']."'";
				$resx = fetchdata($sTotal);
				foreach($resx as $rTotal){
					$totKgkbn    =$rTotal['total'];
					$totBrondolan=$rTotal['totalbrondolan'];
					$totjjg      =$rTotal['jjg'];
				}	
				
				
				if(($totjjg>0 or $totBrondolan>0)){
					$selDt = selectQuery($dbname,"kebun_spbdt_detail","*","nospb='".$noSpb."' and blok='".$param['blokecil']."' and tanggalpanen='".$param['tglPanen']."' and tph='".$param['notph']."' and sesi='".$param['sesi']."' and pemanen='".$param['pemanen']."'");
					$resDt = fetchdata($selDt);
					if (count($resDt) > 0) {
						$delDet = deleteQuery($dbname, "kebun_spbdt_detail","nospb='".$noSpb."' and blok='".$param['blokecil']."' and tanggalpanen='".$param['tglPanen']."' and tph='".$param['notph']."' and sesi='".$param['sesi']."' and pemanen='".$param['pemanen']."'");
						$owlPDO->exec($delDet);
					}

					$colDt = array(
						'nospb'			=> $noSpb,
						'indukblok'		=> $param['indukblok'],
						'blok'			=> $param['blokecil'],
						'tanggalpanen'	=> $param['tglPanen'],
						'tph'			=> $param['notph'],
						'sesi'			=> $param['sesi'],
						'pemanen'		=> $param['pemanen'],
						'jjg'			=> $param['jjgblokcl'],
						'kgwb'			=> $param['kgbrutoblkcl'],
						'kgwbnetto'		=> $param['kgnettoblkcl'],
						'bjr'			=> $param['bjrblkcl'],
						'brondolan'		=> $param['brdblkcl'],
						'totalkg'		=> $param['totalkgblkcl'],
						'kgbjr'			=> $param['kgbjrblkcl']
					);
			
					$cols = array();
					foreach ($colDt as $key => $row) {
						$cols[] = $key;
					}

					$insDt = insertQuery($dbname,"kebun_spbdt_detail",$colDt,$cols);
					$owlPDO->exec($insDt);
					// exit("Warning");

					// $sUpd="update ".$dbname.".kebun_spbdt set kgwbnetto='".$kgWbNetto."',kgwb='".$kgWb."',totalkg='".$totKg."' where nospb='".$rNospb['nospb']."' and blok='".$rNospb['blok']."' and tanggalpanen='".$rNospb['tanggalpanen']."'"; //echo "warning:".$sUpd; echo"warning: berat__".$rNospb['kgbjr']."totalberat:".$rTotal['total']."___persen:".$persen;exit();
					// #exit("error".$sNospb);
					// $owlPDO->exec($sUpd); 
					
					// $sUpdate="update ".$dbname.".kebun_spbht set posting='1',postingby='".$_SESSION['standard']['userid']."' where nospb='".$rNospb['nospb']."'";
					// $owlPDO->exec($sUpdate); 
				}
			}
		
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	break;

	case'PostingData2':
		try {
			$owlPDO->beginTransaction();
			$str = "select distinct tujuan, kodeorg, tanggal, substr(blok,1,6) as divisi  from ".$dbname.".kebun_spbdt a left join ".$dbname.".kebun_spbht b on a.nospb = b.nospb where a.nospb='".$noSpb."'";
			$res = fetchdata($str);
			foreach($res as $val){
				$intex=$val['tujuan'];
				$kodeorg=$val['kodeorg'];
				$tanggal=$val['tanggal'];
				$divisi=$val['divisi'];
			}
			
			if($intex!='3'){
				validasiInput($kodeorg,$divisi,'spbpost',$tanggal,$exit='0');
			}
			
			if($param['intex'] == '3'){
				$whrintex = "and millcode like 'EXT%'";
			}else{
				$whrintex = "and millcode = '".$param['kodeorg']."'";
			}
			$str = "select * from ".$dbname.".pabrik_timbangan where nospb='".$noSpb."' ".$whrintex."";
			$resn = fetchdata($str);
			if(count($resn)==0){
				throw new PDOException("Tiket timbang PKS tidak ada.");
			}
			if(count($resn)>1){
				throw new PDOException("Tiket timbang double.");
			}
			
			if($noTrans==''){
				foreach($resn as $rTimbngn){							
					$noTrans  =$rTimbngn['notransaksi'];
				}			
			}

			$strblk = "SELECT a.nospb,a.blok as indukblok FROM $dbname.kebun_spbdt a
			WHERE nospb='".$noSpb."'";
			$resblk = fetchdata($strblk);
			foreach ($resblk as $bar) {
				$getspbblk = $bar['kodeorg'];
			}
			// exit("Warning ". $strblk);
			

			$sNospb="select nospb,blok,kgbjr,brondolan,jjg,tanggalpanen from ".$dbname.".kebun_spbdt where nospb='".$noSpb."'";
			$res = fetchdata($sNospb);
			$jlhblok = count($res); 
			foreach($res as $rNospb){
				//berat dan total berat
				$sTotal="select sum(kgbjr) as total,sum(brondolan) as totalbrondolan,sum(jjg) as jjg from ".$dbname.".kebun_spbdt where nospb='".$rNospb['nospb']."'";
				$resx = fetchdata($sTotal);
				foreach($resx as $rTotal){
					$totKgkbn    =$rTotal['total'];
					$totBrondolan=$rTotal['totalbrondolan'];
					$totjjg      =$rTotal['jjg'];
				}	
				
				
				if(($totjjg>0 or $totBrondolan>0)){
					$colBs = array(
						'kgwb'			=> $param['kgbrutobesar'],
						'kgwbnetto'		=> $param['kgnettobesar'],
						'bjr'			=> $param['bjrbesar'],
						'totalkg'		=> $param['totalkgbesar'],
						'kgbjr'			=> $param['totkgbjrbesar']
					);

					$updQ = updateQuery($dbname,"kebun_spbdt",$colBs,"nospb='".$noSpb."' and blok='".$param['indukblok']."' and tanggalpanen='".$param['tglPanen']."'");
					// exit("Warning ".$updQ);
					$owlPDO->exec($updQ);
				}
			}

			$colHt = array(
				"posting" 	=> '1',
				"postingby"	=> $_SESSION['standard']['userid']
			);
			$updH = updateQuery($dbname,"kebun_spbht",$colHt,"nospb='".$noSpb."'");
			// exit("Warning: ".$updH);
			$owlPDO->exec($updH);
		
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	break;

	case'PostingDataSPB':
		try {
			$owlPDO->beginTransaction();
			$str = "select distinct tujuan, kodeorg, tanggal, substr(blok,1,6) as divisi  from ".$dbname.".kebun_spbdt a left join ".$dbname.".kebun_spbht b on a.nospb = b.nospb where a.nospb='".$noSpb."'";
			$res = fetchdata($str);
			foreach($res as $val){
				$intex=$val['tujuan'];
				$kodeorg=$val['kodeorg'];
				$tanggal=$val['tanggal'];
				$divisi=$val['divisi'];
			}
			
			if($intex!='3'){
				validasiInput($kodeorg,$divisi,'spbpost',$tanggal,$exit='0');
			}
			
			$str = "select * from ".$dbname.".pabrik_timbangan where nospb='".$noSpb."'";
			$resn = fetchdata($str);
			if(count($resn)==0){
				throw new PDOException("Tiket timbang PKS tidak ada.");
			}
			if(count($resn)>1){
				throw new PDOException("Tiket timbang double.");
			}
			
			if($noTrans==''){
				foreach($resn as $rTimbngn){							
					$noTrans  =$rTimbngn['notransaksi'];
				}			
			}
			
			// kalo plasma harus input nama petani
			$intiplasma='';
			$janjangpetani=0;
			$sTimbngn="select a.blok, b.intiplasma from ".$dbname.".kebun_spbdt a left join ".$dbname.".setup_blok b on a.blok = b.kodeorg where a.nospb='".$noSpb."' "; 
			$resn = fetchdata($sTimbngn);
			foreach($resn as $rTimbngn){
				if($rTimbngn['intiplasma']=='P')$intiplasma='P';							
			}
			$sTimbngn="select nospb, id_kavling, janjang from ".$dbname.".kebun_spbpetani where nospb='".$noSpb."' ";
			$resn = fetchdata($sTimbngn);
			foreach($resn as $rTimbngn){
				$janjangpetani+=$rTimbngn['janjang'];
			}
			if(($intiplasma=='P')and($janjangpetani==0)){
				throw new PDOException("Silakan input nama petani untuk blok Plasma.");
			}
			
			$no=0;
			$sNospb="select nospb,blok,kgbjr,brondolan,jjg,tanggalpanen from ".$dbname.".kebun_spbdt where nospb='".$noSpb."'";
			$res = fetchdata($sNospb);
			$jlhblok = count($res); 
			foreach($res as $rNospb){
				$no+=1;
				//berat dan total berat
				$sTotal="select sum(kgbjr) as total,sum(brondolan) as totalbrondolan,sum(jjg) as jjg from ".$dbname.".kebun_spbdt where nospb='".$rNospb['nospb']."'";
				$resx = fetchdata($sTotal);
				foreach($resx as $rTotal){
					$totKgkbn    =$rTotal['total'];
					$totBrondolan=$rTotal['totalbrondolan'];
					$totjjg      =$rTotal['jjg'];
				}	
				
				$sTimbngn="select beratbersih,brondolan,kgpotsortasi from ".$dbname.".pabrik_timbangan where notransaksi='".$noTrans."' and nospb='".$rNospb['nospb']."' ";
				$resn = fetchdata($sTimbngn);
				foreach($resn as $rTimbngn){							
					$x      =intval($rTimbngn['beratbersih']);
					$kgnetto=intval($rTimbngn['beratbersih'])-intval($rTimbngn['kgpotsortasi']);
				}
	  
				//berat bersih dari PKS sudah termasuk brondolan    
				$brondolanspb=$rNospb['brondolan'];
				$kgkbn       =$rNospb['kgbjr'];
				
				if(($totjjg>0 and $totBrondolan>0) or ($totjjg>0 and $totBrondolan==0)){
					$kgpksminbrd = $x - $totBrondolan;
					$kgprominbrd = $kgkbn/$totKgkbn*$kgpksminbrd;
					#$kgWb = $kgprominbrd + $brondolanspb;
					
					$kgpksminbrdnet = $kgnetto - $totBrondolan;
					$kgprominbrdnet = $kgkbn/$totKgkbn*$kgpksminbrdnet;
					#$kgWbNetto = $kgprominbrdnet + $brondolanspb;
					
					if($jlhblok>1 and $no!=$jlhblok){					
						$kgWb      = round($kgprominbrd + $brondolanspb,0);
						$ttlkgwbx +=$kgWb;
						
						$kgWbNetto = round($kgprominbrdnet + $brondolanspb,0);
						$ttlkgwbnx+=$kgWbNetto;
					}elseif($jlhblok>1 and $no==$jlhblok){
						$kgWb     = $x -$ttlkgwbx;
						$kgWbNetto= $kgnetto - $ttlkgwbnx;
					}else{
						$kgWb     = $kgprominbrd + $brondolanspb;
						$kgWbNetto= $kgprominbrdnet + $brondolanspb;
					}
					
				}else{
					$kgkbnbron=$kgkbn+$brondolanspb;
					$totKgkbnbron=$totKgkbn+$totBrondolan;
					#$kgWb=$kgkbnbron/$totKgkbnbron*$x;
					#$kgWbNetto=$kgkbnbron/$totKgkbnbron*$kgnetto;
					
					if($jlhblok>1 and $no!=$jlhblok){
						$kgWb      =round($kgkbnbron/$totKgkbnbron*$x,0);
						$ttlkgwbx +=$kgWb;
						$kgWbNetto =round($kgkbnbron/$totKgkbnbron*$kgnetto,0);
						$ttlkgwbnx+=$kgWbNetto;
					}elseif($jlhblok>1 and $no==$jlhblok){
						$kgWb     = $x -$ttlkgwbx;
						$kgWbNetto= $kgnetto - $ttlkgwbnx;
					}else{
						$kgWb     =$kgkbnbron/$totKgkbnbron*$x;
						$kgWbNetto=$kgkbnbron/$totKgkbnbron*$kgnetto;
					}
				}
				
				$totKg=$kgWb;
				
				if(($totjjg>0 or $totBrondolan>0)){
					$sUpd="update ".$dbname.".kebun_spbdt set kgwbnetto='".$kgWbNetto."',kgwb='".$kgWb."',totalkg='".$totKg."' where nospb='".$rNospb['nospb']."' and blok='".$rNospb['blok']."' and tanggalpanen='".$rNospb['tanggalpanen']."'"; //echo "warning:".$sUpd; echo"warning: berat__".$rNospb['kgbjr']."totalberat:".$rTotal['total']."___persen:".$persen;exit();
					#exit("error".$sNospb);
					$owlPDO->exec($sUpd); 
					
					$sUpdate="update ".$dbname.".kebun_spbht set posting='1',postingby='".$_SESSION['standard']['userid']."' where nospb='".$rNospb['nospb']."'";
					$owlPDO->exec($sUpdate); 
				}
			}
	
			// tambahan proporsi tbs petani
			$sTotal="select (sum(kgbjr)/sum(jjg)) as bjr from ".$dbname.".kebun_spbdt where nospb='".$rNospb['nospb']."'";
			$resx = fetchdata($sTotal);
			foreach($resx as $rTotal){
				$bjrkebunspb=$rTotal['bjr'];
			}	
	
			$sTotal="select id, nospb, janjang, brondolan from ".$dbname.".kebun_spbpetani where nospb='".$rNospb['nospb']."'";
			$resx = fetchdata($sTotal);
			foreach($resx as $rTotal){
				$listpetani[$rTotal['id']]=$rTotal['id'];
				$kgpetani[$rTotal['id']]=($rTotal['janjang']*$bjrkebunspb);
				$brdpetani[$rTotal['id']]=$rTotal['brondolan'];
				$jjgpetani[$rTotal['id']]=$rTotal['janjang'];
	
				$kgbjrpetani+=($rTotal['janjang']*$bjrkebunspb);
				$kgbrdpetani+=$rTotal['brondolan'];
			}	
			$sTimbngn="select beratbersih,brondolan,kgpotsortasi from ".$dbname.".pabrik_timbangan where notransaksi='".$noTrans."' and nospb='".$rNospb['nospb']."' ";
			$resn = fetchdata($sTimbngn);
			foreach($resn as $rTimbngn){
				$kgpabrik=intval($rTimbngn['beratbersih']);
				$kgsortasi=intval($rTimbngn['beratbersih'])-intval($rTimbngn['kgpotsortasi']);
			}
	
			$propjjg=($kgpabrik-$kgbrdpetani)/$kgbjrpetani;
			$propjjg_sort=($kgsortasi-$kgbrdpetani)/$kgbjrpetani;
	
			foreach($listpetani as $pet){
				$kgwbpetani[$pet]=($kgpetani[$pet]*$propjjg)+$brdpetani[$pet];
				$kgwbpetani_sort[$pet]=($kgpetani[$pet]*$propjjg_sort)+$brdpetani[$pet];
	
				// echo "\n".$pet." ".$kgwbpetani[$pet]." -> (".$kgpetani[$pet]." x ".$propjjg.") + ".$brdpetani[$pet];
				// echo "\n".$pet." ".$kgwbpetani_sort[$pet]." -> (".$kgpetani[$pet]." x ".$propjjg_sort.") + ".$brdpetani[$pet];
	
				$sUpd="update ".$dbname.".kebun_spbpetani set kgwb ='".$kgwbpetani[$pet]."',kgwbnetto='".$kgwbpetani_sort[$pet]."' where nospb='".$rNospb['nospb']."' and id='".$pet."'";
				// echo "\n".$sUpd;
				$owlPDO->exec($sUpd); 			
			}
		
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	break;
	
	default:
	break;
}
?>