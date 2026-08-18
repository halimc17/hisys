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

			##sum ha pnn / luas blok 

			$tglakhir = tglakhir($periodeakhir.'-01');
			// $jumbul = intval(substr($periodeakhir, 5, 2)) - intval(substr($periodeawal, 5, 2));
			// $jumbul = $jumbul + 1;
			$jumbul = count(month_inbetween($periodeawal, $periodeakhir));

			// $str = "select kodeorg from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'left(kodeorg,4)', 'in')."";
			$str = "select indukblok from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'left(kodeorg,4)', 'in')."";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$bloklist[$bar['indukblok']] = $bar['indukblok'];
			}

			/*
			$str = "select max(tanggal) as tanggal, blok, angka from ".$dbname.".kebun_pusingan_vw 
			where tanggal like '".$periodeakhir."%' and unit = '".$kebun."' group by blok order by tanggal desc";
			*/
			$str = "
				select sum(luaspanen) as luaspanen,luasproduksi,blok
				from ".$dbname.".kebun_rekappnn
				where 1=1 ".forKebunAll($kbnarr, 'left(divisi,4)', 'in')." and tanggal between '".$periodeawal."-01' and '".$tglakhir."'
				group by blok
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				// $bloklist[$bar['blok']]=$bar['blok'];
				$data[$bar['blok']] = $bar['luaspanen'] / $bar['luasproduksi'] / $jumbul;
			}
			
			$arrList = array();
			$countlist = -1;
			foreach ($bloklist as $listblok) {
				//((rptjh+rpenm/prodreal))/(bgtrp7+bgt6/prodtbs)
				if ($data[$listblok] != '') {
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
			$str = "select * from ".$dbname.".bi_map_pt where idsvg = '".$idsvg."'";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$expKtr = explode('##', $bar['keterangan']);
			$blok = $expKtr[0];

			// $jumbul = intval(substr($periodeakhir, 5, 2)) - intval(substr($periodeawal, 5, 2));
			// $jumbul = $jumbul + 1;
			$arrper = month_inbetween($periodeawal, $periodeakhir);
			$jumbul = count($arrper);
			$tahun = substr($periodeakhir, 0, 4);

			$str = "
				select sum(luaspanen) as luaspanen,luasproduksi,substr(tanggal,1,7) as periode
				from ".$dbname.".kebun_rekappnn
				where blok='".$blok."' and LEFT(tanggal, 7) BETWEEN '".$periodeawal."' AND '".$periodeakhir."'
				group by substr(tanggal,1,7)
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$data[$bar['periode']] = $bar['luaspanen'] / $bar['luasproduksi'];
				$luaspnn[$bar['periode']] = $bar['luaspanen'];
				$luasprod += $bar['luasproduksi'];
				$luas[$bar['periode']] = $bar['luaspanen'];
			}

			#get kolom max
			$str = "select nilaiawal from ".$dbname.".bi_5warnalaporan where idlap = '".$detaillaporan."' order by nilaiawal desc limit 1";
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
							<td width=20%>".$_SESSION['lang']['luaspanen']."</td>
							<td width=20%>".$_SESSION['lang']['rotasi']."</td>
							<td width=20%>".$_SESSION['lang']['rotasi']." ".$_SESSION['lang']['standard']."</td>
							<td width=20%>".$_SESSION['lang']['pencapaian']." (%)</td>
						</tr>
					</thead>
					<tbody>
			";

			foreach ($arrper as $per) {
				$result .= "
					<tr class=rowcontent align=center>
						<td>".$per."</td>
						<td align=right>".number_format($luas[$per], 2)."</td>
						<td align=right>".number_format($data[$per], 2)."</td>
						<td align=right>".number_format($rotstd, 2)."</td>
						<td align=right>".number_format($data[$per] / $rotstd * 100, 2)."</td>
					</tr>
				";

				$tluaspnn += $luaspnn[$per];
			}

			//warna
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
			}
			foreach ($arrWarna as $key => $row) {
				$data = $tluaspnn / $luasprod / $jumbul;
				if (my_operator($data, $row['awal'], $row['opawal']) && my_operator($data, $row['akhir'], $row['opakhir'])) {
					$bgcol = $key;
				}
			}
			$warna = $bgcol;

			$result .= "
						<tr class=rowcontent align=center style='font-weight:bold'>
							<td>".$_SESSION['lang']['total']."</td>
							<td align=right>".number_format($tluaspnn, 2)."</td>
							<td align=right style=background-color:".$warna.">".number_format($tluaspnn / $luasprod / $jumbul, 2)."</td>
							<td align=right>".number_format($rotstd * $jumbul, 2)."</td>
							<td align=right>".number_format(($tluaspnn / $luasprod / $jumbul) / ($rotstd * $jumbul) * 100, 2)."</td>
						</tr>
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

			##sum ha pnn / luas blok

			$tglakhir = tglakhir($periodeakhir.'-01');
			// $jumbul = intval(substr($periodeakhir, 5, 2)) - intval(substr($periodeawal, 5, 2));
			// $jumbul = $jumbul + 1;
			$jumbul = count(month_inbetween($periodeawal, $periodeakhir));

			/*
			$str = "select max(tanggal) as tanggal, blok, angka from ".$dbname.".kebun_pusingan_vw 
			where tanggal like '".$periodeakhir."%' and unit = '".$kebun."' group by blok order by tanggal desc";
			*/
			$str = "
				select sum(luaspanen) as luaspanen,luasproduksi,blok
				from ".$dbname.".kebun_rekappnn
				where 1=1 ".forKebunAll($kbnarr, 'left(divisi,4)', 'in')." and tanggal between '".$periodeawal."-01' and '".$tglakhir."'
				group by blok
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
