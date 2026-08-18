<?
	include('../config/connection.php');
	include('../lib/nangkoelib.php');
	include('master_validation.php');
	include('lib/zLib.php');
	include('../jpgraph/jpgraph.php');
	include('../jpgraph/jpgraph_pie.php');
	include('../jpgraph/jpgraph_pie3d.php');

	$kebun = checkPostGet('kebun', '');
	$periodeawal = checkPostGet('periodeawal', '');
	$periodeakhir = checkPostGet('periodeakhir', '');
	$detaillaporan = checkPostGet('detaillaporan', '');
	$type = checkPostGet('type', '');
	$idsvg = checkPostGet('idsvg', '');

	$kbnarr = strToArray($kebun, '##');

	switch ($type) {
		case 'preview':
			if (str_replace('-', '', $periodeawal) > str_replace('-', '', $periodeakhir)) {
				exit("Error: Periode awal harus lebih kecil dari periode akhir.");
			}
			// echo $detaillaporan.'xx';

			$str = "SELECT * FROM ".$dbname.".bi_5warnalaporan WHERE idlap = '".$detaillaporan."'";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$arrWarna = array();
			while ($bar = $res->fetch()) {
				$arrWarna[$bar['warna']]['opawal'] = $bar['opawal'];
				$arrWarna[$bar['warna']]['awal'] = $bar['nilaiawal'];
				$arrWarna[$bar['warna']]['opakhir'] = $bar['opakhir'];
				$arrWarna[$bar['warna']]['akhir'] = $bar['nilaiakhir'];
			}

			##sum ha pnn / luas blok 

			$tglakhir = tglakhir($periodeakhir.'-01');
			// $jumbul=intval(substr($periodeakhir,5,2))-intval(substr($periodeawal,5,2));
			// $jumbul=$jumbul+1;

			/*
			$str = "select max(tanggal) as tanggal, blok, angka from ".$dbname.".kebun_pusingan_vw 
			where tanggal like '".$periodeakhir."%' and unit = '".$kebun."' group by blok order by tanggal desc";
			*/

			$str = "SELECT kodeorg FROM ".$dbname.".setup_blok";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$data[$bar['kodeorg']] == '';
			}

			$str = "
				SELECT sum(luaspanen) as luaspanen, luasproduksi, blok
				FROM ".$dbname.".kebun_rekappnn
				WHERE 1 = 1 ".forKebunAll($kbnarr, 'LEFT(divisi, 4)', 'in')." AND LEFT(tanggal, 7) BETWEEN '".$periodeawal."' AND '".$periodeakhir."'
				GROUP BY blok
			";
			$query = $str;
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$bloklist[$bar['blok']] = $bar['blok'];
				$data[$bar['blok']] = $bar['luaspanen'];
			}

			$arrList = array();
			$countlist = -1;
			foreach ($bloklist as $listblok) {
				if (isset($arrWarna)) {
					foreach ($arrWarna as $key => $row) {
						echo my_operator($data[$listblok], $row['akhir'], $row['opawal']);
						if (my_operator($data[$listblok], $row['awal'], $row['opawal']) && my_operator($data[$listblok], $row['akhir'], $row['opakhir'])) {
							$countlist++;

							//$str2 = "select idsvg from ".$dbname.".bi_map_pt where keterangan like '".$listblok."%'";
							$str2 = "SELECT idsvg FROM ".$dbname.".bi_map_pt WHERE keterangan LIKE '".$listblok."%'";
							try {
								$res2 = $owlPDO->query($str2);
							} catch (PDOException $e) {
								echo "Gagal: ".$e->getMessage();
							}
							$res2->setFetchMode(PDO::FETCH_ASSOC);
							$bar2 = $res2->fetch();

							if ($bar2['idsvg'] != '') {
								$arrList[$countlist]['idsvg'] = $bar2['idsvg'];
								$arrList[$countlist]['warna'] = $key;
							}
						}
					}
				}
			}

			$divLegend = "";
			if (isset($arrWarna)) {
				$divLegend .= "
					<div style='padding-top:5px;'>
						<b>".$_SESSION['lang']['keterangan']."</b>
					</div>
					<table cellpading=1 cellspacing=3 style=width:100%>
						<tbody>
				";

				foreach ($arrWarna as $key => $row) {
					$divLegend .= "
						<tr>
							<td bgcolor='".$key."' style='width:20px;'></td>
							<td style='text-align:center'>".$row['opawal']." ".$row['awal']."</td>
							<td style='text-align:center'>&</td>
							<td style='text-align:center'>".$row['opakhir']." ".$row['akhir']."</td>
						</tr>
					";
				}

				$divLegend .= "
						</tbody>
					</table>
				";
			}

			echo json_encode($arrList)."####".showLegend($detaillaporan);
			break;
		case 'detail':
			//Get Kode Blok
			$str = "SELECT * FROM ".$dbname.".bi_map_pt WHERE idsvg = '".$idsvg."'";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$expKtr = explode('##', $bar['keterangan']);
			$blok = $expKtr[0];

			#bentuk tahun
			$tahun = substr(($periodeakhir), 0, 4);
			$whbgt = "tahunbudget='".$tahun."'";

			// $luasreal = makeOption($dbname, 'setup_blok', 'kodeorg,luasareaproduktif');
			// $luasbgt = makeOption($dbname, 'bgt_blok', 'kodeblok,hathnini', $whbgt);

			// luas real
			$str = "SELECT indukblok, luasareaproduktif FROM ".$dbname.".setup_blok WHERE indukblok = '".$blok."'";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$luasreal[$bar['indukblok']] += $bar['luasareaproduktif'];
			}

			// luas bgt
			$str = "SELECT kodeblok, hathnini FROM ".$dbname.".bgt_blok WHERE kodeblok LIKE '".$blok."%' AND tahunbudget BETWEEN '".substr($periodeawal, 0, 4)."' AND '".substr($periodeakhir, 0, 4)."'";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$luasbgt[$blok] += $bar['hathnini'];
			}

			# tenaga kerja
			$str = "
				SELECT COUNT(DISTINCT pemanen) AS pemanen, SUBSTR(tanggalpanen, 1, 7) AS periode
				FROM ".$dbname.".kebun_spb_vw
				WHERE blok = '".$blok."' AND LEFT(tanggalpanen, 7) BETWEEN '".$periodeawal."' AND '".$periodeakhir."'
				GROUP BY SUBSTR(tanggalpanen, 1, 7)
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$tkpanen[$bar['periode']] += $bar['pemanen'];
			}

			// kg real
			$str = "
				SELECT SUBSTR(tanggalpanen, 1, 7) AS periode, SUM(beratbersihtimbangan) AS beratbersih
				FROM (
					SELECT nospb, beratbersihtimbangan, tanggalpanen
					FROM kebun_spb_vw
					WHERE blok = '".$blok."'
						AND LEFT(tanggalpanen, 7) BETWEEN '".$periodeawal."' AND '".$periodeakhir."'
					GROUP BY nospb, SUBSTR(tanggalpanen, 1, 7)
				) AS t
				GROUP BY periode
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$tonreal[$bar['periode']] = $bar['beratbersih'];
			}

			// jjg kirim
			$str = "
				SELECT SUM(kgwb) AS kg, SUM(jjg) AS jjg, SUBSTR(tanggalpanen, 1, 7) AS periode
				FROM ".$dbname.".kebun_spb_vw
				WHERE blok = '".$blok."' AND LEFT(tanggalpanen, 7) BETWEEN '".$periodeawal."' AND '".$periodeakhir."'
				GROUP BY SUBSTR(tanggalpanen, 1, 7)
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				// $tonreal[$bar['periode']] = $bar['kg'];
				$jjgkirim[$bar['periode']] = $bar['jjg'];
			}

			$str = "SELECT * FROM ".$dbname.".bgt_produksi_kbn_kg_vw WHERE kodeblok LIKE '".$blok."%' AND tahunbudget BETWEEN '".substr($periodeawal, 0, 4)."' AND '".substr($periodeakhir, 0, 4)."'";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				for ($i = 1; $i <= 12; $i++) {
					if ($i < 10) {
						$ii = "0".$i;
					} else {
						$ii = $i;
					}

					$tonbgt[$bar['tahunbudget'].'-'.$ii] += ($bar['kg'.$ii]);
				}
			}

			##sum panen - (sum kirim *dr spbvw + sum afkir)

			$str = "SELECT * FROM ".$dbname.".kebun_rekappnn WHERE blok = '".$blok."' AND LEFT(tanggal, 7) BETWEEN '".$periodeawal."' AND '".$periodeakhir."'";
			$query = fetchdata($str);
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$jjgpanen[substr($bar['tanggal'], 0, 7)] += $bar['jjgpanen'];
				$jjgafkir[substr($bar['tanggal'], 0, 7)] += $bar['jjgafkir'];
				// $tkpanen[substr($bar['tanggal'], 0, 7)] += $bar['tenagakerja'];
				$kgkebun[substr($bar['tanggal'], 0, 7)] += $bar['kgkebun'];
			}

			$arrper = month_inbetween(($periodeawal), ($periodeakhir));

			#spb
			$result = "
				<hr>
				<table cellpading=1 cellspacing=1 border=0 class=sortable width=100%>
					<thead>
						<tr align=center>
							<td rowspan=2 width=10%>".$_SESSION['lang']['periode']."</td>
							<td colspan=4>".$_SESSION['lang']['realisasi']."</td>
							<td colspan=2>".$_SESSION['lang']['budget']."</td>
							<td rowspan=2 width=15%>".$_SESSION['lang']['pencapaian']." (%)</td>
						</tr>
						<tr align=center>
							<td >".$_SESSION['lang']['jjg']."</td>
							<td >".$_SESSION['lang']['eksploitasi']."</td>
							<td >".$_SESSION['lang']['kg']."</td>
							<td >".$_SESSION['lang']['jenjangpokoktahun']."</td>
							<td >".$_SESSION['lang']['kg']."</td>
							<td >".$_SESSION['lang']['jenjangpokoktahun']."</td>
						</tr>
					</thead>
					<tbody>
			";

			$arrper = month_inbetween(($periodeawal), ($periodeakhir));
			if (count($query) >= 1) {
				foreach ($arrper as $per) {
					$result .= "
						<tr class=rowcontent align=center>
							<td>".$per."</td>
							<td align=right>".number_format($jjgpanen[$per])."</td>
							<td align=right>".number_format($tkpanen[$per])."</td>
							<td align=right>".number_format($tonreal[$per])."</td>
							<td align=right>".number_format(fixnan(@(($tonreal[$per] / $luasreal[$blok]) / 1000)), 2)."</td>
							<td align=right>".number_format($tonbgt[$per], 2)."</td>
							<td align=right>".number_format(fixnan(@(($tonbgt[$per] / $luasbgt[$blok]) / 1000)), 2)."</td>
							<td align=right>".number_format(fixnan(@($tonreal[$per] / $tonbgt[$per] * 100)), 2)."</td>	
						</tr>
					";
					
					$resbl = $restan;
					$ttjjgreal += $jjgpanen[$per];
					$tttkreal += $tkpanen[$per];
					$ttonreal += $tonreal[$per];
					$ttonbgt += $tonbgt[$per];
				}
			}
			
			//warna
			$str = "SELECT * FROM ".$dbname.".bi_5warnalaporan WHERE idlap = '".$detaillaporan."'";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$arrWarna = array();
			while ($bar = $res->fetch()) {
				$arrWarna[$bar['warna']]['opawal'] = $bar['opawal'];
				$arrWarna[$bar['warna']]['awal'] = $bar['nilaiawal'];
				$arrWarna[$bar['warna']]['opakhir'] = $bar['opakhir'];
				$arrWarna[$bar['warna']]['akhir'] = $bar['nilaiakhir'];
			}

			foreach ($arrWarna as $key => $row) {
				$data = fixnan(@(($ttonreal / $ttonbgt) * 100));
				if (my_operator($data, $row['awal'], $row['opawal']) && my_operator($data, $row['akhir'], $row['opakhir'])) {
					$bgcol = $key;
				}
			}

			$warna = $bgcol;

			if (count($query) < 1) {
				$result .= "
					<tr class=rowcontent align=center>
						<td align=center colspan=8>".$_SESSION['lang']['datanotfound']."</td>
					</tr>
				";
			} else {
				$result .= "
					<tr class=rowcontent align=center style=font-weight:bold;>
						<td align=center>".$_SESSION['lang']['total']."</td>
						<td align=right>".number_format($ttjjgreal)."</td>
						<td align=right>".number_format($tttkreal)."</td>
						<td align=right>".number_format($ttonreal)."</td>
						<td align=right>".number_format(fixnan(@(($ttonreal / $luasreal[$blok]) / 1000)), 2)."</td>
						<td align=right>".number_format($ttonbgt, 2)."</td>
						<td align=right>".number_format(fixnan(@(($ttonbgt / $luasbgt[$blok]) / 1000)), 2)."</td>
						<td align=right style=background-color:".$warna.">
							<b>".number_format(fixnan(@($ttonreal / $ttonbgt * 100)), 2)."</b>
						</td>
					</tr>
				";
			}

			$result .= "	
					</tbody>
				</table>
			";
			
			echo $result;
			break;
		case 'globalreport':
			if ($periodeawal == $periodeakhir) {
				$periode = $periodeawal;
			} else {
				$periode = $periodeawal." ".$_SESSION['lang']['sd']." ".$periodeakhir;
			}

			$str = "SELECT namalaporan FROM ".$dbname.".bi_5laporan WHERE idlap = '".$detaillaporan."'";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$namalaporan = $bar['namalaporan'];

			$str = "SELECT * FROM ".$dbname.".bi_5warnalaporan WHERE idlap = '".$detaillaporan."'";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$arrWarna = array();
			while ($bar = $res->fetch()) {
				$arrWarna[$bar['warna']]['opawal'] = $bar['opawal'];
				$arrWarna[$bar['warna']]['awal'] = $bar['nilaiawal'];
				$arrWarna[$bar['warna']]['opakhir'] = $bar['opakhir'];
				$arrWarna[$bar['warna']]['akhir'] = $bar['nilaiakhir'];
				$arrWarna[$bar['warna']]['keterangan'] = $bar['keterangan'];
			}

			##sum ha pnn / luas blok 

			$tglakhir = tglakhir($periodeakhir.'-01');
			// $jumbul = intval(substr($periodeakhir, 5, 2)) - intval(substr($periodeawal, 5, 2));
			// $jumbul = $jumbul + 1;
			$startYear = intval(substr($periodeawal, 0, 4));
			$startMonth = intval(substr($periodeawal, 5, 2));
			$endYear = intval(substr($periodeakhir, 0, 4));
			$endMonth = intval(substr($periodeakhir, 5, 2));
			$jumbul = ($endYear - $startYear) * 12 + ($endMonth - $startMonth) + 1;
			// echo $jumbul;

			/*
			$str = "SELECT max(tanggal) as tanggal, blok, angka FROM ".$dbname.".kebun_pusingan_vw 
			WHERE tanggal like '".$periodeakhir."%' and unit = '".$kebun."' group by blok order by tanggal desc";
			*/
			$str = "
				SELECT SUM(luaspanen) AS luaspanen, luasproduksi, blok
				FROM ".$dbname.".kebun_rekappnn
				WHERE 1 = 1 ".forKebunAll($kbnarr, 'LEFT(divisi, 4)', 'in')." AND LEFT(tanggal, 7) BETWEEN '{$periodeawal}' AND '{$periodeakhir}'
				GROUP BY blok
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$bloklist[$bar['blok']] = $bar['blok'];
				$data[$bar['blok']] = $bar['luaspanen'] / $bar['luasproduksi'] / $jumbul;
			}

			$datas = array();
			$totalfill = 0;
			foreach ($bloklist as $listblok) {
				if (isset($arrWarna)) {
					foreach ($arrWarna as $key => $row) {
						if (my_operator($data[$listblok], $row['awal'], $row['opawal']) && my_operator($data[$listblok], $row['akhir'], $row['opakhir'])) {
							$datas[$key]['keterangan'] = $row['keterangan'];
							$datas[$key]['count'] += 1;

							$totalfill++;
						}
					}
				}
			}

			$str = "SELECT fill FROM ".$dbname.".bi_5warna WHERE tipe='".$firstPT."'";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$firstColor = $bar['fill'];

			$str = "SELECT count(idsvg) AS countsvg FROM ".$dbname.".bi_map_pt WHERE tipepeta = '".$firstPT."' ".forKebunAll($kbnarr, 'unit', 'in')."";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$totalnofill = $bar['countsvg'];

			$data = array();
			$label = array();
			$ket = array();
			$arrColor = array();
			$no = 2;

			$data[1] = $totalnofill - $totalfill;
			$label[1] = $totalnofill - $totalfill." ".$_SESSION['lang']['blok']."\n(%.1f%%)";
			$ket[1] = "None";
			$arrColor[1] = $firstColor;
			
			foreach ($datas as $key => $row) {
				$data[$no] = $row['count'];
				$label[$no] = $row['count']." ".$_SESSION['lang']['blok']."\n(%.1f%%)";
				$ket[$no] = str_replace('%', ' percent', $row['keterangan']);
				$arrColor[$no] = $key;

				$no++;
			}

			$graph = new PieGraph(670, 330);
			$graph->ClearTheme();
			$graph->SetShadow();
			$graph->title->Set($namalaporan);
			// $graph->title->SetFont(FF_VERDANA,FS_BOLD,12);
			$graph->title->SetColor("darkblue");
			$graph->subtitle->Set($_SESSION['lang']['periode']." : ".$periode);
			$graph->legend->SetShadow('gray@0.4', 5);
			$graph->legend->SetPos(0.1, 0.2, 'right', 'top');
			$graph->legend->SetColumns(1);

			$p1 = new PiePlot($data);
			$p1->SetSize(0.35);
			$p1->SetCenter(0.35);
			$p1->SetLabels($label);
			$p1->SetLabelPos(1);
			$p1->SetLegends($ket);
			$p1->SetLabelType(PIE_VALUE_PER);
			$p1->value->Show();
			// $p1->value->SetFont(FF_ARIAL,FS_NORMAL,9);    
			$p1->value->SetFormat('%2.1f%%');
			$p1->SetSliceColors($arrColor);

			$graph->Add($p1);
			$graph->StrokeCSIM();
			break;
	}

	function my_operator($a, $b, $char) {
		switch ($char) {
			case '=':
				return $a == $b;
			case '<=':
				return $a <= $b;
			case '>=':
				return $a >= $b;
			case '<':
				return $a < $b;
			case '>':
				return $a > $b;
		}
	}
