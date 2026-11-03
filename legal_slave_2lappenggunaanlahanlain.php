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

switch ($proses) {
	case 'getunit':
		$optun = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		$sKbn = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='" . $pt . "' and length(kodeorganisasi)='4' and tipe='KEBUN' order by namaorganisasi asc";
		$qKbn = $owlPDO->query($sKbn) or die(print " Gagal: " . PDOException::getMessage());
		$qKbn->setFetchMode(PDO::FETCH_ASSOC);
		while ($rKbn = $qKbn->fetch()) {
			$optun .= "<option value=" . $rKbn['kodeorganisasi'] . ">" . $rKbn['kodeorganisasi'] . " - " . $rKbn['namaorganisasi'] . "</option>";
		}
		echo $optun;
		break;
	case 'getperiode':
		$optPeriode = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "select distinct tahun from " . $dbname . ".setup_blok_tahunan where tahun!='' order by tahun desc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {
			$optPeriode .= "<option value='" . $bar->tahun . "'>" . substr($bar->tahun, 4, 2) . "-" . substr($bar->tahun, 0, 4) . "</option>";
		}
		echo $optPeriode;
		break;
	case 'preview':
	case 'excel':
		if ($pt == '') {
			exit("Error : PT tidak boleh kosong");
		}
		if ($periode == '') {
			exit("Error : Periode tidak boleh kosong");
		}

		// daftar unit (kebun) sesuai filter PT/unit;
		$whrUnit = " and substr(v.kodeorg,1,4) in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $pt . "' and length(kodeorganisasi)='4')";
		if ($unit != '') {
			$whrUnit = " and substr(v.kodeorg,1,4)='" . $unit . "'";
		}

		// agregasi total luas penggunaan lahan lainnya per unit (status blok aktif 'A');
		$arrData = array();
		$str = "select substr(v.kodeorg,1,4) as kodeunit, sum(v.rumah) as rumah, sum(v.jalan) as jalan, sum(v.konservasi) as konservasi,
					sum(v.kantor) as kantor, sum(v.pabrik) as pabrik, sum(v.umum) as umum, sum(v.kolam) as kolam,
					sum(v.sungai) as sungai, sum(v.okupasi) as okupasi, sum(v.rendahan) as rendahan, sum(v.arealberbatu) as arealberbatu,
					sum(v.enclave) as enclave, sum(v.lc) as lc, sum(v.luasbloking) as luasbloking, sum(v.cadangan) as cadangan
				from " . $dbname . ".setup_blok_tahunan v
				where v.tahun='" . $periode . "' and v.status='A'" . $whrUnit . "
				group by substr(v.kodeorg,1,4)
				order by kodeunit asc";
		$qData = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
		while ($rData = $qData->fetch()) {
			$arrData[$rData['kodeunit']] = $rData;
		}

		// kelompok jenis penggunaan lainnya;
		// Perumahan=rumah, Jalan=jalan, Konservasi=konservasi, Lainnya=kantor+pabrik+umum+kolam+sungai+okupasi+rendahan+arealberbatu+enclave+lc+luasbloking+cadangan;
		$arrKelompok = array(
			'Perumahan' => array('rumah'),
			'Jalan' => array('jalan'),
			'Konservasi' => array('konservasi'),
			'Lainnya' => array('kantor', 'pabrik', 'umum', 'kolam', 'sungai', 'okupasi', 'rendahan', 'arealberbatu', 'enclave', 'lc', 'luasbloking', 'cadangan')
		);

		// ===== render tabel =====
		if ($proses == 'preview') {
			$tab .= "<table class=sortable cellspacing=1 cellpadding=5 border=0 width=100%>";
		} else {
			$tab .= "<table class=sortable cellspacing=1 border=1>";
		}

		$tab .= "<thead>
			<tr class=rowheader>
				<th rowspan=2 align=center>No</th>
				<th rowspan=2 align=center>Jenis Penggunaan Lainnya</th>
				<th rowspan=2 align=center>Luas</th>
			</tr>
			</thead><tbody>";

		$grandTotal = 0;
		$no = 0;
		if (!empty($arrData)) {
			foreach ($arrKelompok as $namakelompok => $arrKolom) {
				$no++;
				$luas = 0;
				foreach ($arrData as $kodeunit => $val) {
					foreach ($arrKolom as $kolom) {
						$luas += (float)$val[$kolom];
					}
				}
				$grandTotal += $luas;
				$tab .= "<tr class=rowcontent>";
				$tab .= "<td align=center>" . $no . "</td>";
				$tab .= "<td align=left>" . $namakelompok . "</td>";
				$tab .= "<td align=right>" . number_format($luas, 2) . "</td>";
				$tab .= "</tr>";
			}
			$tab .= "<tr class=rowcontent style='background-color:#999;font-weight:bold;'>
				<td colspan=2 align=right>GRAND TOTAL</td>
				<td align=right>" . number_format($grandTotal, 2) . "</td>
			</tr>";
		} else {
			$tab .= "<tr class=rowcontent><td colspan=3 align=center>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		}
		$tab .= "</tbody></table>";
		break;
	default:
		break;
}

switch ($proses) {
	case 'preview':
		echo $tab;
		break;
	case 'excel':
		$tab .= "Print Time:" . date('Y-m-d H:i:s') . "<br>By:" . $_SESSION['empl']['name'];
		$nop_ = "lappenggunaanlahanlain_" . ($unit == '' ? 'ALL' : $unit) . "_" . $periode;
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
