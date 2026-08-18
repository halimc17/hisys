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

			$bgtawal = substr($periodeawal, 5, 2);
			$bgtakhir = substr($periodeakhir, 5, 2);

			$arrPer = month_inbetween($periodeawal, $periodeakhir);
			$jumbul = count($arrPer);
			$blnPerThn = array();
			foreach ($arrPer as $key => $value) {
				$blnPerThn[substr($value, 0, 4)][] = substr($value, 5, 2);
			}

			// $addstr = "(";
			// for ($i = intval($bgtawal); $i <= intval($bgtakhir); $i++) {
			// 	if ($i < 10) {
			// 		$isi = "rp0".$i;
			// 	} else {
			// 		$isi = "rp".$i;
			// 	}
			// 	if ($i < intval($bgtakhir)) {
			// 		$addstr .= $isi."+";
			// 	} else {
			// 		$addstr .= $isi;
			// 	}
			// }
			// $addstr .= ")";

			// $addstrkg = "(";
			// for ($i = intval($bgtawal); $i <= intval($bgtakhir); $i++) {
			// 	if ($i < 10) {
			// 		$isi = "kg0".$i;
			// 	} else {
			// 		$isi = "kg".$i;
			// 	}
			// 	if ($i < intval($bgtakhir)) {
			// 		$addstrkg .= $isi."+";
			// 	} else {
			// 		$addstrkg .= $isi;
			// 	}
			// }
			// $addstrkg .= ")";

			// Get Kode Blok
			$str = "select DISTINCT indukblok from ".$dbname.".setup_blok 
					where 1=1 ".forKebunAll($kbnarr, 'left(kodeorg,4)', 'in')."";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$bloklist[$bar['indukblok']] = $bar['indukblok'];
			}

			// Rp Budget
			// $str = " select ".$addstr." as bgt,kodeorg from ".$dbname.".bgt_budget_detail where 1=1 ".forKebunAll($kbnarr, 'left(kodeorg,4)', 'in')." and tahunbudget='".substr($periodeakhir, 0, 4)."' and left(noakun,3) like '611%' ";
			$str = "
				SELECT SUM(totalsebaranrp) AS bgt, LEFT(kodeorg, 9) AS kodeorg, tahunbudget
				FROM ".$dbname.".bgt_budget_detail
				WHERE 1 = 1 ".forKebunAll($kbnarr, 'LEFT(kodeorg, 4)', 'in')." AND tahunbudget BETWEEN '".substr($periodeawal, 0, 4)."' AND '".substr($periodeakhir, 0, 4)."' AND LEFT(noakun, 3) = '611'
				GROUP BY LEFT(kodeorg, 9), tahunbudget
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				// @$rpbgt6[$bar['kodeorg']] += $bar['bgt'];
				@$rpbgt6[$bar['kodeorg']] += ($bar['bgt'] / 12 * count($blnPerThn[$bar['tahunbudget']]));
				// $bloklist[$bar['kodeorg']]=$bar['kodeorg'];
			}

			// Kg Budget
			// $str = "select ".$addstrkg." as bgt,kodeblok from ".$dbname.".bgt_produksi_kbn_kg_vw where 1=1 ".forKebunAll($kbnarr, 'kodeunit', 'in')." and tahunbudget='".substr($periodeakhir, 0, 4)."'";
			$str = "
				SELECT SUM(kgsetahun) AS bgt, LEFT(kodeblok, 9) AS kodeblok, tahunbudget
				FROM ".$dbname.".bgt_produksi_kbn_kg_vw
				WHERE 1 = 1 ".forKebunAll($kbnarr, 'kodeunit', 'in')." AND tahunbudget IN ('".substr($periodeawal, 0, 4)."', '".substr($periodeakhir, 0, 4)."')
				GROUP BY LEFT(kodeblok, 9), tahunbudget
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				// @$bgtfisik[$bar['kodeblok']] += $bar['bgt'];
				@$bgtfisik[$bar['kodeblok']] += ($bar['bgt'] / 12 * count($blnPerThn[$bar['tahunbudget']]));
				// $bloklist[$bar['kodeblok']]=$bar['kodeblok'];
			}

			########################## prod ##

			$perawalsaldo = str_replace('-', '', substr($periodeawal, 0, 7));
			$perakhirsaldo = str_replace('-', '', substr($periodeakhir, 0, 7));

			//and left(noakun,2) in ('62','61') 

			// Rp Aktual
			// $str = " select sum(jumlah) as jumlah,kodeblok from ".$dbname.".keu_jurnaldt_vw where  kodeblok!='' ".forKebunAll($kbnarr, 'kodeorg', 'in')." 
			// and tanggal between '".$periodeawal."-01' and '".$tglakhir."'  and left(noakun,3) like '611%' group by kodeblok ";
			$str = "
				SELECT SUM(jumlah) AS jumlah, LEFT(kodeblok, 9) AS kodeblok
				FROM ".$dbname.".keu_jurnaldt_vw 
				WHERE kodeblok LIKE '".$blok."%' AND periode BETWEEN '".$periodeawal."' AND '".$periodeakhir."' AND LEFT(noakun, 3) = '611'
				GROUP BY LEFT(kodeblok, 9)
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$rpjurnal6[$bar['kodeblok']] = $bar['jumlah'];
				// $bloklist[$bar['kodeblok']]=$bar['kodeblok'];
			}

			// Kg Aktual
			// $str = "select sum(kgwb) as kgprod, blok as kodeorg from ".$dbname.".kebun_spb_vw where 1=1 ".forKebunAll($kbnarr, 'left(blok,4)', 'in')." 
			// and tanggal between '".$periodeawal."-01' and '".$tglakhir."' group by blok ";
			$str = "
				SELECT t.blok, SUM(t.prop_berat) AS beratbersih_proporsional
				FROM (
					SELECT d.nospb, LEFT(d.blok, 9) AS blok, d.jjg, d.beratbersihtimbangan, (d.jjg / total_jjg.total_jjg) * d.beratbersihtimbangan AS prop_berat
					FROM kebun_spb_vw d
					JOIN (
						SELECT nospb, beratbersihtimbangan, SUM(jjg) AS total_jjg
						FROM kebun_spb_vw
						WHERE 1 = 1 ".forKebunAll($kbnarr, 'LEFT(blok, 4)', 'in')." AND LEFT(tanggalpanen, 7) BETWEEN '".$periodeawal."' AND '".$periodeakhir."'
						GROUP BY nospb
					) AS total_jjg ON d.nospb = total_jjg.nospb
					WHERE 1 = 1 ".forKebunAll($kbnarr, 'LEFT(d.blok, 4)', 'in')." AND LEFT(d.tanggalpanen, 7) BETWEEN '".$periodeawal."' AND '".$periodeakhir."'
				) AS t
				GROUP BY t.blok
				ORDER BY t.blok;
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				// $fisik[$bar['kodeorg']] = $bar['kgprod'];
				// $bloklist[$bar['kodeorg']]=$bar['kodeorg'];
				$fisik[$bar['blok']] = $bar['beratbersih_proporsional'];
			}

			$arrList = array();
			$countlist = -1;
			foreach ($bloklist as $listblok) {
				@$data[$listblok] = ((@$rpjurnal6[$listblok] / @$fisik[$listblok]) / (@$rpbgt6[$listblok] / @$bgtfisik[$listblok])) * 100;
				if ($rpjurnal6[$listblok] != 0 or $fisik[$listblok] != 0) {
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

			$result = "
				<hr>
				<table cellpading=1 cellspacing=1 border=0 class=sortable width=100%>
					<thead>
						<tr align=center>
							<td rowspan=2>".$_SESSION['lang']['periode']."</td>
							<td colspan=2>".$_SESSION['lang']['produksi']."</td>
							<td colspan=2>".$_SESSION['lang']['rupiah']."</td>
							<td colspan=2>".$_SESSION['lang']['rpperkg']."</td>
							<td colspan=2>".$_SESSION['lang']['pencapaian']." (%)</td>
						</tr>
						<tr align=center>
							<td>".$_SESSION['lang']['realisasi']."</td>
							<td>".$_SESSION['lang']['budget']."</td>
							<td>".$_SESSION['lang']['realisasi']."</td>
							<td>".$_SESSION['lang']['budget']."</td>
							<td>".$_SESSION['lang']['realisasi']."</td>
							<td>".$_SESSION['lang']['budget']."</td>
							<td>".$_SESSION['lang']['produksi']."</td>
							<td>".$_SESSION['lang']['rpperkg']."</td>
						</tr>
					</thead>
					<tbody>
			";

			$arrper = month_inbetween($periodeawal, $periodeakhir);
			$tahun = substr($periodeakhir, 0, 4);

			#produksi 
			#Kg Aktual
			// $str = "select sum(kgwb) as kg,sum(jjg) as jjg,substr(tanggal,1,7) as periode from ".$dbname.".kebun_spb_vw where
			// 		blok = '".$blok."' and tanggal like '".$tahun."%'
			// 		group by substr(tanggal,1,7) ";
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
				// $kgreal[$bar['periode']] = $bar['kg'];
				$kgreal[$bar['periode']] = $bar['beratbersih'];
			}

			#Kg Budget
			// $str = "select * from ".$dbname.".bgt_produksi_kbn_kg_vw where 
			// 		kodeblok='".$blok."' and tahunbudget='".$tahun."'";
			$str = "
				SELECT * 
				FROM ".$dbname.".bgt_produksi_kbn_kg_vw 
				WHERE kodeblok LIKE '".$blok."%' AND tahunbudget BETWEEN '".substr($periodeawal, 0, 4)."' AND '".substr($periodeakhir, 0, 4)."'
			";
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
					// $kgbgt[$tahun.'-'.$ii] = $bar['kg'.$ii];
					$kgbgt[$bar['tahunbudget'].'-'.$ii] += ($bar['kg'.$ii]);
				}
			}
			
			#biaya

			#Rp Aktual
			// $str = "select sum(jumlah) as jumlah,periode from ".$dbname.".keu_jurnaldt_vw where
			// 		kodeblok = '".$blok."' and periode like '".$tahun."%' and noakun like '611%'
			// 		group by periode ";
			$str = "
				SELECT SUM(jumlah) AS jumlah, periode
				FROM ".$dbname.".keu_jurnaldt_vw 
				WHERE kodeblok LIKE '".$blok."%' AND periode BETWEEN '".$periodeawal."' AND '".$periodeakhir."' AND LEFT(noakun, 3) = '611'
				GROUP BY periode
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$rpreal[$bar['periode']] = $bar['jumlah'];
			}

			#Rp Budget
			// $str = " select * from ".$dbname.".bgt_budget_detail where 
			// 		kodeorg='".$blok."' and tahunbudget='".$tahun."' and noakun like '611%'  ";
			$str = "
				SELECT *
				FROM ".$dbname.".bgt_budget_detail
				WHERE kodeorg LIKE '".$blok."%' AND tahunbudget IN ('".substr($periodeawal, 0, 4)."', '".substr($periodeakhir, 0, 4)."') AND LEFT(noakun, 3) = '611'
			";
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

					// $rpbgt[$tahun.'-'.$ii] += $bar['rp'.$ii];
					$rpbgt[$bar['tahunbudget'].'-'.$ii] += ($bar['rp'.$ii]);
				}
			}
			$tkgreal 	= 0;
			$tkgbgt 	= 0;
			$trpreal 	= 0;
			$trpbgt 	= 0;
			foreach ($arrper as $per) {
				$result .= "
					<tr class=rowcontent align=center>
						<td>".$per."</td>
						<td align=right>".number_format($kgreal[$per])."</td>
						<td align=right>".number_format($kgbgt[$per])."</td>
						<td align=right>".number_format($rpreal[$per])."</td>
						<td align=right>".number_format($rpbgt[$per])."</td>
						<td align=right>".number_format(fixnan($rpreal[$per] / $kgreal[$per]))."</td>
						<td align=right>".number_format(fixnan($rpbgt[$per] / $kgbgt[$per]))."</td>
						<td align=right>".number_format(fixnan($kgreal[$per] / $kgbgt[$per] * 100), 2)."</td>
						<td align=right>".number_format(fixnan(($rpreal[$per] / $kgreal[$per]) / ($rpbgt[$per] / $kgbgt[$per]) * 100), 2)."</td>
				";
				$tkgreal += $kgreal[$per];
				$tkgbgt += $kgbgt[$per];
				$trpreal += $rpreal[$per];
				$trpbgt += $rpbgt[$per];
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

			// if (!isset($arrWarna))
			foreach ($arrWarna as $key => $row) {
				$data = ($trpreal / $tkgreal) / ($trpbgt / $tkgbgt) * 100;
				if (my_operator($data, $row['awal'], $row['opawal']) && my_operator($data, $row['akhir'], $row['opakhir'])) {
					$bgcol = $key;
				}
			}
			
			$warna = $bgcol;
			
			// $trpbgt_ = ($trpbgt / (boolval($tkgbgt) ? $tkgbgt : 1));
			$result .= "
				<tr class=rowcontent align=center style='font-weight:bold;'>
					<td align=center>".$_SESSION['lang']['total']."</td>
					<td align=right>".number_format($tkgreal)."</td>
					<td align=right>".number_format($tkgbgt)."</td>
					<td align=right>".number_format($trpreal)."</td>
					<td align=right>".number_format($trpbgt)."</td>
					<td align=right>".number_format(fixnan($trpreal / $tkgreal))."</td>
					<td align=right>".number_format(fixnan($trpbgt / $tkgbgt))."</td>
					<td align=right>".number_format(fixnan(($tkgreal / $tkgbgt) * 100), 2)."</td>
					<td align=right style=background-color:".$warna.">".number_format(fixnan(($trpreal / $tkgreal) / ($trpbgt / $tkgbgt) * 100), 2)."</b>
					</td>
					<!-- <td align=right>".number_format($trpreal / (boolval($tkgreal) ? $tkgreal : 1))."</td>
					<td align=right>".number_format($trpbgt / (boolval($tkgbgt) ? $tkgbgt : 1))."</td>
					<td align=right>".number_format(($tkgreal / (boolval($tkgbgt) ? $tkgbgt : 1)) * 100)."</td>
					<td align=right style=background-color:".$warna.">
						<b>".number_format(($trpreal / (boolval($tkgreal) ? $tkgreal : 1)) / (boolval($trpbgt_) ? $trpbgt_ : 1) * 100)."</b>
					</td> -->
				</tr>
			";

			echo $result;
			break;

		case 'globalreport':
			if ($periodeawal == $periodeakhir) {
				$periode = $periodeawal;
			} else {
				$periode = $periodeawal." ".$_SESSION['lang']['sd']." ".$periodeakhir;
			}

			#nama laporan
			$str = "select namalaporan from ".$dbname.".bi_5laporan where idlap='".$detaillaporan."'";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$namalaporan = $bar['namalaporan'];

			#warna
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

			$bgtawal = substr($periodeawal, 5, 2);
			$bgtakhir = substr($periodeakhir, 5, 2);

			$arrPer = month_inbetween($periodeawal, $periodeakhir);
			$jumbul = count($arrPer);
			$blnPerThn = array();
			foreach ($arrPer as $key => $value) {
				$blnPerThn[substr($value, 0, 4)][] = substr($value, 5, 2);
			}

			// $addstr = "(";
			// for ($i = intval($bgtawal); $i <= intval($bgtakhir); $i++) {
			// 	if ($i < 10) {
			// 		$isi = "rp0".$i;
			// 	} else {
			// 		$isi = "rp".$i;
			// 	}
			// 	if ($i < intval($bgtakhir)) {
			// 		$addstr .= $isi."+";
			// 	} else {
			// 		$addstr .= $isi;
			// 	}
			// }
			// $addstr .= ")";


			// $addstrkg = "(";
			// for ($i = intval($bgtawal); $i <= intval($bgtakhir); $i++) {
			// 	if ($i < 10) {
			// 		$isi = "kg0".$i;
			// 	} else {
			// 		$isi = "kg".$i;
			// 	}
			// 	if ($i < intval($bgtakhir)) {
			// 		$addstrkg .= $isi."+";
			// 	} else {
			// 		$addstrkg .= $isi;
			// 	}
			// }
			// $addstrkg .= ")";

			#Rp Budget
			// $str = " select ".$addstr." as bgt,kodeorg from ".$dbname.".bgt_budget_detail where 1=1 ".forKebunAll($kbnarr, 'left(kodeorg,4)', 'in')."
			// and tahunbudget='".substr($periodeakhir, 0, 4)."' and left(noakun,3) like '611%' ";
			$str = "
				SELECT SUM(totalsebaranrp) AS bgt, LEFT(kodeorg, 9) AS kodeorg, tahunbudget
				FROM ".$dbname.".bgt_budget_detail
				WHERE 1 = 1 ".forKebunAll($kbnarr, 'LEFT(kodeorg, 4)', 'in')." AND tahunbudget BETWEEN '".substr($periodeawal, 0, 4)."' AND '".substr($periodeakhir, 0, 4)."' AND LEFT(noakun, 3) = '611'
				GROUP BY LEFT(kodeorg, 9), tahunbudget
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				// @$rpbgt6[$bar['kodeorg']] += $bar['bgt'];
				@$rpbgt6[$bar['kodeorg']] += ($bar['bgt'] / 12 * count($blnPerThn[$bar['tahunbudget']]));
				$bloklist[$bar['kodeorg']] = $bar['kodeorg'];
			}

			#Kg Budget
			// $str = " select ".$addstrkg." as bgt,kodeblok from ".$dbname.".bgt_produksi_kbn_kg_vw where 1=1 ".forKebunAll($kbnarr, 'kodeunit', 'in')."
			// and tahunbudget='".substr($periodeakhir, 0, 4)."' ";
			$str = "
				SELECT SUM(kgsetahun) AS bgt, LEFT(kodeblok, 9) AS kodeblok, tahunbudget
				FROM ".$dbname.".bgt_produksi_kbn_kg_vw
				WHERE 1 = 1 ".forKebunAll($kbnarr, 'kodeunit', 'in')." AND tahunbudget IN ('".substr($periodeawal, 0, 4)."', '".substr($periodeakhir, 0, 4)."')
				GROUP BY LEFT(kodeblok, 9), tahunbudget
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				// @$bgtfisik[$bar['kodeblok']] += $bar['bgt'];
				@$bgtfisik[$bar['kodeblok']] += ($bar['bgt'] / 12 * count($blnPerThn[$bar['tahunbudget']]));
				$bloklist[$bar['kodeblok']] = $bar['kodeblok'];
			}

			########################## prod ##

			$perawalsaldo = str_replace('-', '', substr($periodeawal, 0, 7));
			$perakhirsaldo = str_replace('-', '', substr($periodeakhir, 0, 7));

			//and left(noakun,2) in ('62','61') 

			#Rp Aktual
			// $str = " select sum(jumlah) as jumlah,kodeblok from ".$dbname.".keu_jurnaldt_vw where  kodeblok!='' ".forKebunAll($kbnarr, 'kodeorg', 'in')." 
			// and 
			// 	tanggal between '".$periodeawal."-01' and '".$tglakhir."'  and left(noakun,3) like '611%' group by kodeblok ";
			$str = "
				SELECT SUM(jumlah) AS jumlah, LEFT(kodeblok, 9) AS kodeblok
				FROM ".$dbname.".keu_jurnaldt_vw 
				WHERE 1 = 1 ".forKebunAll($kbnarr, 'kodeorg', 'in')." AND LENGTH(kodeblok) > '6' AND LEFT(noakun, 3) = '611' AND tanggal BETWEEN '".$periodeawal."-01' AND '".$tglakhir."'
				GROUP BY LEFT(kodeblok, 9)
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$rpjurnal6[$bar['kodeblok']] = $bar['jumlah'];
				$bloklist[$bar['kodeblok']] = $bar['kodeblok'];
			}

			#Kg Aktual
			// $str = "select sum(kgwb) as kgprod, blok as kodeorg from ".$dbname.".kebun_spb_vw where 1=1 ".forKebunAll($kbnarr, 'left(blok,4)', 'in')." 
			// 		and tanggal between '".$periodeawal."-01' and '".$tglakhir."' group by blok ";
			$str = "
				SELECT t.blok, SUM(t.prop_berat) AS beratbersih_proporsional
				FROM (
					SELECT d.nospb, LEFT(d.blok, 9) AS blok, d.jjg, d.beratbersihtimbangan, (d.jjg / total_jjg.total_jjg) * d.beratbersihtimbangan AS prop_berat
					FROM kebun_spb_vw d
					JOIN (
						SELECT nospb, beratbersihtimbangan, SUM(jjg) AS total_jjg
						FROM kebun_spb_vw
						WHERE 1 = 1 ".forKebunAll($kbnarr, 'LEFT(blok, 4)', 'in')." AND LEFT(tanggalpanen, 7) BETWEEN '".$periodeawal."' AND '".$periodeakhir."'
						GROUP BY nospb
					) AS total_jjg ON d.nospb = total_jjg.nospb
					WHERE 1 = 1 ".forKebunAll($kbnarr, 'LEFT(d.blok, 4)', 'in')." AND LEFT(d.tanggalpanen, 7) BETWEEN '".$periodeawal."' AND '".$periodeakhir."'
				) AS t
				GROUP BY t.blok
				ORDER BY t.blok;
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				// $fisik[$bar['kodeorg']] = $bar['kgprod'];
				// $bloklist[$bar['kodeorg']] = $bar['kodeorg'];
				$fisik[$bar['blok']] = $bar['beratbersih_proporsional'];
				$bloklist[$bar['blok']] = $bar['blok'];
			}

			$datas = array();
			$totalfill = 0;
			foreach ($bloklist as $listblok) {
				@$data[$listblok] = (((@$rpjurnal6[$listblok]) / @$fisik[$listblok]) / ((@$rpbgt6[$listblok]) / @$bgtfisik[$listblok])) * 100;
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
