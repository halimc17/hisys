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

			$str = "SELECT * FROM ".$dbname.".bi_5warnalaporan WHERE idlap = '".$detaillaporan."'";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo " Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$arrWarna = array();
			while ($bar = $res->fetch()) {
				$arrWarna[$bar['warna']]['opawal'] = $bar['opawal'];
				$arrWarna[$bar['warna']]['awal'] = $bar['nilaiawal'];
				$arrWarna[$bar['warna']]['opakhir'] = $bar['opakhir'];
				$arrWarna[$bar['warna']]['akhir'] = $bar['nilaiakhir'];
			}

			#ambil tanggal terakhir di param periode
			// $perdepan = periodeberikut($periodeakhir);
			// $tglawaldpn = $perdepan.'-01';
			// list($year, $month, $day) = explode('-',  $tglawaldpn);
			// $tglakhir = date("Y-m-d", mktime(0, 0, 0, $month, $day - 1, $year));

			// $bgtawal = substr($periodeawal, 5, 2);
			// $bgtakhir = substr($periodeakhir, 5, 2);

			$listPeriode = month_inbetween($periodeawal, $periodeakhir);
			$blnPerThn = array();
			foreach ($listPeriode as $key => $value) {
				$blnPerThn[substr($value, 0, 4)][] = substr($value, 5, 2);
			}

			// $addstr = "(";
			// for ($i = intval($bgtawal); $i <= intval($bgtakhir); $i++) {
			// 	if ($i < 10) {
			// 		$isi = "kg0".$i;
			// 	} else {
			// 		$isi = "kg".$i;
			// 	}
			// 	if ($i < intval($bgtakhir)) {
			// 		$addstr .= $isi."+";
			// 	} else {
			// 		$addstr .= $isi;
			// 	}
			// }
			// $addstr .= ")";
			
			$bloklist = array();
			$databgt = array();
			// $str = "
			// 	SELECT ".$addstr." AS bgt, kodeblok
			// 	FROM ".$dbname.".bgt_produksi_kbn_kg_vw
			// 	WHERE 1 = 1 ".forKebunAll($kbnarr, 'kodeunit', 'in')." AND tahunbudget = '".substr($periodeakhir, 0, 4)."'
			// ";
			$str = "
				SELECT kgsetahun AS bgt, kodeblok, tahunbudget
				FROM ".$dbname.".bgt_produksi_kbn_kg_vw
				WHERE 1 = 1 ".forKebunAll($kbnarr, 'kodeunit', 'in')." AND tahunbudget BETWEEN '".substr($periodeawal, 0, 4)."' AND '".substr($periodeakhir, 0, 4)."'
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo " Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				// @$databgt[$bar['kodeblok']] += $bar['bgt'];
				@$databgt[$bar['kodeblok']] += ($bar['bgt'] / 12 * count($blnPerThn[$bar['tahunbudget']]));
				// $bloklist[$bar['kodeblok']]=$bar['kodeblok'];
			}

			// replace blok induk method
			if (count($databgt) > 0) {
				// $str=" SELECT indukblok,kodeorg FROM ".$dbname.".setup_blok WHERE kodeorg in ('".implode("','",array_keys($databgt))."')";
				$str = "SELECT indukblok, kodeorg FROM ".$dbname.".setup_blok WHERE 1 = 1 ".forKebunAll($kbnarr, 'SUBSTR(kodeorg, 1, 4)', 'in');
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					echo " Gagal: ".$e->getMessage();
				}
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$databgtReplace = array();
				$bloklist = array();
				while ($bar = $res->fetch()) {
					$databgtReplace[$bar['indukblok']] += $databgt[$bar['kodeorg']];
					$bloklist[$bar['indukblok']] = $bar['indukblok'];
				}

				$databgt = $databgtReplace;
			}

			$str = "
				SELECT SUM(kgwb) AS jjg, blok
				FROM ".$dbname.".kebun_spb_vw2 
				WHERE 1 = 1 ".forKebunAll($kbnarr, 'kodeorg', 'in')." AND blok IN ('".implode("','", array_keys($bloklist))."') AND LEFT(tanggalpanen, 7) BETWEEN '".$periodeawal."' AND '".$periodeakhir."'
				GROUP BY blok
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo " Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$dataspb[$bar['blok']] += $bar['jjg'];
				$bloklist[$bar['blok']] = $bar['blok'];
			}

			#masalahnya ada 2 peta untuk :
			/*
				SELECT idsvg FROM owlpdo.bi_map_pt WHERE keterangan LIKE 'DUKE05063C%';
			*/
			$arrList = array();
			$countlist = -1;
			foreach ($bloklist as $listblok) {
				$data[$listblok] = @($dataspb[$listblok] / $databgt[$listblok] * 100);
				if (isset($arrWarna)) {
					foreach ($arrWarna as $key => $row) {
						if (my_operator($data[$listblok], $row['awal'], $row['opawal']) && my_operator($data[$listblok], $row['akhir'], $row['opakhir'])) {
							$countlist++;
							$str2 = "SELECT idsvg FROM ".$dbname.".bi_map_pt WHERE keterangan LIKE '".$listblok."%' AND tipepeta = '".$firstPT."'";
							//$str2 = "SELECT idsvg FROM ".$dbname.".bi_map_pt WHERE keterangan LIKE '".$listblok."%'";
							try {
								$res2 = $owlPDO->query($str2);
							} catch (PDOException $e) {
								echo " Gagal: ".$e->getMessage();
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
				echo " Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$expKtr = explode('##', $bar['keterangan']);
			$blok = $expKtr[0];

			#bentuk tahun
			$tahun = substr($periodeakhir, 0, 4);
			$arrper = month_inbetween($periodeawal, $periodeakhir);

			// $luasreal=makeOption($dbname,'setup_blok','kodeorg,luasareaproduktif');
			// $whbgt = "tahunbudget='".$tahun."'";
			// $luasbgtChild = makeOption($dbname, 'bgt_blok', 'kodeblok,hathnini', $whbgt);
			$str = "
				SELECT tahunbudget, SUM(hathnini) AS hathnini
				FROM ".$dbname.".bgt_blok
				WHERE tahunbudget IN ('".substr($periodeawal, 0, 4)."', '".substr($periodeakhir, 0, 4)."') AND kodeblok LIKE '".$blok."%'
				GROUP BY tahunbudget
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo " Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$luasbgtChild = array();
			while ($bar = $res->fetch()) {
				// $luasbgtChild[$bar['tahunbudget']][$bar['kodeblok']] = $bar['hathnini'];
				$luasbgt[$bar['tahunbudget']] += $bar['hathnini'];
			}

			// replace blok induk method
			$str = "SELECT indukblok, kodeorg, luasareaproduktif FROM ".$dbname.".setup_blok WHERE indukblok = '".$blok."'";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo " Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$databgtReplace = array();
			$bloklist = array();
			while ($bar = $res->fetch()) {
				$luasreal[$bar['indukblok']] += $bar['luasareaproduktif'];
				// $luasbgt[$bar['indukblok']] += $luasbgtChild[$bar['kodeorg']];
				$bloklist[] = $bar['kodeorg'];
			}

			$str = "
				SELECT LEFT(tanggalpanen, 7) AS periode, SUM(jjg) AS jjg, SUM(kgwb) AS kg
				FROM ".$dbname.".kebun_spb_vw2
				WHERE blok = '".$blok."' AND LEFT(tanggalpanen, 7) BETWEEN '".$periodeawal."' AND '".$periodeakhir."'
				GROUP BY periode
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo " Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$tonreal[$bar['periode']] = $bar['kg'];
				$jjgkirim[$bar['periode']] = $bar['jjg'];
			}
			
			$str = "
				SELECT *
				FROM ".$dbname.".bgt_produksi_kbn_kg_vw
				WHERE kodeblok IN ('".implode("', '", $bloklist)."') AND tahunbudget IN ('".substr($periodeawal, 0, 4)."', '".substr($periodeakhir, 0, 4)."')
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo " Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				// for ($i = 1; $i <= 12; $i++) {
				// 	if ($i < 10) {
				// 		$ii = "0".$i;
				// 	} else {
				// 		$ii = $i;
				// 	}

				// 	@$tonbgt[$tahun.'-'.$ii] += ($bar['kg'.$ii]);
				// }
				foreach ($arrper as $per) {
					$thn = substr($per, 0, 4);
					$bln = substr($per, 5, 2);
					if ($thn == $bar['tahunbudget']) $tonbgt[$per] += $bar['kg'.$bln];
				}
			}

			##restan % = jumlah restan akhir bulan / jjg panen periode terpilih
			##SUM panen - (SUM kirim *dr spbvw + SUM afkir)

			// $str = "SELECT * FROM ".$dbname.".kebun_rekappnn WHERE blok = '".$blok."' AND tanggal LIKE '".$tahun."%' ";
			// try {
			// 	$res = $owlPDO->query($str);
			// } catch (PDOException $e) {
			// 	echo " Gagal: ".$e->getMessage();
			// }
			// $res->setFetchMode(PDO::FETCH_ASSOC);
			// while ($bar = $res->fetch()) {
			// 	$jjgpanen[substr($bar['tanggal'], 0, 7)] += $bar['jjgpanen'];
			// 	$jjgafkir[substr($bar['tanggal'], 0, 7)] += $bar['jjgafkir'];
			// }

			#ambil jjg restant bulan lalu
			// $tglawal = $periodeawal.'-01';
			// $tglkmrn = tglkemarin($tglawal);
			// $perresk = periodelalu($periodeawal);

			// $str=" select sum(jjgpanen) as jjgpanen, sum(jjgafkir) as jjgafkir from ".$dbname.".kebun_rekappnn where 
			// 		blok='".$blok."' and tanggal <='".$tglkmrn."' ";
			$str = "
				SELECT LEFT(tanggal, 7) AS periode, SUM(jjgpanen) AS jjgpanen, SUM(jjgafkir) AS jjgafkir
				FROM ".$dbname.".kebun_rekappnn
				WHERE blok = '".$blok."' AND LEFT(tanggal, 7) BETWEEN '".$periodeawal."' AND '".$periodeakhir."'
				GROUP BY periode
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo " Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$jjgpanen[$bar['periode']] = $bar['jjgpanen'];
				$jjgafkir[$bar['periode']] = $bar['jjgafkir'];
			}

			#ambil kirim bulan lalu
			// $str = "select sum(jjg) as jjg from ".$dbname.".kebun_spb_vw where
			// 		blok = '".$blok."' and tanggal <='".$tglkmrn."'";
			// $str = "
			// 	SELECT LEFT(tanggalpanen, 7) AS periode, SUM(jjg) AS jjg
			// 	FROM ".$dbname.".kebun_spb_vw2
			// 	WHERE blok = '".$blok."' AND LEFT(tanggalpanen, 7) BETWEEN '".$periodeawal."' AND '".$periodeakhir."'
			// 	GROUP BY periode
			// ";
			// try {
			// 	$res = $owlPDO->query($str);
			// } catch (PDOException $e) {
			// 	echo " Gagal: ".$e->getMessage();
			// }
			// $res->setFetchMode(PDO::FETCH_ASSOC);
			// while ($bar = $res->fetch()) {
			// 	$jjgkirim1[$bar['periode']] = $bar['jjg'];
			// }

			//rumus untuk restant bulan lalu
			// $resbl = $jjgpanen1 - ($jjgafkir1 + $jjgkirim1);

			#spb
			$result = "
				<hr>
				<table cellpading=1 cellspacing=1 border=0 class=sortable width=100%>
					<thead>
						<tr align=center>
							<td rowspan=2 width=10%>".$_SESSION['lang']['periode']."</td>
							<td colspan=2 width=15%>".$_SESSION['lang']['restan']."</td>
							<td colspan=2>".$_SESSION['lang']['realisasi']."</td>
							<td colspan=2>".$_SESSION['lang']['budget']."</td>
							<td rowspan=2 width=15%>".$_SESSION['lang']['pencapaian']." (%)</td>
						</tr>
						<tr align=center>
							<td>Jjg</td>
							<td>%</td>
							<td>Kg</td>
							<td>Ton / Ha</td>
							<td>Kg</td>
							<td>Ton / Ha</td>
						</tr>
					</thead>
					<tbody>
			";

			$arrper = month_inbetween($periodeawal, $periodeakhir);
			foreach ($arrper as $per) {
				// $restan = ($resbl + $jjgpanen[$per]) - ($jjgkirim[$per] + $jjgafkir[$per]);
				$restan = $jjgpanen[$per] - ($jjgkirim[$per] + $jjgafkir[$per]);
				@$perres = $restan / $jjgpanen[$per] * 100;
				$trestan += $restan[$per];

				$result .= "
					<tr class=rowcontent align=center>
						<td>".$per."</td>
						<td align=right>".number_format($restan)."</td>
						<td align=right>".number_format(fixnan(($restan / $jjgpanen[$per] * 100)), 2)."</td>
						<td align=right>".number_format($tonreal[$per], 2)."</td>
						<td align=right>".number_format(fixnan(($tonreal[$per] / $luasreal[$blok] / 1000)), 2)."</td>
						<td align=right>".number_format($tonbgt[$per], 2)."</td>
						<td align=right>".number_format(fixnan(($tonbgt[$per] / $luasbgt[substr($per, 0, 4)] / 1000)), 2)."</td>
						<td align=right>".number_format(fixnan(($tonreal[$per] / $tonbgt[$per] * 100)), 2)."</td>	
					</tr>
				";
				
				// $resbl = $restan;
				$totalRestan += $restan;
				$totalJjgPanen += $jjgpanen[$per];
				$totalJjgKirim += $jjgkirim[$per];
				$totalLuasReal += $luasreal[$blok];
				$totalLuasBgt += $luasbgt[substr($per, 0, 4)];
				$ttonreal += $tonreal[$per];
				$ttonbgt += $tonbgt[$per];
			}

			//warna
			$str = "SELECT * FROM ".$dbname.".bi_5warnalaporan WHERE idlap = '".$detaillaporan."'";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo " Gagal: ".$e->getMessage();
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
				$data = @(($ttonreal / $ttonbgt) * 100);
				if (my_operator($data, $row['awal'], $row['opawal']) && my_operator($data, $row['akhir'], $row['opakhir'])) {
					$bgcol = $key;
				}
			}

			$warna = $bgcol;

			$result .= "
						<tr class=rowcontent align=center style=font-weight:bold;>
							<td align=center>".$_SESSION['lang']['total']."</td>
							<td align=right>".$totalRestan."</td>
							<td align=right>".number_format(fixnan(($totalRestan / $totalJjgPanen * 100)), 2)."</td>
							<td align=right>".number_format($ttonreal, 2)."</td>
							<td align=right>".number_format(fixnan($ttonreal / $totalLuasReal / 1000), 2)."</td>
							<td align=right>".number_format($ttonbgt, 2)."</td>
							<td align=right>".number_format(fixnan($ttonbgt / $totalLuasBgt / 1000), 2)."</td>
							<td align=right style=background-color:".$warna.">
								<b>".number_format(fixnan($ttonreal / $ttonbgt * 100), 2)."</b>
							</td>
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

			$str = "SELECT namalaporan FROM ".$dbname.".bi_5laporan WHERE idlap = '".$detaillaporan."'";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo " Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$namalaporan = $bar['namalaporan'];

			$str = "SELECT * FROM ".$dbname.".bi_5warnalaporan WHERE idlap = '".$detaillaporan."'";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo " Gagal: ".$e->getMessage();
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
			// $perdepan = periodeberikut($periodeakhir);
			// $tglawaldpn = $perdepan.'-01';
			// list($year, $month, $day) = explode('-',  $tglawaldpn);
			// $tglakhir = date("Y-m-d", mktime(0, 0, 0, $month, $day - 1, $year));

			// $bgtawal = substr($periodeawal, 5, 2); //exit("Error:$bgtawal");
			// $bgtakhir = substr($periodeakhir, 5, 2); //exit("Error:$bgtawal");

			$listPeriode = month_inbetween($periodeawal, $periodeakhir);
			$blnPerThn = array();
			foreach ($listPeriode as $key => $value) {
				$blnPerThn[substr($value, 0, 4)][] = substr($value, 5, 2);
			}

			// $addstr = "(";
			// for ($i = intval($bgtawal); $i <= intval($bgtakhir); $i++) {
			// 	if ($i < 10) {
			// 		$isi = "kg0".$i;
			// 	} else {
			// 		$isi = "kg".$i;
			// 	}
			// 	if ($i < intval($bgtakhir)) {
			// 		$addstr .= $isi."+";
			// 	} else {
			// 		$addstr .= $isi;
			// 	}
			// }
			// $addstr .= ")";

			$bloklist = array();
			$databgt = array();
			// $str = "
			// 	SELECT ".$addstr." AS bgt, kodeblok
			// 	FROM ".$dbname.".bgt_produksi_kbn_kg_vw 
			// 	WHERE 1 = 1 ".forKebunAll($kbnarr, 'kodeunit', 'in')." AND tahunbudget = '".substr($periodeakhir, 0, 4)."'
			// ";
			$str = "
				SELECT kgsetahun AS bgt, kodeblok, tahunbudget
				FROM ".$dbname.".bgt_produksi_kbn_kg_vw
				WHERE 1 = 1 ".forKebunAll($kbnarr, 'kodeunit', 'in')." AND tahunbudget BETWEEN '".substr($periodeawal, 0, 4)."' AND '".substr($periodeakhir, 0, 4)."'
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo " Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				// @$databgt[$bar['kodeblok']] += $bar['bgt'];
				@$databgt[$bar['kodeblok']] += ($bar['bgt'] / 12 * count($blnPerThn[$bar['tahunbudget']]));
				// $bloklist[$bar['kodeblok']]=$bar['kodeblok'];
			}

			// replace blok induk method
			if (count($databgt) > 0) {
				// $str=" SELECT indukblok,kodeorg FROM ".$dbname.".setup_blok WHERE kodeorg in ('".implode("','",array_keys($databgt))."')";
				$str = "SELECT indukblok, kodeorg FROM ".$dbname.".setup_blok WHERE 1 = 1 ".forKebunAll($kbnarr, 'SUBSTR(kodeorg, 1, 4)', 'in');
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					echo " Gagal: ".$e->getMessage();
				}
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$databgtReplace = array();
				$bloklist = array();
				while ($bar = $res->fetch()) {
					$databgtReplace[$bar['indukblok']] += $databgt[$bar['kodeorg']];
					$bloklist[$bar['indukblok']] = $bar['indukblok'];
				}
				
				$databgt = $databgtReplace;
			}

			$str = "
				SELECT SUM(kgwb) AS jjg, blok
				FROM ".$dbname.".kebun_spb_vw2
				WHERE 1 = 1 ".forKebunAll($kbnarr, 'kodeorg', 'in')." AND blok IN ('".implode("','", array_keys($bloklist))."') AND LEFT(tanggalpanen, 7) BETWEEN '".$periodeawal."' AND '".$periodeakhir."'
				GROUP BY blok
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo " Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$dataspb[$bar['blok']] += $bar['jjg'];
				$bloklist[$bar['blok']] = $bar['blok'];
			}

			$datas = array();
			$totalfill = 0;
			foreach ($bloklist as $listblok) {
				$data[$listblok] = @($dataspb[$listblok] / $databgt[$listblok] * 100);
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
				echo " Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$firstColor = $bar['fill'];

			$str = "SELECT count(idsvg) AS countsvg FROM ".$dbname.".bi_map_pt WHERE tipepeta = '".$firstPT."' ".forKebunAll($kbnarr, 'unit', 'in');
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo " Gagal: ".$e->getMessage();
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
				$label[$no] = $row['count']." ".$_SESSION['lang']['blok']." (%.1f%%)";
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
			$graph->legend->SetPos(0.05, 0.2, 'right', 'top');
			$graph->legend->SetColumns(1);

			$p1 = new PiePlot($data);
			$p1->SetSize(0.35);
			$p1->SetCenter(0.25);
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
