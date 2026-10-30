<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
$param = (($_POST == array()) ? $_GET : $_POST);
$proses = checkPostGet('proses', '');
$pt = checkPostGet('pt', '');
$unit = checkPostGet('unit', '');
$periode = checkPostGet('periode', '');
$status = checkPostGet('status', 'aktif');

$optTip = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
$optOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

switch ($proses) {
	case 'getunit':
		$optun = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		$sKbn = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='" . $pt . "' and length(kodeorganisasi)='4' order by namaorganisasi asc";
		$qKbn = $owlPDO->query($sKbn) or die(print " Gagal: " . PDOException::getMessage());
		$qKbn->setFetchMode(PDO::FETCH_ASSOC);
		while ($rKbn = $qKbn->fetch()) {
			$optun .= "<option value=" . $rKbn['kodeorganisasi'] . ">" . $rKbn['kodeorganisasi'] . " - " . $rKbn['namaorganisasi'] . "</option>";
		}
		echo $optun;
		break;

	case 'getperiode':
		$optPeriode = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		if ($unit == '') {
			$str = "select distinct(a.periode) from " . $dbname . ".sdm_5periodegaji a
					left join " . $dbname . ".organisasi b on a.kodeorg=b.kodeorganisasi
					where b.induk ='" . $pt . "' and a.sudahproses='1' order by periode desc";
		} else {
			$str = "select distinct(periode) from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $unit . "'
					and sudahproses='1' order by periode desc";
		}
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {
			$optPeriode .= "<option value='" . $bar->periode . "'>" . $bar->periode . "</option>";
		}
		echo $optPeriode;
		break;
}

