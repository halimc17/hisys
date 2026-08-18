<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method = $_REQUEST['method'] ?? '';
$pt = $_REQUEST['pt'] ?? '';           // KODE PT (3 HURUF)
$tahun = $_REQUEST['tahuntanam'] ?? '';   // TAHUN LAPORAN (YYYY)

switch ($method) {

	case 'preview':

		if ($pt == '' || $tahun == '') {
			echo "WARNING: PT dan Tahun wajib dipilih";
			exit;
		}

		$pt3 = substr($pt, 0, 3);

		$bulanArr = [
			1 => 'JANUARI',
			2 => 'FEBRUARI',
			3 => 'MARET',
			4 => 'APRIL',
			5 => 'MEI',
			6 => 'JUNI',
			7 => 'JULI',
			8 => 'AGUSTUS',
			9 => 'SEPTEMBER',
			10 => 'OKTOBER',
			11 => 'NOVEMBER',
			12 => 'DESEMBER'
		];

		//master tahun tanam
		$sqlTT = "
        SELECT
            sb.tahuntanam,
            SUM(sb.jumlahpokok) AS pokok,
            SUM(sb.luasareaproduktif) AS luas,
           GROUP_CONCAT(
				DISTINCT NULLIF(TRIM(sb.jenisbibit), '')
				SEPARATOR ', '
			) AS varietas
        FROM {$dbname}.setup_blok_tahunan sb
       WHERE LEFT(sb.indukblok,4) in (select kodeorganisasi from {$dbname}.organisasi where induk = '{$pt3}')
        GROUP BY sb.tahuntanam
        ORDER BY sb.tahuntanam
    ";
		$resTT = fetchData($sqlTT);

		//produksi bulanan
		$sqlProd = "
        SELECT
            sb.tahuntanam,
            substring(c.tanggal, 6,2) AS bulan,
            SUM(d.kgwb) AS brutto,
			SUM(d.kgwbnetto) AS netto,
            (SUM(d.kgwb) - SUM(d.kgwbnetto)) AS pot,
            SUM(d.jjg) AS tandan
        FROM {$dbname}.kebun_spbdt_detail d
		LEFT JOIN {$dbname}.kebun_spbht c
		    ON  c.nospb = d.nospb
        JOIN {$dbname}.setup_blok_tahunan sb
            ON sb.indukblok = d.indukblok
        WHERE LEFT(sb.indukblok,4) in (select kodeorganisasi from {$dbname}.organisasi where induk = '{$pt3}'  )
          AND substring(c.tanggal,1,4) = '{$tahun}'
        GROUP BY sb.tahuntanam, substring(c.tanggal,6,2)
    	";
		
		// echo $sqlProd;
		// exit;

		$resProd = fetchData($sqlProd);

		//array produksi
		$dataProd = [];
		foreach ($resProd as $r) {
			$dataProd[$r['tahuntanam']][(int) $r['bulan']] = $r;
		}

		//table
		echo "<table class=sortable cellspacing=1 cellpadding=4 border=0 width=100%>";

		//header
		echo "<thead>
        <tr class=rowheader>
            <th rowspan=2>Tahun Tanam</th>
            <th rowspan=2>Umur</th>
            <th rowspan=2>Jml Pokok</th>
            <th rowspan=2>Varietas</th>
            <th rowspan=2>Luas (Ha)</th>";

		foreach ($bulanArr as $b)
			echo "<th colspan=5>{$b}</th>";
		echo "<th colspan=5>JUMLAH</th>";

		echo "</tr><tr class=rowheader>";
		foreach ($bulanArr as $b)
			echo "<th>Brutto</th><th>Pot</th><th>%</th><th>Netto</th><th>Tandan</th>";
		echo "<th>Brutto</th><th>Pot</th><th>%</th><th>Netto</th><th>Tandan</th>";
		echo "</tr></thead><tbody>";

		//Grandtotal
		$gPokok = $gLuas = 0;
		$gBr = $gPt = $gNt = $gTd = 0;
		$gBulanan = [];

		//grafik
		$chartData = [];


		//body table
		foreach ($resTT as $row) {

			$tt = $row['tahuntanam'];
			$umur = $tahun - $tt - 3;

			echo "<tr class=rowcontent>
            <td align=center>{$tt}</td>
            <td align=center>{$umur}</td>
            <td align=right>" . number_format($row['pokok']) . "</td>
            <td>{$row['varietas']}</td>
            <td align=right>" . number_format($row['luas'], 2) . "</td>";

			$sumBr = $sumPt = $sumNt = $sumTd = 0;

			foreach ($bulanArr as $bln => $nm) {

				$br = $dataProd[$tt][$bln]['brutto'] ?? 0;
				$ptg = $dataProd[$tt][$bln]['pot'] ?? 0;
				$nt = $dataProd[$tt][$bln]['netto'] ?? 0;
				$td = $dataProd[$tt][$bln]['tandan'] ?? 0;
				$pr = $br > 0 ? round(($ptg / $br) * 100, 2) : 0;
				$chartData[$tt][$bln] = $pr;

				// total per baris
				$sumBr += $br;
				$sumPt += $ptg;
				$sumNt += $nt;
				$sumTd += $td;

				// total per bulan (grand)
				$gBulanan[$bln]['br'] = ($gBulanan[$bln]['br'] ?? 0) + $br;
				$gBulanan[$bln]['pt'] = ($gBulanan[$bln]['pt'] ?? 0) + $ptg;
				$gBulanan[$bln]['nt'] = ($gBulanan[$bln]['nt'] ?? 0) + $nt;
				$gBulanan[$bln]['td'] = ($gBulanan[$bln]['td'] ?? 0) + $td;

				echo "
                <td align=right>" . number_format($br) . "</td>
                <td align=right>" . number_format($ptg) . "</td>
                <td align=right>{$pr}%</td>
                <td align=right>" . number_format($nt) . "</td>
                <td align=right>" . number_format($td) . "</td>";
			}

			// JUMLAH per Tahun Tanam
			$sumPr = $sumBr > 0 ? round(($sumPt / $sumBr) * 100, 2) : 0;

			echo "
            <td align=right>" . number_format($sumBr) . "</td>
            <td align=right>" . number_format($sumPt) . "</td>
            <td align=right>{$sumPr}%</td>
            <td align=right>" . number_format($sumNt) . "</td>
            <td align=right>" . number_format($sumTd) . "</td>
        </tr>";

			// grand total kiri
			$gPokok += $row['pokok'];
			$gLuas += $row['luas'];

			// grand total kanan
			$gBr += $sumBr;
			$gPt += $sumPt;
			$gNt += $sumNt;
			$gTd += $sumTd;
		}

		//grandtotal
		$gPr = $gBr > 0 ? round(($gPt / $gBr) * 100, 2) : 0;

		echo "<tr class=rowheader style='font-weight:bold;background:#eee'>
        <td align=center>JUMLAH</td>
        <td></td>
        <td align=right>" . number_format($gPokok) . "</td>
        <td></td>
        <td align=right>" . number_format($gLuas, 2) . "</td>";

		foreach ($bulanArr as $bln => $nm) {

			$br = $gBulanan[$bln]['br'] ?? 0;
			$pt = $gBulanan[$bln]['pt'] ?? 0;
			$nt = $gBulanan[$bln]['nt'] ?? 0;
			$td = $gBulanan[$bln]['td'] ?? 0;
			$pr = $br > 0 ? round(($pt / $br) * 100, 2) : 0;

			echo "
            <td align=right>" . number_format($br) . "</td>
            <td align=right>" . number_format($pt) . "</td>
            <td align=right>{$pr}%</td>
            <td align=right>" . number_format($nt) . "</td>
            <td align=right>" . number_format($td) . "</td>";
		}

		echo "
        <td align=right>" . number_format($gBr) . "</td>
        <td align=right>" . number_format($gPt) . "</td>
        <td align=right>{$gPr}%</td>
        <td align=right>" . number_format($gNt) . "</td>
        <td align=right>" . number_format($gTd) . "</td>
    </tr>";

		echo "</tbody></table>";

		echo "<br><br>";
		echo "<canvas id='grafikGrading' height='120' style='position=fixed'></canvas>";

		echo "<script type='application/json' id='chartData'>
		" . json_encode([
				'bulan' => array_values($bulanArr),
				'data' => $chartData
			]) . "
		</script>";


		break;
	case 'excel':

		if ($pt == '' || $tahun == '') {
			echo "WARNING: PT dan Tahun wajib dipilih";
			exit;
		}

		header("Content-Type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=grading_{$pt}_{$tahun}.xls");
		header("Pragma: no-cache");
		header("Expires: 0");

		$pt3 = substr($pt, 0, 3);

		$bulanArr = [
			1 => 'JANUARI',
			2 => 'FEBRUARI',
			3 => 'MARET',
			4 => 'APRIL',
			5 => 'MEI',
			6 => 'JUNI',
			7 => 'JULI',
			8 => 'AGUSTUS',
			9 => 'SEPTEMBER',
			10 => 'OKTOBER',
			11 => 'NOVEMBER',
			12 => 'DESEMBER'
		];

		// master tahun tanam
		$sqlTT = "
        SELECT
            sb.tahuntanam,
            SUM(sb.jumlahpokok) AS pokok,
            SUM(sb.luasareaproduktif) AS luas,
            GROUP_CONCAT(DISTINCT sb.jenisbibit SEPARATOR ', ') AS varietas
        FROM {$dbname}.setup_blok_tahunan sb
        WHERE LEFT(sb.indukblok,3) = '{$pt3}'
        GROUP BY sb.tahuntanam
        ORDER BY sb.tahuntanam
    ";
		$resTT = fetchData($sqlTT);

		// produksi bulanan
		$sqlProd = "
			SELECT
				sb.tahuntanam,
				substring(c.tanggal, 6,2) AS bulan,
				SUM(d.kgwb) AS brutto,
				SUM(d.kgwb - d.kgwbnetto) AS pot,
				SUM(d.kgwbnetto) AS netto,
				SUM(d.jjg) AS tandan
			FROM {$dbname}.kebun_spbdt_detail d
			LEFT JOIN {$dbname}.kebun_spbht c
				ON  c.nospb = d.nospb
			JOIN {$dbname}.setup_blok_tahunan sb
				ON sb.indukblok = d.indukblok
			WHERE LEFT(sb.indukblok,4) in (select kodeorganisasi from {$dbname}.organisasi where induk = '{$pt3}')
			AND substring(c.tanggal,1,4) = '{$tahun}'
			GROUP BY sb.tahuntanam, substring(c.tanggal,6,2)
		";
		$resProd = fetchData($sqlProd);

		$dataProd = [];
		foreach ($resProd as $r) {
			$dataProd[$r['tahuntanam']][(int) $r['bulan']] = $r;
		}

		echo "<table border=1 cellpadding=4 cellspacing=0>";

		/* ================= HEADER 2 BARIS (SAMA PREVIEW) ================= */
		echo "<tr style='font-weight:bold;background:#eee'>
        <th rowspan=2>Tahun Tanam</th>
        <th rowspan=2>Umur</th>
        <th rowspan=2>Jml Pokok</th>
        <th rowspan=2>Varietas</th>
        <th rowspan=2>Luas (Ha)</th>";

		foreach ($bulanArr as $b) {
			echo "<th colspan=5>{$b}</th>";
		}
		echo "<th colspan=5>JUMLAH</th>";
		echo "</tr>";

		echo "<tr style='font-weight:bold;background:#eee'>";
		foreach ($bulanArr as $b) {
			echo "<th>Brutto</th><th>Pot</th><th>%</th><th>Netto</th><th>Tandan</th>";
		}
		echo "<th>Brutto</th><th>Pot</th><th>%</th><th>Netto</th><th>Tandan</th>";
		echo "</tr>";

		/* ================= BODY ================= */
		$gPokok = $gLuas = 0;
		$gBr = $gPt = $gNt = $gTd = 0;
		$gBulanan = [];

		foreach ($resTT as $row) {

			$tt = $row['tahuntanam'];
			$umur = $tahun - $tt - 3;

			echo "<tr>
            <td align=center>{$tt}</td>
            <td align=center>{$umur}</td>
            <td align=right>{$row['pokok']}</td>
            <td>{$row['varietas']}</td>
            <td align=right>{$row['luas']}</td>";

			$sumBr = $sumPt = $sumNt = $sumTd = 0;

			foreach ($bulanArr as $bln => $nm) {

				$br = $dataProd[$tt][$bln]['brutto'] ?? 0;
				$ptg = $dataProd[$tt][$bln]['pot'] ?? 0;
				$nt = $dataProd[$tt][$bln]['netto'] ?? 0;
				$td = $dataProd[$tt][$bln]['tandan'] ?? 0;
				$pr = $br > 0 ? round(($ptg / $br) * 100, 2) : 0;

				$sumBr += $br;
				$sumPt += $ptg;
				$sumNt += $nt;
				$sumTd += $td;

				$gBulanan[$bln]['br'] = ($gBulanan[$bln]['br'] ?? 0) + $br;
				$gBulanan[$bln]['pt'] = ($gBulanan[$bln]['pt'] ?? 0) + $ptg;
				$gBulanan[$bln]['nt'] = ($gBulanan[$bln]['nt'] ?? 0) + $nt;
				$gBulanan[$bln]['td'] = ($gBulanan[$bln]['td'] ?? 0) + $td;

				echo "<td align=right>{$br}</td>
                  <td align=right>{$ptg}</td>
                  <td align=right>{$pr}%</td>
                  <td align=right>{$nt}</td>
                  <td align=right>{$td}</td>";
			}

			$sumPr = $sumBr > 0 ? round(($sumPt / $sumBr) * 100, 2) : 0;

			echo "<td align=right>{$sumBr}</td>
              <td align=right>{$sumPt}</td>
              <td align=right>{$sumPr}%</td>
              <td align=right>{$sumNt}</td>
              <td align=right>{$sumTd}</td>
        </tr>";

			$gPokok += $row['pokok'];
			$gLuas += $row['luas'];
			$gBr += $sumBr;
			$gPt += $sumPt;
			$gNt += $sumNt;
			$gTd += $sumTd;
		}

		//grandtottal
		$gPr = $gBr > 0 ? round(($gPt / $gBr) * 100, 2) : 0;

		echo "<tr style='font-weight:bold;background:#ddd'>
        <td align=center>JUMLAH</td>
        <td></td>
        <td align=right>{$gPokok}</td>
        <td></td>
        <td align=right>{$gLuas}</td>";

		foreach ($bulanArr as $bln => $nm) {
			$br = $gBulanan[$bln]['br'] ?? 0;
			$pt = $gBulanan[$bln]['pt'] ?? 0;
			$nt = $gBulanan[$bln]['nt'] ?? 0;
			$td = $gBulanan[$bln]['td'] ?? 0;
			$pr = $br > 0 ? round(($pt / $br) * 100, 2) : 0;

			echo "<td align=right>{$br}</td>
              <td align=right>{$pt}</td>
              <td align=right>{$pr}%</td>
              <td align=right>{$nt}</td>
              <td align=right>{$td}</td>";
		}

		echo "<td align=right>{$gBr}</td>
          <td align=right>{$gPt}</td>
          <td align=right>{$gPr}%</td>
          <td align=right>{$gNt}</td>
          <td align=right>{$gTd}</td>
    </tr>";

		echo "</table>";
		exit;

	default:
		echo "WARNING: Method tidak dikenal";
		break;

}
?>