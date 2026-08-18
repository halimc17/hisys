<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

if(count($_POST)>0){	
	$param = $_POST;
}else{
	$param = $_GET;
}
$proses=checkPostGet('proses','');
$kdOrg=checkPostGet('idKbn','');
$tgl=tanggalsystemn(checkPostGet('tglData',''));
$tgl2    =tanggalsystemn(checkPostGet('tglData2',''));
$noSpb=checkPostGet('noSpb','');
$noTrans=checkPostGet('noTrans','');
$intex=checkPostGet('intex','');

switch($proses){
	
	case'excel':
		$where1 = "";
		$where2 = "";
		$tahun = "";
		if ($tgl != "" && $tgl2 != "") {
			$where1 .= " and tanggal between '".$tgl."' and '".$tgl2."'";
			//$where2 .= " and nospb like '%".substr($tgl,4,2)."%' ";
			//$where2 .= " and nospb like '%".substr($tgl2,4,2)."%' ";
			$tahun=substr($tgl, 0,4);

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

		$str = "SELECT a.nospb, substr(a.blok,1,6) as divisi, a.blok as indukblok, a.tph as notph, a.sesi as sesi, a.pemanen as pemanen,
				b.kodeorg as blok, b.tahuntanam, b.luasareaproduktif, a.tanggalpanen,
				a.jjg, a.kgwb, a.kgwbnetto, a.bjr, a.brondolan, a.totalkg, a.kgbjr, c.kodeorg, c.tanggal as tglspb, c.posting
				FROM $dbname.kebun_spbdt a JOIN $dbname.setup_blok b on a.blok = b.indukblok and b.tahuntanam <= (".(intval(date('Y'))-3).") and b.tahuntanam!='0'
				JOIN $dbname.kebun_spbht c on a.nospb = c.nospb
				WHERE 1=1 and ($tahun-b.tahuntanam)>=3 ".$where1." ".$whereKodeOrg."";
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
		}

		// echo "<pre>";
		// print_r($jjgindkx);
		// echo "</pre>";

		$strbjr = "SELECT a.kodeorg, a.bjr FROM kebun_5bjr a JOIN setup_blok b
		on a.kodeorg = b.kodeorg where a.periode='".substr($tgl,0,7)."' and b.indukblok in
		(
		SELECT c.blok FROM kebun_spbdt c 
		JOIN kebun_spbht d on c.nospb = d.nospb
		where d.tanggal between '".$tgl."' and '".$tgl2."' ".$whereKodeOrgSPB."
		and d.tujuan='".$intex."'
		)";
		$resbjr = fetchdata($strbjr);
		foreach ($resbjr as $val) {
			$bjrblk[$val['kodeorg']] = $val['bjr'];
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
				$kgbruto[$dtp['nospb']]		= ($dtp['beratmasuk']-$dtp['beratkeluar']);
				// Perhitungan mendapatkan Kg Netto
				$kgnetto[$dtp['nospb']]		= ($dtp['beratbersih']);
			}
			foreach ($key1 as $div => $key2) {
				foreach ($key2 as $tglPanen => $key3) {
					foreach ($key3 as $indk => $key4) {
						$nob++;
						foreach ($key4 as $blk => $val) {
									$strkls = "SELECT b.kodeorg,a.kodelahan,a.tahuntanam,a.nilai FROM $dbname.kebun_5tandanhathn a JOIN $dbname.setup_blok b
												ON a.kodelahan = b.klasifikasitanah and a.tahuntanam = b.tahuntanam
												WHERE indukblok='".$indk."'
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
									$kgbrutobrd[$spb] = ($kgbruto[$spb] - $brdindkx[$spb][$div][$tglPanen][$indk]);
									// Perhitungan Total (Kg Netto - Brondolan)
									$kgnettobrd[$spb] = ($kgnetto[$spb] - $brdindkx[$spb][$div][$tglPanen][$indk]);
			
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
								$klsbjrkg[$spb][$div][$tglPanen][$indk][$blk]=($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk]*$bjrblk[$blk][substr($tglPanen,0,7)])+$brdblk[$spb][$div][$tglPanen][$indk][$blk];
								// Hasil Brondolan Per Blok Kecil, TPH, Sesi, Nik
							} else {
								// Hasil Panen (Tdn/Blok) / JJG
								$hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] = round(fixnan($tdnblok[$spb][$div][$tglPanen][$indk][$blk] / $totaltdnblok[$spb][$div][$tglPanen][$indk]) * $jjgindkx[$spb][$div][$tglPanen][$indk],2);
								$brdblk[$spb][$div][$tglPanen][$indk][$blk] = round(fixnan($tdnblok[$spb][$div][$tglPanen][$indk][$blk] / $totaltdnblok[$spb][$div][$tglPanen][$indk]) * $brdindkx[$spb][$div][$tglPanen][$indk],2);
								$klsbjrkg[$spb][$div][$tglPanen][$indk][$blk]=($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk]*$bjrblk[$blk][substr($tglPanen,0,7)])+$brdblk[$spb][$div][$tglPanen][$indk][$blk];
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
		
		$stream = "";
		$border = "border=1";
		if ($nob > 0) {
			$datasalah=0;
			foreach ($nospbx as $spb) {
				if(!isset($hsltimbang[$spb])){
					$datasalah=1;
				}
			}
			foreach ($blokx as $spb => $key1) {
				$kgbrutospb=$kgbruto[$spb];
				$kgnettospb=$kgnetto[$spb];
				$kgbrutobrdspb=$kgbrutobrd[$spb];
				$kgnettobrdspb=$kgnettobrd[$spb];
				$noblokx=0;
				$no++;
					$stream.="<table cellspacing=1 ".$border." cellpadding=2>";
						$stream.="<thead>";
						$stream.="<tr>
							<th align=center>".$_SESSION['lang']['kebun']."</th>
							<th align=center>".$_SESSION['lang']['pabrik']."</th>
						</tr>";
						$stream.="</thead>";
						$stream.="<tbody>";
							$stream.="<tr class=rowcontent>";
								$stream.="<td>";
									$stream.="
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
									$stream.="<tr class=rowcontent>
									<td align=center>".$no."</td>
									<td>".$kdorgb[$spb]."</td>
									<td>".$nospbx[$spb]."</td>
									<td>".$tanggalspb[$spb]."</td>
									<td align=right>".$totjjgspb[$spb]."</td>
									<td align=right>".$totbrdspb[$spb]."</td>
									<td>".$arrstatus[$statusposting[$spb]]."</td>";
									$stream.="</tr>";
									$stream.="</tbody>
									</table>";
								$stream.="</td>";
	
								$stream.="<td>";
									$stream.="
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
									$stream.="<tr class=rowcontent>
										<td align=center>".$no."</td>
										<td>".$tgltimbang[$spb]."</td>
										<td>".$notranswb[$spb]."</td>
										<td>".$nospbwb[$spb]."</td>
										<td align=right>".$kgnetto[$spb]."</td>";
									$stream.="</tr>";
									$stream.="</tbody></table>";
								$stream.="</td>";
								
							$stream.="</tr>";
	
							$stream.="<tr class=rowcontent>";
								$stream.="<table cellspacing=1 ".$border." cellpadding=5 class='sortable'>";
								$stream.="<thead>";
								$stream.="<tr class=rowheader>";
								$stream.="<th rowspan=2>".$_SESSION['lang']['divisi']."</th>";
								$stream.="<th rowspan=2>".$_SESSION['lang']['tahuntanam']."</th>";
								$stream.="<th rowspan=2>".$_SESSION['lang']['blok']."</th>";
								$stream.="<th>".$_SESSION['lang']['luas']."</th>";
								$stream.="<th colspan=2>".$_SESSION['lang']['tanggal']."</th>";
								$stream.="<th colspan=3>Tandan</th>";
								$stream.="<th rowspan=2>".$_SESSION['lang']['bjr']."</th>";
								$stream.="<th rowspan=2>Brondolan</th>";
								$stream.="<th colspan=5>".$_SESSION['lang']['kg']."</th>";
								$stream.="</tr>";
								
								$stream.="<tr class=rowheader>";
								$stream.="<th>(HA)</th>";
								$stream.="<th>".$_SESSION['lang']['panen']."</th>";
								$stream.="<th>".$_SESSION['lang']['timbangan']."</th>";
								$stream.="<th>Tdn/Pokok/Thn</th>";
								$stream.="<th>Tdn/Pokok/Blok</th>";
								$stream.="<th>Hasil Panen<br>(Tdn/Blok)</th>";
	
								$stream.="<th>Kelas<br>Lahan</th>";
								$stream.="<th>Hasil Timbang<br>(Bruto)</th>";
								$stream.="<th>Hasil Timbang<br>(Bruto - Brondolan)</th>";
								$stream.="<th>Hasil Timbang<br>(Netto)</th>";
								$stream.="<th>Hasil Timbang<br>(Netto - Brondolan)</th>";
								$stream.="</tr>";
								$stream.="</thead>";
								$stream.="<tbody>";
				foreach ($key1 as $div => $key2) {
					foreach ($key2 as $tglPanen => $key3) {
						foreach ($key3 as $indk => $key4) {
							$nod++;
							$stream.="<tr bgcolor=#DAF7A6>";
							$stream.="<td align=left colspan=8 style='font-weight:bold;'>".$indk."</td>";
							$stream.="<td>".round($jjgindkx[$spb][$div][$tglPanen][$indk],2)."</td>";
							$stream.="<td></td>";
							$stream.="<td>".round($brdindkx[$spb][$div][$tglPanen][$indk],2)."</td>";
							$stream.="<td></td>";
							$stream.="<td></td>";
							$stream.="<td></td>";
							$stream.="<td></td>";
							$stream.="<td></td>";
							$stream.="</tr>";
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
								$kgbrutoproporsi[$spb][$div][$tglPanen][$indk][$blk] = (fixnan($klsbjrkg[$spb][$div][$tglPanen][$indk][$blk] / $totklsbjrkg[$spb]) * $kgbruto[$spb]);
								// Perhitungan Kg Netto Diproporsi Per TPH, Sesi, Nik
								$kgnettoproporsi[$spb][$div][$tglPanen][$indk][$blk] = (fixnan($klsbjrkg[$spb][$div][$tglPanen][$indk][$blk] / $totklsbjrkg[$spb]) * $kgnetto[$spb]);
								// Perhitungan Kg (Bruto - Brondolan) Diproporsi Per TPH, Sesi, Nik
								$kgbrutobrdproporsi[$spb][$div][$tglPanen][$indk][$blk] = round(fixnan( $klsbjrkg[$spb][$div][$tglPanen][$indk][$blk] / $totklsbjrkg[$spb]) * $kgbrutobrd[$spb],2);
								// Perhitungan Kg (Netto - Brondolan) Diproporsi Per TPH, Sesi, Nik
								$kgnettobrdproporsi[$spb][$div][$tglPanen][$indk][$blk] = round(fixnan( $klsbjrkg[$spb][$div][$tglPanen][$indk][$blk] / $totklsbjrkg[$spb]) * $kgnettobrd[$spb],2);
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
								$kgbrutobrdspb=round($kgbrutobrdspb-round($kgbrutobrdproporsi[$spb][$div][$tglPanen][$indk][$blk],2),2);
								$kgnettobrdspb=round($kgnettobrdspb-round($kgnettobrdproporsi[$spb][$div][$tglPanen][$indk][$blk],2),2);
								
								if($jlhblokxx[$spb]==$noblokx){
									// if($spb=='0002/PPPE/08/2024'){
									// 	echo $kgnettospb.'='.$kgnettoproporsi[$spb][$div][$tglPanen][$indk][$blk].'<br>';
									// }
									$kgbrutoproporsi[$spb][$div][$tglPanen][$indk][$blk]+=$kgbrutospb;
									$kgnettoproporsi[$spb][$div][$tglPanen][$indk][$blk]+=$kgnettospb;
									$kgbrutobrdproporsi[$spb][$div][$tglPanen][$indk][$blk]+=$kgbrutobrdspb;
									$kgnettobrdproporsi[$spb][$div][$tglPanen][$indk][$blk]+=$kgnettobrdspb;
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
								$stream.="<tr id=row".$nok." class=rowcontent>";
									$stream.="<td>".getNamaOrg($div)."</td>";
									$stream.="<td>".$thntnmx[$blk]."</td>";
									$stream.="<td id=blok_".$nok.">".$blk."</td>";
									$stream.="<td>".$luasblokx[$blk]."</td>";
									$stream.="<td id=tglpanen_".$nok.">".$tglPanen."</td>";
									$stream.="<td>". $tgltimbang[$spb] ."</td>";
									$stream.="<td>". $tdnblk[$blk][$thntnmx[$blk]] ."</td>";
									$stream.="<td>". round($tdnblok[$spb][$div][$tglPanen][$indk][$blk],2) ."</td>";
									$stream.="<td id=jjgblkx_".$nok.">". round($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk],2) ."</td>";
									$stream.="<td id=bjrblk_".$nok.">". round($bjrblk[$blk][substr($tglPanen,0,7)],2) ."</td>";
									$stream.="<td id=brdblk_".$nok.">". round($brdblk[$spb][$div][$tglPanen][$indk][$blk],2) ."</td>";
									$stream.="<td>".round($klsbjrkg[$spb][$div][$tglPanen][$indk][$blk],2)."</td>";
									$stream.="<td id=kgwbbrutobrd_".$nok.">".round($kgbrutoproporsi[$spb][$div][$tglPanen][$indk][$blk],2)."</td>";
									$stream.="<td>".round($kgbrutobrdproporsi[$spb][$div][$tglPanen][$indk][$blk],2)."</td>";
									$stream.="<td id=kgwbnettobrd_".$nok.">".round($kgnettoproporsi[$spb][$div][$tglPanen][$indk][$blk],2)."</td>";
									$stream.="<td>".round($kgnettobrdproporsi[$spb][$div][$tglPanen][$indk][$blk],2)."</td>";
								$stream.="</tr>";
							}
							$stream.="<tr id=row_".$nod." class=rowheader bgcolor=#8AD5DE>";
								$stream.="<td colspan=2>TOTAL ".tanggalnormal($tglPanen)." ".getNamaOrg($div)."</td>";
								$stream.="<td>".substr($blk,0,9)."</td>";
								$stream.="<td>". $totallusbloktanggal[$spb][$tglPanen][$div][$indk] ."</td>";
								$stream.="<td></td>";
								$stream.="<td></td>";
								$stream.="<td>".round($tottdnblkthn[$spb][$tglPanen][$div][$indk],2)."</td>";
								$stream.="<td>".round($totaltdnblok[$spb][$div][$tglPanen][$indk],2)."</td>";
								$stream.="<td>".round($tothslpnn[$spb][$tglPanen][$div][$indk],2)."</td>";
								$stream.="<td>".round($totbjrblk[$spb][$tglPanen][$div][$indk],2)."</td>";
								$stream.="<td>".round($totbrdindk[$spb][$tglPanen][$div][$indk],2)."</td>";
								$stream.="<td>".round($totklsbjrkgx[$spb][$div][$tglPanen][$indk],2)."</td>";
								// $stream.="<td>". round($kgbruto[$spb],2) ." xxx</td>";
								$stream.="<td>". round($totalkgbrutoproporsi[$spb][$tglPanen][$div][$indk],2) ."</td>";
								$stream.="<td id=kgwbindk_".$nod.">". round($kgbrutobrdindk[$spb][$tglPanen][$div][$indk],2) ."</td>";
								// $stream.="<td>" . round($kgnetto[$spb],2) . " yyy</td>";
								$stream.="<td>" . round($totalkgnettoroporsi[$spb][$tglPanen][$div][$indk],2) . "</td>";
								$stream.="<td id=kgwbnettoindk_".$nod.">" . round($kgnettobrdindk[$spb][$tglPanen][$div][$indk],2) . "</td>";
							$stream.="</tr>";
						}
					}
				}
					$stream.="</tbody>";
								$stream.="</table>";
							$stream.="</tr>";
						$stream.="</tbody>";
					$stream.="</table>";
				$stream.="<br><br>";
			}
			
		
			
			
		} else {
			$stream.="<table cellspacing=1 ".$border." cellpadding=2>";
				$stream.="<thead>";
				$stream.="<tr>
					<th align=center>".$_SESSION['lang']['kebun']."</th>
					<th align=center>".$_SESSION['lang']['pabrik']."</th>
				</tr>";
				$stream.="</thead>";
				$stream.="<tbody>";
					$stream.="<tr class=rowcontent>";
						$stream.="<td>";
							$stream.="
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
							$stream.="<tr class=rowcontent>
							<td align=center colspan=7>".$_SESSION['lang']['errdatanotexist']."</td>";
							$stream.="</tr>";
							$stream.="</tbody>
							</table>";
						$stream.="</td>";

						$stream.="<td>";
							$stream.="
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
							$stream.="<tr class=rowcontent>
								<td align=center colspan=5>".$_SESSION['lang']['errdatanotexist']."</td>";
							$stream.="</tr>";
							$stream.="</tbody></table>";
						$stream.="</td>";
						
					$stream.="</tr>";
				$stream.="</tbody>";
			$stream.="</table>";
		}

		$nop_ = "laporan_ambil_kg_timbangan";
		if (strlen($stream) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $stream)) {
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
	break;

}