if ($proses == 'preview' || $proses == 'excel') {

	// unit kosong = seluruh unit di bawah PT
	if ($unit == '') {
		$where = "where induk='" . $pt . "' and length(kodeorganisasi)='4'";
	} else {
		$where = "where kodeorganisasi = '" . $unit . "'";
	}

	// status filter
	if ($status == 'tidakaktif') {
		$whr_hist = " and h.statuskaryawan='Keluar'";
		$whr_cur = " and statuskaryawan='Keluar'";
	} else if ($status == 'seluruhnya') {
		$whr_hist = "";
		$whr_cur = "";
	} else {
		$whr_hist = " and h.statuskaryawan<>'Keluar'";
		$whr_cur = " and statuskaryawan<>'Keluar'";
	}

	// ambil daftar unit
	$arrunit = array();
	$strunit = "select * from " . $dbname . ".organisasi " . $where . " order by kodeorganisasi asc";
	$resunit = $owlPDO->query($strunit) or die(print " Gagal: " . PDOException::getMessage());
	$resunit->setFetchMode(PDO::FETCH_ASSOC);
	while ($barunit = $resunit->fetch()) {
		$arrunit[$barunit['kodeorganisasi']] = $barunit['namaorganisasi'];
	}

	// agregasi: cek snapshot hist per unit, jika tidak ada fallback ke datakaryawan realtime (sesuai acuan sdm_slave_2summarykaryawan)
	$arrjk = array();
	foreach ($arrunit as $kodeunit => $namaunit) {
		$strc = "select count(*) as jml from " . $dbname . ".datakaryawan_hist where periodegaji='" . $periode . "' and lokasitugas='" . $kodeunit . "'";
		$resc = $owlPDO->query($strc) or die(print " Gagal: " . PDOException::getMessage());
		$rowc = $resc->fetch(PDO::FETCH_ASSOC);

		if ($rowc['jml'] > 0) {
			$str = "select h.lokasitugas, h.tipekaryawan, sum(h.jeniskelamin='L') as jkl, sum(h.jeniskelamin='P') as jkp
				from " . $dbname . ".datakaryawan_hist h
				join (select max(nourut) as mx from " . $dbname . ".datakaryawan_hist where periodegaji='" . $periode . "' group by karyawanid) g on h.nourut = g.mx
				where h.periodegaji='" . $periode . "' " . $whr_hist . " and h.lokasitugas = '" . $kodeunit . "'
				group by h.lokasitugas, h.tipekaryawan
				order by h.tipekaryawan asc";
		} else {
			$str = "select lokasitugas, tipekaryawan, sum(jeniskelamin='L') as jkl, sum(jeniskelamin='P') as jkp
				from " . $dbname . ".datakaryawan
				where lokasitugas='" . $kodeunit . "'
				and tanggalmasuk <= '" . $periode . "-31'
				and (tanggalkeluar >= '" . $periode . "-01' or tanggalkeluar = '0000-00-00' or tanggalkeluar is null)
				" . $whr_cur . "
				group by lokasitugas, tipekaryawan
				order by tipekaryawan asc";
		}

		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			@$arrjk[$kodeunit][$bar['tipekaryawan']]['L'] += $bar['jkl'];
			@$arrjk[$kodeunit][$bar['tipekaryawan']]['P'] += $bar['jkp'];
			@$arrinduk[$kodeunit] = $pt;
		}
	}

	// ===== render tabel =====
	if ($proses == 'preview') {
		$tab .= "<table class=sortable cellspacing=1 cellpadding=5 border=0 width=100%>";
	} else {
		$tab .= "<table class=sortable cellspacing=1 border=1>";
	}

	$tab .= "<thead>
		<tr class=rowheader>
			<th rowspan=2 align=center>" . $_SESSION['lang']['pt'] . "</th>
			<th rowspan=2 align=center>" . $_SESSION['lang']['unit'] . "</th>
			<th rowspan=2 align=center>Level Tenaga Kerja</th>
			<th colspan=2 align=center>Jumlah (Orang)</th>
		</tr>
		<tr class=rowheader>
			<th align=center>Pria</th>
			<th align=center>Wanita</th>
		</tr>
		</thead><tbody>";

	$totl = $totp = 0;
	if (!empty($arrjk)) {
		foreach ($arrunit as $kodeunit => $namaunit) {
			if (empty($arrjk[$kodeunit])) {
				continue;
			}
			$subl = $subp = 0;
			$first = true;
			foreach ($arrjk[$kodeunit] as $tipe => $val) {
				$tab .= "<tr class=rowcontent>";
				if ($first) {
					$tab .= "<td align=center>" . $pt . " - " . @$optOrg[$pt] . "</td>";
					$tab .= "<td align=center>" . $kodeunit . " - " . $namaunit . "</td>";
				} else {
					$tab .= "<td></td><td></td>";
				}
				$tab .= "<td align=center>" . @$optTip[$tipe] . "</td>";
				$tab .= "<td align=center>" . $val['L'] . "</td>";
				$tab .= "<td align=center>" . $val['P'] . "</td>";
				$tab .= "</tr>";
				$subl += $val['L'];
				$subp += $val['P'];
				$first = false;
			}
			$tab .= "<tr class=rowcontent style='background-color:#ccc;font-weight:bold;'>
				<td align=center colspan=3>TOTAL " . $pt . " - " . @$optOrg[$pt] . " / " . $kodeunit . " - " . $namaunit . "</td>
				<td align=center>" . $subl . "</td>
				<td align=center>" . $subp . "</td>
			</tr>";
			$totl += $subl;
			$totp += $subp;
		}
		$tab .= "<tr class=rowcontent style='background-color:#999;font-weight:bold;'>
			<td align=center colspan=3>GRAND TOTAL " . $pt . " - " . @$optOrg[$pt] . "</td>
			<td align=center>" . $totl . "</td>
			<td align=center>" . $totp . "</td>
		</tr>";
	} else {
		$tab .= "<tr class=rowcontent><td colspan=5 align=center>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
	}
	$tab .= "</tbody></table>";
}

switch ($proses) {
	case 'preview':
		echo $tab;
		break;
	case 'excel':
		$tab .= "Print Time:" . date('Y-m-d H:i:s') . "<br>By:" . $_SESSION['empl']['name'];
		$nop_ = "tksipditjenbun_" . $pt . "_" . ($unit == '' ? 'ALL' : $unit) . "_" . $periode . "_" . $status;
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
				echo "<script language=javascript1.2>
				parent.window.alert('Cant convert to excel format');
				</script>";
				exit;
			} else {
				echo "<script language=javascript1.2>
				window.location='tempExcel/" . $nop_ . ".xls';
				</script>";
			}
			fclose($handle);
		}
		break;
	default:
		break;
}
?>
