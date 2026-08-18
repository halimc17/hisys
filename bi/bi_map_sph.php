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
				exit("error : Periode awal harus lebih kecil dari periode akhir.");
			}

			$str = "select * from ".$dbname.".bi_5warnalaporan where idlap = '".$detaillaporan."'";
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

				if ($bar['opawal'] && $bar['opakhir'] == 'NULL') {
					$arrWarnaNull[$bar['warna']]['NULL'] = $bar['keterangan'];
				}
			}

			#ambil tanggal terakhir di param periode
			$perdepan = periodeberikut($periodeakhir);
			$tglawaldpn = $perdepan.'-01';
			list($year, $month, $day) = explode('-',  $tglawaldpn);
			$tglakhir = date("Y-m-d", mktime(0, 0, 0, $month, $day - 1, $year));

			// $str = "select luasareaproduktif,jumlahpokok,kodeorg from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'left(kodeorg,4)', 'in')."";
			$str = "
				SELECT SUM(luasareaproduktif) AS luasareaproduktif, SUM(jumlahpokok) AS jumlahpokok, indukblok
				FROM ".$dbname.".setup_blok
				WHERE 1 = 1 ".forKebunAll($kbnarr, 'LEFT(kodeorg, 4)', 'in')."
				GROUP BY indukblok
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				// $pkk[$bar['kodeorg']] = $bar['jumlahpokok'];
				// $luas[$bar['kodeorg']] = $bar['luasareaproduktif'];
				// $bloklist[$bar['kodeorg']] = $bar['kodeorg'];
				$pkk[$bar['indukblok']] = $bar['jumlahpokok'];
				$luas[$bar['indukblok']] = $bar['luasareaproduktif'];
				$bloklist[$bar['indukblok']] = $bar['indukblok'];
			}

			foreach ($bloklist as $listblok) {
				@$data[$listblok] = @$pkk[$listblok] / @$luas[$listblok];
			}

			$arrList = array();
			$countlist = -1;
			foreach ($bloklist as $listblok) {
				@$data[$listblok] = @$pkk[$listblok] / @$luas[$listblok];
				if ($data[$listblok] != 0 or $data[$listblok] != '') {
					if (isset($arrWarna)) {
						foreach ($arrWarna as $key => $row) {
							if (my_operator($data[$listblok], $row['awal'], $row['opawal']) && my_operator($data[$listblok], $row['akhir'], $row['opakhir'])) {
								$countlist++;
								//$str2 = "select idsvg from ".$dbname.".bi_map_pt where keterangan like '".$listblok."%'";
								$str2 = "select idsvg from ".$dbname.".bi_map_pt where keterangan like '".$listblok."%' and tipepeta='".$firstPT."'";
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
				} else {
					foreach ($arrWarnaNull as $key => $row) {
						$countlist++;
						//$str2 = "select idsvg from ".$dbname.".bi_map_pt where keterangan like '".$listblok."%'";
						$str2 = "select idsvg from ".$dbname.".bi_map_pt where keterangan like '".$listblok."%' and tipepeta='".$firstPT."'";
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

			$divLegend = "";
			if (isset($arrWarna)) {
				$divLegend .= "<div style='padding-top:5px;'>
				<b>".$_SESSION['lang']['keterangan']."</b>
				</div>
				<table cellpading=1 cellspacing=3 style=width:100%>
				<tbody>";
				foreach ($arrWarna as $key => $row) {
					$divLegend .= "<tr>
						<td bgcolor='".$key."' style='width:20px;'></td>
						<td style='text-align:center'>".$row['opawal']." ".$row['awal']."</td>
						<td style='text-align:center'>&</td>
						<td style='text-align:center'>".$row['opakhir']." ".$row['akhir']."</td>
					<tr>";
				}
				$divLegend .= "</tbody>
			</table>";
			}

			echo json_encode($arrList)."####".showLegend($detaillaporan);
			break;
		case 'detail':
			break;
		case 'globalreport':
			if ($periodeawal == $periodeakhir) {
				$periode = $periodeawal;
			} else {
				$periode = $periodeawal." ".$_SESSION['lang']['sd']." ".$periodeakhir;
			}

			$str = "select namalaporan from ".$dbname.".bi_5laporan where idlap='".$detaillaporan."'";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$namalaporan = $bar['namalaporan'];

			$str = "select * from ".$dbname.".bi_5warnalaporan where idlap = '".$detaillaporan."'";
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

			#ambil tanggal terakhir di param periode
			$perdepan = periodeberikut($periodeakhir);
			$tglawaldpn = $perdepan.'-01';
			list($year, $month, $day) = explode('-',  $tglawaldpn);
			$tglakhir = date("Y-m-d", mktime(0, 0, 0, $month, $day - 1, $year));

			// $str = "select luasareaproduktif,jumlahpokok,kodeorg from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'left(kodeorg,4)', 'in')." ";
			$str = "
				SELECT SUM(luasareaproduktif) AS luasareaproduktif, SUM(jumlahpokok) AS jumlahpokok, indukblok
				FROM ".$dbname.".setup_blok
				WHERE 1 = 1 ".forKebunAll($kbnarr, 'LEFT(kodeorg, 4)', 'in')."
				GROUP BY indukblok
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				// $pkk[$bar['kodeorg']] = $bar['jumlahpokok'];
				// $luas[$bar['kodeorg']] = $bar['luasareaproduktif'];
				// $bloklist[$bar['kodeorg']] = $bar['kodeorg'];
				$pkk[$bar['indukblok']] = $bar['jumlahpokok'];
				$luas[$bar['indukblok']] = $bar['luasareaproduktif'];
				$bloklist[$bar['indukblok']] = $bar['indukblok'];
			}

			$datas = array();
			$totalfill = 0;
			foreach ($bloklist as $listblok) {
				@$data[$listblok] = @$pkk[$listblok] / @$luas[$listblok];
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

			$str = "select fill from ".$dbname.".bi_5warna where tipe='".$firstPT."'";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$firstColor = $bar['fill'];

			$str = "select count(idsvg) as countsvg from ".$dbname.".bi_map_pt where tipepeta='".$firstPT."' ".forKebunAll($kbnarr, 'unit', 'in')." ";
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
				$ket[$no] = $row['keterangan'];
				$arrColor[$no] = $key;
				$no++;
			}

			$graph = new PieGraph(670, 330);
			$graph->ClearTheme();
			$graph->SetShadow();

			$graph->title->Set($namalaporan);
			// $graph->title->SetFont(FF_VERDANA,FS_BOLD,12); 
			$graph->title->SetColor("darkblue");
			$graph->subtitle->Set("".$_SESSION['lang']['periode']." : ".$periode);

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
