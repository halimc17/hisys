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

			$arrPer = month_inbetween($periodeawal, $periodeakhir);
			$jumbul = count($arrPer);
			$blnPerThn = array();
			foreach ($arrPer as $key => $value) {
				$blnPerThn[substr($value, 0, 4)][] = substr($value, 5, 2);
			}

			// $str = "select ".$addstr." as bgt,kodeorg from ".$dbname.".bgt_budget_detail where 1=1 ".forKebunAll($kbnarr, 'left(kodeorg,4)', 'in')."  and tahunbudget='".substr($periodeakhir, 0, 4)."' and left(noakun,3) in ('621','126')";
			$str = "
				SELECT SUM(totalsebaranrp) AS bgt, LEFT(kodeorg, 9) AS kodeorg, tahunbudget
				FROM ".$dbname.".bgt_budget_detail
				WHERE 1 = 1 ".forKebunAll($kbnarr, 'LEFT(kodeorg, 4)', 'in')." AND tahunbudget BETWEEN '".substr($periodeawal, 0, 4)."' AND '".substr($periodeakhir, 0, 4)."' AND LEFT(noakun, 3) IN ('621', '126')
				GROUP BY LEFT(kodeorg, 9), tahunbudget
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				@$rpbgt[$bar['kodeorg']] += ($bar['bgt'] / 12 * count($blnPerThn[$bar['tahunbudget']]));
				// $bloklist[$bar['kodeorg']]=$bar['kodeorg'];
			}

			$str = "
				select sum(jumlah) as jumlah, LEFT(kodeblok, 9) AS kodeblok
				from ".$dbname.".keu_jurnaldt_vw 
				where 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'in')." and length(kodeblok)>'6'  and left(noakun,3) in ('621','126') and tanggal between '".$periodeawal."-01' and '".$tglakhir."'
				group by LEFT(kodeblok, 9)
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$rpjurnal[$bar['kodeblok']] = $bar['jumlah'];
				// $bloklist[$bar['kodeblok']]=$bar['kodeblok'];
			}

			#ha bgt
			// $str = "select kodeblok,hathnini from ".$dbname.".bgt_blok where 1=1 ".forKebunAll($kbnarr, 'left(kodeblok,4)', 'in')."
			// 		and tahunbudget='".substr($periodeakhir, 0, 4)."'";
			$str = "
				SELECT LEFT(kodeblok, 9) AS kodeblok, SUM(hathnini) AS hathnini
				FROM ".$dbname.".bgt_blok
				WHERE 1 = 1 ".forKebunAll($kbnarr, 'LEFT(kodeblok, 4)', 'in')." AND tahunbudget BETWEEN '".substr($periodeawal, 0, 4)."' AND '".substr($periodeakhir, 0, 4)."'
				GROUP BY LEFT(kodeblok, 9)
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				// $bloklist[$bar['kodeblok']]=$bar['kodeblok'];
				$luasbgt[$bar['kodeblok']] = $bar['hathnini'];
			}

			#ha real
			// $str = "select kodeorg,luasareaproduktif from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'left(kodeorg,4)', 'in')." ";
			$str = "
				SELECT indukblok, SUM(luasareaproduktif) AS luasareaproduktif
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
				$bloklist[$bar['indukblok']] = $bar['indukblok'];
				$luasreal[$bar['indukblok']] = $bar['luasareaproduktif'];
			}

			// @$rpharealisasi=$rpjurnal/$luasreal;
			// @$rphabgt=$rpbgt/$luasbgt;
			// @$penc=$rpharealisasi/$rphabgt*100;

			$arrList = array();
			$countlist = -1;

			foreach ($bloklist as $listblok) {
				@$rpharealisasi[$listblok] = $rpjurnal[$listblok] / $luasreal[$listblok];
				@$rphabgt[$listblok] = $rpbgt[$listblok] / $luasbgt[$listblok];
				@$data[$listblok] = ($rpharealisasi[$listblok] / $rphabgt[$listblok]) * 100;
				if ($rpharealisasi[$listblok] != 0) {
					if (isset($arrWarna)) {
						foreach ($arrWarna as $key => $row) {
							if (my_operator($data[$listblok], $row['awal'], $row['opawal']) && my_operator($data[$listblok], $row['akhir'], $row['opakhir'])) {
								$countlist++;
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
							<td rowspan=2>".$_SESSION['lang']['status']."</td>
							<td colspan=2>".$_SESSION['lang']['rupiah']."</td>
							<td colspan=2>".$_SESSION['lang']['rpperha']."</td>
							<td colspan=2>".$_SESSION['lang']['pencapaian']." (%)</td>
						</tr>
						<tr align=center>
							<td>".$_SESSION['lang']['realisasi']."</td>
							<td>".$_SESSION['lang']['budget']."</td>
							<td>".$_SESSION['lang']['realisasi']."</td>
							<td>".$_SESSION['lang']['budget']."</td>
							<td>".$_SESSION['lang']['rupiah']."</td>
							<td>".$_SESSION['lang']['rpperha']."</td>
						</tr>
					</thead>
					<tbody>
			";

			$arrper = month_inbetween($periodeawal, $periodeakhir);
			$jumbul = count($arrper);
			$blnPerThn = array();
			foreach ($arrper as $key => $value) {
				$blnPerThn[substr($value, 0, 4)][] = substr($value, 5, 2);
			}
			$tahun = substr($periodeakhir, 0, 4);

			$bgtawal = substr($periodeawal, 5, 2);
			$bgtakhir = substr($periodeakhir, 5, 2);

			// $whbgt = "tahunbudget='".$tahun."'";
			// $luasbgt = makeOption($dbname, 'bgt_blok', 'kodeblok,hathnini', $whbgt);
			// $luasreal = makeOption($dbname, 'setup_blok', 'kodeorg,luasareaproduktif');

			#LUAS REAL
			$str = "
				SELECT indukblok, SUM(luasareaproduktif) AS luasareaproduktif
				FROM ".$dbname.".setup_blok
				WHERE 1 = 1 AND indukblok = '".$blok."'
				GROUP BY indukblok
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$luasreal[$bar['indukblok']] = $bar['luasareaproduktif'];
			}

			#LUAS BGT
			$str = "
				SELECT LEFT(kodeblok, 9) AS kodeblok, SUM(hathnini) AS hathnini
				FROM ".$dbname.".bgt_blok
				WHERE 1 = 1 AND tahunbudget IN ('".substr($periodeawal, 0, 4)."', '".substr($periodeakhir, 0, 4)."') AND LEFT(kodeblok, 9) = '".$blok."'
				GROUP BY LEFT(kodeblok, 9)
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				// $bloklist[$bar['kodeblok']]=$bar['kodeblok'];
				$luasbgt[$bar['kodeblok']] = $bar['hathnini'];
			}

			#RP

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

			// $str = " select ".$addstr." as jumlah,kodeorg,left(noakun,3) as noakun from ".$dbname.".bgt_budget_detail where 
			// 		kodeorg='".$blok."' and tahunbudget='".$tahun."' and left(noakun,3) in ('621','126') ";
			$str = "
				SELECT SUM(totalsebaranrp) AS jumlah, LEFT(kodeorg, 9) AS kodeorg, LEFT(noakun, 3) AS noakun, tahunbudget
				FROM ".$dbname.".bgt_budget_detail
				WHERE kodeorg LIKE '".$blok."%' AND tahunbudget IN ('".substr($periodeawal, 0, 4)."', '".substr($periodeakhir, 0, 4)."') AND LEFT(noakun, 3) IN ('621', '126')
				GROUP BY LEFT(kodeorg, 9), LEFT(noakun, 3), tahunbudget
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				if ($bar['noakun'] == '621') {
					$rpbgt['tm'] += ($bar['jumlah'] / 12 * count($blnPerThn[$bar['tahunbudget']]));
				}
				if ($bar['noakun'] == '126') {
					$rpbgt['tbm'] += ($bar['jumlah'] / 12 * count($blnPerThn[$bar['tahunbudget']]));
				}
			}

			$str = "
				select sum(jumlah) as jumlah,left(noakun,3) AS noakun
				from ".$dbname.".keu_jurnaldt_vw
				where periode between '".$periodeawal."' and '".$periodeakhir."' and kodeblok LIKE '".$blok."%' and left(noakun,3) in ('621','126')
				group by left(noakun,3)
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				if ($bar['noakun'] == '621') {
					$rpreal['tm'] += $bar['jumlah'];
				}
				if ($bar['noakun'] == '126') {
					$rpreal['tbm'] += $bar['jumlah'];
				}
			}

			#tutup rp

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
				$data = fixnan((($rpreal['tm'] + $rpreal['tbm']) / $luasreal[$blok]) / (($rpbgt['tm'] + $rpbgt['tbm']) / $luasbgt[$blok]) * 100);
				if (my_operator($data, $row['awal'], $row['opawal']) && my_operator($data, $row['akhir'], $row['opakhir'])) {
					$bgcol = $key;
				}
			}

			$warna = $bgcol;

			$result .= "
				<tr class=rowcontent align=center>
					<td align=left>TM</td>
					<td align=right>".@number_format($rpreal['tm'])."</td>
					<td align=right>".@number_format($rpbgt['tm'])."</td>
					<td align=right>".@number_format(fixnan($rpreal['tm'] / $luasreal[$blok]))."</td>
					<td align=right>".@number_format(fixnan($rpbgt['tm'] / $luasbgt[$blok]))."</td>
					<td align=right>".@number_format(fixnan($rpreal['tm'] / $rpbgt['tm'] * 100), 2)."</td>
					<td align=right>".@number_format(fixnan(($rpreal['tm'] / $luasreal[$blok]) / ($rpbgt['tm'] / $luasbgt[$blok]) * 100), 2)."</td>
				</tr>
				<tr class=rowcontent align=center>
					<td align=left>TBM</td>
					<td align=right>".@number_format($rpreal['tbm'])."</td>
					<td align=right>".@number_format($rpbgt['tbm'])."</td>
					<td align=right>".@number_format(fixnan($rpreal['tbm'] / $luasreal[$blok]))."</td>
					<td align=right>".@number_format(fixnan($rpbgt['tbm'] / $luasbgt[$blok]))."</td>
					<td align=right>".@number_format(fixnan($rpreal['tbm'] / $rpbgt['tbm'] * 100), 2)."</td>
					<td align=right>".@number_format(fixnan(($rpreal['tbm'] / $luasreal[$blok]) / ($rpbgt['tbm'] / $luasbgt[$blok]) * 100), 2)."</td>
				</tr>
				<tr class=rowcontent align=center style=font-weight:bold;>
					<td align=Center>".$_SESSION['lang']['total']."</td>
					<td align=right>".@number_format($rpreal['tm'] + $rpreal['tbm'])."</td>
					<td align=right>".@number_format($rpbgt['tm'] + $rpbgt['tbm'])."</td>
					<td align=right>".@number_format(fixnan(($rpreal['tm'] + $rpreal['tbm']) / $luasreal[$blok]))."</td>
					<td align=right>".@number_format(fixnan(($rpbgt['tm'] + $rpbgt['tbm']) / $luasbgt[$blok]))."</td>
					<td align=right>".@number_format(fixnan(($rpreal['tm'] + $rpreal['tbm']) / ($rpbgt['tm'] + $rpbgt['tbm']) * 100), 2)."</td>
					<td align=right style=background-color:".$warna.">".@number_format(fixnan((($rpreal['tm'] + $rpreal['tbm']) / $luasreal[$blok]) / (($rpbgt['tm'] + $rpbgt['tbm']) / $luasbgt[$blok]) * 100), 2)."</td>
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

			// $str = " select ".$addstr." as bgt,kodeorg from ".$dbname.".bgt_budget_detail where 1=1 ".forKebunAll($kbnarr, 'left(kodeorg,4)', 'in')." and tahunbudget='".substr($periodeakhir, 0, 4)."' and left(noakun,3) in ('621','126')  ";
			$str = "
				SELECT SUM(totalsebaranrp) AS bgt, LEFT(kodeorg, 9) AS kodeorg, tahunbudget
				FROM ".$dbname.".bgt_budget_detail
				WHERE 1 = 1 ".forKebunAll($kbnarr, 'LEFT(kodeorg, 4)', 'in')." AND tahunbudget BETWEEN '".substr($periodeawal, 0, 4)."' AND '".substr($periodeakhir, 0, 4)."' AND LEFT(noakun, 3) IN ('621', '126')
				GROUP BY LEFT(kodeorg, 9), tahunbudget
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				@$rpbgt[$bar['kodeorg']] += ($bar['bgt'] / 12 * count($blnPerThn[$bar['tahunbudget']]));
				$bloklist[$bar['kodeorg']] = $bar['kodeorg'];
			}

			$str = "
				select sum(jumlah) as jumlah, LEFT(kodeblok, 9) AS kodeblok
				from ".$dbname.".keu_jurnaldt_vw 
				where 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'in')." and length(kodeblok)>'6'  and left(noakun,3) in ('621','126') and tanggal between '".$periodeawal."-01' and '".$tglakhir."'
				group by LEFT(kodeblok, 9)
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$rpjurnal[$bar['kodeblok']] = $bar['jumlah'];
				$bloklist[$bar['kodeblok']] = $bar['kodeblok'];
			}

			#ha bgt
			// $str = "select kodeblok,hathnini from ".$dbname.".bgt_blok where 1=1 ".forKebunAll($kbnarr, 'left(kodeblok,4)', 'in')."
			// 		and tahunbudget='".substr($periodeakhir, 0, 4)."'";
			$str = "
				SELECT LEFT(kodeblok, 9) AS kodeblok, SUM(hathnini) AS hathnini
				FROM ".$dbname.".bgt_blok
				WHERE 1 = 1 ".forKebunAll($kbnarr, 'LEFT(kodeblok, 4)', 'in')." AND tahunbudget BETWEEN '".substr($periodeawal, 0, 4)."' AND '".substr($periodeakhir, 0, 4)."'
				GROUP BY LEFT(kodeblok, 9)
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				echo "Gagal: ".$e->getMessage();
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$bloklist[$bar['kodeblok']] = $bar['kodeblok'];
				$luasbgt[$bar['kodeblok']] = $bar['hathnini'];
			}

			#ha real
			// $str = "select kodeorg,luasareaproduktif from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'left(kodeorg,4)', 'in')." ";
			$str = "
				SELECT indukblok, SUM(luasareaproduktif) AS luasareaproduktif
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
				$bloklist[$bar['indukblok']] = $bar['indukblok'];
				$luasreal[$bar['indukblok']] = $bar['luasareaproduktif'];
			}

			$datas = array();
			$totalfill = 0;
			foreach ($bloklist as $listblok) {
				@$rpharealisasi[$listblok] = $rpjurnal[$listblok] / $luasreal[$listblok];
				@$rphabgt[$listblok] = $rpbgt[$listblok] / $luasbgt[$listblok];
				@$data[$listblok] = ($rpharealisasi[$listblok] / $rphabgt[$listblok]) * 100;
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
