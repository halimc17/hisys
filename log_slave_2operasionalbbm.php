<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
require_once('dompdfv2/autoload.inc.php');
include_once('lib/HtmlExcel.php');
require_once('lib/fpdf.php');

use Dompdf\Dompdf;

function bbmKeluarkan($tab, $tipeprint, $unit, $periode)
{
	if ($tipeprint == 'html') {
		echo $tab;
		return;
	}
	$nop_ = "Laporan_OperasionalBBM_Karyawan_" . $unit . "_" . $periode;
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
				parent.window.alert('Tidak bisa membuat file Excel');
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

# divisi dari isian kodeblok: kode organisasi 6 huruf (divisi, traksi, workshop, dan lain-lain) atau awalan 6 huruf dari kode blok; selain itu kosong
function bbmDivisi($kodeblok, $optorg)
{
	$kodeblok = (string)$kodeblok;
	if (strlen($kodeblok) >= 6 && isset($optorg[substr($kodeblok, 0, 6)])) {
		return substr($kodeblok, 0, 6);
	}
	return '';
}

# judul kolom 2 dan 3 menurut tampilan
function bbmLabelKolom($tampilan)
{
	if ($tampilan == 'karyawan') {
		return array('No. Kendaraan', 'Nama Karyawan');
	}
	if ($tampilan == 'divisi') {
		return array('Kode Divisi', 'Nama Divisi');
	}
	return array('Kode', 'Nama');
}

function bbmAmbilData($unit, $periode, $kodebarang, $tampilan)
{
	global $dbname;
	$data = array('tgl' => rangeTanggalarr($periode . '-01', tglakhir($periode)), 'baris' => array(), 'totaltgl' => array(), 'total' => 0);
	$in = implode("','", $kodebarang);
	if ($tampilan == 'karyawan') {
		$res = fetchdata("select namapenerima,tanggal,sum(jumlah) as jumlah,group_concat(distinct if(kodemesin<>'',kodemesin,null) separator ',') as mesin from " . $dbname . ".log_transaksi_vw where kodegudang like '" . $unit . "%' and tipetransaksi = '5' and kodebarang in ('" . $in . "') and tanggal like '" . $periode . "%' group by namapenerima,tanggal");
		$arr = array();
		$mesin = array();
		foreach ($res as $val) {
			$arr[$val['namapenerima']]['hari'][$val['tanggal']] = array('jumlah' => $val['jumlah']);
			@$arr[$val['namapenerima']]['total'] += $val['jumlah'];
			if ($val['mesin'] != '') {
				foreach (explode(',', $val['mesin']) as $msn) {
					$mesin[$val['namapenerima']][$msn] = $msn;
				}
			}
		}
		$optkary = array();
		if (count($arr) > 0) {
			foreach (fetchdata("select lpad(karyawanid,10,'0') as id,namakaryawan from " . $dbname . ".datakaryawan where karyawanid in (" . implode(',', array_map('intval', array_keys($arr))) . ")") as $kary) {
				$optkary[$kary['id']] = $kary['namakaryawan'];
			}
		}
		foreach ($arr as $id => $dt) {
			$data['baris'][] = array(
				'kode' => isset($mesin[$id]) ? implode(', ', $mesin[$id]) : '',
				'nama' => isset($optkary[$id]) ? $optkary[$id] : ($id == '' ? '(tanpa penerima)' : $id),
				'hari' => $dt['hari'],
				'total' => $dt['total']
			);
		}
		usort($data['baris'], function ($a, $b) {
			return strcasecmp($a['nama'], $b['nama']);
		});
	} else if ($tampilan == 'divisi') {
		$optorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
		$res = fetchdata("select kodeblok,tanggal,sum(jumlah) as jumlah from " . $dbname . ".log_transaksi_vw where kodegudang like '" . $unit . "%' and tipetransaksi = '5' and kodebarang in ('" . $in . "') and tanggal like '" . $periode . "%' group by kodeblok,tanggal");
		$arr = array();
		foreach ($res as $val) {
			$dv = bbmDivisi($val['kodeblok'], $optorg);
			@$arr[$dv]['hari'][$val['tanggal']]['jumlah'] += $val['jumlah'];
			@$arr[$dv]['total'] += $val['jumlah'];
		}
		ksort($arr);
		foreach ($arr as $dv => $dt) {
			if ($dv === '') {
				continue;
			}
			$data['baris'][] = array('kode' => $dv, 'nama' => trim($optorg[$dv]), 'hari' => $dt['hari'], 'total' => $dt['total']);
		}
		if (isset($arr[''])) {
			$data['baris'][] = array('kode' => '-', 'nama' => 'Tanpa divisi (unit, SPK, dan lainnya)', 'hari' => $arr['']['hari'], 'total' => $arr['']['total']);
		}
	} else {
		$optnamavhc = makeOption($dbname, 'vhc_5master', 'kodevhc,detailvhc');
		$optjenisvhc = makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc');
		$optsubunit = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
		$optkegiatan = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
		$dasar = " FROM " . $dbname . ".log_transaksi_vw WHERE kodegudang like '" . $unit . "%' and tanggal like '" . $periode . "%' and tipetransaksi = '5' and kodebarang in ('" . $in . "')";
		$res = fetchdata("(SELECT SUBSTR(kodemesin, 5, 2) AS kodejenis,kodemesin AS alokasi,tanggal,MAX(kmhm) AS kmhm,SUM(jumlah) AS jumlah" . $dasar . " and kodemesin != '' GROUP BY alokasi,tanggal)
			UNION ALL
			(SELECT kodekegiatan AS kodejenis,kodeblok AS alokasi,tanggal,MAX(kmhm) AS kmhm,SUM(jumlah) AS jumlah" . $dasar . " AND kodekegiatan IN (SELECT kodekegiatan FROM " . $dbname . ".setup_kegiatan WHERE noakun LIKE '%7140909%' AND namakegiatan LIKE '%genset%') GROUP BY alokasi,tanggal)
			ORDER BY CASE WHEN kodejenis REGEXP '^[A-Za-z]' THEN 1 ELSE 2 END,kodejenis,alokasi,tanggal");
		$arr = array();
		foreach ($res as $val) {
			$k = $val['kodejenis'] . '|' . $val['alokasi'];
			if (!isset($arr[$k])) {
				$arr[$k] = array(
					'grup' => strlen($val['kodejenis']) > 2 ? (isset($optkegiatan[$val['kodejenis']]) ? $optkegiatan[$val['kodejenis']] : $val['kodejenis']) : (isset($optjenisvhc[$val['kodejenis']]) ? $optjenisvhc[$val['kodejenis']] : $val['kodejenis']),
					'kode' => $val['alokasi'],
					'nama' => strlen($val['alokasi']) > 6 ? (isset($optnamavhc[$val['alokasi']]) ? $optnamavhc[$val['alokasi']] : '') : (isset($optsubunit[$val['alokasi']]) ? $optsubunit[$val['alokasi']] : ''),
					'hari' => array(),
					'total' => 0
				);
			}
			$arr[$k]['hari'][$val['tanggal']] = array('kmhm' => $val['kmhm'], 'jumlah' => $val['jumlah']);
			$arr[$k]['total'] += $val['jumlah'];
		}
		$data['baris'] = array_values($arr);
	}
	foreach ($data['baris'] as $r) {
		foreach ($r['hari'] as $tg => $h) {
			@$data['totaltgl'][$tg] += $h['jumlah'];
		}
		$data['total'] += $r['total'];
	}
	return $data;
}

# angka liter ringkas: 5 / 7.5 / 19.25
function bbmAngka($v)
{
	return rtrim(rtrim(number_format((float)$v, 2, '.', ''), '0'), '.');
}

function bbmInfoFilter($unit, $periode, $jenisbbm, $tampilan, $kodebarang)
{
	global $dbname;
	$nmunit = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $unit . "'");
	$nmbrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang in ('" . implode("','", $kodebarang) . "')");
	$bbm = array();
	foreach ($kodebarang as $kb) {
		$bbm[] = isset($nmbrg[$kb]) ? $nmbrg[$kb] : $kb;
	}
	return 'Unit: ' . $unit . (isset($nmunit[$unit]) ? ' - ' . $nmunit[$unit] : '') . ' | Periode: ' . $periode . ' | Jenis BBM: ' . ($jenisbbm == 'all' ? 'Semua BBM' : implode(', ', $bbm)) . ' | Tampilan: ' . ($tampilan == 'karyawan' ? 'Per Karyawan Penerima' : ($tampilan == 'divisi' ? 'Per Divisi' : 'Per Kendaraan / Alokasi'));
}

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
$jenisbbm = checkPostGet('jenisbbm', '');
$tampilan = checkPostGet('tampilan', '');
$penerima = checkPostGet('penerima', '');
$divisi = checkPostGet('divisi', '');
# jenis BBM: kosong = sesuai setup (BRGOPRBBM), all = semua barang kelompok BBM (351), atau satu kode barang
if ($jenisbbm == 'all') {
	$dftrkodebarang = array();
	foreach (fetchData("select kodebarang from " . $dbname . ".log_5masterbarang where kodebarang like '351%' and inactive=0") as $brg) {
		$dftrkodebarang[] = $brg['kodebarang'];
	}
} else if (preg_match('/^[0-9]+$/', $jenisbbm)) {
	$dftrkodebarang = array($jenisbbm);
}

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
		if ($tampilan == 'divisi') {
			$d = bbmAmbilData($unit, $periode, $dftrkodebarang, $tampilan);
			if ($tipeprint == 'html') {
				$border = "border=0";
			} else {
				$border = "border=1";
			}
			$tab = "<table cellpadding=5 cellspacing=1 " . $border . " class=sortable style='width:100%'>
				<thead>
				<tr class=rowheader style='text-align:center;font-weight:bold'>
					<th>" . $_SESSION['lang']['nourut'] . "</th>
					<th>Kode Divisi</th>
					<th>Nama Divisi</th>";
			foreach ($d['tgl'] as $tgl) {
				$dayOfWeek = date('w', strtotime($tgl));
				$tab .= "<th" . ($dayOfWeek == 0 ? " style='color:red'" : "") . ">" . substr($tgl, 8, 2) . "</th>";
			}
			$tab .= "<th>" . $_SESSION['lang']['total'] . "</th>
				</tr>
				</thead><tbody>";
			$no = 0;
			foreach ($d['baris'] as $r) {
				$no++;
				$tab .= "<tr class='rowcontent'>
					<td align='center'>" . $no . "</td>
					<td align='center'>" . $r['kode'] . "</td>
					<td>" . htmlspecialchars($r['nama']) . "</td>";
				foreach ($d['tgl'] as $tgl) {
					if (isset($r['hari'][$tgl]) && $r['hari'][$tgl]['jumlah'] != 0) {
						$tab .= "<td style='min-width:30px;color:blue;cursor:pointer' align='center' onclick=\"detailDivisi('" . $r['kode'] . "','" . $tgl . "')\">" . round($r['hari'][$tgl]['jumlah'], 2) . "</td>";
					} else {
						$tab .= "<td style='min-width:30px' align='center'></td>";
					}
				}
				$tab .= "<td align='center' style='background-color:green;color:white;font-weight:bold'>" . round($r['total'], 2) . "</td></tr>";
			}
			$tab .= "<tr class='rowcontent' style='background-color:green;color:white;font-weight:bold'>
				<td colspan='3' align=center>Total</td>";
			foreach ($d['tgl'] as $tgl) {
				$tab .= "<td align=center>" . (isset($d['totaltgl'][$tgl]) ? round($d['totaltgl'][$tgl], 2) : '') . "</td>";
			}
			$tab .= "<td align=center>" . round($d['total'], 2) . "</td></tr></tbody></table>";
			bbmKeluarkan($tab, $tipeprint, $unit, $periode);
			break;
		}
		if ($tampilan == 'karyawan') {
			$gettglawal = $periode . "-01";
			$gettglakhir = tglakhir($periode);
			$rangetgl = rangeTanggalarr($gettglawal, $gettglakhir);

			$str = "select namapenerima,tanggal,sum(jumlah) as jumlah,group_concat(distinct if(kodemesin<>'',kodemesin,null) separator ',') as mesin from " . $dbname . ".log_transaksi_vw where kodegudang like '" . $unit . "%' and tipetransaksi = '5' and kodebarang in ('" . implode("','", $dftrkodebarang) . "') and tanggal like '" . $periode . "%' group by namapenerima,tanggal";
			$res = fetchdata($str);
			$arrpenerima = array();
			$arrmesin = array();
			$arrtotalpenerima = array();
			$arrtotaltgl = array();
			foreach ($res as $val) {
				$arrpenerima[$val['namapenerima']][$val['tanggal']] = $val['jumlah'];
				@$arrtotalpenerima[$val['namapenerima']] += $val['jumlah'];
				@$arrtotaltgl[$val['tanggal']] += $val['jumlah'];
				if ($val['mesin'] != '') {
					foreach (explode(',', $val['mesin']) as $msn) {
						$arrmesin[$val['namapenerima']][$msn] = $msn;
					}
				}
			}

			$optkary = array();
			if (count($arrpenerima) > 0) {
				$resk = fetchdata("select lpad(karyawanid,10,'0') as id,namakaryawan from " . $dbname . ".datakaryawan where karyawanid in (" . implode(',', array_map('intval', array_keys($arrpenerima))) . ")");
				foreach ($resk as $kary) {
					$optkary[$kary['id']] = $kary['namakaryawan'];
				}
			}
			uksort($arrpenerima, function ($a, $b) use ($optkary) {
				return strcasecmp(isset($optkary[$a]) ? $optkary[$a] : $a, isset($optkary[$b]) ? $optkary[$b] : $b);
			});

			if ($tipeprint == 'html') {
				$border = "border=0";
			} else {
				$border = "border=1";
			}

			$tab = "<table cellpadding=5 cellspacing=1 " . $border . " class=sortable style='width:100%'>
				<thead>
				<tr class=rowheader style='text-align:center;font-weight:bold'>
					<th>" . $_SESSION['lang']['nourut'] . "</th>
					<th>No. Kendaraan</th>
					<th>" . $_SESSION['lang']['namakaryawan'] . "</th>";
			foreach ($rangetgl as $tgl) {
				$dayOfWeek = date('w', strtotime($tgl));
				if ($dayOfWeek == 0) {
					$tab .= "<th style='color:red'>" . substr($tgl, 8, 2) . "</th>";
				} else {
					$tab .= "<th>" . substr($tgl, 8, 2) . "</th>";
				}
			}
			$tab .= "<th>" . $_SESSION['lang']['total'] . "</th>
				</tr>
				</thead><tbody>";

			$no = 0;
			foreach ($arrpenerima as $idpen => $dtgl) {
				$no++;
				$nmpen = isset($optkary[$idpen]) ? $optkary[$idpen] : ($idpen == '' ? '(tanpa penerima)' : $idpen);
				$tab .= "<tr class='rowcontent'>
					<td align='center'>" . $no . "</td>
					<td align='center'>" . (isset($arrmesin[$idpen]) ? implode(', ', $arrmesin[$idpen]) : '') . "</td>
					<td>" . $nmpen . "</td>";
				foreach ($rangetgl as $tgl) {
					if (isset($dtgl[$tgl]) && $dtgl[$tgl] != 0) {
						$tab .= "<td style='min-width:30px;color:blue;cursor:pointer' align='center' onclick=\"detailKaryawan('" . $idpen . "','" . $tgl . "')\">" . round($dtgl[$tgl], 2) . "</td>";
					} else {
						$tab .= "<td style='min-width:30px' align='center'></td>";
					}
				}
				$tab .= "<td align='center' style='background-color:green;color:white;font-weight:bold'>" . round($arrtotalpenerima[$idpen], 2) . "</td>
					</tr>";
			}

			$tab .= "<tr class='rowcontent' style='background-color:green;color:white;font-weight:bold'>
				<td colspan='3' align=center>Total</td>";
			$ttlall = 0;
			foreach ($rangetgl as $tgl) {
				$tab .= "<td align=center>" . (isset($arrtotaltgl[$tgl]) ? round($arrtotaltgl[$tgl], 2) : '') . "</td>";
				$ttlall += isset($arrtotaltgl[$tgl]) ? $arrtotaltgl[$tgl] : 0;
			}
			$tab .= "<td align=center>" . round($ttlall, 2) . "</td></tr></tbody></table>";

			bbmKeluarkan($tab, $tipeprint, $unit, $periode);
			break;
		}



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
		$str = "(SELECT SUBSTR(kodemesin, 5, 2) AS kodejenis,kodemesin AS alokasi,tanggal,MAX(kmhm) AS kmhm,SUM(jumlah) AS jumlah FROM " . $dbname . ".log_transaksi_vw WHERE kodegudang like '" . $unit . "%' " . $where . " and kodemesin != '' and tipetransaksi = '5' and kodebarang in ('" . implode("','", $dftrkodebarang) . "') GROUP BY alokasi,tanggal )
			UNION ALL 
			(SELECT kodekegiatan AS kodejenis,kodeblok AS alokasi,tanggal,MAX(kmhm) AS kmhm,SUM(jumlah) AS jumlah FROM " . $dbname . ".log_transaksi_vw WHERE kodegudang like '" . $unit . "%' " . $where . " and tipetransaksi = '5' and kodebarang in ('" . implode("','", $dftrkodebarang) . "') AND kodekegiatan IN (SELECT kodekegiatan FROM `setup_kegiatan` WHERE `noakun` LIKE '%7140909%' AND `namakegiatan` LIKE '%genset%') group by alokasi,tanggal)
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

	case 'excellist':
		$d = bbmAmbilData($unit, $periode, $dftrkodebarang, $tampilan);
		$ptinduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $unit . "'");
		$pt = isset($ptinduk[$unit]) && $ptinduk[$unit] != '' ? $ptinduk[$unit] : $unit;
		$hdpt = setheadreport($pt, $pt);
		$kar = ($tampilan == 'karyawan' || $tampilan == 'divisi');
		$lbl = bbmLabelKolom($tampilan);
		$jmltgl = count($d['tgl']);
		$kolom = 4 + ($kar ? $jmltgl : $jmltgl * 2);
		$stream = "<table>
			<tr><td colspan=" . $kolom . "><b>" . htmlspecialchars($hdpt['nama']) . "</b></td></tr>
			<tr><td colspan=" . $kolom . "><b>LAPORAN OPERASIONAL BBM</b></td></tr>
			<tr><td colspan=" . $kolom . ">" . htmlspecialchars(bbmInfoFilter($unit, $periode, $jenisbbm, $tampilan, $dftrkodebarang)) . "</td></tr>
			<tr><td colspan=" . $kolom . ">Ditarik oleh " . htmlspecialchars($_SESSION['empl']['name']) . " (" . htmlspecialchars($_SESSION['standard']['username']) . ") pada " . date('d-m-Y H:i:s') . "</td></tr>
			<tr><td colspan=" . $kolom . ">&nbsp;</td></tr>
			</table>";
		$rs = $kar ? '' : " rowspan=2";
		$stream .= "<table class=sortable cellspacing=1 cellpadding=3 border=1>
			<thead><tr>
				<th align=center bgcolor=#CCCCCC" . $rs . ">No</th>
				<th align=center bgcolor=#CCCCCC" . $rs . ">" . $lbl[0] . "</th>
				<th align=center bgcolor=#CCCCCC" . $rs . ">" . $lbl[1] . "</th>";
		foreach ($d['tgl'] as $tg) {
			$stream .= "<th align=center bgcolor=#CCCCCC" . ($kar ? '' : ' colspan=2') . ">" . substr($tg, 8, 2) . "</th>";
		}
		$stream .= "<th align=center bgcolor=#CCCCCC" . $rs . ">Total (L)</th></tr>";
		if (!$kar) {
			$stream .= "<tr>";
			foreach ($d['tgl'] as $tg) {
				$stream .= "<th align=center bgcolor=#CCCCCC>KM/HM</th><th align=center bgcolor=#CCCCCC>L</th>";
			}
			$stream .= "</tr>";
		}
		$stream .= "</thead><tbody>";
		$no = 0;
		$grup = '';
		foreach ($d['baris'] as $r) {
			if (!$kar && $r['grup'] != $grup) {
				$grup = $r['grup'];
				$stream .= "<tr><td colspan=" . $kolom . " bgcolor=#FFFF99><b>" . htmlspecialchars($grup) . "</b></td></tr>";
			}
			$no++;
			$stream .= "<tr><td align=center>" . $no . "</td><td>" . htmlspecialchars($r['kode']) . "</td><td>" . htmlspecialchars($r['nama']) . "</td>";
			foreach ($d['tgl'] as $tg) {
				$h = isset($r['hari'][$tg]) && $r['hari'][$tg]['jumlah'] != 0 ? $r['hari'][$tg] : null;
				if (!$kar) {
					$stream .= "<td align=right>" . ($h ? bbmAngka($h['kmhm']) : '') . "</td>";
				}
				$stream .= "<td align=right>" . ($h ? bbmAngka($h['jumlah']) : '') . "</td>";
			}
			$stream .= "<td align=right><b>" . bbmAngka($r['total']) . "</b></td></tr>";
		}
		if ($no == 0) {
			$stream .= "<tr><td colspan=" . $kolom . " align=center>Data tidak ditemukan</td></tr>";
		} else {
			$stream .= "<tr><td colspan=3 align=center bgcolor=#CCCCCC><b>Total</b></td>";
			foreach ($d['tgl'] as $tg) {
				if (!$kar) {
					$stream .= "<td bgcolor=#CCCCCC></td>";
				}
				$stream .= "<td align=right bgcolor=#CCCCCC><b>" . (isset($d['totaltgl'][$tg]) ? bbmAngka($d['totaltgl'][$tg]) : '') . "</b></td>";
			}
			$stream .= "<td align=right bgcolor=#CCCCCC><b>" . bbmAngka($d['total']) . "</b></td></tr>";
		}
		$stream .= "</tbody></table>";
		$xls = new HtmlExcel();
		$xls->addSheet("Operasional BBM", $stream);
		$xls->headers("OperasionalBBM_" . $unit . "_" . $periode . ".xls");
		echo $xls->buildFile();
		exit;

	case 'pdflist':
		$d = bbmAmbilData($unit, $periode, $dftrkodebarang, $tampilan);
		$ptinduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $unit . "'");
		$pt = isset($ptinduk[$unit]) && $ptinduk[$unit] != '' ? $ptinduk[$unit] : $unit;
		$hdpt = setheadreport($pt, $pt);
		$infoFilter = bbmInfoFilter($unit, $periode, $jenisbbm, $tampilan, $dftrkodebarang);
		$kar = ($tampilan == 'karyawan' || $tampilan == 'divisi');
		$lbl = bbmLabelKolom($tampilan);
		$tglPdf = $d['tgl'];
		if (!class_exists('PDFListBBM')) {
			class PDFListBBM extends FPDF
			{
				public $colW = array();
				public $tglPdf = array();
				public $kar = false;
				public $lbl = array('Kode', 'Nama');

				function Header()
				{
					global $hdpt, $infoFilter;
					$width = $this->w - $this->lMargin - $this->rMargin;
					if (file_exists($hdpt['logo'])) {
						$this->Image($hdpt['logo'], $this->lMargin, $this->tMargin, 18);
					}
					$this->SetFont('Arial', 'B', 11);
					$this->SetXY($this->lMargin + 22, $this->tMargin + 2);
					$this->Cell(160, 6, utf8_decode($hdpt['nama']), 0, 1, 'L');
					$this->SetY($this->tMargin + 15);
					$this->SetFont('Arial', 'B', 13);
					$this->Cell($width, 7, 'LAPORAN OPERASIONAL BBM', 0, 1, 'C');
					$this->SetFont('Arial', '', 8);
					$this->Cell($width, 5, utf8_decode($infoFilter), 0, 1, 'C');
					$this->Cell($width, 5, utf8_decode('Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s')) . ($this->kar ? '' : utf8_decode('  |  Liter per hari (KM/HM hanya ada di Excel)')), 0, 1, 'C');
					$this->Ln(1);
					$this->SetFillColor(220, 220, 220);
					$this->SetFont('Arial', 'B', 6);
					$i = 0;
					foreach (array_merge(array('No'), $this->lbl) as $j) {
						$this->Cell($this->colW[$i++], 6, $j, 1, 0, 'C', true);
					}
					foreach ($this->tglPdf as $tg) {
						$this->Cell($this->colW[$i++], 6, substr($tg, 8, 2), 1, 0, 'C', true);
					}
					$this->Cell($this->colW[$i], 6, 'Total', 1, 1, 'C', true);
				}

				function Footer()
				{
					$this->SetY(-12);
					$this->SetFont('Arial', 'I', 7);
					$this->Cell(0, 8, 'Halaman ' . $this->PageNo() . ' / {nb}', 0, 0, 'R');
				}
			}
		}
		if (!function_exists('bbmFit')) {
			function bbmFit($pdf, $teks, $w)
			{
				$teks = utf8_decode((string)$teks);
				while (strlen($teks) > 0 && $pdf->GetStringWidth($teks) > $w - 1.5) {
					$teks = substr($teks, 0, -1);
				}
				return $teks;
			}
		}
		$pdf = new PDFListBBM('L', 'mm', 'A4');
		$pdf->kar = $kar;
		$pdf->lbl = $lbl;
		$pdf->tglPdf = $tglPdf;
		$pdf->AliasNbPages();
		$pdf->SetAutoPageBreak(false, 16);
		$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
		$wTgl = ($width - (7 + 26 + 44 + 12)) / count($tglPdf);
		$pdf->colW = array_merge(array(7, 26, 44), array_fill(0, count($tglPdf), $wTgl), array(12));
		$pdf->AddPage();
		$h = 5;
		$no = 0;
		$grup = '';
		foreach ($d['baris'] as $r) {
			if (!$kar && $r['grup'] != $grup) {
				$grup = $r['grup'];
				if ($pdf->GetY() + 2 * $h > $pdf->h - $pdf->bMargin) {
					$pdf->AddPage();
				}
				$pdf->SetFont('Arial', 'B', 7);
				$pdf->SetFillColor(255, 255, 153);
				$pdf->Cell(array_sum($pdf->colW), $h, utf8_decode($grup), 1, 1, 'L', true);
			}
			if ($pdf->GetY() + $h > $pdf->h - $pdf->bMargin) {
				$pdf->AddPage();
			}
			$no++;
			$c = $pdf->colW;
			$pdf->SetFont('Arial', '', 6);
			$pdf->Cell($c[0], $h, $no, 1, 0, 'C');
			$pdf->Cell($c[1], $h, bbmFit($pdf, $r['kode'], $c[1]), 1, 0, 'L');
			$pdf->Cell($c[2], $h, bbmFit($pdf, $r['nama'], $c[2]), 1, 0, 'L');
			$i = 3;
			foreach ($tglPdf as $tg) {
				$v = isset($r['hari'][$tg]) && $r['hari'][$tg]['jumlah'] != 0 ? bbmAngka($r['hari'][$tg]['jumlah']) : '';
				$pdf->Cell($c[$i++], $h, $v, 1, 0, 'C');
			}
			$pdf->SetFont('Arial', 'B', 6);
			$pdf->Cell($c[$i], $h, bbmAngka($r['total']), 1, 1, 'R');
		}
		if ($no == 0) {
			$pdf->Cell(array_sum($pdf->colW), $h, 'Data tidak ditemukan', 1, 1, 'C');
		} else {
			if ($pdf->GetY() + $h > $pdf->h - $pdf->bMargin) {
				$pdf->AddPage();
			}
			$c = $pdf->colW;
			$pdf->SetFont('Arial', 'B', 6);
			$pdf->SetFillColor(220, 220, 220);
			$pdf->Cell($c[0] + $c[1] + $c[2], $h, 'Total', 1, 0, 'C', true);
			$i = 3;
			foreach ($tglPdf as $tg) {
				$pdf->Cell($c[$i++], $h, isset($d['totaltgl'][$tg]) ? bbmAngka($d['totaltgl'][$tg]) : '', 1, 0, 'C', true);
			}
			$pdf->Cell($c[$i], $h, bbmAngka($d['total']), 1, 1, 'R', true);
		}
		$pdf->Output();
		exit;

	case 'detail':
		$optbarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang like '351%'");
		if ($tampilan == 'divisi') {
			$optorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
			$optkegiatan = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
			$dvcari = ($divisi == '-') ? '' : $divisi;
			$res = fetchdata("select * from " . $dbname . ".log_transaksi_vw where tanggal = '" . $tanggal . "' and kodegudang like '" . $unit . "%' and tipetransaksi = '5' and kodebarang in ('" . implode("','", $dftrkodebarang) . "') order by notransaksi");
			$ids = array();
			foreach ($res as $val) {
				if (bbmDivisi($val['kodeblok'], $optorg) === $dvcari && $val['namapenerima'] != '') {
					$ids[intval($val['namapenerima'])] = intval($val['namapenerima']);
				}
			}
			$optkary = array();
			if (count($ids) > 0) {
				foreach (fetchdata("select lpad(karyawanid,10,'0') as id,namakaryawan from " . $dbname . ".datakaryawan where karyawanid in (" . implode(',', $ids) . ")") as $kary) {
					$optkary[$kary['id']] = $kary['namakaryawan'];
				}
			}
			$tab = "<table cellpadding=5 cellspacing=1 class=sortable style='width:100%'>
				<thead>
				<tr class=rowheader style='text-align:center;font-weight:bold'>
					<th>" . $_SESSION['lang']['nourut'] . "</th>
					<th>" . $_SESSION['lang']['notransaksi'] . "</th>
					<th>Bahan Bakar</th>
					<th>Penerima</th>
					<th>" . $_SESSION['lang']['kode'] . "</th>
					<th>Jumlah</th>
					<th>" . $_SESSION['lang']['kegiatan'] . "</th>
					<th>" . $_SESSION['lang']['catatan'] . "</th>
				</tr>
				</thead><tbody>";
			$no = 0;
			foreach ($res as $val) {
				if (bbmDivisi($val['kodeblok'], $optorg) !== $dvcari) {
					continue;
				}
				$no++;
				$tab .= "<tr class='rowcontent'>
					<td align='center'>" . $no . "</td>
					<td align='center'>" . $val['notransaksi'] . "</td>
					<td align='center'>" . $optbarang[$val['kodebarang']] . "</td>
					<td align='center'>" . (isset($optkary[$val['namapenerima']]) ? $optkary[$val['namapenerima']] : $val['namapenerima']) . "</td>
					<td align='center'>" . ($val['kodemesin'] != '' ? $val['kodemesin'] : $val['kodeblok']) . "</td>
					<td align='center'>" . $val['jumlah'] . "</td>
					<td align='center'>" . $optkegiatan[$val['kodekegiatan']] . "</td>
					<td align='center'>" . $val['keterangan'] . "</td>
					</tr>";
			}
			$tab .= "</tbody></table>";
			echo $tab;
			break;
		}
		if ($tampilan == 'karyawan') {
			$optkegiatan = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
			$str = "select * from " . $dbname . ".log_transaksi_vw where namapenerima='" . $penerima . "' and tanggal = '" . $tanggal . "' and kodegudang like '" . $unit . "%' and tipetransaksi = '5' and kodebarang in ('" . implode("','", $dftrkodebarang) . "') order by notransaksi";
			$res = fetchdata($str);
			$tab = "<table cellpadding=5 cellspacing=1 class=sortable style='width:100%'>
				<thead>
				<tr class=rowheader style='text-align:center;font-weight:bold'>
					<th>" . $_SESSION['lang']['nourut'] . "</th>
					<th>" . $_SESSION['lang']['notransaksi'] . "</th>
					<th>Bahan Bakar</th>
					<th>" . $_SESSION['lang']['kode'] . "</th>
					<th>KMHM</th>
					<th>Jumlah</th>
					<th>" . $_SESSION['lang']['kegiatan'] . "</th>
					<th>" . $_SESSION['lang']['catatan'] . "</th>
				</tr>
				</thead><tbody>";
			$no = 0;
			foreach ($res as $val) {
				$no++;
				$tab .= "<tr class='rowcontent'>
					<td align='center'>" . $no . "</td>
					<td align='center'>" . $val['notransaksi'] . "</td>
					<td align='center'>" . $optbarang[$val['kodebarang']] . "</td>
					<td align='center'>" . ($val['kodemesin'] != '' ? $val['kodemesin'] : $val['kodeblok']) . "</td>
					<td align='center'>" . $val['kmhm'] . "</td>
					<td align='center'>" . $val['jumlah'] . "</td>
					<td align='center'>" . $optkegiatan[$val['kodekegiatan']] . "</td>
					<td align='center'>" . $val['keterangan'] . "</td>
					</tr>";
			}
			$tab .= "</tbody></table>";
			echo $tab;
			break;
		}
		$optnamavhc = makeOption($dbname, 'vhc_5master', 'kodevhc,detailvhc');
		$optsubunit = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
		$optkegiatan = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
		$tab = "<table cellpadding=5 cellspacing=1 " . $border . " class=sortable style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center;font-weight:bold'>
				<th>" . $_SESSION['lang']['nourut'] . "</th>
				<th>" . $_SESSION['lang']['notransaksi'] . "</th>
				<th>Bahan Bakar</th>
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
					$tab .= "<td align='center'>" . $optbarang[$val['kodebarang']] . "</td>";
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
					$tab .= "<td align='center'>" . $optbarang[$val['kodebarang']] . "</td>";
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
