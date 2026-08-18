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

	$nmBar = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');

	switch ($type) {
		case 'preview':
			if (str_replace('-', '', ($periodeawal)) > str_replace('-', '', ($periodeakhir))) {
				exit("Error: Periode awal harus lebih kecil dari periode akhir.");
			}

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

			// $tglakhir=tglakhir(tanggalsystemn($periodeakhir).'-01');
			// $tglakhir= tanggalsystemn($periodeakhir);

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
				SELECT SUM(jurnal) AS jurnal, kodeorg, kodebarang, SUBSTR(tanggal, 1, 7)
				FROM ".$dbname.".kebun_pakai_material_vw
				WHERE 1 = 1 ".forKebunAll($kbnarr, 'LEFT(kodegudang, 4)', 'in')." AND LEFT(tanggal, 7) BETWEEN '".$periodeawal."' AND '".$periodeakhir."'
				GROUP BY kodeorg
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$bloklist[$bar['kodeorg']] = $bar['kodeorg'];
				$data[$bar['kodeorg']] = $bar['jurnal'];
			}

			$arrList = array();
			$countlist = -1;
			foreach ($bloklist as $listblok) {
				if (isset($arrWarna)) {
					foreach ($arrWarna as $key => $row) {
						echo my_operator($data[$listblok], $row['akhir'], $row['opawal']);
						if (my_operator($data[$listblok], $row['awal'], $row['opawal']) && my_operator($data[$listblok], $row['akhir'], $row['opakhir'])) {
							$countlist++;

							//$str2 = "SELECT idsvg FROM ".$dbname.".bi_map_pt WHERE keterangan LIKE '".$listblok."%'";
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

			$jumbul = intval(substr(($periodeakhir), 5, 2)) - intval(substr(($periodeawal), 5, 2));
			$jumbul = $jumbul + 1;
			$arrper = month_inbetween(($periodeawal), ($periodeakhir));
			$tahun = substr(($periodeakhir), 0, 4);

			// $str = "
			// 	SELECT kwantitas, kodebarang, kwantitasha, SUBSTR(tanggal, 1, 7) AS periode
			// 	FROM ".$dbname.".kebun_pakai_material_vw
			// 	WHERE kodeorg = '".$blok."' AND kodebarang != '' AND tanggal LIKE '".$tahun."%'
			// 	GROUP BY SUBSTR(tanggal, 1, 7), kodebarang
			// ";
			$str = "
				SELECT SUM(kwantitas) AS kwantitas, kodebarang, SUM(kwantitasha) AS kwantitasha, LEFT(tanggal, 7) AS periode
				FROM ".$dbname.".kebun_pakai_material_vw
				WHERE kodeorg = '".$blok."' AND kodebarang != '' AND LEFT(tanggal, 7) BETWEEN '".$periodeawal."' AND '".$periodeakhir."'
				GROUP BY LEFT(tanggal, 7), kodebarang
			";
			$query = fetchdata($str);
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$data[$bar['periode']][] = $bar['kodebarang'];
				$luasppuk[$bar['periode']][] = $bar['kwantitasha'];
				$jmlppukx[$bar['periode']][] = $bar['kwantitas'];
			}

			#get kolom max
			$str = "SELECT nilaiawal FROM ".$dbname.".bi_5warnalaporan WHERE idlap = '".$detaillaporan."' ORDER BY nilaiawal DESC LIMIT 1";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$rotstd = $bar['nilaiawal'];

			$result = "
				<hr>
				<table cellpading=1 cellspacing=1 border=0 class=sortable width=100%>
					<thead>
						<tr align=center>
							<td width=20%>".$_SESSION['lang']['periode']."</td>
							<td width=20%>".$_SESSION['lang']['namabarang']."</td>
							<td width=20%>".$_SESSION['lang']['luas']."</td>
							<td width=20%>".$_SESSION['lang']['kwantitas']."</td>
							<!-- <td width=20%>".$_SESSION['lang']['jumlah']."</td>
							<td width=20%>".$_SESSION['lang']['pencapaian']." (%)</td> -->
						</tr>
					</thead>
					<tbody>
			";

			if (count($query) >= 1) {
				foreach ($arrper as $per) {
					$rows = count($data[$per]) == 0 ? 1 : count($data[$per]);

					$result .= "
						<tr class=rowcontent align=center>
							<td rowspan=".$rows.">".$per."</td>
							<td align=right>".$nmBar[$data[$per][0]]."</td>
							<td align=right>".(count($data[$per]) > 0 ? number_format($luasppuk[$per][0], 2) : "")."</td>
							<td align=right>".(count($data[$per]) > 0 ? number_format($jmlppukx[$per][0], 2) : "")."</td>
							<!-- <td align=right>".(count($data[$per]) > 0 ? number_format(fixnan($data[$per][0] / $rotstd * 100, 2), 2) : "")."</td> -->
						</tr>
					";
					
					if ($rows > 1) {
						for ($i = 1; $i < $rows; $i++) {
							$result .= "
								<tr class=rowcontent align=center>
									<td align=right>".$nmBar[$data[$per][$i]]."</td>
									<td align=right>".number_format($luasppuk[$per][$i], 2)."</td>
									<td align=right>".(count($data[$per]) > 0 ? number_format($jmlppukx[$per][$i], 2) : "")."</td>
									<!-- <td align=right>".(count($data[$per]) > 0 ? number_format(fixnan($data[$per][$i] / $rotstd * 100, 2), 2) : "")."</td> -->
								</tr>
							";
						}
					}

					$tluasppuk += array_sum($luasppuk[$per]);
					$tjmlppukx += array_sum($jmlppukx[$per]);
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
				$data = ($tluasppuk / $jmlppuk) / $jumbul;
				if (my_operator($data, $row['awal'], $row['opawal']) && my_operator($data, $row['akhir'], $row['opakhir'])) {
					$bgcol = $key;
				}
			}
			
			$warna = $bgcol;

			if (count($query) < 1) {
				$result .= "
					<tr class=rowcontent align=center>
						<td align=center colspan=5>".$_SESSION['lang']['datanotfound']."</td>
					</tr>
				";
			} else {
				$result .= "
					<tr class=rowcontent align=center style='font-weight:bold;'>
						<td colspan=2 align=center>".$_SESSION['lang']['total']."</td>
						<td align=right>".number_format($tluasppuk, 2)."</td>
						<td align=right>".number_format($tjmlppukx, 2)."</td>
						<!-- <td align=right>".number_format(fixnan(($tluasppuk / $jmlppuk) / ($rotstd * $jumbul) * 100, 2))."</td> -->
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

			// $tglakhir = tglakhir(tanggalsystemn($periodeakhir).'-01');
			// $jumbul = intval(substr(tanggalsystemn($periodeakhir), 5, 2)) - intval(substr(tanggalsystemn($periodeawal), 5, 2));
			// $jumbul = $jumbul + 1;

			/*
				$str = "SELECT max(tanggal) as tanggal, blok, angka FROM ".$dbname.".kebun_pusingan_vw 
				WHERE tanggal LIKE '".$periodeakhir."%' AND unit = '".$kebun."' GROUP BY blok ORDER BY tanggal DESC";
				*/
			$str = "
				SELECT SUM(jurnal) AS jurnal, kodeorg, kodebarang, SUBSTR(tanggal, 1, 7)
				FROM ".$dbname.".kebun_pakai_material_vw
				WHERE 1 = 1 ".forKebunAll($kbnarr, 'LEFT(kodegudang, 4)', 'in')." AND LEFT(tanggal, 7) BETWEEN '".$periodeawal."' AND '".$periodeakhir."'
				GROUP BY kodeorg
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				// $bloklist[$bar['blok']] = $bar['blok'];
				$bloklist[$bar['kodeorg']] = $bar['kodeorg'];
				// $data[$bar['kodeorg']] = fixnan($bar['luaspanen'] / $bar['luasproduksi'] / $jumbul);
				$data[$bar['kodeorg']] = $bar['jurnal'];
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

			$str = "SELECT fill FROM ".$dbname.".bi_5warna WHERE tipe = '".$firstPT."'";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$firstColor = $bar['fill'];

			$str = "SELECT count(idsvg) as countsvg FROM ".$dbname.".bi_map_pt WHERE tipepeta = '".$firstPT."' ".forKebunAll($kbnarr, 'unit', 'in')."";
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
			$ket[1] = "none";
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
