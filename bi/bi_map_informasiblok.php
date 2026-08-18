<?
	include('../config/connection.php');
	include('../lib/nangkoelib.php');
	include('master_validation.php');
	include('lib/zLib.php');
	include('../jpgraph/jpgraph.php');
	include('../jpgraph/jpgraph_pie.php');
	include('../jpgraph/jpgraph_pie3d.php');

	$type = checkPostGet('type', '');
	$kebun = checkPostGet('kebun', '');
	$filterblok = checkPostGet('filterblok', '');

	$kbnarr = strToArray($kebun, '##');

	switch ($type) {
		case 'preview':
			if ($filterblok == '') {
				exit("error : Filter harus dipilih.");
			}

			$divLegend = "
				<table cellpading=1 cellspacing=1 border=0 class=sortable width=100%>
					<thead>
						<tr align=center>
							<td>Legend</td>
							<td>".$_SESSION['lang']['keterangan']."</td>
						</tr>
					</thead>
					<tbody>
			";

			if ($filterblok == 1) {
				$str = "select distinct(tahuntanam) as tahuntanam from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')." order by tahuntanam asc";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					echo "Gagal: ".$e->getMessage();
				}
				$numrows = $res->rowCount();
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$arrColor = gradient($numrows);
				$count = 0;
				while ($bar = $res->fetch()) {
					$divLegend .= "
						<tr class=rowcontent>
							<td bgcolor='".$arrColor[$count]."' style='width:10px;'>&nbsp;</td>
							<!--<td style='width:10px;'>&nbsp;</td>-->
							<td style='text-align:justify'>&nbsp; ".$bar['tahuntanam']."</td>
						</tr>
					";

					$arrWarna[$bar['tahuntanam']] = $arrColor[$count];
					$count++;
				}

				// $str = "select distinct(kodeorg) as kodeorg, tahuntanam from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')."";
				$str = "
					SELECT indukblok, tahuntanam, MAX(jumlahpokok) AS jumlahpokok
					FROM ".$dbname.".setup_blok
					WHERE 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')."
					GROUP BY indukblok
				";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					echo "Gagal: ".$e->getMessage();
				}
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bloklist = array();
				$countlist = -1;
				while ($bar = $res->fetch()) {
					$str2 = "select idsvg from ".$dbname.".bi_map_pt where keterangan like '".$bar['indukblok']."%' and tipepeta='".$firstPT."'";
					try {
						$res2 = $owlPDO->query($str2);
					} catch (PDOException $e) {
						echo "Gagal: ".$e->getMessage();
					}
					$res2->setFetchMode(PDO::FETCH_ASSOC);
					$bar2 = $res2->fetch();
					if ($bar2['idsvg'] != '') {
						$countlist++;
						$arrList[$countlist]['idsvg'] = $bar2['idsvg'];
						$arrList[$countlist]['warna'] = $arrWarna[$bar['tahuntanam']];
					}
				}
			} else if ($filterblok == 2) {
				$str = "select distinct(statusblok) as statusblok from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')." order by statusblok asc";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					echo "Gagal: ".$e->getMessage();
				}
				$numrows = $res->rowCount();
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$arrColor = gradient($numrows);
				$count = 0;
				while ($bar = $res->fetch()) {
					$divLegend .= "
						<tr class=rowcontent>
							<td bgcolor='".$arrColor[$count]."' style='width:10px;'>&nbsp;</td>
							<!--<td style='width:10px;'>&nbsp;</td>-->
							<td style='text-align:justify'>&nbsp; ".$bar['statusblok']."</td>
						</tr>
					";
					$arrWarna[$bar['statusblok']] = $arrColor[$count];
					$count++;
				}

				// $str = "select distinct(kodeorg) as kodeorg, statusblok from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')."";
				$str = "
					SELECT indukblok , statusblok, MAX(jumlahpokok) AS jumlahpokok
					FROM ".$dbname.".setup_blok
					WHERE 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')."
					GROUP BY indukblok
				";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					echo "Gagal: ".$e->getMessage();
				}
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bloklist = array();
				$countlist = -1;
				while ($bar = $res->fetch()) {
					$str2 = "select idsvg from ".$dbname.".bi_map_pt where keterangan like '".$bar['indukblok']."%' and tipepeta='".$firstPT."'";
					try {
						$res2 = $owlPDO->query($str2);
					} catch (PDOException $e) {
						echo "Gagal: ".$e->getMessage();
					}
					$res2->setFetchMode(PDO::FETCH_ASSOC);
					$bar2 = $res2->fetch();
					if ($bar2['idsvg'] != '') {
						$countlist++;
						$arrList[$countlist]['idsvg'] = $bar2['idsvg'];
						$arrList[$countlist]['warna'] = $arrWarna[$bar['statusblok']];
					}
				}
			} else if ($filterblok == 3) {
				$str = "select distinct(topografi) as topografi from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')." order by topografi asc";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					echo "Gagal: ".$e->getMessage();
				}
				$numrows = $res->rowCount();
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$arrColor = gradient($numrows);
				$count = 0;
				while ($bar = $res->fetch()) {
					$divLegend .= "
						<tr class=rowcontent>
							<td bgcolor='".$arrColor[$count]."' style='width:10px;'>&nbsp;</td>
							<!--<td style='width:10px;'>&nbsp;</td>-->
							<td style='text-align:justify'>&nbsp; ".$bar['topografi']."</td>
						</tr>
					";
					$arrWarna[$bar['topografi']] = $arrColor[$count];
					$count++;
				}

				// $str = "select distinct(kodeorg) as kodeorg, topografi from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')."";
				$str = "
					SELECT indukblok , topografi, MAX(jumlahpokok) AS jumlahpokok
					FROM ".$dbname.".setup_blok
					WHERE 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')."
					GROUP BY indukblok
				";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					echo "Gagal: ".$e->getMessage();
				}
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bloklist = array();
				$countlist = -1;
				while ($bar = $res->fetch()) {
					$str2 = "select idsvg from ".$dbname.".bi_map_pt where keterangan like '".$bar['indukblok']."%' and tipepeta='".$firstPT."'";
					try {
						$res2 = $owlPDO->query($str2);
					} catch (PDOException $e) {
						echo "Gagal: ".$e->getMessage();
					}
					$res2->setFetchMode(PDO::FETCH_ASSOC);
					$bar2 = $res2->fetch();
					if ($bar2['idsvg'] != '') {
						$countlist++;
						$arrList[$countlist]['idsvg'] = $bar2['idsvg'];
						$arrList[$countlist]['warna'] = $arrWarna[$bar['topografi']];
					}
				}
			} else if ($filterblok == 4) {
				$str = "select distinct(jenisbibit) as jenisbibit from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')." order by jenisbibit asc";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					echo "Gagal: ".$e->getMessage();
				}
				$numrows = $res->rowCount();
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$arrColor = gradient($numrows);
				$count = 0;
				while ($bar = $res->fetch()) {
					$divLegend .= "
						<tr class=rowcontent>
							<td bgcolor='".$arrColor[$count]."' style='width:10px;'>&nbsp;</td>
							<!--<td style='width:10px;'>&nbsp;</td>-->
							<td style='text-align:justify'>&nbsp; ".$bar['jenisbibit']."</td>
						</tr>
					";
					$arrWarna[$bar['jenisbibit']] = $arrColor[$count];
					$count++;
				}

				// $str = "select distinct(kodeorg) as kodeorg, jenisbibit from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')."";
				$str = "
					SELECT indukblok , jenisbibit, MAX(jumlahpokok) AS jumlahpokok
					FROM ".$dbname.".setup_blok
					WHERE 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')."
					GROUP BY indukblok
				";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					echo "Gagal: ".$e->getMessage();
				}
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bloklist = array();
				$countlist = -1;
				while ($bar = $res->fetch()) {
					$str2 = "select idsvg from ".$dbname.".bi_map_pt where keterangan like '".$bar['indukblok']."%' and tipepeta='".$firstPT."'";
					try {
						$res2 = $owlPDO->query($str2);
					} catch (PDOException $e) {
						echo "Gagal: ".$e->getMessage();
					}
					$res2->setFetchMode(PDO::FETCH_ASSOC);
					$bar2 = $res2->fetch();
					if ($bar2['idsvg'] != '') {
						$countlist++;
						$arrList[$countlist]['idsvg'] = $bar2['idsvg'];
						$arrList[$countlist]['warna'] = $arrWarna[$bar['jenisbibit']];
					}
				}
			} else if ($filterblok == 5) {
				$str = "select distinct(intiplasma) as intiplasma from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')." order by intiplasma asc";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					echo "Gagal: ".$e->getMessage();
				}
				$numrows = $res->rowCount();
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$arrColor = gradient($numrows);
				$count = 0;
				while ($bar = $res->fetch()) {
					$divLegend .= "
						<tr class=rowcontent>
							<td bgcolor='".$arrColor[$count]."' style='width:10px;'>&nbsp;</td>
							<!--<td style='width:10px;'>&nbsp;</td>-->
							<td style='text-align:justify'>&nbsp; ".($bar['intiplasma'] == 'I' ? 'Inti' : 'Plasma')."</td>
						</tr>
					";
					$arrWarna[$bar['intiplasma']] = $arrColor[$count];
					$count++;
				}

				// $str = "select distinct(kodeorg) as kodeorg, intiplasma from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')."";
				$str = "
					SELECT indukblok , intiplasma, MAX(jumlahpokok) AS jumlahpokok
					FROM ".$dbname.".setup_blok
					WHERE 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')."
					GROUP BY indukblok
				";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					echo "Gagal: ".$e->getMessage();
				}
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bloklist = array();
				$countlist = -1;
				while ($bar = $res->fetch()) {
					$str2 = "select idsvg from ".$dbname.".bi_map_pt where keterangan like '".$bar['indukblok']."%' and tipepeta='".$firstPT."'";
					try {
						$res2 = $owlPDO->query($str2);
					} catch (PDOException $e) {
						echo "Gagal: ".$e->getMessage();
					}
					$res2->setFetchMode(PDO::FETCH_ASSOC);
					$bar2 = $res2->fetch();
					if ($bar2['idsvg'] != '') {
						$countlist++;
						$arrList[$countlist]['idsvg'] = $bar2['idsvg'];
						$arrList[$countlist]['warna'] = $arrWarna[$bar['intiplasma']];
					}
				}
			}

			$divLegend .= "
					</tbody>
				</table>
				<div style='width:100%;text-align:right;color:#3399FF;padding-top:5px;'>
					<table width='100%'>
						<tr>
							<td style='text-align:left'>
								<img src='../images/print.png' style='width:20px;height:20px;cursor:pointer' title='print map' onclick='printmap()'>
							</td>
							<td style='text-align:right'>
								<span style='cursor:pointer;' onclick=\"detailinformasiblokgraph('".$filterblok."',event)\">Detail Report</span>
							</td>
						</tr>
					</table>
				</div>
			";

			echo json_encode($arrList)."####".$divLegend;
			break;

		case 'globalreport':
			$datas = array();
			$totalfill = 0;

			if ($filterblok == 1) {
				$str = "select distinct(tahuntanam) as tahuntanam from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')." order by tahuntanam asc";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					echo "Gagal: ".$e->getMessage();
				}
				$numrows = $res->rowCount();
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$arrColor = gradient($numrows);
				$count = 0;
				while ($bar = $res->fetch()) {
					$arrWarna[$bar['tahuntanam']] = $arrColor[$count];
					$count++;
				}

				// $str = "select distinct(kodeorg) as kodeorg, tahuntanam from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')."";
				$str = "
					SELECT indukblok, tahuntanam, MAX(jumlahpokok) AS jumlahpokok
					FROM ".$dbname.".setup_blok
					WHERE 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')."
					GROUP BY indukblok
				";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					echo "Gagal: ".$e->getMessage();
				}
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()) {
					$str2 = "select idsvg from ".$dbname.".bi_map_pt where keterangan like '".$bar['indukblok']."%' and tipepeta='".$firstPT."'";
					try {
						$res2 = $owlPDO->query($str2);
					} catch (PDOException $e) {
						echo "Gagal: ".$e->getMessage();
					}
					$res2->setFetchMode(PDO::FETCH_ASSOC);
					$bar2 = $res2->fetch();
					if ($bar2['idsvg'] != '') {
						$datas[$arrWarna[$bar['tahuntanam']]]['keterangan'] = $bar['tahuntanam'];
						$datas[$arrWarna[$bar['tahuntanam']]]['count'] += 1;
						$totalfill++;
					}
				}
				$namalaporan = $_SESSION['lang']['tahuntanam'];
			} else if ($filterblok == 2) {
				$str = "select distinct(statusblok) as statusblok from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')." order by statusblok asc";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					echo "Gagal: ".$e->getMessage();
				}
				$numrows = $res->rowCount();
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$arrColor = gradient($numrows);
				$count = 0;
				while ($bar = $res->fetch()) {
					$arrWarna[$bar['statusblok']] = $arrColor[$count];
					$count++;
				}

				// $str = "select distinct(kodeorg) as kodeorg, statusblok from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')."";
				$str = "
					SELECT indukblok , statusblok, MAX(jumlahpokok) AS jumlahpokok
					FROM ".$dbname.".setup_blok
					WHERE 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')."
					GROUP BY indukblok
				";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					echo "Gagal: ".$e->getMessage();
				}
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()) {
					$str2 = "select idsvg from ".$dbname.".bi_map_pt where keterangan like '".$bar['indukblok']."%' and tipepeta='".$firstPT."'";
					try {
						$res2 = $owlPDO->query($str2);
					} catch (PDOException $e) {
						echo "Gagal: ".$e->getMessage();
					}
					$res2->setFetchMode(PDO::FETCH_ASSOC);
					$bar2 = $res2->fetch();
					if ($bar2['idsvg'] != '') {
						$datas[$arrWarna[$bar['statusblok']]]['keterangan'] = $bar['statusblok'];
						$datas[$arrWarna[$bar['statusblok']]]['count'] += 1;
						$totalfill++;
					}
				}
				$namalaporan = $_SESSION['lang']['statusblok'];
			} else if ($filterblok == 3) {
				$str = "select distinct(topografi) as topografi from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')." order by topografi asc";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					echo "Gagal: ".$e->getMessage();
				}
				$numrows = $res->rowCount();
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$arrColor = gradient($numrows);
				$count = 0;
				while ($bar = $res->fetch()) {
					$arrWarna[$bar['topografi']] = $arrColor[$count];
					$count++;
				}

				// $str = "select distinct(kodeorg) as kodeorg, topografi from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')."";
				$str = "
					SELECT indukblok , topografi, MAX(jumlahpokok) AS jumlahpokok
					FROM ".$dbname.".setup_blok
					WHERE 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')."
					GROUP BY indukblok
				";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					echo "Gagal: ".$e->getMessage();
				}
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()) {
					$str2 = "select idsvg from ".$dbname.".bi_map_pt where keterangan like '".$bar['indukblok']."%' and tipepeta='".$firstPT."'";
					try {
						$res2 = $owlPDO->query($str2);
					} catch (PDOException $e) {
						echo "Gagal: ".$e->getMessage();
					}
					$res2->setFetchMode(PDO::FETCH_ASSOC);
					$bar2 = $res2->fetch();
					if ($bar2['idsvg'] != '') {
						$datas[$arrWarna[$bar['topografi']]]['keterangan'] = $bar['topografi'];
						$datas[$arrWarna[$bar['topografi']]]['count'] += 1;
						$totalfill++;
					}
				}
				$namalaporan = "Topografi";
			} else if ($filterblok == 4) {
				$str = "select distinct(jenisbibit) as jenisbibit from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')." order by jenisbibit asc";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					echo "Gagal: ".$e->getMessage();
				}
				$numrows = $res->rowCount();
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$arrColor = gradient($numrows);
				$count = 0;
				while ($bar = $res->fetch()) {
					$arrWarna[$bar['jenisbibit']] = $arrColor[$count];
					$count++;
				}

				// $str = "select distinct(kodeorg) as kodeorg, jenisbibit from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')."";
				$str = "
					SELECT indukblok , jenisbibit, MAX(jumlahpokok) AS jumlahpokok
					FROM ".$dbname.".setup_blok
					WHERE 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')."
					GROUP BY indukblok
				";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					echo "Gagal: ".$e->getMessage();
				}
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()) {
					$str2 = "select idsvg from ".$dbname.".bi_map_pt where keterangan like '".$bar['indukblok']."%' and tipepeta='".$firstPT."'";
					try {
						$res2 = $owlPDO->query($str2);
					} catch (PDOException $e) {
						echo "Gagal: ".$e->getMessage();
					}
					$res2->setFetchMode(PDO::FETCH_ASSOC);
					$bar2 = $res2->fetch();
					if ($bar2['idsvg'] != '') {
						$datas[$arrWarna[$bar['jenisbibit']]]['keterangan'] = $bar['jenisbibit'];
						$datas[$arrWarna[$bar['jenisbibit']]]['count'] += 1;
						$totalfill++;
					}
				}
				$namalaporan = $_SESSION['lang']['jenisbibit'];
			} else if ($filterblok == 5) {
				$str = "select distinct(intiplasma) as intiplasma from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')." order by intiplasma asc";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					echo "Gagal: ".$e->getMessage();
				}
				$numrows = $res->rowCount();
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$arrColor = gradient($numrows);
				$count = 0;
				while ($bar = $res->fetch()) {
					$arrWarna[$bar['intiplasma']] = $arrColor[$count];
					$count++;
				}

				// $str = "select distinct(kodeorg) as kodeorg, intiplasma from ".$dbname.".setup_blok where 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')."";
				$str = "
					SELECT indukblok , intiplasma, MAX(jumlahpokok) AS jumlahpokok
					FROM ".$dbname.".setup_blok
					WHERE 1=1 ".forKebunAll($kbnarr, 'kodeorg', 'like')."
					GROUP BY indukblok
				";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					echo "Gagal: ".$e->getMessage();
				}
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()) {
					$str2 = "select idsvg from ".$dbname.".bi_map_pt where keterangan like '".$bar['indukblok']."%' and tipepeta='".$firstPT."'";
					try {
						$res2 = $owlPDO->query($str2);
					} catch (PDOException $e) {
						echo "Gagal: ".$e->getMessage();
					}
					$res2->setFetchMode(PDO::FETCH_ASSOC);
					$bar2 = $res2->fetch();
					if ($bar2['idsvg'] != '') {
						$datas[$arrWarna[$bar['intiplasma']]]['keterangan'] = ($bar['intiplasma'] == 'I' ? 'Inti' : 'Plasma');
						$datas[$arrWarna[$bar['intiplasma']]]['count'] += 1;
						$totalfill++;
					}
				}
				$namalaporan = $_SESSION['lang']['intiplasma'];
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

			$str = "select count(idsvg) as countsvg from ".$dbname.".bi_map_pt where tipepeta='".$firstPT."' ".forKebunAll($kbnarr, 'unit', 'in')."";
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
			$label[1] = $totalnofill - $totalfill." ".$_SESSION['lang']['blok']." (%.1f%%)";
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
			$graph->title->SetColor("darkblue");

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
			$p1->value->SetFormat('%2.1f%%');

			$p1->SetSliceColors($arrColor);

			$graph->Add($p1);

			$graph->StrokeCSIM();

			break;
	}

	function gradient($graduations = 10) {
		$from_color = '#00FF00';
		$to_color = '#0000FF';
		$graduations--;
		$startcol = str_replace("#", "", $from_color);
		$endcol = str_replace("#", "", $to_color);
		$RedOrigin = hexdec(substr($startcol, 0, 2));
		$GrnOrigin = hexdec(substr($startcol, 2, 2));
		$BluOrigin = hexdec(substr($startcol, 4, 2));
		if ($graduations >= 2) { // for at least 3 colors
			$GradientSizeRed = (hexdec(substr($endcol, 0, 2)) - $RedOrigin) / $graduations; //Graduation Size Red
			$GradientSizeGrn = (hexdec(substr($endcol, 2, 2)) - $GrnOrigin) / $graduations;
			$GradientSizeBlu = (hexdec(substr($endcol, 4, 2)) - $BluOrigin) / $graduations;
			for ($i = 0; $i <= $graduations; $i++) {
				$RetVal[$i] = strtoupper("#".str_pad(dechex($RedOrigin + ($GradientSizeRed * $i)), 2, '0', STR_PAD_LEFT) .
					str_pad(dechex($GrnOrigin + ($GradientSizeGrn * $i)), 2, '0', STR_PAD_LEFT) .
					str_pad(dechex($BluOrigin + ($GradientSizeBlu * $i)), 2, '0', STR_PAD_LEFT));
			}
		} elseif ($graduations == 1) { // exactlly 2 colors
			$RetVal[] = $from_color;
			$RetVal[] = $to_color;
		} else { // one color
			$RetVal[] = $from_color;
		}
		return $RetVal;
	}
