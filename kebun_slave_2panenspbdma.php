<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/utilities.php');
include_once('lib/HtmlExcel.php');
error_reporting(0);
$param = $_POST;
if (count($param) == 0) {
	$param = $_GET;
}

$border = 1;
$class  = "";

switch ($param['method']) {
	//Modify Header
	case 'previewData':
		$tab = "";
		if ($param['tipe'] == 'html') {
			$tab .= "<div class='table-scroll' style='height:54vh'>";
			$border = 0;
			$class  = "sortable";
		}

		$exptglawal = explode('-', $param['tanggalawal']);
		$exptglakhir = explode('-', $param['tanggalakhir']);

		if ($exptglawal[1] != $exptglakhir[1]) {
			exit("Warning, Periode tanggal harus dibulan yang sama");
		}
		$wehrexx = '';
		$where = " and (tanggal between '" . tanggalsystemn($param['tanggalawal']) . "' and '" . tanggalsystemn($param['tanggalakhir']) . "')";
		if ($param['unit'] != '') {
			$where .= " and kodeorg like '" . $param['unit'] . "%'";
			$wehrexx = " and blok like '" . $param['unit'] . "%'";
		}

		if ($param['divisi'] != '') {
			$where .= " and kodeorg like '" . $param['divisi'] . "%'";
			$wehrexx = " and blok like '" . $param['divisi'] . "%'";
		}

		$tab .= "<table cellspacing='1' cellpadding='3' border='" . $border . "' class='" . $class . "' style='margin-top:0px;width:99.9%'>
			<thead>
			<tr class='rowheader'>
				<th style='text-align:center;vertical-align:middle' rowspan='2'>" . $_SESSION['lang']['nourut'] . "</th>
				<th style='text-align:center;vertical-align:middle' colspan=9>" . $_SESSION['lang']['panen'] . "</th>
				<th style='text-align:center;vertical-align:middle' colspan=6>SPB</th>
				<th style='text-align:center;vertical-align:middle' rowspan='2'>" . $_SESSION['lang']['kgwb'] . "</th>
				<th style='text-align:center;vertical-align:middle' rowspan='2'>" . $_SESSION['lang']['kgwb'] . " Netto</th>
			</tr>
			<tr class='rowheader'>
				<th style='text-align:center;vertical-align:middle'>" . $_SESSION['lang']['tanggal'] . "</th>
				<th style='text-align:center;vertical-align:middle'>" . $_SESSION['lang']['notransaksi'] . "</th>
				<th style='text-align:center;vertical-align:middle'>" . $_SESSION['lang']['mandor'] . " 1</th>
				<th style='text-align:center;vertical-align:middle'>" . $_SESSION['lang']['mandor'] . "</th>
				<th style='text-align:center;vertical-align:middle'>" . $_SESSION['lang']['kerani'] . "</th>
				<th style='text-align:center;vertical-align:middle'>" . $_SESSION['lang']['blok'] . "</th>
				<th style='text-align:center;vertical-align:middle'>" . $_SESSION['lang']['tph'] . "</th>
				<th style='text-align:center;vertical-align:middle'>" . $_SESSION['lang']['jjg'] . "</th>
				<th style='text-align:center;vertical-align:middle'>" . $_SESSION['lang']['brondol'] . " (KG)</th>
				
				<th style='text-align:center;vertical-align:middle'>" . $_SESSION['lang']['jjg'] . "</th>
				<th style='text-align:center;vertical-align:middle'>" . $_SESSION['lang']['brondol'] . " (KG)</th>
				<th style='text-align:center;vertical-align:middle'>No. SPB</th>
				<th style='text-align:center;vertical-align:middle'>" . $_SESSION['lang']['tanggal'] . "</th>
				<th style='text-align:center;vertical-align:middle'>" . $_SESSION['lang']['nopol'] . "</th>
				<th style='text-align:center;vertical-align:middle'>" . $_SESSION['lang']['supir'] . "</th>
			</tr>
			</thead><tbody>";

		## GET DETAIL PANEN
		$datablok = '';
		$datatph = '';
		$datatanggalpanen = '';
		$datapemanen = '';
		$datanospb = '';
		$arrnospb = array();
		$arrdatablok = array();
		$arrdatatph = array();
		$arrdatatglpanen = array();
		$arrpemanen = array();
		$str = "select tanggal,notransaksi,nikmandor,kerani,karyawanid,kodeorg,tph,sum(hasilkerja) as hasilkerja, sum(brondolan) as brondolan from " . $dbname . ".kebun_prestasi_detail_vw where 1=1 " . $where . " group by tanggal,notransaksi,kodeorg,tph order by tanggal asc";
		$res = fetchdata($str);
		foreach ($res as $val) {
			$arrtgl[$val['tanggal']] = $val['tanggal'];
			$arrtrk[$val['tanggal']][$val['notransaksi']]['notransaksi'] = $val['notransaksi'];
			$arrtrk[$val['tanggal']][$val['notransaksi']]['mandor1'] = $val['nikmandor1'];
			$arrtrk[$val['tanggal']][$val['notransaksi']]['mandor'] = $val['nikmandor'];
			$arrtrk[$val['tanggal']][$val['notransaksi']]['kerani'] = $val['kerani'];
			$arrkar[$val['tanggal']][$val['notransaksi']] = $val['karyawanid'];
			$arrblok[$val['tanggal']][$val['notransaksi']][$val['kodeorg']] = $val['kodeorg'];
			$arrtph[$val['tanggal']][$val['notransaksi']][$val['kodeorg']][$val['tph']]['tph'] = $val['tph'];
			$arrtph[$val['tanggal']][$val['notransaksi']][$val['kodeorg']][$val['tph']]['jjg'] += $val['hasilkerja'];
			$arrtph[$val['tanggal']][$val['notransaksi']][$val['kodeorg']][$val['tph']]['brondol'] += $val['brondolan'];

			if (!isset($arrdatablok[$val['kodeorg']])) {
				$arrdatablok[$val['kodeorg']] = $val['kodeorg'];
				if ($datablok == '') {
					$datablok = "'" . $val['kodeorg'] . "'";
				} else {
					$datablok .= ",'" . $val['kodeorg'] . "'";
				}
			}

			if (!isset($arrdatatph[$val['tph']])) {
				$arrdatatph[$val['tph']] = $val['tph'];
				if ($datatph == '') {
					$datatph = "'" . $val['tph'] . "'";
				} else {
					$datatph .= ",'" . $val['tph'] . "'";
				}
			}

			if (!isset($arrdatatglpanen[$val['tanggal']])) {
				$arrdatatglpanen[$val['tanggal']] = $val['tanggal'];
				if ($datatanggalpanen == '') {
					$datatanggalpanen = "'" . $val['tanggal'] . "'";
				} else {
					$datatanggalpanen .= ",'" . $val['tanggal'] . "'";
				}
			}

			if (!isset($arrpemanen[$val['karyawanid']])) {
				$arrpemanen[$val['karyawanid']] = $val['karyawanid'];
				if ($datapemanen == '') {
					$datapemanen = "'" . $val['karyawanid'] . "'";
				} else {
					$datapemanen .= ",'" . $val['karyawanid'] . "'";
				}
			}
			## GET DETAIL SPB
			$strx = "select tanggal,tph,blok,pemanen,nospb,sum(jjg) as jjg, sum(brondolan) as brondolan, nokendaraan,supir,sum(kgwb) as kgwb,sum(kgwbnetto) as kgwbnetto from " . $dbname . ".kebun_spb_vw4 where blok='" . $val['kodeorg'] . "' and tph='" . $val['tph'] . "' and tanggalpanen='" . $val['tanggal'] . "'
			group by nospb,blok,tph,tanggalpanen";
			// echo "<br>";
			$resx = fetchdata($strx);
			foreach ($resx as $xbv) {

				if (!isset($arrnospb[$xbv['nospb']])) {
					$arrnospb[$xbv['nospb']] = $xbv['nospb'];

					if ($datanospb == '') {
						$datanospb = "'" . $xbv['nospb'] . "'";
					} else {
						$datanospb .= ",'" . $xbv['nospb'] . "'";
					}
				}


				$arrspb[$val['tanggal']][$val['notransaksi']][$val['kodeorg']][$val['tph']][$xbv['nospb']]['spbjjg'] += $xbv['jjg'];
				$arrspb[$val['tanggal']][$val['notransaksi']][$val['kodeorg']][$val['tph']][$xbv['nospb']]['spbbrondol'] += $xbv['brondolan'];
				$arrspb[$val['tanggal']][$val['notransaksi']][$val['kodeorg']][$val['tph']][$xbv['nospb']]['spbno'] = $xbv['nospb'];
				$arrspb[$val['tanggal']][$val['notransaksi']][$val['kodeorg']][$val['tph']][$xbv['nospb']]['spbtanggal'] = $xbv['tanggal'];
				if ($xbv['nokendaraan'] == '') {
					$noRef = makeOption($dbname, 'kebun_spbht', 'nospb,noreferensi', "nospb = '" . $xbv['nospb'] . "'");
					$nomorKendaraan = makeOption($dbname, 'pabrik_timbangan', 'nospbmobile,nokendaraan', "nospbmobile = '" . $noRef[$xbv['nospb']] . "'");
					$xbv['nokendaraan'] = $nomorKendaraan[$noRef[$xbv['nospb']]];
				}
				$arrspb[$val['tanggal']][$val['notransaksi']][$val['kodeorg']][$val['tph']][$xbv['nospb']]['spbnopol'] = $xbv['nokendaraan'];
				if ($xbv['supir'] == '') {
					$noRef = makeOption($dbname, 'kebun_spbht', 'nospb,noreferensi', "nospb = '" . $xbv['nospb'] . "'");
					$namaSupir = makeOption($dbname, 'pabrik_timbangan', 'nospbmobile,supir', "nospbmobile = '" . $noRef[$xbv['nospb']] . "'");
					$xbv['supir'] = $namaSupir[$noRef[$xbv['nospb']]];
				}
				$arrspb[$val['tanggal']][$val['notransaksi']][$val['kodeorg']][$val['tph']][$xbv['nospb']]['spbsupir'] = $xbv['supir'];
				$arrspb[$val['tanggal']][$val['notransaksi']][$val['kodeorg']][$val['tph']][$xbv['nospb']]['spbkg'] = +$xbv['kgwb'];
				$arrspb[$val['tanggal']][$val['notransaksi']][$val['kodeorg']][$val['tph']][$xbv['nospb']]['spbkgnetto'] = +$xbv['kgwbnetto'];
			}

			$arrspb[$val['tanggal']][$val['notransaksi']][$val['kodeorg']][$val['tph']][$xbv['nospb']]['jumx'] = count($resx);
			$color = '';
			if (count($resx) == 0) {
				// echo $val['karyawanid'];
				// echo "<br>";
				$arrspb[$val['tanggal']][$val['notransaksi']][$val['kodeorg']][$val['tph']][$xbv['nospb']]['colorx'] = "bgcolor='yellow'";
				//$color="bgcolor=yellow";

			}
		}

		## GET DETAIL SPB YANG TIDAK ADA DATA PANENNNYA
		$arrtdkcocok = array();
		if ($datatph == '' or $datatanggalpanen == '' or $datanospb == '') {
		} else {
			$strx = "select tanggal,tph,blok,pemanen,nospb,sum(jjg) as jjg, sum(brondolan) as brondolan, nokendaraan,supir,sum(kgwb) as kgwb from " . $dbname . ".kebun_spb_vw4 where blok not in (" . $datablok . ") and tph not in (" . $datatph . ") and tanggalpanen in (" . $datatanggalpanen . ") and nospb not in (" . $datanospb . ") " . $wehrexx . "
				group by nospb,blok,tph,tanggalpanen";
			// echo $strx;
			$resx = fetchdata($strx);
			foreach ($resx as $xbv) {
				$arrtdkcocok[$xbv['tanggal']][$xbv['nospb']][$xbv['blok']][$xbv['tph']]['brondolan'] += $xbv['brondolan'];
				$arrtdkcocok[$xbv['tanggal']][$xbv['nospb']][$xbv['blok']][$xbv['tph']]['jjg'] += $xbv['jjg'];
				$arrtdkcocok[$xbv['tanggal']][$xbv['nospb']][$xbv['blok']][$xbv['tph']]['kgwb'] += $xbv['kgwb'];
				$arrtdkcocok[$xbv['tanggal']][$xbv['nospb']][$xbv['blok']][$xbv['tph']]['spbnopol'] = $xbv['nokendaraan'];
				$arrtdkcocok[$xbv['tanggal']][$xbv['nospb']][$xbv['blok']][$xbv['tph']]['spbsupir'] = $xbv['supir'];
			}
		}

		/*	echo "<pre>";
		print_r($arrsesi);
		echo"</pre>";*/

		$total = array();

		if (count($arrtgl) > 0) {
			$no = 0;
			foreach ($arrtgl as $tanggal) {
				$no++;
				$tab .= "<tr class='rowcontent'>";
				$tab .= "<td style='text-align:center;vertical-align:top'>" . $no . "</td>";
				$tab .= "<td style='text-align:center;vertical-align:top' nowrap>" . tanggalnormal($tanggal) . "</td>";
				$nox1 = 0;
				foreach ($arrtrk[$tanggal] as $key => $val) {
					$nox1++;
					if ($nox1 > 1) {
						$tab .= "<tr class='rowcontent'>";
						$tab .= "<td></td>";
						$tab .= "<td></td>";
					}
					$tab .= "<td style='text-align:left;vertical-align:top'>" . $val['notransaksi'] . "</td>";
					$tab .= "<td style='text-align:left;vertical-align:top'>" . getNK($val['mandor1']) . "</td>";
					$tab .= "<td style='text-align:left;vertical-align:top'>" . getNK($val['mandor']) . "</td>";
					$tab .= "<td style='text-align:left;vertical-align:top'>" . getNK($val['kerani']) . "</td>";
					// $nox2=0;
					// foreach($arrkar[$tanggal][$val['notransaksi']] as $karyawanid){
					// 	$nox2++;
					// 	if($nox2 > 1){
					// 		$tab.="<tr class='rowcontent'>";						
					// 		$tab.="<td></td>";					
					// 		$tab.="<td></td>";					
					// 		$tab.="<td></td>";					
					// 		$tab.="<td></td>";					
					// 		$tab.="<td></td>";					
					// 		$tab.="<td></td>";					
					// 	}
					// 	$tab.="<td style='text-align:left;vertical-align:top' ".$color.">".getNK($karyawanid,'nik')."</td>";	
					// 	$tab.="<td style='text-align:left;vertical-align:top' ".$color.">".getNK($karyawanid)."</td>";	
					$nox3 = 0;
					foreach ($arrblok[$tanggal][$val['notransaksi']] as $blok) {
						$nox3++;
						if ($nox3 > 1) {
							$tab .= "<tr class='rowcontent'>";
							// $tab.="<td></td>";					
							// $tab.="<td></td>";					
							$tab .= "<td></td>";
							$tab .= "<td></td>";
							$tab .= "<td></td>";
							$tab .= "<td></td>";
							$tab .= "<td></td>";
							$tab .= "<td></td>";
						}
						$tab .= "<td style='text-align:left;vertical-align:top' " . $color . ">" . substr($blok, 6, 4) . "</td>";
						$nox4 = 0;
						foreach ($arrtph[$tanggal][$val['notransaksi']][$blok] as $tph => $valdt) {
							$nox4++;
							if ($nox4 > 1) {
								$tab .= "<tr class='rowcontent'>";
								// $tab.="<td></td>";					
								// $tab.="<td></td>";					
								$tab .= "<td></td>";
								$tab .= "<td></td>";
								$tab .= "<td></td>";
								$tab .= "<td></td>";
								$tab .= "<td></td>";
								$tab .= "<td></td>";
								$tab .= "<td></td>";
							}
							$tab .= "<td style='text-align:center;vertical-align:top' " . $color . ">" . $tph . "</td>";
							$tab .= "<td style='text-align:right;vertical-align:top'>" . number_format($valdt['jjg'], 0) . "</td>";
							$total['jjg'] += $valdt['jjg'];
							$tab .= "<td style='text-align:right;vertical-align:top'>" . hidezerodecimal($valdt['brondol'], 2) . "</td>";
							$total['brondol'] += $valdt['brondol'];


							$nox5 = 0;
							foreach ($arrspb[$tanggal][$val['notransaksi']][$blok][$tph] as $spbno => $valdt) {
								$nox5++;
								if ($nox5 > 1) {
									$tab .= "<tr class='rowcontent'>";
									// $tab.="<td></td>";					
									// $tab.="<td></td>";					
									$tab .= "<td></td>";
									$tab .= "<td></td>";
									$tab .= "<td></td>";
									$tab .= "<td></td>";
									$tab .= "<td></td>";
									$tab .= "<td></td>";
									$tab .= "<td></td>";
									$tab .= "<td></td>";
									$tab .= "<td></td>";
									$tab .= "<td></td>";
								}

								$tab .= "<td style='text-align:right;vertical-align:top' " . $valdt['colorx'] . ">" . ($valdt['spbjjg'] == '' ? '' : number_format($valdt['spbjjg'], 0)) . "</td>";
								$total['spbjjg'] += $valdt['spbjjg'];
								$tab .= "<td style='text-align:right;vertical-align:top' " . $valdt['colorx'] . ">" . ($valdt['spbbrondol'] == '' ? '' : hidezerodecimal($valdt['spbbrondol'], 2)) . "</td>";
								$total['spbbrondol'] += $valdt['spbbrondol'];



								$tab .= "<td style='text-align:left;vertical-align:top' " . $bgcol . " " . $title . "  " . $valdt['colorx'] . ">" . $valdt['spbno'] . "</td>";


								$tab .= "<td style='text-align:center;vertical-align:top' nowrap  " . $valdt['colorx'] . ">" . ($valdt['spbtanggal'] == '' ? '' : tanggalnormal($valdt['spbtanggal'])) . "</td>";
								$tab .= "<td style='text-align:center;vertical-align:top'  " . $valdt['colorx'] . ">" . $valdt['spbnopol'] . "</td>";
								$tab .= "<td style='text-align:left;vertical-align:top'  " . $valdt['colorx'] . ">" . $valdt['spbsupir'] . "</td>";
								$total['spbkg'] += ($valdt['spbkg'] <= 0 ? 0 : $valdt['spbkg']);
								$total['spbkgnetto'] += ($valdt['spbkgnetto'] <= 0 ? 0 : $valdt['spbkgnetto']);
								$tab .= "<td style='text-align:right;vertical-align:top'  " . $valdt['colorx'] . ">" . ($valdt['spbkg'] <= '0' ? '' : hidezerodecimal($valdt['spbkg'], 2)) . "</td>";
								$tab .= "<td style='text-align:right;vertical-align:top'  " . $valdt['colorx'] . ">" . ($valdt['spbkgnetto'] <= '0' ? '' : hidezerodecimal($valdt['spbkgnetto'], 2)) . "</td>";
							}
						}
					}
					// }
				}
				$tab .= "</tr>";
			}
		}

		foreach ($arrtdkcocok as $tgl => $key1) {
			foreach ($key1 as $nospb => $key2) {
				foreach ($key2 as $blok => $key3) {
					foreach ($key3 as $tph => $valx) {
						// foreach ($key4 as $pemanen => $valx) {
						$tab .= "<tr bgcolor='yellow'>";
						// $tab.="<td></td>";					
						// $tab.="<td></td>";					
						$tab .= "<td></td>";
						$tab .= "<td></td>";
						$tab .= "<td></td>";
						$tab .= "<td></td>";
						$tab .= "<td></td>";
						$tab .= "<td></td>";
						$tab .= "<td></td>";
						$tab .= "<td></td>";
						$tab .= "<td></td>";
						$tab .= "<td></td>";
						$tab .= "<td style='text-align:right;vertical-align:top'>" . ($valx['jjg'] == '' ? '' : number_format($valx['jjg'], 0)) . "</td>";
						$tab .= "<td style='text-align:right;vertical-align:top'>" . ($valx['brondolan'] == '' ? '' : hidezerodecimal($valx['brondolan'], 2)) . "</td>";
						$tab .= "<td style='text-align:left;vertical-align:top' " . $bgcol . " " . $title . ">" . $nospb . "</td>";
						$tab .= "<td style='text-align:center;vertical-align:top' nowrap>" . ($tgl == '' ? '' : tanggalnormal($tgl)) . "</td>";
						$tab .= "<td style='text-align:center;vertical-align:top'>" . $valx['spbnopol'] . " blok : " . $blok . " tph : " . $tph . " pemanen : " . getNK($pemanen) . "</td>";
						$tab .= "<td style='text-align:left;vertical-align:top'>" . $valx['spbsupir'] . "</td>";
						$tab .= "<td style='text-align:right;vertical-align:top'>" . ($valx['kgwb'] <= '0' ? '' : hidezerodecimal($valx['kgwb'], 2)) . "</td>";
						$tab .= "</tr>";
						// }
					}
				}
			}
		}
		// echo"<pre>";
		// print_r($arrtrk);
		// echo"</pre>";

		$tab .= "<tr class='rowcontent'>
						<td colspan='8'  text-align='center'>" . $_SESSION['lang']['total'] . "</td>
						<td align='right'>" . number_format($total['jjg']) . "</td>
						<td align='right'>" . number_format($total['brondol']) . "</td>
						<td align='right'>" . number_format($total['spbjjg']) . "</td>
						<td align='right'>" . number_format($total['spbbrondol']) . "</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td align='right'>" . number_format($total['spbkg']) . "</td>
						<td align='right'>" . number_format($total['spbkgnetto']) . "</td>
					</tr>";

		$tab .= "</tbody></table>";



		if ($param['tipe'] == 'html') {
			$tab .= "</div>";
			echo $tab;
		} else {
			$tab .= "Print Time : " . date('Y-m-d H:i:s') . " <br> By : " . $_SESSION['empl']['name'];
			$nop_ = "Laporan Annual HK " . getNamaKeg($param['kegiatan']) . " Tahun " . $param['tahun'];
			$css = "";
			$dte = date("YmdHis");
			$nop = $nop_ . ".xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet($nop_, $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}
		break;

	//Utilities
	case 'getDivisi':
		$query  = "SELECT kodeorganisasi, namaorganisasi FROM $dbname.organisasi WHERE induk = '" . $param['unit'] . "' AND tipe IN ('AFDELING','BIBITAN')";
		$result = fetchData($query, 'OBJECT');
		$opt    = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		foreach ($result as $key => $value) {
			$opt .= "<option value='" . $value->kodeorganisasi . "'>" . $value->kodeorganisasi . " - " . $value->namaorganisasi . "</option>";
		}

		echo $opt;
		break;
}
