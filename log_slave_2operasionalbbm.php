<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
require_once('dompdfv2/autoload.inc.php');

use Dompdf\Dompdf;

$method = checkPostGet('method', '');
$tipeprint = checkPostGet('tipeprint', '');

$unit = checkPostGet('unit', '');
$kodeorgnya = checkPostGet('kodeorgnya', '');
$subunit = checkPostGet('subunit', '');
$periode = checkPostGet('periode', '');

$tanggal = checkPostGet('tanggal', '');
$nik = checkPostGet('nik', '');
$kodemesin = checkPostGet('kodemesin', '');
$tanggal = checkPostGet('tanggal', '');
$sStr = selectQuery($dbname, "setup_parameterappl", "nilai", "kodeaplikasi = 'TX' AND kodeparameter = 'BRGOPRBBM'");
$qStr = fetchData($sStr);
$dftrkodebarang = explode(',', $qStr[0]['nilai']);

switch ($method) {
	case 'getsubunit':
		$optSubUnit = "<option value='all'>" . $_SESSION['lang']['all'] . "</option>";
		$optSubUnit .= "<option value=''>" . $unit . " - Kantor</option>";
		$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='" . $unit . "' order by kodeorganisasi";
		$res = fetchdata($str);
		foreach ($res as $val) {
			$optSubUnit .= "<option value='" . $val['kodeorganisasi'] . "'>" . $val['kodeorganisasi'] . " - " . $val['namaorganisasi'] . "</option>";
		}

		echo $optSubUnit;
		break;

	case 'preview':



		$tab = "";
		$optnamavhc = makeOption($dbname, 'vhc_5master', 'kodevhc,detailvhc');

		$gettglawal = $periode . "-01";
		$gettglakhir = tglakhir($periode);
		$bulan = tanggalbulan($periode);
		$exptglakhir = explode('-', $gettglakhir);
		$tglawal = '01';
		$tglakhir = $exptglakhir[2];


		$rangetgl = rangeTanggalarr($gettglawal, $gettglakhir);

		$where = "";
		if ($subunit == 'all') {
			$where .= "";
			$wheres_fp .= "";
		} else if ($subunit == '') {
			$where .= " and subbagian=''";
			$wheres_fp .= " and subbagian=''";
		} else {
			$where .= " and subbagian='" . $subunit . "'";
			$where_fp .= " and subbagian='" . $subunit . "'";
		}

		$where .= " and tanggal like '" . $periode . "%'";

		// $str0="select substr(kodemesin,5,2) as jenisvhc,kodemesin,tanggal,kmhm,jumlah from ".$dbname.".log_transaksi_vw where kodegudang like '".$unit."%' ".$where." and kodemesin != '' and tipetransaksi = '5' and kodebarang in ('".implode("','",$dftrkodebarang)."') group by kodemesin order by kodemesin asc";
		$str0 = "(SELECT SUBSTR(kodemesin, 5, 2) AS kodejenis,kodemesin AS alokasi,tanggal,kmhm,jumlah FROM " . $dbname . ".log_transaksi_vw WHERE kodegudang like '" . $unit . "%' " . $where . " and kodemesin != '' and tipetransaksi = '5' and kodebarang in ('" . implode("','", $dftrkodebarang) . "') GROUP BY alokasi )
			UNION ALL 
			(SELECT kodekegiatan AS kodejenis,kodeblok AS alokasi,tanggal,kmhm,jumlah FROM " . $dbname . ".log_transaksi_vw WHERE kodegudang like '" . $unit . "%' " . $where . " and tipetransaksi = '5' and kodebarang in ('" . implode("','", $dftrkodebarang) . "') AND kodekegiatan IN (SELECT kodekegiatan FROM `setup_kegiatan` WHERE `noakun` LIKE '%7140909%' AND `namakegiatan` LIKE '%genset%') group by alokasi)
			ORDER BY CASE WHEN kodejenis REGEXP '^[A-Za-z]' THEN 1 ELSE 2 END,kodejenis";
		$res0 = fetchdata($str0);
		$arrkary = $res0;

		// $str="select kodemesin,tanggal,kmhm,jumlah from ".$dbname.".log_transaksi_vw where kodegudang like '".$unit."%' ".$where." and kodemesin != '' and tipetransaksi = '5' and kodebarang in ('".implode("','",$dftrkodebarang)."') order by kodemesin asc";
		$str = "(SELECT SUBSTR(kodemesin, 5, 2) AS kodejenis,kodemesin AS alokasi,tanggal,kmhm,jumlah FROM " . $dbname . ".log_transaksi_vw WHERE kodegudang like '" . $unit . "%' " . $where . " and kodemesin != '' and tipetransaksi = '5' and kodebarang in ('" . implode("','", $dftrkodebarang) . "') GROUP BY alokasi,tanggal )
			UNION ALL 
			(SELECT kodekegiatan AS kodejenis,kodeblok AS alokasi,tanggal,kmhm,jumlah FROM " . $dbname . ".log_transaksi_vw WHERE kodegudang like '" . $unit . "%' " . $where . " and tipetransaksi = '5' and kodebarang in ('" . implode("','", $dftrkodebarang) . "') AND kodekegiatan IN (SELECT kodekegiatan FROM `setup_kegiatan` WHERE `noakun` LIKE '%7140909%' AND `namakegiatan` LIKE '%genset%') group by alokasi,tanggal)
			ORDER BY CASE WHEN kodejenis REGEXP '^[A-Za-z]' THEN 1 ELSE 2 END,kodejenis";
		$res = fetchdata($str);
		$arrkary2 = array();
		foreach ($res as $val) {
			$arrkary2[$val['alokasi']][$val['tanggal']]['jumlah'] += $val['jumlah'];
			$arrkary2[$val['alokasi']][$val['tanggal']]['kmhm'] += $val['kmhm'];

			// total per kendaraan
			$arrtotalperkendaraan[$val['alokasi']]['jumlah'] += $val['jumlah'];
			// total per tanggal
			$arrtotalpertanggal[$val['tanggal']]['jumlah'] += $val['jumlah'];
		}

		if ($tipeprint == 'html') {
			$border = "border=0";
		} else {
			$border = "border=1";
		}

		$colspn = $tglakhir * 2;

		$tab .= "<table cellpadding=5 cellspacing=1 " . $border . " class=sortable style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center;font-weight:bold'>
				<th rowspan='3'>" . $_SESSION['lang']['nourut'] . "</th>
				<th rowspan='3'>" . $_SESSION['lang']['kode'] . "</th>
				<th rowspan='3'>" . $_SESSION['lang']['nama'] . "</th>
				<th colspan='" . $colspn . "'>" . $bulan . "</th>
				<th rowspan='3'>" . $_SESSION['lang']['total'] . "</th>
			</tr>";
		$tab .= "<tr class=rowheader style='text-align:center;font-weight:bold'>";
		for ($i = $tglawal; $i <= $tglakhir; $i++) {

			$dayOfWeek = date('w', strtotime($periode . "-" . $i));
			if ($dayOfWeek == 0) {
				$tab .= "<th colspan=2 style='color:red'>" . addZero($i, 2) . "</th>";
			} else {
				$tab .= "<th colspan=2>" . addZero($i, 2) . "</th>";
			}
		}
		$tab .= "</tr>";
		$tab .= "<tr>";
		for ($i = $tglawal; $i <= $tglakhir; $i++) {
			$tab .= "<th>KM</th>";
			$tab .= "<th>L</th>";
		}
		$tab .= "</tr>";

		$tab .= "</thead><tbody>";

		$no = 0;
		$ttlabs = [];
		$jenisvhccek = "";
		$optjenisvhc = makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc');
		$optsubunit = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
		$optkegiatan = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
		foreach ($arrkary as $val) {

			$d = $val['kodejenis'];
			if (strlen($d) > 2) {
				if ($d != $n) {
					$tab .= "<tr class='rowcontent' style='background-color:yellow;font-weight:bold'>
					<td colspan = " . ($colspn + 3) . ">" . $optkegiatan[$d] . "</td>
					<td></td>
					</tr>";
				}
			} else {
				if ($d != $n) {
					$tab .= "<tr class='rowcontent' style='background-color:yellow;font-weight:bold'>
					<td colspan = " . ($colspn + 3) . ">" . $optjenisvhc[$d] . "</td>
					<td></td>
					</tr>";
				}
			}

			$no++;
			$tab .= "<tr class='rowcontent'>
					<td align='center'>" . $no . "</td>
					<td align='center'>" . $val['alokasi'] . "</td>";

			if (strlen($val['alokasi']) > 6) {
				$tab .= "<td align='center'>" . $optnamavhc[$val['alokasi']] . "</td>";
			} else {
				$tab .= "<td align='center'>" . $optsubunit[$val['alokasi']] . "</td>";
			}

			foreach ($rangetgl as $tgl) {
				if ($arrkary2[$val['alokasi']][$tgl]['jumlah'] != 0) {
					$tab .= "<td style='min-width:30px;color:blue;cursor:pointer' align='center' " . $style . " onclick=\"detail('" . $val['alokasi'] . "','" . $tgl . "')\">" . $arrkary2[$val['alokasi']][$tgl]['kmhm'] . "</td>";
					$tab .= "<td style='min-width:30px;color:blue;cursor:pointer' align='center' " . $style . " onclick=\"detail('" . $val['alokasi'] . "','" . $tgl . "')\">" . $arrkary2[$val['alokasi']][$tgl]['jumlah'] . "</td>";
				} else {
					$tab .= "<td style='min-width:30px' align='center' " . $style . " onclick=\"detail('" . $val['alokasi'] . "','" . $tgl . "')\">" . $arrkary2[$val['alokasi']][$tgl]['kmhm'] . "</td>";
					$tab .= "<td style='min-width:30px' align='center' " . $style . " onclick=\"detail('" . $val['alokasi'] . "','" . $tgl . "')\">" . $arrkary2[$val['alokasi']][$tgl]['jumlah'] . "</td>";
				}
			}
			$tab .= "<td align='center' style='background-color:green;color:white;font-weight:bold'>" . $arrtotalperkendaraan[$val['alokasi']]['jumlah'] . "</td>";
			$tab .= "</tr>";

			$n = $d;
		}

		$tab .= "<tr class='rowcontent' style='background-color:green;color:white;font-weight:bold'>
			<td colspan='3' align=center>Total</td>";

		foreach ($rangetgl as $tgl) {
			$tab .= "
				<td align=center></td>
				<td align=center>" . $arrtotalpertanggal[$tgl]['jumlah'] . "</td>
				";
		}
		$tab .= "<td align=center></td>";
		$tab .= "</tr>";



		if ($tipeprint == 'html') {
			echo $tab;
		} else {
			$nop_ = "Laporan_OperasionalBBM_" . $unit . "_" . $periode;
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
					echo "<script language=javascript>
						parent.window.alert('Can't convert to excel format');
						</script>";
					exit;
				} else {
					echo "<script language=javascript>
						window.location='tempExcel/" . $nop_ . ".xls';
						</script>";
				}
				fclose($handle);
			}
		}
		break;

	case 'detail':
		$optnamavhc = makeOption($dbname, 'vhc_5master', 'kodevhc,detailvhc');
		$optsubunit = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
		$optkegiatan = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
		$tab = "<table cellpadding=5 cellspacing=1 " . $border . " class=sortable style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center;font-weight:bold'>
				<th>" . $_SESSION['lang']['nourut'] . "</th>
				<th>" . $_SESSION['lang']['notransaksi'] . "</th>
				<th>" . $_SESSION['lang']['kode'] . "</th>
				<th>" . $_SESSION['lang']['nama'] . "</th>
				<th>KMHM</th>
				<th>Jumlah</th>
				<th>" . $_SESSION['lang']['kegiatan'] . "</th>
				<th>" . $_SESSION['lang']['catatan'] . "</th>
			</tr>";
		$tab .= "</thead><tbody>";


		$no = 0;
		if (strlen($kodemesin) > 6) {
			$str = "select * from " . $dbname . ".log_transaksi_vw where kodemesin='" . $kodemesin . "' and tanggal = '" . $tanggal . "' and tipetransaksi = '5' and kodebarang in ('" . implode("','", $dftrkodebarang) . "') order by tanggal asc";
			$res = fetchdata($str);
			if ($kodemesin == '') {
				$tab .= "<tr class='rowcontent'>";
				$tab .= "<td colspan=7 align='center'><b>DATA TIDAK ADA</b></td>";
				$tab .= "</tr>";
			} else {
				foreach ($res as $val) {
					$no++;
					$tab .= "<tr class='rowcontent'>";
					$tab .= "<td align='center'>" . $no . "</td>";
					$tab .= "<td align='center'>" . $val['notransaksi'] . "</td>";
					$tab .= "<td align='center'>" . $val['kodemesin'] . "</td>";
					$tab .= "<td align='center'>" . $optnamavhc[$val['kodemesin']] . "</td>";
					$tab .= "<td align='center'>" . $val['kmhm'] . "</td>";
					$tab .= "<td align='center'>" . $val['jumlah'] . "</td>";
					$tab .= "<td align='center'>" . $optkegiatan[$val['kodekegiatan']] . "</td>";
					$tab .= "<td align='center'>" . $val['keterangan'] . "</td>";
				}
				$tab .= "</tr>";
			}
		} else {
			$str = "select * from " . $dbname . ".log_transaksi_vw where kodeblok='" . $kodemesin . "' and tanggal = '" . $tanggal . "' and tipetransaksi = '5' and kodebarang in ('" . implode("','", $dftrkodebarang) . "') order by tanggal asc";
			$res = fetchdata($str);
			if ($kodemesin == '') {
				$tab .= "<tr class='rowcontent'>";
				$tab .= "<td colspan=7 align='center'><b>DATA TIDAK ADA</b></td>";
				$tab .= "</tr>";
			} else {
				foreach ($res as $val) {
					$no++;
					$tab .= "<tr class='rowcontent'>";
					$tab .= "<td align='center'>" . $no . "</td>";
					$tab .= "<td align='center'>" . $val['notransaksi'] . "</td>";
					$tab .= "<td align='center'>" . $val['kodeblok'] . "</td>";
					$tab .= "<td align='center'>" . $optsubunit[$val['kodeblok']] . "</td>";
					$tab .= "<td align='center'>" . $val['kmhm'] . "</td>";
					$tab .= "<td align='center'>" . $val['jumlah'] . "</td>";
					$tab .= "<td align='center'>" . $optkegiatan[$val['kodekegiatan']] . "</td>";
					$tab .= "<td align='center'>" . $val['keterangan'] . "</td>";
				}
				$tab .= "</tr>";
			}
		}


		echo $tab;
		break;
}
