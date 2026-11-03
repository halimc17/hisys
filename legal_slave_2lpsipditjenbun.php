<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
$param = (($_POST == array()) ? $_GET : $_POST);
$proses = checkPostGet('proses', '');
$periode = checkPostGet('periode', '');
$unit = checkPostGet('unit', '');
$intiplasma = checkPostGet('intiplasma', '');
$jenis = checkPostGet('jenis', '');

if ($proses == 'preview' || $proses == 'excel') {

	if ($periode == '') {
		exit("Error : Periode tidak boleh kosong");
	}
	if ($jenis == '') {
		exit("Error : Jenis produksi tidak boleh kosong");
	}

	// filter unit (kebun)
	$whrUnit = "";
	if ($unit != '') {
		$whrUnit = " and v.kodeorg='" . $unit . "'";
	}

	// filter inti/plasma via organisasi.inti (1=Inti, 0=Plasma)
	$whrIntiplasma = "";
	if ($intiplasma == 'I') {
		$whrIntiplasma = " and o.inti='1'";
	} elseif ($intiplasma == 'P') {
		$whrIntiplasma = " and o.inti='0'";
	}

	// nama jenis produksi
	$strNama = "select namabarang from " . $dbname . ".log_5masterbarang where kodebarang='" . $jenis . "'";
	$qNama = $owlPDO->query($strNama) or die(print " Gagal: " . PDOException::getMessage());
	$qNama->setFetchMode(PDO::FETCH_ASSOC);
	$rNama = $qNama->fetch();
	$namaJenis = $rNama['namabarang'];

	// daftar kebun sesuai filter (nama + inti/plasma)
	$arrKebun = array();
	$strKbn = "select kodeorganisasi, namaorganisasi, inti from " . $dbname . ".organisasi
				where tipe='KEBUN' and kodeorganisasi in (select distinct kodeorg from " . $dbname . ".pabrik_timbangan where kodebarang='" . $jenis . "' and substr(tanggal,1,4)='" . $periode . "' and kodeorg!='')" .
				$whrIntiplasma . "
				order by kodeorganisasi asc";
	if ($unit != '') {
		$strKbn = "select kodeorganisasi, namaorganisasi, inti from " . $dbname . ".organisasi
					where tipe='KEBUN' and kodeorganisasi='" . $unit . "'" . $whrIntiplasma . "
					order by kodeorganisasi asc";
	}
	$qKbn = $owlPDO->query($strKbn) or die(print " Gagal: " . PDOException::getMessage());
	$qKbn->setFetchMode(PDO::FETCH_ASSOC);
	while ($rKbn = $qKbn->fetch()) {
		$arrKebun[$rKbn['kodeorganisasi']] = array('nama' => $rKbn['namaorganisasi'], 'inti' => $rKbn['inti']);
	}

	// agregasi per unit per bulan
	$arrData = array();
	$str = "select v.kodeorg, substr(v.tanggal,1,7) as bulan, sum(v.beratbersih) as jumlah
			from " . $dbname . ".pabrik_timbangan v
			join " . $dbname . ".organisasi o on o.kodeorganisasi=v.kodeorg
			where v.kodebarang='" . $jenis . "'
			and substr(v.tanggal,1,4)='" . $periode . "'
			and v.kodeorg!=''" .
			$whrUnit . $whrIntiplasma . "
			group by v.kodeorg, substr(v.tanggal,1,7)
			order by v.kodeorg asc, bulan asc";
	$qData = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$qData->setFetchMode(PDO::FETCH_ASSOC);
	while ($rData = $qData->fetch()) {
		$bln = (int)substr($rData['bulan'], 5, 2);
		$arrData[$rData['kodeorg']][$bln] = $rData['jumlah'];
	}

	// ===== render tabel =====
	if ($proses == 'preview') {
		$tab .= "<table class=sortable cellspacing=1 cellpadding=5 border=0 width=100%>";
	} else {
		$tab .= "<table class=sortable cellspacing=1 border=1>";
	}

	$tab .= "<thead>
		<tr class=rowheader>
			<th rowspan=2 align=center>" . $_SESSION['lang']['nomor'] . "</th>
			<th rowspan=2 align=center>" . $_SESSION['lang']['bulan'] . "</th>
			<th rowspan=2 align=center>Jenis Produksi</th>
			<th rowspan=2 align=center>Jumlah (KG)</th>
		</tr>
		</thead><tbody>";

	$grandTotal = 0;
	$no = 0;
	if (!empty($arrKebun) && !empty($arrData)) {
		foreach ($arrKebun as $kodekebun => $valkebun) {
			if (empty($arrData[$kodekebun])) {
				continue;
			}
			$no++;
			$tab .= "<tr class=rowheader>
				<td colspan=4 align=left><b>" . $kodekebun . " - " . $valkebun['nama'] . " (" . ($valkebun['inti'] == '1' ? 'INTI' : 'PLASMA') . ")</b>
				</td>
			</tr>";

			$sub1 = $sub2 = 0;
			for ($bln = 1; $bln <= 12; $bln++) {
				$jml = isset($arrData[$kodekebun][$bln]) ? $arrData[$kodekebun][$bln] : 0;
				if ($bln <= 6) {
					$sub1 += $jml;
				} else {
					$sub2 += $jml;
				}
				$tab .= "<tr class=rowcontent>";
				$tab .= "<td align=center>" . $bln . "</td>";
				$tab .= "<td align=center>" . str_pad($bln, 2, '0', STR_PAD_LEFT) . "-" . $periode . "</td>";
				$tab .= "<td align=center>" . $namaJenis . "</td>";
				$tab .= "<td align=right>" . number_format($jml, 0) . "</td>";
				$tab .= "</tr>";

				// subtotal 1-6 setelah bulan 6
				if ($bln == 6) {
					$tab .= "<tr class=rowcontent style='background-color:#ccc;font-weight:bold;'>
						<td colspan=3 align=right>SUBTOTAL BULAN 1-6</td>
						<td align=right>" . number_format($sub1, 0) . "</td>
					</tr>";
				}
			}
			// subtotal 7-12
			$tab .= "<tr class=rowcontent style='background-color:#ccc;font-weight:bold;'>
				<td colspan=3 align=right>SUBTOTAL BULAN 7-12</td>
				<td align=right>" . number_format($sub2, 0) . "</td>
			</tr>";
			// total per unit
			$tab .= "<tr class=rowcontent style='background-color:#bbb;font-weight:bold;'>
				<td colspan=3 align=right>TOTAL " . $kodekebun . " - " . $valkebun['nama'] . "</td>
				<td align=right>" . number_format($sub1 + $sub2, 0) . "</td>
			</tr>";
			$grandTotal += ($sub1 + $sub2);
		}
		$tab .= "<tr class=rowcontent style='background-color:#999;font-weight:bold;'>
			<td colspan=3 align=right>GRAND TOTAL " . $periode . " (" . $namaJenis . ")</td>
			<td align=right>" . number_format($grandTotal, 0) . "</td>
		</tr>";
	} else {
		$tab .= "<tr class=rowcontent><td colspan=4 align=center>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
	}
	$tab .= "</tbody></table>";
}

switch ($proses) {
	case 'preview':
		echo $tab;
		break;
	case 'excel':
		$tab .= "Print Time:" . date('Y-m-d H:i:s') . "<br>By:" . $_SESSION['empl']['name'];
		$nop_ = "lpsipditjenbun_" . ($unit == '' ? 'ALL' : $unit) . "_" . $periode . "_" . $jenis . "_" . ($intiplasma == '' ? 'ALL' : $intiplasma);
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
