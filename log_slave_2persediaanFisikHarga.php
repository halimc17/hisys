<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method  = checkPostGet('method', '');
$pt      = checkPostGet('pt', '');
$gudang  = checkPostGet('gudang', '');
$periode = checkPostGet('periode', '');
$kelompok = checkPostGet('kelompok', '');
$tipe = checkPostGet('tipe', '');

switch ($method) {
	case 'getLaporanFisikHarga':
		if ($gudang == '') {
			exit("Warning : Gudang Harus dipilih.");
		}

		if ($tipe == 'excel') {
			$tab .= "<table class=sortable style='position:absolut;' cellspacing=1 border=1>
			<thead>
			<tr>
				<th rowspan=2 align=center style='width:50px;'>No.</th>
				<th rowspan=2 align=center>" . $_SESSION['lang']['periode'] . "</th>
				<th rowspan=2 align=center>" . $_SESSION['lang']['kodebarang'] . "</th>
				<th rowspan=2 align=center>" . $_SESSION['lang']['namabarang'] . "</th>
				<th rowspan=2 align=center>" . $_SESSION['lang']['satuan'] . "</th>
				<th colspan=3 align=center>" . $_SESSION['lang']['saldoawal'] . "</th>
				<th colspan=3 align=center>" . $_SESSION['lang']['masuk'] . "</th>
				<th colspan=3 align=center>" . $_SESSION['lang']['keluar'] . "</th>
				<th colspan=3 align=center>" . $_SESSION['lang']['saldoakhir'] . "</th>
			</tr>
			<tr>
				<th align=center>" . $_SESSION['lang']['kuantitas'] . "</th>
				<th align=center>" . $_SESSION['lang']['hargasatuan'] . "</th>
				<th align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['rupiah'] . "</th>
				<th align=center>" . $_SESSION['lang']['kuantitas'] . "</th>
				<th align=center>" . $_SESSION['lang']['hargasatuan'] . "</th>
				<th align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['rupiah'] . "</th>
				<th align=center>" . $_SESSION['lang']['kuantitas'] . "</th>
				<th align=center>" . $_SESSION['lang']['hargasatuan'] . "</th>
				<th align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['rupiah'] . "</th>
				<th align=center>" . $_SESSION['lang']['kuantitas'] . "</th>
				<th align=center>" . $_SESSION['lang']['hargasatuan'] . "</th>
				<th align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['rupiah'] . "</th>  
			</tr>   
			</thead>";
		}

		$rpawal = array();
		$rpmasuk = array();
		$rpkeluar = array();
		$rpakhir = array();

		$qtyawal = array();
		$qtymasuk = array();
		$qtykeluar = array();
		$qtyakhir = array();

		$ttlqtyawal = 0;
		$ttlqtymasuk = 0;
		$ttlqtykeluar = 0;
		$ttlqtyakhir = 0;
		$ttlrpawal = 0;
		$ttlrpmasuk = 0;
		$ttlrpkeluar = 0;
		$ttlrpakhir = 0;

		$where = "";
		if ($kelompok != '') {
			$where .= "AND kodebarang LIKE '{$kelompok}%'";
		}

		$gudangx = substr($gudang, 0, 4);
		## GET MATERIAL
		$arrmaterial = array();
		// $str="select * from ".$dbname.".log_5saldobulanan where kodegudang like '".$gudangx."%' and periode='".$periode."' and (qtymasuk!=0 or qtykeluar!=0 or saldoakhirqty!=0) ".$where." group by kodebarang order by kodebarang";
		$str = "select * from " . $dbname . ".log_5saldobulanan where kodegudang like '" . $gudangx . "%' and periode='" . $periode . "' and (qtymasuk!=0 or qtykeluar!=0 or saldoakhirqty!=0) " . $where . " order by kodebarang";
		$res = fetchdata($str);
		foreach ($res as $val) {
			$gdgcentral = substr($val['kodegudang'], 4, 2);
			$arrmaterial[$val['kodebarang']] = $val['kodebarang'];

			$qtyawal[$val['kodebarang']] += $val['saldoawalqty'];
			$rpawal[$val['kodebarang']] += $val['nilaisaldoawal'];

			$qtykeluar[$val['kodebarang']] += $val['qtykeluar'];
			$rpkeluar[$val['kodebarang']] += $val['qtykeluarxharga'];

			$ttlqtykeluar += $val['qtykeluar'];
			$ttlrpkeluar += $val['qtykeluarxharga'];

			if ($gdgcentral == 'GC') {
				$qtymasuk[$val['kodebarang']] += $val['qtymasuk'];
				$rpmasuk[$val['kodebarang']] += $val['qtymasukxharga'];

				$ttlqtymasuk += $val['qtymasuk'];
				$ttlrpmasuk += $val['qtymasukxharga'];
			} else {
				$qtykeluar[$val['kodebarang']] -= $val['qtymasuk'];
				$rpkeluar[$val['kodebarang']] -= $val['qtymasukxharga'];

				$ttlqtykeluar -= $val['qtymasuk'];
				$ttlrpkeluar -= $val['qtymasukxharga'];
			}

			$qtyakhir[$val['kodebarang']] += $val['saldoakhirqty'];
			$rpakhir[$val['kodebarang']] += $val['nilaisaldoakhir'];

			$ttlqtyawal += $val['saldoawalqty'];
			$ttlrpawal += $val['nilaisaldoawal'];

			$ttlqtyakhirl += $val['saldoakhirqty'];
			$ttlrpakhir += $val['nilaisaldoakhir'];
		}

		$no = 0;
		foreach ($arrmaterial as $key) {
			$no++;
			$strx = "select namabarang,satuan from " . $dbname . ".log_5masterbarang where kodebarang='" . $key . "'";
			$resx = fetchdata($strx);
			$namabarang = $resx[0]['namabarang'];
			$satuan = $resx[0]['satuan'];

			$tab .= "<tr class=rowcontent  style='cursor:pointer;' title='Click' onclick=\"detailMutasiBarangHargaExcel(event,'" . $pt . "','" . $periode . "','" . $gudang . "','" . $key . "','" . $namabarang . "','" . $satuan . "','log_laporanMutasiDetailPerBarangHarga_Excel.php');\">";
			$tab .= "<td>" . $no . "</td>";
			$tab .= "<td style='text-align:center'>" . $periode . "</td>";
			$tab .= "<td style='text-align:center'>" . $key . "</td>";
			$tab .= "<td style='text-align:left'>" . $namabarang . "</td>";
			$tab .= "<td style='text-align:center'>" . $satuan . "</td>";

			$rataawal = @($rpawal[$key] / $qtyawal[$key]);
			$rataawal = fixnan($rataawal);
			$tab .= "<td style='text-align:right'>" . hidezerodecimalv2($qtyawal[$key], 5) . "</td>";
			$tab .= "<td style='text-align:right'>" . hidezerodecimalv2($rataawal, 2) . "</td>";
			$tab .= "<td style='text-align:right'>" . hidezerodecimalv2($rpawal[$key], 2) . "</td>";

			$ratamasuk = @($rpmasuk[$key] / $qtymasuk[$key]);
			$ratamasuk = fixnan($ratamasuk);
			$tab .= "<td style='text-align:right'>" . hidezerodecimalv2($qtymasuk[$key], 5) . "</td>";
			$tab .= "<td style='text-align:right'>" . hidezerodecimalv2($ratamasuk, 2) . "</td>";
			$tab .= "<td style='text-align:right'>" . hidezerodecimalv2($rpmasuk[$key], 2) . "</td>";

			$ratakeluar = @($rpkeluar[$key] / $qtykeluar[$key]);
			$ratakeluar = fixnan($ratakeluar);
			$tab .= "<td style='text-align:right'>" . hidezerodecimalv2($qtykeluar[$key], 5) . "</td>";
			$tab .= "<td style='text-align:right'>" . hidezerodecimalv2($ratakeluar, 2) . "</td>";
			$tab .= "<td style='text-align:right'>" . hidezerodecimalv2($rpkeluar[$key], 2) . "</td>";

			$rataakhir = @($rpakhir[$key] / $qtyakhir[$key]);
			$rataakhir = fixnan($rataakhir);
			$tab .= "<td style='text-align:right'>" . hidezerodecimalv2($qtyakhir[$key], 5) . "</td>";
			$tab .= "<td style='text-align:right'>" . hidezerodecimalv2($rataakhir, 2) . "</td>";
			$tab .= "<td style='text-align:right'>" . hidezerodecimalv2($rpakhir[$key], 2) . "</td>";

			$tab .= "</tr>";
		}

		## TOTAL
		$tab .= "<tr class=rowcontent>";
		$tab .= "<td colspan=5 align=center><b>" . $_SESSION['lang']['total'] . "</b></td>";
		$tab .= "<td colspan=2></td>";
		$tab .= "<td colspan align=right><b>" . hidezerodecimalv2($ttlrpawal, 0) . "</b></td>";
		$tab .= "<td colspan=2></td>";
		$tab .= "<td colspan align=right><b>" . hidezerodecimalv2($ttlrpmasuk, 0) . "</b></td>";
		$tab .= "<td colspan=2></td>";
		$tab .= "<td colspan align=right><b>" . hidezerodecimalv2($ttlrpkeluar, 0) . "</b></td>";
		$tab .= "<td colspan=2></td>";
		$tab .= "<td colspan align=right><b>" . hidezerodecimalv2($ttlrpakhir, 0) . "</b></td>";
		$tab .= "</tr>";

		if ($tipe == 'excel') {
			$stream .= $tab;
			$stream .= "</table>Print Time:" . date('YmdHis') . "<br>By:" . $_SESSION['empl']['name'];
			$tempnm = explode("/", $_SERVER['PHP_SELF']);
			$nop_ = substr($tempnm[2], 0, strripos($tempnm[2], '.'));
			if (strlen($stream) > 0) {
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/' . $file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
				if (!fwrite($handle, $stream)) {
					echo "<script language=javascript1.2>
								parent.window.alert('Can't convert to excel format');
								</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
								window.location='tempExcel/" . $nop_ . ".xls';
								</script>";
				}
				fclose($handle);
			}
		} else {
			echo $tab;
		}
		break;
}
function hidezerodecimalv2($val, $no = 0)
{
	if ($no == 0) {
		$hasil = @number_format(@$val);
	} else {
		if ($val == '') {
			$hasil = rtrim(rtrim(@number_format(0, $no), '0'), '.');
		} else {
			$hasil = rtrim(rtrim(@number_format(@$val, $no), '0'), '.');
		}
	}
	if ($hasil == 0) {
		$hasil = '';
	}
	return $hasil;
}
