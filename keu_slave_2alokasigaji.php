<?php
// error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');

use Dompdf\Dompdf;

$deb = false;
// $param     = $_POST;
$param = $_POST;
if (count($param) == 0) {
	$param = $_GET;
}
$method    = checkPostGet('method', '');
$tipeprint = checkPostGet('tipeprint', '');
$pt        = checkPostGet('pt', '');
$unit      = checkPostGet('unit', '');
$periode   = checkPostGet('periode', '');
$karyawanid = checkPostGet('karyawanid', '');

switch ($method) {
	case 'loadunit':
		$optunit = "<option value=>" . $_SESSION['lang']['all'] . "</option>";
		foreach (getOrgDetail(11) as $key => $val) {
			$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $key . "'");
			$d = $induk[$key];
			if ($d == $pt) {
				$optunit .= "<option value=" . $key . ">" . $key . " - " . $val . "</option>";
			}
		}

		echo $optunit;
		break;

	case 'preview':
		//error_reporting(0);
		if ($unit != '') {
			$where = "AND a.kodeorg = '" . $unit . "'";
			$wh = "AND kodeorg = '" . $unit . "'";
			// $whnew = " AND kodeblok like '".$unit."%'";
		} else if ($pt != '') {
			$wh = "AND kodeorg in (SELECT kodeorganisasi FROM organisasi WHERE induk = '" . $pt . "')";
			$where = "AND a.kodeorg in (SELECT kodeorganisasi FROM organisasi WHERE induk = '" . $pt . "')";
		} else {
			$wh = "AND kodeorg in (SELECT kodeorganisasi FROM organisasi WHERE length(kodeorganisasi) = '4')";
			$where = "AND a.kodeorg in (SELECT kodeorganisasi FROM organisasi WHERE length(kodeorganisasi) = '4')";
		}

		if ($tipeprint == 'html') {
			$tab = "<table border=0 class=sortable cellpadding=5 cellspacing=1>";
		} else {
			$tab = "<center><h2>LAPORAN ALOKASI GAJI</h2></center>
					<table border=0.5 class=sortable cellpading=0 cellspacing=0 style='width:100%'>";
		}
		$tab .= "<thead>
					<tr class=rowheader style='background:#275370; color:white;'>
						<th align=center rowspan=2 valign=center>" . $_SESSION['lang']['nourut'] . "</th>";
		if ($param['jenis'] == 'detail') {
			$tab .= "<th align=center rowspan=2 valign=center>" . $_SESSION['lang']['namakaryawan'] . "</th>
						<th align=center rowspan=2 valign=center>" . $_SESSION['lang']['nik'] . "</th>";
		}

		$tab .= "<th align=center rowspan=2 valign=center>" . $_SESSION['lang']['unit'] . "</th>
						<th align=center colspan=3 valign=center>" . $_SESSION['lang']['gaji'] . "</th>
						<th align=center colspan=3 valign=center>" . $_SESSION['lang']['alokasi'] . "</th>
						<th align=center colspan=3 valign=center>" . $_SESSION['lang']['selisih'] . "</th>
					</tr>
					<tr class=rowheader style='background:#275370; color:white;'>
						<th align=center>" . $_SESSION['lang']['penambah'] . "<br>(+)</th>
						<th align=center>" . $_SESSION['lang']['pengurang'] . "<br>(-)</th>
						<th align=center>" . $_SESSION['lang']['total'] . "</th>
						<th align=center>" . $_SESSION['lang']['debet'] . "<br>(+)</th>
						<th align=center>" . $_SESSION['lang']['kredit'] . "<br>(-)</th>
						<th align=center>" . $_SESSION['lang']['total'] . "</th>
						<th align=center>(+)</th>
						<th align=center>(-)</th>
						<th align=center>" . $_SESSION['lang']['total'] . "</th>
					</tr>
				</thead>
				<tbody>";

		// exit("Error".$tab);
		#= pemanbahan ada karyawan an YOHANES HERRY	0000000501, diperiode 2 dia mau potong pphnya saja,
		#= jadi kasih penguncian tanpa pph

		## GET NAMA KARYAWAN
		$getkaryawan = "SELECT 
						a.kodeorg, 
						a.periodegaji, 
						a.karyawanid, 
						b.namakaryawan, 
						b.nik
						FROM " . $dbname . ".sdm_gajidetail_vw a
						JOIN " . $dbname . ".datakaryawan_hist b ON a.karyawanid = b.karyawanid
						WHERE a.periodegaji = '" . $periode . "' and b.periodegaji = '" . $periode . "' 
						AND b.version_type = 'B'and a.idkomponen!='42'
						" . $where . "
						GROUP BY a.kodeorg, a.periodegaji, a.karyawanid order by b.namakaryawan";
		// echo $getkaryawan;
		$reskaryawan = fetchdata($getkaryawan);


		if (count($reskaryawan) < 1) {
			$tab .= "<tr class=rowcontent>
						<td colspan=13 align=center>Tidak Ada Data</td>
					</tr>";
		} else {
			$num = 0;
			$wherekarid = '';
			$arrkaryawan = array();
			foreach ($reskaryawan as $key => $val) {
				$arrkarya[$val['karyawanid']] = $val['karyawanid'];
				$arrkaryawan[$val['namakaryawan']]['nik'] = $val['nik'];
				$arrkaryawan[$val['namakaryawan']]['lokasitugas'] = $val['kodeorg'];

				if ($num == 0) {
					$wherekarid .= "'" . $val['karyawanid'] . "'";
				} else {
					$wherekarid .= ",'" . $val['karyawanid'] . "'";
				}
				$num++;
			}

			## GET JUMLAH GAJI PLUS
			$getjumlahplus = "SELECT karyawanid, sum(jumlah) as jmlh, kodeorg
							 FROM " . $dbname . ".sdm_gajidetail_vw
							 WHERE plus = 1
							 AND periodegaji = '" . $periode . "'
							 AND karyawanid in (" . $wherekarid . ")
							 GROUP BY karyawanid";
			$resjumlahplus = fetchdata($getjumlahplus);

			$arrjmlhplus = array();
			foreach ($resjumlahplus as $key => $val) {
				$arrjmlhplus[$val['karyawanid']] = $val['jmlh'];
				$arrkarya[$val['karyawanid']] = $val['karyawanid'];

				$plusrekap[$val['kodeorg']] += $val['jmlh'];
				$data[$val['kodeorg']] = $val['kodeorg'];
			}

			## GET JUMLAH GAJI MINUS
			$getjumlahminus = "SELECT karyawanid, sum(jumlah) as jmlh, kodeorg
							 FROM " . $dbname . ".sdm_gajidetail_vw
							 WHERE plus = 0
							 AND periodegaji = '" . $periode . "'
							 AND karyawanid in (" . $wherekarid . ") " . $wh . "
							 GROUP BY karyawanid";

			$resjumlahminus = fetchdata($getjumlahminus);

			$arrjmlhminus = array();
			foreach ($resjumlahminus as $key => $val) {
				$arrjmlhminus[$val['karyawanid']] = $val['jmlh'];
				$arrkarya[$val['karyawanid']] = $val['karyawanid'];

				$minrekap[$val['kodeorg']] += $val['jmlh'];
				$data[$val['kodeorg']] = $val['kodeorg'];
			}

			## GET TOTAL ALOKASI PLUS
			$getalokplus1 = "SELECT nik, sum(kredit) as debet, kodeorg
							FROM " . $dbname . ".keu_jurnaldt_vw 
							WHERE jumlah<0
							AND periode = '" . $periode . "'
						 	AND nik !='' AND nik!='0000000000' " . $wh . " 
						 	AND kodejurnal='POT' and nojurnal not like '%/M/%'
							GROUP BY nik";
			$resalokplus1 = fetchdata($getalokplus1);

			$arralokplus1 = array();
			foreach ($resalokplus1 as $key => $val) {
				if ($unit != '') {
					if (getKaryHist($val['nik'], $periode, 'lokasitugas') != $unit) {
						continue;
					}
				} else {
					if (getKaryHist($val['nik'], $periode, 'lokasitugas') != $val['kodeorg']) {
						continue;
					}
				}
				
				$arralokplus[$val['nik']] = $val['debet'];
				$arrkarya[$val['nik']] = $val['nik'];

				$alkplusrekap[$val['kodeorg']] += $val['debet'];
				$data[$val['kodeorg']] = $val['kodeorg'];
			}

			## GET TOTAL ALOKASI PLUS untuk pph 21 minus
			$getalokplus2 = "SELECT nik, sum(debet) as debet, kodeorg
							FROM " . $dbname . ".keu_jurnaldt_vw 
							WHERE jumlah>0
							AND periode = '" . $periode . "'
						 	AND nik !='' AND nik!='0000000000' " . $wh . " 
						 	AND kodejurnal='POT' and keterangan  like '%Pot. PPh 21%'
							GROUP BY nik";
			$resalokplus2 = fetchdata($getalokplus2);
			//echo $getalokplus;
			//exit('Warning : Sedang dalam perbaikan');
			//$arralokplus = array();
			foreach ($resalokplus2 as $key => $val) {
				if ($unit != '') {
					if (getKaryHist($val['nik'], $periode, 'lokasitugas') != $unit) {
						continue;
					}
				} else {
					if (getKaryHist($val['nik'], $periode, 'lokasitugas') != $val['kodeorg']) {
						continue;
					}
				}

				$arralokplus[$val['nik']] -= $val['debet'];
				//$arrkarya[$val['nik']] = $val['nik'];

				$alkplusrekap[$val['kodeorg']] -= $val['debet'];
				//$data[$val['kodeorg']] = $val['kodeorg'];
			}

			## GET TOTAL ALOKASI MINUS
			$getalokminus1 = "SELECT nik, sum(kredit) as kredit, kodeorg
							FROM " . $dbname . ".keu_jurnaldt_vw 
							WHERE noakun like '213%' and substr(noakun,1,5) < '21302'
							AND periode = '" . $periode . "'
						 	AND nik !='' AND nik!='0000000000' " . $wh . " 
						 	AND jumlah < 0
						 	AND noreferensi NOT LIKE 'ALK_POT%' and nojurnal not like '%/M/%'
							GROUP BY nik";
			$resalokminus1 = fetchdata($getalokminus1);

			$arralokminus = array();
			foreach ($resalokminus1 as $key => $val) {
				if ($unit != '') {
					if (getKaryHist($val['nik'], $periode, 'lokasitugas') != $unit) {
						continue;
					}
				} else {
					if (getKaryHist($val['nik'], $periode, 'lokasitugas') != $val['kodeorg']) {
						continue;
					}
				}

				$arralokminus[$val['nik']] = $val['kredit'];
				$arrkarya[$val['nik']] = $val['nik'];

				$alkminrekap[$val['kodeorg']] += $val['kredit'];
				$data[$val['kodeorg']] = $val['kodeorg'];
			}

			## GET TOTAL ALOKASI MINUS untuk tunjangan pajak minus
			$getalokminus2 = "SELECT nik, sum(debet) as kredit, kodeorg
							FROM " . $dbname . ".keu_jurnaldt_vw 
							WHERE noakun like '213%' and substr(noakun,1,5) < '21302'
							AND periode = '" . $periode . "'
						 	AND nik !='' AND nik!='0000000000' " . $wh . " 
						 	AND jumlah > 0
						 	AND keterangan  LIKE '%Tunjangan Pajak%' 
							GROUP BY nik";
			$resalokminus2 = fetchdata($getalokminus2);

			//$arralokminus = array();
			foreach ($resalokminus2 as $key => $val) {
				if ($unit != '') {
					if (getKaryHist($val['nik'], $periode, 'lokasitugas') != $unit) {
						continue;
					}
				} else {
					if (getKaryHist($val['nik'], $periode, 'lokasitugas') != $val['kodeorg']) {
						continue;
					}
				}

				$arralokminus[$val['nik']] -= $val['kredit'];
				//$arrkarya[$val['nik']] = $val['nik'];

				$alkminrekap[$val['kodeorg']] -= $val['kredit'];
				//$data[$val['kodeorg']] = $val['kodeorg'];
			}

			## GET TOTAL ALOKASI MINUS
			$getalokminus3 = "SELECT nik, sum(debet) as debet, kodeorg
							FROM " . $dbname . ".keu_jurnaldt_vw 
							WHERE noakun like '213%' and substr(noakun,1,5) < '21302'
							AND periode = '" . $periode . "'
						 	AND nik !='' AND nik!='0000000000' " . $wh . " 
						 	AND jumlah > 0
						 	AND noreferensi NOT LIKE 'ALK_POT%' and nojurnal not like '%/M/%' 
						 	and nojurnal like '%PNN%' 
						 	and autojurnal='1'
						 	and keterangan like '%potong buah dan premi panen%'
							GROUP BY nik";
			$resalokminus3 = fetchdata($getalokminus3);

			//$arralokminus = array();
			foreach ($resalokminus3 as $key => $val) {
				if ($unit != '') {
					if (getKaryHist($val['nik'], $periode, 'lokasitugas') != $unit) {
						continue;
					}
				} else {
					if (getKaryHist($val['nik'], $periode, 'lokasitugas') != $val['kodeorg']) {
						continue;
					}
				}

				$arralokminus[$val['nik']] += ($val['debet'] * -1);
				$arrkarya[$val['nik']] = $val['nik'];

				$alkminrekap[$val['kodeorg']] += ($val['debet'] * -1);
				$data[$val['kodeorg']] = $val['kodeorg'];
			}


			## GET TOTAL ALOKASI MINUS
			$getalokminus4 = "SELECT nik, sum(debet) as debet, kodeorg
							FROM " . $dbname . ".keu_jurnaldt_vw 
							WHERE noakun like '213%' and substr(noakun,1,5) < '21302'
							AND periode = '" . $periode . "'
						 	AND nik !='' AND nik!='0000000000' " . $wh . " 
						 	AND jumlah > 0
						 	AND noreferensi NOT LIKE 'ALK_POT%' and nojurnal not like '%/M/%' 
						 	and nojurnal like '%KBNB0%' 
						 	and autojurnal='1'
							GROUP BY nik";
			$resalokminus4 = fetchdata($getalokminus4);

			//$arralokminus = array();
			foreach ($resalokminus4 as $key => $val) {
				if ($unit != '') {
					if (getKaryHist($val['nik'], $periode, 'lokasitugas') != $unit) {
						continue;
					}
				} else {
					if (getKaryHist($val['nik'], $periode, 'lokasitugas') != $val['kodeorg']) {
						continue;
					}
				}

				$arralokminus[$val['nik']] += ($val['debet'] * -1);
				$arrkarya[$val['nik']] = $val['nik'];

				$alkminrekap[$val['kodeorg']] += ($val['debet'] * -1);
				$data[$val['kodeorg']] = $val['kodeorg'];
			}

			## GET TOTAL ALOKASI MINUS
			$getalokminus5 = "SELECT nik, sum(debet) as debet, kodeorg
							FROM " . $dbname . ".keu_jurnaldt_vw 
							WHERE noakun like '213%' and substr(noakun,1,5) < '21302'
							AND periode = '" . $periode . "'
						 	AND nik !='' AND nik!='0000000000' " . $wh . " 
						 	AND jumlah > 0
						 	AND noreferensi LIKE 'ALK_GAJI_TERTINGGAL%' 
							AND nojurnal like '%/M/%'
							AND autojurnal='1'
							AND noaruskas=''
		
							GROUP BY nik";
			$resalokminus5 = fetchdata($getalokminus5);

			//$arralokminus = array();
			foreach ($resalokminus5 as $key => $val) {
				if ($unit != '') {
					if (getKaryHist($val['nik'], $periode, 'lokasitugas') != $unit) {
						continue;
					}
				} else {
					if (getKaryHist($val['nik'], $periode, 'lokasitugas') != $val['kodeorg']) {
						continue;
					}
				}

				$arralokminus[$val['nik']] += ($val['debet'] * -1);
				$arrkarya[$val['nik']] = $val['nik'];

				$alkminrekap[$val['kodeorg']] += ($val['debet'] * -1);
				$data[$val['kodeorg']] = $val['kodeorg'];
			}

			## GET TOTAL ALOKASI MINUS
			$getalokminus6 = "SELECT nik, sum(kredit) as kredit, kodeorg
							FROM " . $dbname . ".keu_jurnaldt_vw 
							WHERE noakun like '213%' and substr(noakun,1,5) < '21302'
							AND periode = '" . $periode . "'
						 	AND nik !='' AND nik!='0000000000' " . $wh . " 
						 	AND jumlah < 0
						 	AND noreferensi NOT LIKE 'ALK_POT%' 
							AND nojurnal like '%/M/%'
							AND autojurnal='1'
							AND noaruskas=''
							GROUP BY nik";
			$resalokminus6 = fetchdata($getalokminus6);

			//$arralokminus = array();
			foreach ($resalokminus6 as $key => $val) {
				if ($unit != '') {
					if (getKaryHist($val['nik'], $periode, 'lokasitugas') != $unit) {
						continue;
					}
				} else {
					if (getKaryHist($val['nik'], $periode, 'lokasitugas') != $val['kodeorg']) {
						continue;
					}
				}

				$arralokminus[$val['nik']] += $val['kredit'];
				$arrkarya[$val['nik']] = $val['nik'];

				$alkminrekap[$val['kodeorg']] += $val['kredit'];
				$data[$val['kodeorg']] = $val['kodeorg'];
			}


			#==============================================================================#
			#= KHUSUS BULAN > 2025-03 (PALMA)
			## Tambah untuk BM TBS
			## PNN01,PNN02,PNN03
			## GET TOTAL ALOKASI MINUS
			// if($periode > '2025-03') {
			// $getalokminus = "SELECT nik, sum(debet) as kredit, kodeorg
			// 				FROM ".$dbname.".keu_jurnaldt_vw 
			// 				WHERE noakun not like '213%'
			// 				AND periode = '".$periode."'
			// 				AND nik !='' ".$wh." 
			// 				AND jumlah > 0
			// 				AND kodejurnal in ('BM01','PNN01,'PNN02','PNN03')
			// 				AND autojurnal='1'
			// 				AND noaruskas=''
			// 				GROUP BY nik";

			$getalokminus7 = "SELECT nik, sum(debet) as kredit, kodeorg
								FROM " . $dbname . ".keu_jurnaldt_vw 
								WHERE noakun not like '213%'
								AND periode = '" . $periode . "'
								AND nik !='' AND nik!='0000000000' " . $wh . " 
								AND jumlah > 0
								AND kodejurnal in ('BM01')
								AND autojurnal='1'
								AND noaruskas=''
								" . $whnew . "
								GROUP BY nik";
			// echo $getalokminus;
			$resalokminus7 = fetchdata($getalokminus7);
			//$arralokminus = array();
			foreach ($resalokminus7 as $key => $val) {

				if ($unit != '') {
					if (getKaryHist($val['nik'], $periode, 'lokasitugas') != $unit) {
						continue;
					}
				} else {
					if (getKaryHist($val['nik'], $periode, 'lokasitugas') != $val['kodeorg']) {
						continue;
					}
				}

				$arralokminus[$val['nik']] += $val['kredit'];
				$arrkarya[$val['nik']] = $val['nik'];

				$alkminrekap[$val['kodeorg']] += $val['kredit'];
				$data[$val['kodeorg']] = $val['kodeorg'];
			}
			// }
			#==============================================================================#

			// echo"<pre>";
			// print_r($arralokminus);
			// echo"</pre>";


			$no = 0;
			$totaljmlhplus = 0;
			$totaljmlhminus = 0;
			$totaljmlhtotal = 0;
			$totalalokplus = 0;
			$totalalokminus = 0;
			$totalaloktotal = 0;
			$selisihplus = 0;
			$selisihminus = 0;
			$selisihtotal = 0;
			$totalselisihplus = 0;
			$totalselisihminus = 0;
			$totalselisihtotal = 0;
			if ($param['jenis'] == 'rekap') {
				foreach ($data as $kodeorg) {
					$no++;
					$totaljmlh = $plusrekap[$kodeorg] - $minrekap[$kodeorg];
					$totalalok = $alkplusrekap[$kodeorg] - $alkminrekap[$kodeorg];
					$selisihplus = $plusrekap[$kodeorg] - $alkminrekap[$kodeorg];
					$selisihminus = $minrekap[$kodeorg] - $alkplusrekap[$kodeorg];
					$selisihtotal = $selisihplus - $selisihminus;

					$tab .= "<tr class=rowcontent>";
					$tab .= "<td align=center>" . $no . "</td>";
					$tab .= "<td>" . $kodeorg . "</td>";
					$tab .= "<td align=right>" . number_format($plusrekap[$kodeorg]) . "</td>";
					$tab .= "<td align=right>" . number_format($minrekap[$kodeorg]) . "</td>";
					$tab .= "<td align=right>" . number_format($totaljmlh) . "</td>";
					$tab .= "<td align=right>" . number_format($alkplusrekap[$kodeorg]) . "</td>";
					$tab .= "<td align=right>" . number_format($alkminrekap[$kodeorg]) . "</td>";
					$tab .= "<td align=right>" . number_format($totalalok) . "</td>";
					$tab .= "<td align=right>" . number_format($selisihplus) . "</td>";
					$tab .= "<td align=right>" . number_format($selisihminus) . "</td>";
					$tab .= "<td align=right>" . number_format($selisihtotal) . "</td>";

					$totaljmlhplus += $plusrekap[$kodeorg];
					$totaljmlhminus += $minrekap[$kodeorg];
					$totaljmlhtotal += $totaljmlh;

					$totalalokplus += $alkplusrekap[$kodeorg];
					$totalalokminus += $alkminrekap[$kodeorg];
					$totalaloktotal += $totalalok;

					$totalselisihplus = $totalselisihplus + $selisihplus;
					$totalselisihminus = $totalselisihminus + $selisihminus;
					$totalselisihtotal = $totalselisihtotal + $selisihtotal;
				}
				$colspan = "colspan=2";
			} else {
				if (stripos($_SESSION['standard']['username'], "tim.owl") !== false && $deb) {
					echo $getkaryawan.";<br>";
					echo $getalokplus1.";<br>";
					echo $getalokplus2.";<br>";
					echo $getalokminus1.";<br>";
					echo $getalokminus2.";<br>";
					echo $getalokminus3.";<br>";
					echo $getalokminus4.";<br>";
					echo $getalokminus5.";<br>";
					echo $getalokminus6.";<br>";
					echo $getalokminus7.";<br>";
					exit("error");
				}
				foreach ($arrkarya as $karid) {
					$val['karyawanid'] = $karid;

					$no++;
					$totaljmlh = $arrjmlhplus[$val['karyawanid']] - $arrjmlhminus[$val['karyawanid']];
					$totalalok = $arralokplus[$val['karyawanid']] - $arralokminus[$val['karyawanid']];
					$selisihplus = $arrjmlhplus[$val['karyawanid']] - $arralokminus[$val['karyawanid']];
					$selisihminus = $arrjmlhminus[$val['karyawanid']] - $arralokplus[$val['karyawanid']];
					$selisihtotal = $selisihplus - $selisihminus;

					$val['namakaryawan'] = getKary($val['karyawanid'], 'namakaryawan');
					$val['nik'] = getKary($val['karyawanid'], 'nik');
					$val['kodeorg'] = getKary($val['karyawanid'], 'lokasitugas');

					// $val['namakaryawan']=getKaryHist($val['karyawanid'],'namakaryawan');
					// $val['nik']=getKaryHist($val['karyawanid'],'nik');
					// $val['kodeorg']=getKaryHist($val['karyawanid'],'lokasitugas');

					$tab .= "<tr class=rowcontent>";
					$tab .= "<td align=center>" . $no . "</td>";
					$tab .= "<td>" . $val['namakaryawan'] . "</td>";
					$tab .= "<td align=left>" . $val['nik'] . "</td>";
					// $tab .= "<td align=right>".$val['karyawanid']."</td>";
					$tab .= "<td>" . $val['kodeorg'] . "</td>";
					$tab .= "<td align=right style=color:blue; class='td' onclick=\"jmlhgaji('" . $val['karyawanid'] . "', '" . $periode . "', '" . $val['namakaryawan'] . "');\">" . number_format($arrjmlhplus[$val['karyawanid']]) . "</td>";
					$tab .= "<td align=right style=color:blue; class='td' onclick=\"jmlhgaji('" . $val['karyawanid'] . "', '" . $periode . "', '" . $val['namakaryawan'] . "');\">" . number_format($arrjmlhminus[$val['karyawanid']]) . "</td>";
					$tab .= "<td align=right style=color:blue; class='td' onclick=\"jmlhgaji('" . $val['karyawanid'] . "', '" . $periode . "', '" . $val['namakaryawan'] . "');\">" . number_format($totaljmlh) . "</td>";
					$tab .= "<td align=right style=color:blue; class='td' onclick=\"alokasi('" . $val['karyawanid'] . "', '" . $periode . "', '" . $val['namakaryawan'] . "');\">" . number_format($arralokplus[$val['karyawanid']]) . "</td>";
					$tab .= "<td align=right style=color:blue; class='td' onclick=\"alokasi('" . $val['karyawanid'] . "', '" . $periode . "', '" . $val['namakaryawan'] . "');\">" . number_format($arralokminus[$val['karyawanid']]) . "</td>";
					$tab .= "<td align=right style=color:blue; class='td' onclick=\"alokasi('" . $val['karyawanid'] . "', '" . $periode . "', '" . $val['namakaryawan'] . "');\">" . number_format($totalalok) . "</td>";
					$tab .= "<td align=right>" . number_format($selisihplus) . "</td>";
					$tab .= "<td align=right>" . number_format($selisihminus) . "</td>";
					$tab .= "<td align=right>" . number_format($selisihtotal) . "</td>";
					$tab .= "</tr>";

					$totaljmlhplus = $totaljmlhplus + $arrjmlhplus[$val['karyawanid']];
					$totaljmlhminus = $totaljmlhminus + $arrjmlhminus[$val['karyawanid']];
					$totaljmlhtotal = $totaljmlhtotal + $totaljmlh;

					$totalalokplus = $totalalokplus + $arralokplus[$val['karyawanid']];
					$totalalokminus = $totalalokminus + $arralokminus[$val['karyawanid']];
					$totalaloktotal = $totalaloktotal + $totalalok;

					$totalselisihplus = $totalselisihplus + $selisihplus;
					$totalselisihminus = $totalselisihminus + $selisihminus;
					$totalselisihtotal = $totalselisihtotal + $selisihtotal;
				}
				$colspan = "colspan=4";
			}
			$tab .= "<tr class=rowcontent style=background-color:cyan>
						<td align=center " . $colspan . ">TOTAL</td>
						<td align=right>" . number_format($totaljmlhplus) . "</td>
						<td align=right>" . number_format($totaljmlhminus) . "</td>
						<td align=right>" . number_format($totaljmlhtotal) . "</td>
						<td align=right>" . number_format($totalalokplus) . "</td>
						<td align=right>" . number_format($totalalokminus) . "</td>
						<td align=right>" . number_format($totalaloktotal) . "</td>
						<td align=right>" . number_format($totalselisihplus) . "</td>
						<td align=right>" . number_format($totalselisihminus) . "</td>
						<td align=right>" . number_format($totalselisihtotal) . "</td>
					</tr>";
		}


		$tab .= "</tbody></table>";
		if ($tipeprint == 'html') {
			echo $tab;
		} else if ($tipeprint == 'excel') {
			$nop = "LAPORAN ALOKASI GAJI.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("LPF", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		} else {
			$dompdf = new Dompdf();
			$dompdf->loadHtml($tab);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$dompdf->stream("LAPORAN ALOKASI GAJI", array("Attachment" => false));
		}
		break;

	case 'jmlhgaji':

		$str = "SELECT b.namakaryawan,
				b.nik,
				a.kodeorg,
				a.karyawanid,
				a.periodegaji,
				a.jumlah,
				a.name as komponen,
				a.plus
				FROM " . $dbname . ".sdm_gajidetail_vw a
				JOIN " . $dbname . ".datakaryawan_hist b
				ON a.karyawanid = b.karyawanid
				WHERE a.karyawanid = " . $karyawanid . "
				AND a.periodegaji = '" . $periode . "'
				AND b.periodegaji = '" . $periode . "'
				AND b.version_type = 'B'
				AND a.plus = 1
				GROUP BY a.karyawanid, a.periodegaji, a.name
				ORDER BY a.name DESC";
		$res = fetchdata($str);

		$str2 = "SELECT b.namakaryawan,
				b.nik,
				a.kodeorg,
				a.karyawanid,
				a.periodegaji,
				a.jumlah,
				a.name as komponen,
				a.plus
				FROM " . $dbname . ".sdm_gajidetail_vw a
				JOIN " . $dbname . ".datakaryawan_hist b
				ON a.karyawanid = b.karyawanid
				WHERE a.karyawanid = " . $karyawanid . "
				AND a.periodegaji = '" . $periode . "'
				AND b.periodegaji = '" . $periode . "'
				AND b.version_type = 'B'
				AND a.plus = 0
				GROUP BY a.karyawanid, a.periodegaji, a.name
				ORDER BY a.name DESC";
		$res2 = fetchdata($str2);

		$arr = array();
		foreach ($res as $key => $val) {
			$arr['nama'] = $val['namakaryawan'];
			$arr['nik'] = $val['nik'];
			$arr['unit'] = $val['kodeorg'];

			// $jmlhminus = 0;
			// $jmlhplus = 0;
			// if ($val['plus'] == 0) {
			// 	$jmlhminus = $val['jumlah'];
			// } else {
			// 	$jmlhplus = $val['jumlah'];
			// }
			// $total = $total + $jmlhplus - $jmlhminus;
		}

		// echo"<pre>";
		// print_r($res);
		// echo"</pre>";

		$tab = "<link rel=stylesheet type=text/css href=style/generic.css>
				<div>
					<table>
						<tr>
							<th align=left>NAMA</th>
							<td>:</td>
							<td>" . $arr['nama'] . "</td>
						</tr>
						<tr>
							<th align=left>NIK</th>
							<td>:</td>
							<td>" . $arr['nik'] . "</td>
						</tr>
						<tr>
							<th align=left>UNIT</th>
							<td>:</td>
							<td>" . $arr['unit'] . "</td>
						</tr>
					</table>
					<hr>
					<table width=100% cellpadding=7><td><b>Penambah</b></td></table>
					<table border=0 class=sortable cellpadding=5 cellspacing=1 style=min-width:500px>
						<thead>
							<tr class=rowheader>
								<th>No</th>
								<th>Komponen Gaji</th>
								<th>Jumlah</th>
							</tr>
						</thead>
						<tbody>";
		$no = 0;
		$total = 0;
		foreach ($res as $key => $val) {
			$no++;
			$tab .= "<tr class=rowcontent>
						<td align=center>" . $no . "</td>
						<td>" . $val['komponen'] . "</td>
						<td align=right>" . number_format($val['jumlah']) . "</td>
					</tr>";
			$total = $total + $val['jumlah'];
		}

		$tab .= "<tr class=rowcontent>
					<td colspan=2 align=center>TOTAL</td>
					<td align=right>" . number_format($total) . "</td>
				</tr>
				</tbody>
				</table>
				</fieldset>";

		$tab .= "<br>
				<table width=100% cellpadding=7><td><b>Pengurang</b></td></table>
				<table border=0 class=sortable cellpadding=5 cellspacing=1 style=min-width:500px>
					<thead>
						<tr class=rowheader>
							<th>No</th>
							<th>Komponen Gaji</th>
							<th>Jumlah</th>
						</tr>
					</thead>
					<tbody>";
		$no2 = 0;
		$total2 = 0;
		foreach ($res2 as $key => $val) {
			$no2++;
			$tab .= "<tr class=rowcontent>
						<td align=center>" . $no2 . "</td>
						<td>" . $val['komponen'] . "</td>
						<td align=right>" . number_format($val['jumlah']) . "</td>
					</tr>";
			$total2 = $total2 + $val['jumlah'];
		}

		$tab .= "<tr class=rowcontent>
					<td colspan=2 align=center>TOTAL</td>
					<td align=right>" . number_format($total2) . "</td>
				</tr>
				</tbody>
				</table>
				</fieldset>";

		$tab .= "</div>";

		echo $tab;
		break;

	case 'alokasi':

		$strd = "SELECT 
				a.kodeorg,
				a.nik as karyawanid,
				a.periode,
				a.debet,
				a.kredit,
				a.keterangan,a.nojurnal
				FROM " . $dbname . ".keu_jurnaldt_vw a
				WHERE a.nik = '" . $karyawanid . "'
				AND a.periode = '" . $periode . "'
				AND jumlah<0
				AND kodejurnal ='POT' and nojurnal not like '%/M/%'
				ORDER BY a.keterangan DESC";
		$resd = fetchdata($strd);

		$strk = "SELECT 
				a.kodeorg,
				a.nik as karyawanid,
				a.periode,
				a.debet,
				a.kredit,
				a.keterangan,a.nojurnal
				FROM " . $dbname . ".keu_jurnaldt_vw a
				WHERE a.nik = '" . $karyawanid . "'
				AND a.periode = '" . $periode . "'
				AND a.noakun like '213%' and substr(noakun,1,5) < '21302'
			 	AND jumlah < 0
			 	AND noreferensi NOT LIKE 'ALK_POT%' and nojurnal not like '%/M/%'
				ORDER BY a.keterangan DESC";
		$resk = fetchdata($strk);

		$strkv = "SELECT 
				a.kodeorg,
				a.nik as karyawanid,
				a.periode,
				a.debet,
				a.kredit,
				a.keterangan,a.nojurnal
				FROM " . $dbname . ".keu_jurnaldt_vw a
				WHERE a.nik = '" . $karyawanid . "'
				AND a.periode = '" . $periode . "'
				AND a.noakun like '213%' and substr(noakun,1,5) < '21302'
			 	AND jumlah < 0
			 	AND noreferensi NOT LIKE 'ALK_POT%'
				AND nojurnal like '%/M/%'
				AND autojurnal='1'
				AND noaruskas=''
				ORDER BY a.keterangan DESC";
		$reskv = fetchdata($strkv);

		## Tambah untuk BM TBS
		$strkbm = "SELECT 
				a.kodeorg,
				a.nik as karyawanid,
				a.periode,
				a.debet,
				a.kredit,
				a.keterangan,a.nojurnal
				FROM " . $dbname . ".keu_jurnaldt_vw a
				WHERE a.nik = '" . $karyawanid . "'
				AND a.periode = '" . $periode . "'
				AND a.noakun not like '213%'
			 	AND jumlah > 0
				AND kodejurnal='BM01'
				AND autojurnal='1'
				AND noaruskas=''
				ORDER BY a.keterangan DESC";
		$resalokbm = fetchdata($strkbm);

		$str2 = "SELECT
				namakaryawan,
				karyawanid,
				nik,
				lokasitugas
				FROM " . $dbname . ".datakaryawan_hist
				WHERE karyawanid = '" . $karyawanid . "'
				AND version_type = 'B'
				AND periodegaji = '" . $periode . "'";
		$res2 = fetchdata($str2);

		$arr = array();
		foreach ($res2 as $key => $val) {
			$arr['nama'] = $val['namakaryawan'];
			$arr['nik'] = $val['nik'];
			$arr['unit'] = $val['lokasitugas'];
		}

		// echo"<pre>";
		// print_r($res);
		// echo"</pre>";

		$tab = "<link rel=stylesheet type=text/css href=style/generic.css>
				<div style='margin: 10px !important'>
					<table>
						<tr>
							<th align=left>NAMA</th>
							<td>:</td>
							<td>" . $arr['nama'] . "</td>
						</tr>
						<tr>
							<th align=left>NIK</th>
							<td>:</td>
							<td>" . $arr['nik'] . "</td>
						</tr>
						<tr>
							<th align=left>UNIT</th>
							<td>:</td>
							<td>" . $arr['unit'] . "</td>
						</tr>
					</table>
					<hr>
					
					<table width=100% cellpadding=7><td><b>Debet</b></td></table>
					<table border=0 class=sortable cellpadding=5 cellspacing=1 style=min-width:500px>
						<thead>
							<tr class=rowheader>
								<th>No</th>
								<th>Keterangan</th>
								<th>Debet</th>
							</tr>
						</thead>
						<tbody>";
		$no = 0;
		$totaldebet = 0;
		foreach ($resd as $key => $val) {
			$no++;

			$tab .= "<tr class=rowcontent>
						<td align=center>" . $no . "</td>
						<td>" . $val['keterangan'] . "</td>
						<td align=right>" . number_format($val['kredit']) . "</td>
					</tr>";

			$totaldebet = $totaldebet + $val['kredit'];
		}

		$tab .= "<tr class=rowcontent>
					<td align=center colspan=2>TOTAL</td>
					<td align=right>" . number_format($totaldebet) . "</td>
				</tr>
				</tbody>
				</table>
				</fieldset>";


		$tab .= "<br>
				
				<table width=100% cellpadding=7><td><b>Kredit</b></td></table>
				<table border=0 class=sortable cellpadding=5 cellspacing=1 style=min-width:500px>
					<thead>
						<tr class=rowheader>
							<th>No</th>
							<th>Keterangan</th>
							<th>Kredit</th>
							<th>No. Jurnal</th>
						</tr>
					</thead>
					<tbody>";
		$no2 = 0;
		$totalkredit = 0;
		foreach ($resk as $key => $val) {
			$no2++;

			$tab .= "<tr class=rowcontent>
						<td align=center>" . $no2 . "</td>
						<td>" . $val['keterangan'] . "</td>
						<td align=right>" . number_format($val['kredit']) . "</td>
						<td align=right>" . $val['nojurnal'] . "</td>
					</tr>";

			$totalkredit = $totalkredit + $val['kredit'];
		}

		foreach ($reskv as $key => $val) {
			$no2++;

			$tab .= "<tr class=rowcontent>
						<td align=center>" . $no2 . "</td>
						<td>" . $val['keterangan'] . "</td>
						<td align=right>" . number_format($val['kredit']) . "</td>
						<td align=right>" . $val['nojurnal'] . "</td>
					</tr>";

			$totalkredit = $totalkredit + $val['kredit'];
		}

		foreach ($resalokbm as $key => $val) {
			$no2++;

			$tab .= "<tr class=rowcontent>
						<td align=center>" . $no2 . "</td>
						<td>" . $val['keterangan'] . "</td>
						<td align=right>" . number_format($val['debet']) . "</td>
						<td align=right>" . $val['nojurnal'] . "</td>
					</tr>";

			$totalkredit = $totalkredit + $val['debet'];
		}

		$tab .= "<tr class=rowcontent>
					<td align=center colspan=2>TOTAL</td>
					<td align=right>" . number_format($totalkredit) . "</td>
					<td align=center></td>
				</tr>
				</tbody>
				</table>
				</fieldset>";


		$tab .= "</div>";

		echo $tab;
		break;
}
