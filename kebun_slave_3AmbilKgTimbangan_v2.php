<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

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
		$bulan = "";

		$whereBaru = "";
		if ($tgl != "" && $tgl2 != "") {
			$where1 .= " and tanggal between '".$tgl."' and '".$tgl2."'";
			$whereBaru .= " and c.tanggal between '".$tgl."' and '".$tgl2."'";


			$tahun=substr($tgl, 0,4);
			$bulan=substr($tgl, 4,2);
		}


		if ($intex != "") {
			$where1 .= " and tujuan='".$intex."'";
			$whereBaru .= " and tujuan='".$intex."'";
		}

		$whereKodeOrg = " and c.kodeorg='".$kdOrg."' ";
		$where2 = " and kodeorg='".$kdOrg."' ";
		$whereKodeOrgSPB = " and d.nospb like '%".$kdOrg."%' ";
		$whereKodeOrgTimbangan = " and kodeorg = '".$kdOrg."' ";

		$whereKodeOrgPANEN = "and unit = '".$kdOrg."'";
		
		if(strlen($kdOrg)>4){
			$whereKodeOrg = " and a.kodeorg like '".$kdOrg."%' ";
			$whereKodeOrgTimbangan = " and divcode = '".$kdOrg."' ";
			$whereKodeOrgSPB = " and c.blok like '%".$kdOrg."%' ";

			$whereKodeOrgPANEN= " and c.kodeorg like '%".$kdOrg."%' ";
			$where2 = " and divcode='".$kdOrg."' ";

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

		$str = "SELECT a.notransaksi as nospb,c.noreferensi as noreferensi, substr(a.kodeorg,1,6) as divisi, a.kodeorg as indukblok,
				a.blokkecil as blok, a.tahuntanam, a.tanggal as tanggalpanen,
				a.jjg, a.brondolan, c.kodeorg, c.tanggal as tglspb, c.posting
				FROM $dbname.kebun_proporsitahuntanam_spb a 
				JOIN $dbname.kebun_spbht c on a.notransaksi = c.nospb
				WHERE 1=1 ".$whereBaru." ".$whereKodeOrg."";

		$res = fetchdata($str);
		$jumlahData = count($res); 

		$nospbx = $divisix = $blokx = $indukblokx = $thntnmx = $luasblokx = $klaslahanx = $tglpanenx = $tglpanenindk = array();
		$sesix = $notphx = $pemanenx = array();
		$tgltimbang = $tdnblk = $kgbruto = $kgnetto = $bjrblk = $tdnblok = $klsbjrkg = $totjjgspb = $totbrdspb = $hslpnnblok = $totbrdindk = array();
		$totkgbruto = $tottdnblkthn = $totbjrblk = $totaltdnblok = $totklsbjrkg = $totluasblk = $tothslpnn = $brdblk = array();
		$kgbrutobrd = $kgnettobrd = $kgbrutoproporsi = $kgnettoproporsi = $kgbrutobrdproporsi = $kgnettobrdproporsi = array();

		if($jumlahData == 0){
			exit("Warning : Data tidak ada, pastikan SPB sudah di proposi tahun tanam ");
		}

		foreach ($res as $val) {
			$nospbx[$val['nospb']] = $val['nospb'];
			
			if(!isset($jlhblokxx[$val['nospb']])){
				$jlhblokxx[$val['nospb']]=0;
			}
			
			if(!isset($blokx[$val['nospb']][$val['divisi']][$val['tanggalpanen']][$val['indukblok']][$val['blok']])){
				$jlhblokxx[$val['nospb']]+=1;
			}
			
			$blokx[$val['nospb']][$val['divisi']][$val['tanggalpanen']][$val['indukblok']][$val['blok']] = $val['blok'];

			if(getNamaOrg($kdOrg,'induk')=='MHA' or getNamaOrg($kdOrg,'induk')=='DMA'){
				$noref[$val['nospb']] = $val['nospb'];
				$ambilNoreferensi[$val['nospb']][$val['divisi']][$val['tanggalpanen']][$val['indukblok']][$val['blok']] = $val['noreferensi'];
				$brdindkx2[$val['nospb']] 	+= $val['brondolan'];
				
			}else{
				$noref[$val['nospb']] = $val['noreferensi'];
				$ambilNoreferensi[$val['noreferensi']][$val['divisi']][$val['tanggalpanen']][$val['indukblok']][$val['blok']] = $val['noreferensi'];
				$brdindkx2[$val['noreferensi']] 	+= $val['brondolan'];

			}
			

			$thntnmx[$val['blok']] = $val['tahuntanam'];

			$kdorgb[$val['nospb']] 							= $val['kodeorg'];
			$tanggalspb[$val['nospb']] 						= $val['tglspb'];
			$statusposting[$val['nospb']]					= $val['posting'];

			$hslpnnblok[$val['nospb']][$val['divisi']][$val['tanggalpanen']][$val['indukblok']][$val['blok']] +=$val['jjg'];
			$brdblk[$val['nospb']][$val['divisi']][$val['tanggalpanen']][$val['indukblok']][$val['blok']] +=$val['brondolan'];

			$totjjgspb[$val['nospb']] += $val['jjg'];
			$totbrdspb[$val['nospb']] += $val['brondolan'];

			$jjgindkx[$val['nospb']][$val['divisi']][$val['tanggalpanen']][$val['indukblok']] 	+= $val['jjg'];
			$brdindkx[$val['nospb']][$val['divisi']][$val['tanggalpanen']][$val['indukblok']] 	+= $val['brondolan'];

		}

		$strcek = "SELECT * FROM $dbname.pabrik_timbangan WHERE 1=1 ".$where2."";
		$rescek = fetchdata($strcek);
		$hsltimbang=array();
		foreach ($rescek as $val) {
			if(getNamaOrg($kdOrg,'induk')=='MHA' or getNamaOrg($kdOrg,'induk')=='DMA'){
				$hsltimbang[$val['nospb']]=$val['nospb'];
			}else{
				$hsltimbang[$val['nospbmobile']]=$val['nospbmobile'];
			}
		}

		$strbjr = "SELECT * FROM kebun_5bjr where 1=1 and (periode='".substr($pertgl,0,7)."' or periode='".periodelalu(substr($pertgl,0,7))."')";
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

		if(getNamaOrg($kdOrg,'induk')=='MHA' or getNamaOrg($kdOrg,'induk')=='DMA'){
			foreach ($ambilNoreferensi as $spb => $key1) {
				$strPabrik = "SELECT * FROM $dbname.pabrik_timbangan where nospb ='".$spb."' ".$whereKodeOrgTimbangan."";
				$resPabrik = fetchData($strPabrik);
				foreach ($resPabrik as $dtp) {
					
					$dtp['nospb'] = $dtp['nospb'];
	
					$tgltimbang[$dtp['nospb']]	= substr($dtp['tanggal'],0,10);
					$nospbwb[$dtp['nospb']]		= $dtp['nospb'];
					$notranswb[$dtp['nospb']]	= $dtp['notransaksi'];
	
					// Perhitungan mendapatkan Kg Bruto
					$kgbruto[$dtp['nospb']]		   = ($dtp['beratmasuk']-$dtp['beratkeluar']);
	
					// Perhitungan mendapatkan Kg Bruto - Brondolan
					$kgbruto_minbrd[$dtp['nospb']] = ($dtp['beratmasuk']-$dtp['beratkeluar']) - $brdindkx2[$dtp['nospb']];
	
					// Perhitungan mendapatkan Kg Netto
					$kgnetto[$dtp['nospb']]		   = ($dtp['beratbersih']);
	
					// Perhitungan mendapatak Kg Netto - Brondolan
					$kgnetto_minbrd[$dtp['nospb']] = ($dtp['beratbersih'] - $brdindkx2[$dtp['nospb']] );
					
				}
			}
		}else{
			foreach ($ambilNoreferensi as $spb => $key1) {
				$strPabrik = "SELECT * FROM $dbname.pabrik_timbangan where nospbmobile ='".$spb."' ".$whereKodeOrgTimbangan."";
				$resPabrik = fetchData($strPabrik);
				foreach ($resPabrik as $dtp) {
					
					$dtp['nospb'] = $dtp['nospbmobile'];
	
					$tgltimbang[$dtp['nospb']]	= substr($dtp['tanggal'],0,10);
					$nospbwb[$dtp['nospb']]		= $dtp['nospb'];
					$notranswb[$dtp['nospb']]	= $dtp['notransaksi'];
	
					// Perhitungan mendapatkan Kg Bruto
					$kgbruto[$dtp['nospb']]		   = ($dtp['beratmasuk']-$dtp['beratkeluar']);
	
					// Perhitungan mendapatkan Kg Bruto - Brondolan
					$kgbruto_minbrd[$dtp['nospb']] = ($dtp['beratmasuk']-$dtp['beratkeluar']) - $brdindkx2[$dtp['nospb']];
	
					// Perhitungan mendapatkan Kg Netto
					$kgnetto[$dtp['nospb']]		   = ($dtp['beratbersih']);
	
					// Perhitungan mendapatak Kg Netto - Brondolan
					$kgnetto_minbrd[$dtp['nospb']] = ($dtp['beratbersih'] - $brdindkx2[$dtp['nospb']] );
					
				}
			}

		}
						
		foreach ($blokx as $spb => $key1) {
			foreach ($key1 as $div => $key2) {
				foreach ($key2 as $tglPanen => $key3) {
					foreach ($key3 as $indk => $key4) {
						$nob++;
						foreach ($key4 as $blk => $val) {

							## Jika Cuman Brondol doang
							if($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] == 0){
								## Kg Kebun
								$KgKebunx[$spb][$div][$tglPanen][$indk][$blk] = round(($brdblk[$spb][$div][$tglPanen][$indk][$blk]),2);
	
								## Total KG KEBUN
								$TTkgkebunspb[$spb] += $KgKebunx[$spb][$div][$tglPanen][$indk][$blk];
							}else{
								## Kg Kebun
								$KgKebunx[$spb][$div][$tglPanen][$indk][$blk] = round(($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] * $bjrblk[$blk][substr($tglPanen,0,7)]),2);
	
								## Total KG KEBUN
								$TTkgkebunspb[$spb] += $KgKebunx[$spb][$div][$tglPanen][$indk][$blk];
							}
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
			foreach ($noref as $spb) {
				if(!isset($hsltimbang[$spb])){
					$datasalah=1;
				}
			}

			if($datasalah==1){
				echo "<span style='color:red'>MASIH ADA DATA SPB DI TANGGAL INI YANG BELUM MEMILIKI HASIL TIMBANG</span><br>
						<table cellspacing=1 ".$border." cellpadding=2>
							<thead>
								<tr class=rowheader>
									<th>No</th>
									<th>No SPB</th>
									<th>No Referensi</th>
								</tr>
							</thead>
							<tbody>
						";

						$nx = 0;
						foreach ($noref as $spb => $valuexx) {
							if(!isset($hsltimbang[$valuexx])){
								$nx++;
								echo "
								<tr class=rowcontent>
									<td>".$nx."</td>
									<td>".$spb."</td>
									<td>".$valuexx."</td>
								</tr>
								";
							}
						}

						echo "
							</tbody>
						</table>
						";

			}else{
				if($param['tipe']!='excel'){
					$tab.="<a class=mybutton onclick=\"postingbro()\">".$_SESSION['lang']['posting']." All</a>";
				}
			}


			$tab.="<br><br>";
			foreach ($blokx as $spb => $key1) {
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
										<th>".$_SESSION['lang']['berat']." Bruto</th>
										<th>".$_SESSION['lang']['berat']." Bersih</th>
									</tr>
									</thead>
									<tbody>";
									$tab.="<tr class=rowcontent>
										<td align=center>".$no."</td>
										<td>".$tgltimbang[$noref[$spb]]."</td>
										<td>".$notranswb[$noref[$spb]]."</td>
										<td>".$nospbwb[$noref[$spb]]."</td>
										<td align=right>".number_format($kgbruto[$noref[$spb]],2)."</td>
										<td align=right>".number_format($kgnetto[$noref[$spb]],2)."</td>";
									$tab.="</tr>";
									$tab.="</tbody></table>";
								$tab.="</td>";
								
							$tab.="</tr>";
	
							$tab.="<tr class=rowcontent>";
								$tab.="<table cellspacing=1 ".$border." cellpadding=5 class='sortable' width = 100%>";
								$tab.="<thead>";
								$tab.="<tr class=rowheader>";
								$tab.="<th rowspan=2>".$_SESSION['lang']['divisi']."</th>";
								$tab.="<th rowspan=2>".$_SESSION['lang']['tahuntanam']."</th>";
								$tab.="<th rowspan=2>".$_SESSION['lang']['blok']."</th>";
								$tab.="<th colspan=2>".$_SESSION['lang']['tanggal']."</th>";
								$tab.="<th rowspan=2>JJG</th>";
								$tab.="<th rowspan=2>".$_SESSION['lang']['bjr']."</th>";
								$tab.="<th rowspan=2>Brondolan</th>";
								$tab.="<th colspan=5>".$_SESSION['lang']['kg']."</th>";
								$tab.="</tr>";
								
								$tab.="<tr class=rowheader>";
								$tab.="<th>".$_SESSION['lang']['panen']."</th>";
								$tab.="<th>".$_SESSION['lang']['timbangan']."</th>";
	
								$tab.="<th>Kg <br> Kebun </th>";
								$tab.="<th>Hasil Timbang <br>(Bruto)</th>";
								$tab.="<th>Hasil Timbang <br>(Bruto - Brondolan)</th>";
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
							$tab.="<td align=left colspan=5 style='font-weight:bold;'>".$indk."</td>";
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
								$tab.="<td align=center id=tglpanen_".$nok.">".$tglPanen."</td>";
								$tab.="<td align=center>". $tgltimbang[$noref[$spb]] ."</td>";
								$tab.="<td id=jjgblkx_".$nok.">". round($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk],2) ."</td>";
								$tab.="<td id=bjrblk_".$nok.">". round($bjrblk[$blk][substr($tglPanen,0,7)],2) ."</td>";
								$tab.="<td id=brdblk_".$nok.">". round($brdblk[$spb][$div][$tglPanen][$indk][$blk],2) ."</td>";

								## KG KEBUN
								if($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] == 0){
									$KgKebun[$spb][$div][$tglPanen][$indk][$blk] = round($brdblk[$spb][$div][$tglPanen][$indk][$blk],2);
									$persentaseBlok[$spb][$div][$tglPanen][$indk][$blk] = round($brdblk[$spb][$div][$tglPanen][$indk][$blk]/$TTkgkebunspb[$spb],2);
								}else{
									$KgKebun[$spb][$div][$tglPanen][$indk][$blk] = round(($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] * $bjrblk[$blk][substr($tglPanen,0,7)]),2);
								}
								
								$tab.="<td align=right id=kgbjr_".$nok.">".$KgKebun[$spb][$div][$tglPanen][$indk][$blk]."</td>";
								
								## Hasil Timbang Bruto
								if($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] == 0){
									$kgbrutoHasiltimbang[$spb][$div][$tglPanen][$indk][$blk] = round((($KgKebun[$spb][$div][$tglPanen][$indk][$blk] * $kgbruto[$noref[$spb]])/ $TTkgkebunspb[$spb]),2)	;
								}else{
									$kgbrutoHasiltimbang[$spb][$div][$tglPanen][$indk][$blk] = round((($KgKebun[$spb][$div][$tglPanen][$indk][$blk] * $kgbruto[$noref[$spb]]) / $TTkgkebunspb[$spb]),2)	;
								}

								$tab.="<td align=right id=kgwbbrutobrd_".$nok.">".$kgbrutoHasiltimbang[$spb][$div][$tglPanen][$indk][$blk]."</td>";

								## Hasil Timbang Bruto - Brondolan
								if($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] == 0){
									$kgbrutoHasiltimbang_minbrd[$spb][$div][$tglPanen][$indk][$blk] = round((($KgKebun[$spb][$div][$tglPanen][$indk][$blk] * $kgbruto[$noref[$spb]]) / $TTkgkebunspb[$spb]),2)	;
									
								}else{
									$kgbrutoHasiltimbang_minbrd[$spb][$div][$tglPanen][$indk][$blk] = round((($KgKebun[$spb][$div][$tglPanen][$indk][$blk] * $kgbruto_minbrd[$noref[$spb]]) / $TTkgkebunspb[$spb]),2)	;
								}
								
								$tab.="<td align=right>".$kgbrutoHasiltimbang_minbrd[$spb][$div][$tglPanen][$indk][$blk]."</td>";
								
								## Hasil Timbang Netto
								if($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] == 0){
									$kgnettoHasiltimbang[$spb][$div][$tglPanen][$indk][$blk] = round((($KgKebun[$spb][$div][$tglPanen][$indk][$blk] * $kgnetto[$noref[$spb]])/ $TTkgkebunspb[$spb] ),2)	;
								}else{
									$kgnettoHasiltimbang[$spb][$div][$tglPanen][$indk][$blk] = round((($KgKebun[$spb][$div][$tglPanen][$indk][$blk] * $kgnetto[$noref[$spb]]) / $TTkgkebunspb[$spb]),2)	;
								}

								$tab.="<td align=right id=kgwbnettobrd_".$nok.">".$kgnettoHasiltimbang[$spb][$div][$tglPanen][$indk][$blk]."</td>";

								## Hasil TImbang Netto - Brondolan
								if($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] == 0){
									$kgnettoHasiltimbang_minbrd[$spb][$div][$tglPanen][$indk][$blk] = round((($KgKebun[$spb][$div][$tglPanen][$indk][$blk] * $kgnetto[$noref[$spb]]) / $TTkgkebunspb[$spb]),2)	;
								}else{
									$kgnettoHasiltimbang_minbrd[$spb][$div][$tglPanen][$indk][$blk] = round((($KgKebun[$spb][$div][$tglPanen][$indk][$blk] * $kgnetto_minbrd[$noref[$spb]]) / $TTkgkebunspb[$spb]),2)	;
								}

								$tab.="<td align=right>".$kgnettoHasiltimbang_minbrd[$spb][$div][$tglPanen][$indk][$blk]."</td>";
								
								if($param['tipe']!='excel'){
									$tab.="<td hidden id=totalkg_".$nok.">".round(($kgbrutoHasiltimbang[$spb][$div][$tglPanen][$indk][$blk][$spb][$div][$tglPanen][$indk][$blk]),2)."</td>";
								}

								$tab.="</tr>";

								$jjgindkx[$spb][$tglPanen][$div][$indk]  					+= $hslpnnblok[$spb][$div][$tglPanen][$indk][$blk];
								$totbrdindk[$spb][$tglPanen][$div][$indk] 					+= $brdblk[$spb][$div][$tglPanen][$indk][$blk];
								$totalKgkebun[$spb][$tglPanen][$div][$indk] 				+= $KgKebun[$spb][$div][$tglPanen][$indk][$blk];
								$hasilTimbang_Bruto[$spb][$tglPanen][$div][$indk] 			+= $kgbrutoHasiltimbang[$spb][$div][$tglPanen][$indk][$blk];
								$hasilTimbang_Bruto_Minbrd[$spb][$tglPanen][$div][$indk] 	+= $kgbrutoHasiltimbang_minbrd[$spb][$div][$tglPanen][$indk][$blk];
								$hasilTimbang_Netto[$spb][$tglPanen][$div][$indk] 			+= $kgnettoHasiltimbang[$spb][$div][$tglPanen][$indk][$blk];
								$hasilTimbang_Netto_Minbrd[$spb][$tglPanen][$div][$indk] 	+= $kgnettoHasiltimbang_minbrd[$spb][$div][$tglPanen][$indk][$blk];

							}

							$tab.="<tr id=row_".$nod." class=rowheader ".$bgcolor.">";
							if($param['tipe']!='excel'){
								$tab.="<td hidden id=nospbdiv_".$nod.">".$spb."</td>";
								$tab.="<td hidden id=indukblokdiv_".$nod.">".$indk."</td>";
								$tab.="<td hidden id=tglpanendiv_".$nod.">".$tglPanen."</td>";
							}

								$tab.="<td colspan=2>TOTAL ".tanggalnormal($tglPanen)." ".getNamaOrg($div)."</td>";
								$tab.="<td>".substr($blk,0,9)."</td>";
								$tab.="<td></td>";
								$tab.="<td></td>";
								$tab.="<td>".round($jjgindkx[$spb][$tglPanen][$div][$indk],2)."</td>";
								$tab.="<td></td>";

							if($param['tipe']!='excel'){
								$tab.="<td hidden id=totkgbjrindk_".$nod.">".round($totalkgbjrindk[$spb][$tglPanen][$div][$indk],2)."</td>";
								$tab.="<td hidden id=bjrblokbesar_".$nod.">".round(($totalkgbjrindk[$spb][$tglPanen][$div][$indk] / $jjgindkx[$spb][$div][$tglPanen][$indk]),2)."</td>";
							}

								$tab.="<td align=center>".round($totbrdindk[$spb][$tglPanen][$div][$indk],2)."</td>";
								$tab.="<td align=center>".round($totalKgkebun[$spb][$tglPanen][$div][$indk],2)."</td>";
								$tab.="<td align=center>".round($hasilTimbang_Bruto[$spb][$tglPanen][$div][$indk],2) ."</td>";
								$tab.="<td align=center id=kgwbindk_".$nod.">". round($hasilTimbang_Bruto_Minbrd[$spb][$tglPanen][$div][$indk],2) ."</td>";

								$tab.="<td align=center>".round($hasilTimbang_Netto[$spb][$tglPanen][$div][$indk],2)."</td>";
								$tab.="<td align=center id=kgwbnettoindk_".$nod.">" . round($hasilTimbang_Netto_Minbrd[$spb][$tglPanen][$div][$indk],2) . "</td>";

							if($param['tipe']!='excel'){
								$tab.="<td hidden id=totalkgindk_".$nod.">".round($hasilTimbang_Bruto[$spb][$tglPanen][$div][$indk],2)."</td>";
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
		$where2 = "";
		$tahun = "";
		$bulan = "";

		$whereBaru = "";
		if ($tgl != "" && $tgl2 != "") {
			$where1 .= " and tanggal between '".$tgl."' and '".$tgl2."'";
			$whereBaru .= " and c.tanggal between '".$tgl."' and '".$tgl2."'";


			$tahun=substr($tgl, 0,4);
			$bulan=substr($tgl, 4,2);
		}


		if ($intex != "") {
			$where1 .= " and tujuan='".$intex."'";
			$whereBaru .= " and tujuan='".$intex."'";
		}

		$whereKodeOrg = " and c.kodeorg='".$kdOrg."' ";
		$where2 = " and kodeorg='".$kdOrg."' ";
		$whereKodeOrgSPB = " and d.nospb like '%".$kdOrg."%' ";
		$whereKodeOrgTimbangan = " and kodeorg = '".$kdOrg."' ";

		$whereKodeOrgPANEN = "and unit = '".$kdOrg."'";
		
		if(strlen($kdOrg)>4){
			$whereKodeOrg = " and a.kodeorg like '".$kdOrg."%' ";
			$whereKodeOrgTimbangan = " and divcode = '".$kdOrg."' ";
			$whereKodeOrgSPB = " and c.blok like '%".$kdOrg."%' ";

			$whereKodeOrgPANEN= " and c.kodeorg like '%".$kdOrg."%' ";
			$where2 = " and divcode='".$kdOrg."' ";

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

		$str = "SELECT a.notransaksi as nospb,c.noreferensi as noreferensi, substr(a.kodeorg,1,6) as divisi, a.kodeorg as indukblok,
				a.blokkecil as blok, a.tahuntanam, a.tanggal as tanggalpanen,
				a.jjg, a.brondolan, c.kodeorg, c.tanggal as tglspb, c.posting
				FROM $dbname.kebun_proporsitahuntanam_spb a 
				JOIN $dbname.kebun_spbht c on a.notransaksi = c.nospb
				WHERE 1=1 ".$whereBaru." ".$whereKodeOrg."";

		$res = fetchdata($str);
		$jumlahData = count($res); 

		$nospbx = $divisix = $blokx = $indukblokx = $thntnmx = $luasblokx = $klaslahanx = $tglpanenx = $tglpanenindk = array();
		$sesix = $notphx = $pemanenx = array();
		$tgltimbang = $tdnblk = $kgbruto = $kgnetto = $bjrblk = $tdnblok = $klsbjrkg = $totjjgspb = $totbrdspb = $hslpnnblok = $totbrdindk = array();
		$totkgbruto = $tottdnblkthn = $totbjrblk = $totaltdnblok = $totklsbjrkg = $totluasblk = $tothslpnn = $brdblk = array();
		$kgbrutobrd = $kgnettobrd = $kgbrutoproporsi = $kgnettoproporsi = $kgbrutobrdproporsi = $kgnettobrdproporsi = array();

		if($jumlahData == 0){
			exit("Warning : Data tidak ada, pastikan SPB sudah di proposi tahun tanam ");
		}

		foreach ($res as $val) {
			$nospbx[$val['nospb']] = $val['nospb'];
			
			if(!isset($jlhblokxx[$val['nospb']])){
				$jlhblokxx[$val['nospb']]=0;
			}
			
			if(!isset($blokx[$val['nospb']][$val['divisi']][$val['tanggalpanen']][$val['indukblok']][$val['blok']])){
				$jlhblokxx[$val['nospb']]+=1;
			}
			
			$blokx[$val['nospb']][$val['divisi']][$val['tanggalpanen']][$val['indukblok']][$val['blok']] = $val['blok'];

			if(getNamaOrg($kdOrg,'induk')=='MHA' or getNamaOrg($kdOrg,'induk')=='DMA'){
				$noref[$val['nospb']] = $val['nospb'];
				$ambilNoreferensi[$val['nospb']][$val['divisi']][$val['tanggalpanen']][$val['indukblok']][$val['blok']] = $val['noreferensi'];
				$brdindkx2[$val['nospb']] 	+= $val['brondolan'];
				
			}else{
				$noref[$val['nospb']] = $val['noreferensi'];
				$ambilNoreferensi[$val['noreferensi']][$val['divisi']][$val['tanggalpanen']][$val['indukblok']][$val['blok']] = $val['noreferensi'];
				$brdindkx2[$val['noreferensi']] 	+= $val['brondolan'];

			}
			

			$thntnmx[$val['blok']] = $val['tahuntanam'];

			$kdorgb[$val['nospb']] 							= $val['kodeorg'];
			$tanggalspb[$val['nospb']] 						= $val['tglspb'];
			$statusposting[$val['nospb']]					= $val['posting'];

			$hslpnnblok[$val['nospb']][$val['divisi']][$val['tanggalpanen']][$val['indukblok']][$val['blok']] +=$val['jjg'];
			$brdblk[$val['nospb']][$val['divisi']][$val['tanggalpanen']][$val['indukblok']][$val['blok']] +=$val['brondolan'];

			$totjjgspb[$val['nospb']] += $val['jjg'];
			$totbrdspb[$val['nospb']] += $val['brondolan'];

			$jjgindkx[$val['nospb']][$val['divisi']][$val['tanggalpanen']][$val['indukblok']] 	+= $val['jjg'];
			$brdindkx[$val['nospb']][$val['divisi']][$val['tanggalpanen']][$val['indukblok']] 	+= $val['brondolan'];

		}

		$strcek = "SELECT * FROM $dbname.pabrik_timbangan WHERE 1=1 ".$where2."";
		$rescek = fetchdata($strcek);
		$hsltimbang=array();
		foreach ($rescek as $val) {
			if(getNamaOrg($kdOrg,'induk')=='MHA' or getNamaOrg($kdOrg,'induk')=='DMA'){
				$hsltimbang[$val['nospb']]=$val['nospb'];
			}else{
				$hsltimbang[$val['nospbmobile']]=$val['nospbmobile'];
			}
		}

		$strbjr = "SELECT * FROM kebun_5bjr where 1=1 and (periode='".substr($pertgl,0,7)."' or periode='".periodelalu(substr($pertgl,0,7))."')";
		$resbjr = fetchdata($strbjr);
		if(count($resbjr) > 0) {	
			foreach ($resbjr as $val) {
				if ($val['bjr'] == 0 && isset($thntnmx[$val['kodeorg']])) exit('Error: Gagal posting <br><br>Blok<b> ' . $val['kodeorg'] . '</b> BJRnya masih 0.');
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

		if(getNamaOrg($kdOrg,'induk')=='MHA' or getNamaOrg($kdOrg,'induk')=='DMA'){
			foreach ($ambilNoreferensi as $spb => $key1) {
				$strPabrik = "SELECT * FROM $dbname.pabrik_timbangan where nospb ='".$spb."' ".$whereKodeOrgTimbangan."";
				$resPabrik = fetchData($strPabrik);
				foreach ($resPabrik as $dtp) {
					
					$dtp['nospb'] = $dtp['nospb'];
	
					$tgltimbang[$dtp['nospb']]	= substr($dtp['tanggal'],0,10);
					$nospbwb[$dtp['nospb']]		= $dtp['nospb'];
					$notranswb[$dtp['nospb']]	= $dtp['notransaksi'];
	
					// Perhitungan mendapatkan Kg Bruto
					$kgbruto[$dtp['nospb']]		   = ($dtp['beratmasuk']-$dtp['beratkeluar']);
	
					// Perhitungan mendapatkan Kg Bruto - Brondolan
					$kgbruto_minbrd[$dtp['nospb']] = ($dtp['beratmasuk']-$dtp['beratkeluar']) - $brdindkx2[$dtp['nospb']];
	
					// Perhitungan mendapatkan Kg Netto
					$kgnetto[$dtp['nospb']]		   = ($dtp['beratbersih']);
	
					// Perhitungan mendapatak Kg Netto - Brondolan
					$kgnetto_minbrd[$dtp['nospb']] = ($dtp['beratbersih'] - $brdindkx2[$dtp['nospb']] );
					
				}
			}
		}else{
			foreach ($ambilNoreferensi as $spb => $key1) {
				$strPabrik = "SELECT * FROM $dbname.pabrik_timbangan where nospbmobile ='".$spb."' ".$whereKodeOrgTimbangan."";
				$resPabrik = fetchData($strPabrik);
				foreach ($resPabrik as $dtp) {
					
					$dtp['nospb'] = $dtp['nospbmobile'];
	
					$tgltimbang[$dtp['nospb']]	= substr($dtp['tanggal'],0,10);
					$nospbwb[$dtp['nospb']]		= $dtp['nospb'];
					$notranswb[$dtp['nospb']]	= $dtp['notransaksi'];
	
					// Perhitungan mendapatkan Kg Bruto
					$kgbruto[$dtp['nospb']]		   = ($dtp['beratmasuk']-$dtp['beratkeluar']);
	
					// Perhitungan mendapatkan Kg Bruto - Brondolan
					$kgbruto_minbrd[$dtp['nospb']] = ($dtp['beratmasuk']-$dtp['beratkeluar']) - $brdindkx2[$dtp['nospb']];
	
					// Perhitungan mendapatkan Kg Netto
					$kgnetto[$dtp['nospb']]		   = ($dtp['beratbersih']);
	
					// Perhitungan mendapatak Kg Netto - Brondolan
					$kgnetto_minbrd[$dtp['nospb']] = ($dtp['beratbersih'] - $brdindkx2[$dtp['nospb']] );
					
				}
			}

		}
						
		foreach ($blokx as $spb => $key1) {
			foreach ($key1 as $div => $key2) {
				foreach ($key2 as $tglPanen => $key3) {
					foreach ($key3 as $indk => $key4) {
						$nob++;
						foreach ($key4 as $blk => $val) {

							## Jika Cuman Brondol doang
							if($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] == 0){
								## Kg Kebun
								$KgKebunx[$spb][$div][$tglPanen][$indk][$blk] = round(($brdblk[$spb][$div][$tglPanen][$indk][$blk]),2);
	
								## Total KG KEBUN
								$TTkgkebunspb[$spb] += $KgKebunx[$spb][$div][$tglPanen][$indk][$blk];
							}else{
								## Kg Kebun
								$KgKebunx[$spb][$div][$tglPanen][$indk][$blk] = round(($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] * $bjrblk[$blk][substr($tglPanen,0,7)]),2);
	
								## Total KG KEBUN
								$TTkgkebunspb[$spb] += $KgKebunx[$spb][$div][$tglPanen][$indk][$blk];
							}
						}
					}
				}
			}							
		}

		$sdta2='';
		if ($nob > 0) {
			foreach ($blokx as $spb => $key1) {
				$delDet = deleteQuery($dbname, "kebun_spbdt_detail","nospb='".$spb."'");
				$owlPDO->exec($delDet);

				$no++;
				foreach ($key1 as $div => $key2) {
					foreach ($key2 as $tglPanen => $key3) {
						foreach ($key3 as $indk => $key4) {
							$nod++;
							foreach ($key4 as $blk => $val) {
								$nok++;
								$noblokx++;


								## KG KEBUN
								if($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] == 0){
									$KgKebun[$spb][$div][$tglPanen][$indk][$blk] = round($brdblk[$spb][$div][$tglPanen][$indk][$blk],2);
									$persentaseBlok[$spb][$div][$tglPanen][$indk][$blk] = round($brdblk[$spb][$div][$tglPanen][$indk][$blk]/$TTkgkebunspb[$spb],2);
								}else{
									$KgKebun[$spb][$div][$tglPanen][$indk][$blk] = round(($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] * $bjrblk[$blk][substr($tglPanen,0,7)]),2);
								}
																
								## Hasil Timbang Bruto
								if($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] == 0){
									$kgbrutoHasiltimbang[$spb][$div][$tglPanen][$indk][$blk] = round((($KgKebun[$spb][$div][$tglPanen][$indk][$blk] * $kgbruto[$noref[$spb]])/ $TTkgkebunspb[$spb]),2)	;
								}else{
									$kgbrutoHasiltimbang[$spb][$div][$tglPanen][$indk][$blk] = round((($KgKebun[$spb][$div][$tglPanen][$indk][$blk] * $kgbruto[$noref[$spb]]) / $TTkgkebunspb[$spb]),2)	;
								}

								## Hasil Timbang Bruto - Brondolan
								if($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] == 0){
									$kgbrutoHasiltimbang_minbrd[$spb][$div][$tglPanen][$indk][$blk] = round((($KgKebun[$spb][$div][$tglPanen][$indk][$blk] * $kgbruto[$noref[$spb]]) / $TTkgkebunspb[$spb]),2)	;
									
								}else{
									$kgbrutoHasiltimbang_minbrd[$spb][$div][$tglPanen][$indk][$blk] = round((($KgKebun[$spb][$div][$tglPanen][$indk][$blk] * $kgbruto_minbrd[$noref[$spb]]) / $TTkgkebunspb[$spb]),2)	;
								}
																
								## Hasil Timbang Netto
								if($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] == 0){
									$kgnettoHasiltimbang[$spb][$div][$tglPanen][$indk][$blk] = round((($KgKebun[$spb][$div][$tglPanen][$indk][$blk] * $kgnetto[$noref[$spb]])/ $TTkgkebunspb[$spb] ),2)	;
								}else{
									$kgnettoHasiltimbang[$spb][$div][$tglPanen][$indk][$blk] = round((($KgKebun[$spb][$div][$tglPanen][$indk][$blk] * $kgnetto[$noref[$spb]]) / $TTkgkebunspb[$spb]),2)	;
								}

								## Hasil TImbang Netto - Brondolan
								if($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk] == 0){
									$kgnettoHasiltimbang_minbrd[$spb][$div][$tglPanen][$indk][$blk] = round((($KgKebun[$spb][$div][$tglPanen][$indk][$blk] * $kgnetto[$noref[$spb]]) / $TTkgkebunspb[$spb]),2)	;
								}else{
									$kgnettoHasiltimbang_minbrd[$spb][$div][$tglPanen][$indk][$blk] = round((($KgKebun[$spb][$div][$tglPanen][$indk][$blk] * $kgnetto_minbrd[$noref[$spb]]) / $TTkgkebunspb[$spb]),2)	;
								}
							
								if($sdta2==''){
									$sdta2=" ('".$spb."','".$indk."','".$blk."','".$tglPanen."','".round($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk],2)."','".$kgbrutoHasiltimbang[$spb][$div][$tglPanen][$indk][$blk]."','".$kgnettoHasiltimbang[$spb][$div][$tglPanen][$indk][$blk]."','".round($bjrblk[$blk][substr($tglPanen,0,7)],2)."','".round($brdblk[$spb][$div][$tglPanen][$indk][$blk],2)."','".$kgbrutoHasiltimbang[$spb][$div][$tglPanen][$indk][$blk]."','".$KgKebun[$spb][$div][$tglPanen][$indk][$blk]."')";
								}else{
									$sdta2.=" , ('".$spb."','".$indk."','".$blk."','".$tglPanen."','".round($hslpnnblok[$spb][$div][$tglPanen][$indk][$blk],2)."','".round($kgbrutoHasiltimbang[$spb][$div][$tglPanen][$indk][$blk],2)."','".$kgnettoHasiltimbang[$spb][$div][$tglPanen][$indk][$blk]."','".round($bjrblk[$blk][substr($tglPanen,0,7)],2)."','".round($brdblk[$spb][$div][$tglPanen][$indk][$blk],2)."','".$kgbrutoHasiltimbang[$spb][$div][$tglPanen][$indk][$blk]."','".$KgKebun[$spb][$div][$tglPanen][$indk][$blk]."')";
								}
							}
						}
					}
				}
			}
		}

		try {
			$owlPDO->beginTransaction();
							
		$sdta1="insert into ".$dbname.".kebun_spbdt_detail (nospb,indukblok,blok,tanggalpanen,jjg,kgwb,kgwbnetto,bjr,brondolan,totalkg,kgbjr) values ";
		$sinsert=$sdta1." ".$sdta2.";";
		$owlPDO->exec($sinsert);
		
		foreach ($nospbx as $spb) {
			$colHt = array(
				"posting" 	=> '1',
				"postingby"	=> $_SESSION['standard']['userid']
			);
			$updH = updateQuery($dbname,"kebun_spbht",$colHt,"nospb='".$spb."'");
			$owlPDO->exec($updH);


			if($noref[$spb] != ''){
				## Update Pabrik Timbangan
				$colHt2 = array(
					"nospb" 	=> $spb,
				);
	
				$updH = updateQuery($dbname,"pabrik_timbangan",$colHt2,"nospbmobile ='".$noref[$spb]."'");
				$owlPDO->exec($updH);
			}


		}
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
			echo 'SUKSES';
	break;

	case'recektimbangan':

		$result = "";

		$result.="<div class='table-scroll'>
			<table cellspacing=1 cellpadding=2 border=0 width=100% class=sortable>
				<thead class=rowheader>
				<tr>
					<th style='text-align:center' colspan=3>Data SPB</th>
					<th style='text-align:center' rowspan=2>".$_SESSION['lang']['noTiket']."</th>
					<th style='text-align:center' rowspan=2>".$_SESSION['lang']['kodenopol']."</th>
					<th style='text-align:center' rowspan=2>".$_SESSION['lang']['sopir']."</th>    
					<th style='text-align:center' rowspan=2>Jjg</th>
					<th style='text-align:center' rowspan=2>Brondolan</th>
					<th style='text-align:center' rowspan=2>Berat Bersih</th>
					<th style='text-align:center' rowspan=2>".$_SESSION['lang']['tanggal']." Timbang</th>
					<th style='text-align:center' rowspan=2>No.Referensi Mobile</th>
					<th style='text-align:center' rowspan=2>Kode <br>Kemandoran</th>
					<th style='text-align:center' colspan=2>Status</th>
				</tr>
				<tr>
					<th style='text-align:center' >No.</th>
					<th style='text-align:center' >".$_SESSION['lang']['nospb']."</th>
					<th style='text-align:center' >".$_SESSION['lang']['tanggal']." SPB</th>


					<th style='text-align:center' rowspan=2>Tidak Ada SPB</th>
					<th style='text-align:center' rowspan=2>Posting SPB</th>
				</tr>
				</thead>
				<tbody>";
		

		$postingSPB = makeOption($dbname,'kebun_spbht','nospb,posting');
		$tanggalSPB = makeOption($dbname,'kebun_spbht','nospb,tanggal');

		$str="select * from ".$dbname.".pabrik_timbangan where left(tanggal,10) between '".$pertgl."' and '".$pertgl2."' and kodeorg='".$kdOrg."' and kodebarang = '40000003' order by tanggal asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=$res->rowCount();

		if($numrows <= 0){
			$result.="<tr class=rowcontent><td colspan=15 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
		}else{
			while($bar=$res->fetch()){
				$no+=1;

				$result.="<tr class=rowcontent >";
					$result.="<td>".$no."</td>";
					$result.="<td>".$bar['nospb']."</td>";
					$result.="<td align=center>".$tanggalSPB[$bar['nospb']]."</td>";
					$result.="<td align=center>".$bar['notransaksi']."</td>";
					$result.="<td align=center>".$bar['nokendaraan']."</td>";
					$result.="<td align=center>".$bar['supir']."</td>";
					$result.="<td align=center>".number_format($bar['jumlahtandan1'])."</td>";
					$result.="<td align=center>".number_format($bar['brondolan'])."</td>";
					$result.="<td align=center>".number_format($bar['beratbersih'])."</td>";
					$result.="<td align=center>".$bar['tanggal']."</td>";
					$result.="<td align=center>".$bar['nospbmobile']."</td>";
					$result.="<td align=center>".$bar['kemandoran']."</td>";

					if($bar['nospb'] == ''){
						$result .= "<td align='center' style='background-color: red; color: black;'>Data tidak ada SPB</td>";
					}else{
						$result .= "<td align='center' style='background-color: green; color: black;'>OK</td>";
					}

					if($postingSPB[$bar['nospb']] == '1' ){
						$result .= "<td align='center' style='background-color: green; color: black;'>OK</td>";
					}else{
						$result .= "<td align='center' style='background-color: red; color: black;'>SPB belum di posting</td>";
					}



				$result.="</tr>";

			}	

		}

		$result.="</tbody></table></div>";
		echo $result;
	break;
	
	default:
	break;
}
?>