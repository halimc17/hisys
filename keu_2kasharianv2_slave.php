<?php
require_once 'master_validation.php';
require_once 'lib/zLib.php';
include_once 'lib/nangkoelib.php';
require_once 'dompdf/autoload.inc.php';
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

use Dompdf\Dompdf;

$stream = '';
$method = checkPostGet('method', '');
$unit = checkPostGet('unit', '');
$noakun = checkPostGet('noakun', '');
$bank = checkPostGet('bank', '');
$tipe = checkPostGet('tipe', '');
$rek = checkPostGet('rek', '');
$pembayaran = checkPostGet('pembayaran', '');

$tgl1 = tanggalsystemn(checkPostGet('tgl1', ''));
$tgl2 = tanggalsystemn(checkPostGet('tgl2', ''));
$tanggal1 = checkPostGet('tgl1', '');
$tanggal2 = checkPostGet('tgl2', '');

if ($tgl1 == '--') {
	$tgl1 = '';
}
if ($tgl2 == '--') {
	$tgl2 = '';
}
$wherebank = "";

$nmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun');

if ($bank != '') {
	$wherebank = " and rekening='" . $bank . "'";
}
$whererek = "";
if ($rek != '') {
	$whererek = " and a.rekening = '" . $rek . "'";
} else {
	if ($unit == "") {
		$arrunit = getOrgDetail(1);
		$arrkodeunit = array_keys($arrunit);
		$joinunit = join("','", $arrkodeunit);
		$whererek = " and a.rekening in (select noakun from keu_5akunbank where pemilik IN ('" . $joinunit . "'))";
	} else {
		$whererek = " and a.rekening in (select noakun from keu_5akunbank where pemilik='" . $unit . "')";
	}
}

$per1 = substr($tgl1, 0, 7);
$tglawalbln = $per1 . '-01';
$per1 = str_replace('-', '', $per1);
$dtper1 = substr($per1, 4, 2);
$sawal = $tawalkm = $tawalkk = 0;
$sawall = $tawalkmm = $tawalkkk = 0;

$str = "select * from " . $dbname . ".keu_5akunbank_vw";
$res = fetchdata($str);
foreach ($res as $bar) {
	$namabank[$bar['noakun']] = $bar['namabank'] . " " . $bar['rekening'];
}

#= kas
$unit = checkPostGet('unit', '');
$noakun = checkPostGet('noakun', '');
$tglvoc1 = tanggalsystemn(checkPostGet('tglvoc1', ''));
$tglvoc2 = tanggalsystemn(checkPostGet('tglvoc2', ''));
$tglinput1 = tanggalsystemn(checkPostGet('tglinput1', ''));
$tglinput2 = tanggalsystemn(checkPostGet('tglinput2', ''));
$posting = checkPostGet('posting', '');
$pembayaran = checkPostGet('pembayaran', '');
$group = checkPostGet('group', '');
$tipetransaksi = checkPostGet('tipetransaksi', '');

switch ($method) {
	######PREVIEW
	case 'preview':
		if ($tgl1 == '' or $tgl2 == '') {
			exit("Warning:Tanggal kosong");
		}
		if ($tipe == 'excel' or $tipe == 'pdf') {
			$border = 1;

			$fontsize = '';
			if ($tipe == "pdf") {
				$fontsize = "style='font-size:11px'";
			}
		} else {
			$border = 0;
		}

		$countbank = 1;
		$arrbank[1] = $bank;
		if ($group == '1') {
			if ($bank == '') {
				// if ($noakun=='1110101') {
				// $whr=" and matauang='IDR'";
				// }else{
				// $whr=" and matauang!='IDR'";
				// }

				$str = "select * from " . $dbname . ".keu_5akunbank_vw where 1=1 and status=1 and pemilik='" . $unit . "' and noakuncoa='" . $noakun . "'";
				$res = fetchdata($str);
				foreach ($res as $val) {
					$arrbank[$countbank] = $val['noakun'];
					// $optbank.="<option value=".$bar['noakun'].">".$bar['pemilik'].":".$optNamaBank[$bar['namabank']]." ".$bar['rekening']."</option>";
					$countbank++;
				}
				$countbank = $countbank - 1;
			}
		}

		for ($i = 1; $i <= $countbank; $i++) {
			$wherebank = "";
			$bank = $arrbank[$i];

			if ($bank != '') {
				$wherebank = " and rekening='" . $bank . "'";
			}
			$sawal = 0;
			#= data
			$str = "select * from " . $dbname . ".organisasi where kodeorganisasi='" . $unit . "' ";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$tipeorg = $bar['tipe'];
			$namaorganisasi = $bar['namaorganisasi'];

			// if($tipe=='excel'){
			$stream .= "Laporan Kas Bank Harian<br>";
			$stream .= "" . $unit . " - " . $namaorganisasi . "<br>";
			$stream .= "" . tanggalnormal($tgl1) . " s/d " . tanggalnormal($tgl2) . "<br>";
			if ($bank != '') {
				$stream .= "" . $namabank[$bank] . "<br>";
			} else {
				$stream .= "" . $nmakun[$noakun] . "<br>";
			}
			$stream .= "<br>";
			// }else{
			// $stream.="Laporan Kas Bank Harian<br>";
			// }

			$stream .= "<table class=sortable cellspacing='1' border='" . $border . "' width=100% {$fontsize}>";
			$stream .= "
				<thead>
					<tr class=rowheader>
						<th align='center'>" . $_SESSION['lang']['nourut'] . "</th>
						<th align='center' style='min-width:70px;'>" . $_SESSION['lang']['tanggal'] . "</th>
						<th align='center'>" . $_SESSION['lang']['novoucher'] . "</th>
						<th align='center' style='min-width:70px;'>Cheque/Giro</th>
						<th align='center'>Bayar Ke</th>
						<th align='center' colspan=4>Keterangan</th>";
			$stream .= "<th align='center'>" . $_SESSION['lang']['penerimaan'] . "</th>
						<th align='center'>" . $_SESSION['lang']['pengeluaran'] . "</th>
						<th align='center'>" . $_SESSION['lang']['saldo'] . "</th>
					</tr>
				</thead>
			 <tbody>";
			$wherepembayaran = '';
			if ($pembayaran != '') {
				$wherepembayaran = "and pembayaran='" . $pembayaran . "'";
			}

			// if($pembayaran=='1'){
			// $str="select *,sum(jumlah) as debet,sum(jumlah) as kredit  from ".$dbname.".keu_kasbankht where kodeorg='".$unit."' and tanggal between '".$tgl1."' and '".$tgl2."' and noakun='".$noakun."' ".$wherebank." and posting='1' and pembayaran=1 group by novoucher ASC order by tanggal asc,tipetransaksi asc,novoucher asc,notransaksi asc";
			// }else{
			$str = "select notransaksi,tanggal,cgttu,nocek,bayarkepada,novoucher,keterangan,tipetransaksi,sum(jumlah*kurs) as debet,sum(jumlah*kurs) as kredit,matauang,kurs from " . $dbname . ".keu_kasbankht where kodeorg='" . $unit . "' and tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' and noakun = '" . $noakun . "' " . $wherebank . "  " . $wherepembayaran . " group by notransaksi ASC order by tanggal asc,tipetransaksi asc,novoucher asc,notransaksi asc";
			// }

			// exit ("WARNING  ".$str);
			$dttgl = $dtnotran = $lstcgttu = $lstnobuktibayar = $lstbayarkepada = $lsnotran = $lsnovoucher = $ket = $km = $kk = array();
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$dttgl[$bar['tanggal']] = $bar['tanggal'];
				$dtnotran[$bar['notransaksi']] = $bar['notransaksi'];
				$lstcgttu[$bar['tanggal']][$bar['notransaksi']] = $bar['cgttu'];
				$lstnobuktibayar[$bar['tanggal']][$bar['notransaksi']] = $bar['nocek'];
				$lstbayarkepada[$bar['tanggal']][$bar['notransaksi']] = $bar['bayarkepada'];
				// $lstanggal[$bar['tanggal']][$bar['notransaksi']] = $bar['tanggal'];
				if ($pembayaran == '1') {
					$lsnotran[$bar['tanggal']][$bar['notransaksi']] = $bar['novoucher'];
				} else {
					$lsnotran[$bar['tanggal']][$bar['notransaksi']] = $bar['notransaksi'];
				}
				$lsnovoucher[$bar['tanggal']][$bar['notransaksi']] = $bar['novoucher'];
				$ket[$bar['tanggal']][$bar['notransaksi']] = $bar['keterangan'];
				$matauang[$bar['tanggal']][$bar['notransaksi']] = $bar['matauang'];
				$kurs[$bar['tanggal']][$bar['notransaksi']] = $bar['kurs'];
				if ($bar['tipetransaksi'] == 'M') {
					$km[$bar['tanggal']][$bar['notransaksi']] = $bar['debet'];
				} else {
					$kk[$bar['tanggal']][$bar['notransaksi']] = $bar['kredit'];
				}
				$strinv = "select a.keterangan1 from " . $dbname . ".keu_kasbankdt a join " . $dbname . ".keu_tagihanht b on a.keterangan1 = b.noinvoice where notransaksi='" . $bar['notransaksi'] . "' group by a.keterangan1";
				$resinv = fetchdata($strinv);
				// $noinv[$bar['tanggal']][$bar['notransaksi']]=$resinv[0]['keterangan1']; //jaga2 buat ntar
				if (count($resinv[0]) > 0) {
					$noinvket[$bar['notransaksi']] = " AP:<br/>" . $resinv[0]['keterangan1'];
				}

				$strinv = "select a.keterangan1 from " . $dbname . ".keu_kasbankdt a join " . $dbname . ".keu_penagihanht b on a.keterangan1 = b.noinvoice where notransaksi='" . $bar['notransaksi'] . "' group by a.keterangan1";
				$resinv = fetchdata($strinv);
				// $noinv[$bar['tanggal']][$bar['notransaksi']]=$resinv[0]['keterangan1']; //jaga2 buat ntar
				if (count($resinv[0]) > 0) {
					$noinvket[$bar['notransaksi']] = " AR:<br/>" . $resinv[0]['keterangan1'];
				}
			}

			$ids = implode("','", $dtnotran);
			$nodt = $arrdt = $arrht = array();
			// $str = "select * from " . $dbname . ".keu_jurnaldt_vw where noreferensi in ('" . $ids . "') and nourut!='1' and kodeorg='" . $unit . "' order by nourut";
			// $res = fetchdata($str);
			// foreach ($res as $val) {
			//     $nodt[$val['noreferensi']] += 1;
			//     $arrdt[$val['noreferensi']][$nodt[$val['noreferensi']]]['keterangan'] = $val['keterangan'];
			//     $arrdt[$val['noreferensi']][$nodt[$val['noreferensi']]]['jumlah'] = $val['jumlah'];
			//     $arrdt[$val['noreferensi']][$nodt[$val['noreferensi']]]['noinv'] = $noinvket[$val['noreferensi']];
			// }

			$str = "select * from " . $dbname . ".keu_kasbankdtht_vw where notransaksi in ('" . $ids . "') and kodeorg='" . $unit . "' order by nodok";
			$res = fetchdata($str);
			foreach ($res as $val) {
				if ($val['tipetransaksi'] == 'K') {
					$arrht[$val['notransaksi']][$val['nodok']]['keterangan'] = $val['keterangan'];
					$arrht[$val['notransaksi']][$val['nodok']]['jumlah'] += $val['jumlah'];
					if ($val['jumlah'] < 0) {
						$arrht[$val['notransaksi']][$val['nodok']]['jlhmin'] += 1;
					}
				}
			}

			$tempnodok = "";
			foreach ($res as $val) {
				if ($val['tipetransaksi'] == 'K') {
					// if ($arrht[$val['notransaksi']][$val['nodok']]['jlhmin'] > 0) {
					//     if ($tempnodok != $val['nodok']) {
					//         $nodt[$val['notransaksi']] += 1;
					//         $arrdt[$val['notransaksi']][$nodt[$val['notransaksi']]]['keterangan'] = $val['keterangan'];
					//         $arrdt[$val['notransaksi']][$nodt[$val['notransaksi']]]['jumlah'] = $arrht[$val['notransaksi']][$val['nodok']]['jumlah'];
					//         $arrdt[$val['notransaksi']][$nodt[$val['notransaksi']]]['tipe'] = $val['tipetransaksi'];
					//     }
					//     $tempnodok = $val['nodok'];
					// } else {
					//     $nodt[$val['notransaksi']] += 1;
					//     $arrdt[$val['notransaksi']][$nodt[$val['notransaksi']]]['keterangan'] = $val['keterangan2'];
					//     $arrdt[$val['notransaksi']][$nodt[$val['notransaksi']]]['jumlah'] = $val['jumlah'];
					//     $arrdt[$val['notransaksi']][$nodt[$val['notransaksi']]]['tipe'] = $val['tipetransaksi'];
					// }
					// $nodt[$val['notransaksi']] += 1;
					$arrdt[$val['notransaksi']][$val['nourut']]['jumlah'] = $val['jumlah'];
					$arrdt[$val['notransaksi']][$val['nourut']]['tipe'] = $val['tipetransaksi'];
				}
				$arrdt[$val['notransaksi']][$val['nourut']]['keterangan'] = $val['keterangan2'];
				if ($val['tipetransaksi'] == 'M') {
					// $val['nourut'] += 1;
					// $arrdt[$val['notransaksi']][$val['nourut']]['keterangan'] = $val['keterangan'];
					$arrdt[$val['notransaksi']][$val['nourut']]['jumlah'] = $val['jumlah'];
					$arrdt[$val['notransaksi']][$val['nourut']]['tipe'] = $val['tipetransaksi'];
				}
			}

			#= bentuk sawal
			#= ambil dari keu_saldo (untuk kas) / keu_keu_saldobank (jika kolom bank terisi)
			// if ($noakun=='1110101' or $noakun=='1111101') {
			// if($bank!='') {
			// $str="select sum(awal".$dtper1.") as jumlah from ".$dbname.".keu_saldobank where
			// kodeorg='".$unit."' and periode='".$per1."' and norek='".$bank."'";
			// }else{
			// $str="select sum(awal".$dtper1.") as jumlah from ".$dbname.".keu_saldobank where
			// kodeorg='".$unit."' and periode='".$per1."'";
			// }
			// }else{
			// $str="select sum(awal".$dtper1.") as jumlah from ".$dbname.".keu_saldobulanan where
			// kodeorg='".$unit."' and periode='".$per1."' and noakun='".$noakun."'";
			// // echo $str;
			// }

			#11101,11102,11103
			// if (in_array(substr($noakun, 0, 5), ['11101', '11102', '11103'])) {
			//     // exit('error ===='.$noakun);
			//     $str = "select noakun from " . $dbname . ".keu_5akunbank where noakuncoa='" . $noakun . "'";
			//     $res = fetchdata($str);
			//     $bank = $res[0]['noakun'];
			//     $str = "select sum(awal" . $dtper1 . ") as jumlah from " . $dbname . ".keu_saldobank where
			//         kodeorg='" . $unit . "' and periode='" . $per1 . "' and norek='" . $bank . "'";
			// } else {
			//     $str = "select sum(awal" . $dtper1 . ") as jumlah from " . $dbname . ".keu_saldobulanan where
			//     kodeorg='" . $unit . "' and periode='" . $per1 . "' and noakun='" . $noakun . "'";
			// }

			if ($bank != '') {
				$str = "select sum(awal" . $dtper1 . ") as jumlah from " . $dbname . ".keu_saldobank where
					kodeorg='" . $unit . "' and periode='" . $per1 . "' and norek='" . $bank . "'";
			} else {
				$str = "select sum(awal" . $dtper1 . ") as jumlah from " . $dbname . ".keu_saldobulanan where
				kodeorg='" . $unit . "' and periode='" . $per1 . "' and noakun='" . $noakun . "'";
			}

			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$sawal = $bar['jumlah'];

			#jika saldo awal dari saldobulanan belum ada maka hitung nilai saldo awalnya
			/*
            if($sawal==0){
            #cut off per 2022-01-01
            if($bank==''){
            $str = "select sum(awal01) as jumlah from ".$dbname.".keu_saldobulanan where kodeorg='".$unit."' and periode='202101' and noakun='".$noakun."'";
            $res = fetchdata($str);
            foreach($res as $bar){
            $awaljan21=$bar['jumlah'];
            }
            }else{
            $str="select sum(awal01) as jumlah from ".$dbname.".keu_saldobank where
            kodeorg='".$unit."' and periode='202101' and norek='".$bank."'";
            foreach($res as $bar){
            $awaljan21=$bar['jumlah'];
            }

            }

            $str="select tipetransaksi, sum(jumlah) as debet,sum(jumlah) as kredit  from ".$dbname.".keu_kasbankht where kodeorg='".$unit."' and tanggal between '2022-01-01' and '".tglkemarin($tgl1)."' and noakun='".$noakun."' ".$wherebank." and posting='1' ".$wherepembayaran." group by tipetransaksi";
            $res=fetchdata($str);
            foreach($res as $bar){
            if($bar['tipetransaksi']=='M'){
            $kmasuk=$bar['debet'];
            } else {
            $kkeluar=$bar['kredit'];
            }
            }
            }
             */

			#= ambil transaksi s/d tanggal pertama u/ mendapatkan real saldo awal
			#= if disini mencegah jika ada transaksi di tgl 1, agar maka saldo awal tidak menjumlah, melainkan mengambil dr awal

			#= revisi jika dipilih tanggal 7-7, maka transaksi awal km/kk diambil dari 1-6, karna saldo awal 7 dan transaksi 7 ditampilkan
			#= sebelumnya saldo awal mengambil tgl 1, transaksi awal km/kk diambil dari 1-7, dan yang ditampilkan transaksi 7 juga, sehinggal double transaksi
			#= cara tes : buat tgl 1 - 7 ; bandingkan dengan tgl 7 - 7, saldo akhir harus sama, saldo awal di 7 = saldo akhir di tgl 6
			if ($tgl1 != $tglawalbln) {
				$tawalkm = $tawalkk = 0;
				$str = "select * from " . $dbname . ".keu_kasbankht where kodeorg='" . $unit . "' and tanggal between '" . $tglawalbln . "' and '" . tglkemarin($tgl1) . "' and noakun='" . $noakun . "'  and posting='1' " . $wherepembayaran . " " . $wherebank . " order by tanggal asc,tipetransaksi asc,notransaksi asc";

				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()) {
					if ($bar['tipetransaksi'] == 'M') {
						@$tawalkm += $bar['jumlah'] * $bar['kurs'];
					} else {
						@$tawalkk += $bar['jumlah'] * $bar['kurs'];
					}
				}
			}

			// echo $str;

			// echo $sawal._.$tawalkm._.$tawalkk;
			// if ($sawal == 0) {
			//     $sawal = $awaljan21 + $kmasuk - $kkeluar;
			// } else {
			//     $sawal = $sawal + $tawalkm - $tawalkk;
			// }
			$sawal = $sawal + $tawalkm - $tawalkk;
			#= sawal

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td></td>";
			$stream .= "<td></td>";
			$stream .= "<td></td>";
			$stream.="<td></td>";
			$stream .= "<td></td>";
			$stream .= "<td></td>";
			if ($tipe != 'excel') {
				$stream .= "<td colspan=3><b>Saldo Awal " . tanggalnormal($tgl1) . "</b></td>";
			} else {
				$stream .= "<td colspan=3><b>Saldo Awal " . $tgl1 . "</b></td>";
			}
			// $stream .= "<td></td>";
			$stream .= "<td></td>";
			$stream .= "<td></td>";
			$stream .= "<td align=right><b>" . number_format($sawal, 2) . "</b></td>";
			$stream .= "</tr>";

			@$cdata = count($dtnotran);
			if ($cdata < 1 or $cdata == '') {
				$salak = $sawal;
				$stream .= "Data kosong";
			} else {
				#= data
				$tkm = $tkk = 0;
				foreach ($dttgl as $tgl) {
					foreach ($dtnotran as $notran) {
						if ($lsnotran[$tgl][$notran] != '') {
							@$no += 1;
							// $stream.="<tr class=rowcontent style='cursor:pointer;background-color:#fef687' title='Click untuk melihat detail' onclick=\"lihatDetail('".$lsnotran[$tgl][$notran]."',event);\">";
							$stream .= "<tr class=rowcontent>";
							$stream .= "<td>" . $no . "</td>"; // nourut
							if ($tipe != 'excel') {
								$stream .= "<td style='text-align:center'>" . tanggalnormal($tgl) . "</td>"; //tglvoucher
							} else {
								$stream .= "<td style='text-align:center'>" . $tgl . "</td>"; //tglvoucher
							}
							$stream .= "<td>" . ($lsnovoucher[$tgl][$notran] == '' ? "<i> Belum dilakukan pembayaran </i>" : $lsnovoucher[$tgl][$notran]) . "</td>";
							// $stream .= "<td style='text-align:center'>" . ($lstglbayar[$tgl][$notran] == '0000-00-00' ? "<i> Belum dilakukan pembayaran </i>" : tanggalnormal($lstglbayar[$tgl][$notran])) . "</td>";
							//$stream.="<td>".$lstcgttu[$tgl][$notran]."</td>";
							$stream .= "<td>" . $lstnobuktibayar[$tgl][$notran] . "</td>";
							$stream .= "<td>" . $lstbayarkepada[$tgl][$notran] . "</td>";
							// $stream.="<td>".$ket[$tgl][$notran]."</td>";
							$stream .= "<td colspan='3'>".$ket[$tgl][$notran]."</td>";
							$stream .= "<td></td>";
							$stream .= "<td align=right>" . number_format($km[$tgl][$notran], 2) . "</td>";
							$stream .= "<td align=right>" . number_format($kk[$tgl][$notran], 2) . "</td>";
							$salak = $sawal + $km[$tgl][$notran] - $kk[$tgl][$notran];
							$stream .= "<td align=right>" . number_format($salak, 2) . "</td>";
							$sawal = $salak;
							@$tkm += $km[$tgl][$notran];
							@$tkk += $kk[$tgl][$notran];
							$stream .= "</tr>";

							ksort($arrdt[$notran]);
							foreach ($arrdt[$notran] as $key => $val) {
								if ($km[$tgl][$notran] > 0) {
									$val['jumlah'] *= -1;
								}
								$stream .= "<tr class=rowcontent>";
								$stream .= "<td>&nbsp;</td>";
								$stream .= "<td></td>";
								$stream .= "<td></td>";
								$stream .= "<td></td>";
								$stream .= "<td></td>";
								$stream .= "<td>" . $val['keterangan'] . "</td>";
								$stream .= "<td>" . $matauang[$tgl][$notran] . "</td>";
								$stream .= "<td>" . ($val['jumlah'] < 0 ? 'Cr' : 'Db') . "</td>";
								$stream .= "<td align='right'>" . number_format(abs($val['jumlah'] / $kurs[$tgl][$notran]), 2) . "</td>";
								// $stream .= "<td align='center' nowrap>" . $val['noinv'] . "</td>";
								$stream .= "<td></td>";
								$stream .= "<td align=right></td>";
								$stream .= "<td></td>";
								$stream .= "</tr>";
							}
						}
					}
				}
			}

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td></td>";
			$stream .= "<td></td>";
			$stream .= "<td></td>";
			$stream .= "<td></td>";
			$stream .= "<td></td>";
			$stream .= "<td></td>";
			$stream .= "<td></td>";
			$stream .= "<td colspan=2><b>" . $_SESSION['lang']['total'] . "</b></td>";
			// $stream .= "<td></td>";
			$stream .= "<td align=right><b>" . number_format($tkm, 2) . "</b></td>";
			$stream .= "<td align=right><b>" . number_format($tkk, 2) . "</b></td>";
			$stream .= "<td align=right><b>" . number_format($salak, 2) . "</b></td>";
			$stream .= "</tr>";

			$stream .= "
			</tbody>
			</table>
			<br>
			<div style='clear:both'></div>
			<br>";
		}

		if ($tipe == 'excel') {
			$tglSkrg = date("Ymd");
			$nop_ = "laporan_kas";
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
							parent.window.alert('Cannot convert to excel format');
							</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
							window.location='tempExcel/" . $nop_ . ".xls';
							</script>";
				}
				fclose($handle);
			}
		} else if ($tipe == 'pdf') {
			$dompdf = new Dompdf();
			$dompdf->loadHtml($stream);
			$dompdf->setPaper('F4', 'landscape');
			$dompdf->render();
			$dompdf->stream("form survey", array("Attachment" => 0));
		} else {
			echo $stream;
		}
		break;

	case 'detail':
		$notransaksi = checkPostGet('notransaksi', '');
		$novoucher = checkPostGet('novoucher', '');
		$stream = "";
		$theme = $_SESSION['theme'];
		if ($theme == 'skyblue' || $theme == '') {
			$men = 'menu.css';
			$gen = 'generic.css';
		} else if ($theme == 'red') {
			$men = 'menuRed.css';
			$gen = 'genericRed.css';
		} else {
			$men = 'menuGray.css';
			$gen = 'genericGray.css';
		}
		$stream .= "<link rel=stylesheet type='text/css' href='style/" . $gen . "'>";
		// $stream.="<fieldset>";
		$stream .= "<legend>Detail</legend><table class=sortable cellspacing=1 border='0' width=100%>";
		$stream .= "
			<thead>
				<tr class=rowheader>
					<td align='center'>" . $_SESSION['lang']['nourut'] . "</td>
					<td align='center'>" . $_SESSION['lang']['nojurnal'] . "</td>
					<td align='center'>" . $_SESSION['lang']['notransaksi'] . "</td>
					<td align='center'>" . $_SESSION['lang']['tanggal'] . "</td>
					<td align='center'>" . $_SESSION['lang']['noakundisplay'] . "</td>
					<td align='center'>" . $_SESSION['lang']['nik'] . "</td>
					<td align='center'>" . $_SESSION['lang']['keterangan'] . "</td>
					<td align='center'>" . $_SESSION['lang']['debet'] . "</td>
					<td align='center'>" . $_SESSION['lang']['kredit'] . "</td>
				</tr>
			</thead>
		 <tbody>";
		$str = "select * from " . $dbname . ".keu_jurnaldt_vw where noreferensi='" . $notransaksi . "' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {

			@$no += 1;
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center>" . $no . "</td>";
			$stream .= "<td>" . $bar['nojurnal'] . "</td>";
			$stream .= "<td>" . $bar['noreferensi'] . "</td>";
			$stream .= "<td>" . tanggalnormal($bar['tanggal']) . "</td>";
			$stream .= "<td>" . $nmakun[$bar['noakun']] . "</td>";
			$stream .= "<td>" . getKary($bar['nik'], 'namakaryawan') . "</td>";
			$stream .= "<td>" . $bar['keterangan'] . "</td>";
			$stream .= "<td align=right>" . number_format($bar['debet'], 2) . "</td>";
			$stream .= "<td align=right>" . number_format($bar['kredit'], 2) . "</td>";
			$stream .= "</tr>";
			@$tdebet += $bar['debet'];
			@$tkredit += $bar['kredit'];
		}
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center colspan=7>" . $_SESSION['lang']['total'] . "</td>";
		$stream .= "<td align=right>" . number_format($tdebet, 2) . "</td>";
		$stream .= "<td align=right>" . number_format($tkredit, 2) . "</td>";
		$stream .= "</tr>";
		$stream .= "</tbody>
			</table>";
		// $stream.="</fieldset>";
		echo $stream;
		break;

	case 'getbank':
		$arrtipeunit = getOrgDetail(10);
		$str = "select * from " . $dbname . ".keu_5akunbank_vw where status=1 and pemilik IN ('" . implode("','", $arrtipeunit) . "') and noakuncoa='" . $noakun . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optbank .= "<option value=" . $bar['noakun'] . ">" . $bar['namabank'] . ":" . $bar['rekening'] . " " . $bar['cabang'] . " " . $bar['atasnama'] . "</option>";
		}
		// }
		echo $optbank;

		break;

	case 'getrekening':
		$optrek = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		$str = "select a.noakun,a.rekening, b.namabank, a.pemilik from " . $dbname . ".keu_5akunbank a
		left join keu_5daftarbank b on a.namabank = b.kodebank
		where a.pemilik='" . $unit . "' and a.status=1";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optrek .= "<option value=" . $bar['noakun'] . ">" . $bar['pemilik'] . ":" . $bar['namabank'] . " - " . $bar['noakun'] . "</option>";
		}
		echo $optrek;

		break;

	#========================= KK ====================================

	case 'previewkk':
		if ($tgl1 == '' or $tgl2 == '') {
			exit("Warning:Tanggal kosong");
		}
		if ($tipe == 'excel' or $tipe == 'pdf') {
			$border = 1;
		} else {
			$border = 0;
		}

		$stream .= "<table class=sortable cellspacing=1 border='" . $border . "' width=100%>";
		$stream .= "
			<thead>
				<tr class=rowheader>
					<td align='center'>" . $_SESSION['lang']['nourut'] . "</td>
					<td align='center'>" . $_SESSION['lang']['tanggal'] . "</td>
					<td align='center'>" . $_SESSION['lang']['notransaksi'] . "</td>
					<td align='center'>" . $_SESSION['lang']['keterangan'] . "</td>
					<td align='center'>" . $_SESSION['lang']['penerimaan'] . "</td>
					<td align='center'>" . $_SESSION['lang']['pengeluaran'] . "</td>
					<td align='center'>" . $_SESSION['lang']['saldo'] . "</td>
				</tr>
			</thead>
		 <tbody>";

		#= data
		$str = "select * from " . $dbname . ".keu_kaskecil_vw where unit='" . $unit . "' and posting='1' and tanggal between '" . $tgl1 . "' and '" . $tgl2 . "'
			order by tanggal asc,tipe desc,notransaksi asc,createtime asc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$dttgl[$bar['tanggal']] = $bar['tanggal'];
			$dtnotran[$bar['notransaksi']] = $bar['notransaksi'];
			$dtnourut[$bar['nourut']] = $bar['nourut'];
			$lsnotran[$bar['tanggal']][$bar['notransaksi']][$bar['nourut']] = $bar['notransaksi'];
			$ket[$bar['tanggal']][$bar['notransaksi']][$bar['nourut']] = $bar['keterangan2'];
			if ($bar['tipe'] == 'M') {
				$km[$bar['tanggal']][$bar['notransaksi']][$bar['nourut']] = $bar['jumlah'];
			} else {
				$kk[$bar['tanggal']][$bar['notransaksi']][$bar['nourut']] = $bar['jumlah'];
			}
		}

		@$cdata = count($dtnotran);
		if ($cdata < 1 or $cdata == '') {
			exit("Warning:Tidak ada transaksi");
		}

		#= bentuk sawal
		#= ambil dari keu_saldo (untuk kas) / keu_keu_saldobank (jika kolom bank terisi)

		// $str="select sum(awal".$dtper1.") as jumlah from ".$dbname.".keu_saldobulanan where
		// kodeorg='".$unit."' and periode='".$per1."' and noakun='".$noakun."'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		//     $bar=$res->fetch();
		//     $sawal=$bar['jumlah'];

		$periodekaskecil = substr($per1, 0, 4) . "-" . substr($per1, 4, 2);
		$str = "select saldoawal from " . $dbname . ".keu_5kaskecil where
		unit='" . $unit . "' and periode='" . $periodekaskecil . "' and noakun='" . $noakun . "'";
		// exit('Warning : '.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$sawal = $bar['saldoawal'];

		#= ambil transaksi s/d tanggal pertama u/ mendapatkan real saldo awal
		#= if disini mencegah jika ada transaksi di tgl 1, agar maka saldo awal tidak menjumlah, melainkan mengambil dr awal
		if ($tgl1 != $tglawalbln) {
			$str = "select * from " . $dbname . ".keu_kasbankht where kodeorg='" . $unit . "' and tanggal between '" . $tglawalbln . "' and '" . $tgl1 . "'
				and noakun='" . $noakun . "'  and posting='1' order by tanggal asc,tipetransaksi asc,notransaksi asc";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				if ($bar['tipetransaksi'] == 'M') {
					@$tawalkm += $bar['jumlah'];
				} else {
					@$tawalkk += $bar['jumlah'];
				}
			}
		}

		$sawal = $sawal + $tawalkm - $tawalkk;

		#= sawal

		$stream .= "<tr class=rowcontent>";
		$stream .= "<td></td>";
		$stream .= "<td></td>";
		$stream .= "<td></td>";
		if ($tipe != 'excel') {
			$stream .= "<td>Saldo Awal " . tanggalnormal($tgl1) . "</td>";
		} else {
			$stream .= "<td>Saldo Awal " . $tgl1 . "</td>";
		}
		$stream .= "<td></td>";
		$stream .= "<td></td>";
		$stream .= "<td align=right><b>" . number_format($sawal, 2) . "</b></td>";
		$stream .= "</tr>";

		#= data
		foreach ($dttgl as $tgl) {
			foreach ($dtnotran as $notran) {
				foreach ($dtnourut as $nourut) {
					if ($lsnotran[$tgl][$notran][$nourut]) {
						@$no += 1;
						$stream .= "<tr class=rowcontent>";
						$stream .= "<td>" . $no . "</td>";
						if ($tipe != 'excel') {
							$stream .= "<td>" . tanggalnormal($tgl) . "</td>";
						} else {
							$stream .= "<td>" . $tgl . "</td>";
						}
						$stream .= "<td>" . $notran . "</td>";
						$stream .= "<td>" . $ket[$tgl][$notran][$nourut] . "</td>";
						$stream .= "<td align=right>" . number_format($km[$tgl][$notran][$nourut], 2) . "</td>";
						$stream .= "<td align=right>" . number_format($kk[$tgl][$notran][$nourut], 2) . "</td>";

						$whrjenis = "notransaksi='" . $notran . "'";
						$optjenis = makeOption($dbname, 'keu_kaskecil_vw', 'notransaksi,jenis', $whrjenis);
						if ($optjenis[$notran] != 1) {
							$salak = $sawal + $km[$tgl][$notran][$nourut] - $kk[$tgl][$notran][$nourut];
						}

						$stream .= "<td align=right>" . number_format($salak, 2) . "</td>";
						$sawal = $salak;
						@$tkm += $km[$tgl][$notran][$nourut];
						@$tkk += $kk[$tgl][$notran][$nourut];
						$stream .= "</tr>";
					}
				}
			}
		}

		$stream .= "<tr class=rowcontent>";
		$stream .= "<td></td>";
		$stream .= "<td></td>";
		$stream .= "<td></td>";
		$stream .= "<td>Jumlah</td>";
		$stream .= "<td align=right>" . number_format($tkm, 2) . "</td>";
		$stream .= "<td align=right>" . number_format($tkk, 2) . "</td>";
		$stream .= "<td align=right><b>" . number_format($salak, 2) . "</td>";
		$stream .= "</tr>";
		$stream .= "
		 </tbody>
			 </table>";

		if ($tipe == 'excel') {
			$tglSkrg = date("Ymd");
			$nop_ = "laporan_kaskecil";
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
							parent.window.alert('Cannot convert to excel format');
							</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
							window.location='tempExcel/" . $nop_ . ".xls';
							</script>";
				}
				fclose($handle);
			}
		} else if ($tipe == 'pdf') {
			$dompdf = new Dompdf();
			$dompdf->loadHtml($stream);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$dompdf->stream("form survey", array("Attachment" => 0));
		} else {

			echo $stream;
		}
		break;

	case 'previewsum':
		if ($tgl1 == '' or $tgl2 == '') {
			exit("Warning:Tanggal kosong");
		}
		if ($tipe == 'excel' or $tipe == 'pdf') {
			$border = 1;
			$bgclr = "bgcolor= #7FFFD4";
		} else {
			$border = 0;
			$bgclr = "";
		}
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $unit . "'");
		$stream .= "Laporan Summary Kas Bank / Rekening<br>";
		$stream .= "" . $unit . " - " . $nmorg[$unit] . "<br>";
		$stream .= "" . $tanggal1 . " s/d " . $tanggal2 . "<br><br>";
		$stream .= "<table class=sortable cellspacing=1 border='" . $border . "' width=100%>";
		$stream .= "
			<thead>
				<tr class=rowheader>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['nourut'] . "</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['rekening'] . "</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['saldo'] . " awal</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['penerimaan'] . "</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['pengeluaran'] . "</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['saldo'] . " akhir</td>
				</tr>
			</thead>
		 <tbody>";

		$arrunit = getOrgDetail(1);
		$arrkodeunit = array_keys($arrunit);
		$joinunit = join("','", $arrkodeunit);
		if ($unit == "") {
			$whereUnit = "and a.kodeorg IN ('" . $joinunit . "')";
		} else {
			$whereUnit = "and a.kodeorg = '" . $unit . "'";
		}

		$str = "select a.rekening, sum(a.jumlah*a.kurs) as jumlah,a.tipetransaksi,a.kurs,b.namabank, c.namabank as bank, b.pemilik,b.rekening as rekdisplay,b.matauang as currency
		 from " . $dbname . ".keu_kasbankht a
		 left join keu_5akunbank b on b.noakun = a.rekening
		 left join keu_5daftarbank c on c.kodebank = b.namabank
		 where a.tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' " . $whereUnit . " " . $whererek . " and a.posting='1' and a.pembayaran='1' AND b.status = '1'
		 group by a.rekening,a.tipetransaksi order by currency desc,a.rekening asc";
		//  echo $str; exit;
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$namarekening[$bar['rekening']] = $bar['rekening'];
			$nmBank[$bar['rekening']] = $bar['bank'];
			$nmRek[$bar['rekening']] = $bar['rekdisplay'];
			$nmMtUang[$bar['rekening']] = $bar['currency'];
			if ($tempRek != $bar['rekening']) {
				$tempRek = $bar['rekening'];
				$rowMtUang[$bar['currency']][] = $tempRek;
			}

			if ($bar['tipetransaksi'] == 'M') {
				$summ[$bar['rekening']] = $bar['jumlah'];
			} else {
				$sumk[$bar['rekening']] = $bar['jumlah'];
			}
		}
		// echo count($rowMtUang['IDR']);
		// exit('warning');
		@$cdata = count($namarekening);
		if ($cdata < 1 or $cdata == '') {
			exit("Warning:Tidak ada transaksi");
		}
		#= bentuk sawal
		#= ambil dari keu_saldo (untuk kas) / keu_keu_saldobank (jika kolom bank terisi)

		if ($rek != '') {
			$str = "select sum(awal" . $dtper1 . ") as jumlah, norek from " . $dbname . ".keu_saldobank where
			kodeorg='" . $unit . "' and periode='" . $per1 . "' and norek='" . $rek . "'";
		} else {
			$str = "select sum(awal" . $dtper1 . ") as jumlah, norek from " . $dbname . ".keu_saldobank where
			kodeorg='" . $unit . "' and periode='" . $per1 . "' group by norek";
		}
		// if($bank!='') {
		//     $str="select sum(awal".$dtper1.") as jumlah from ".$dbname.".keu_saldobank where
		//     kodeorg='".$unit."' and periode='".$per1."' and norek='".$bank."'";
		// }else{
		//     $str="select sum(awal".$dtper1.") as jumlah from ".$dbname.".keu_saldobulanan where
		//     kodeorg='".$unit."' and periode='".$per1."' and noakun='".$noakun."'";
		// }
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$sawalll[$bar['norek']] = $bar['jumlah'];
		}

		$tawalkmm = array();
		$tawalkkk = array();
		#= ambil transaksi s/d tanggal pertama u/ mendapatkan real saldo awal
		#= if disini mencegah jika ada transaksi di tgl 1, agar maka saldo awal tidak menjumlah, melainkan mengambil dr awal
		if ($tgl1 != $tglawalbln) {
			$str = "select * from " . $dbname . ".keu_kasbankht where kodeorg='" . $unit . "' and tanggal>='" . $tglawalbln . "' and tanggal<'" . $tgl1 . "'
				and rekening!='' order by tanggal asc,tipetransaksi asc,notransaksi asc";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				if ($bar['tipetransaksi'] == 'M') {
					$tawalkmm[$bar['rekening']] += $bar['jumlah'] * $bar['kurs'];
				} else {
					$tawalkkk[$bar['rekening']] += $bar['jumlah'] * $bar['kurs'];
				}
			}
		}

		#= sawal
		$tempMtUang = "";
		foreach ($namarekening as $valrek) {
			if ($tgl1 != $tglawalbln) {
				$sawalll[$valrek] = $sawalll[$valrek] + $tawalkmm[$valrek] - $tawalkkk[$valrek];
			}
			$salakk = 0;
			@$salakk += $sawalll[$valrek] + $summ[$valrek] - $sumk[$valrek];
			@$no += 1;
			if ($tempMtUang != $nmMtUang[$valrek]) {
				$tempMtUang = $nmMtUang[$valrek];
				$mulMtUang = 1;
				$tsawal = $tsalakk = $tkmm = $tkkk = 0;
			} else {
				$mulMtUang += 1;
			}
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align='center'>" . $no . "</td>";
			$stream .= "<td align='left'>" . $nmBank[$valrek] . " - " . $nmRek[$valrek] . "-" . $nmMtUang[$valrek] . "</td>";
			$stream .= "<td align='right'>" . number_format($sawalll[$valrek], 2) . "</td>";
			$stream .= "<td align='right'>" . number_format($summ[$valrek], 2) . "</td>";
			$stream .= "<td align='right'>" . number_format($sumk[$valrek], 2) . "</td>";
			$stream .= "<td align='right'>" . number_format($salakk, 2) . "</td>";
			@$tsawal += $sawalll[$valrek];
			@$tsalakk += $salakk;
			@$tkmm += $summ[$valrek];
			@$tkkk += $sumk[$valrek];
			if (count($rowMtUang[$tempMtUang]) == $mulMtUang) {
				$stream .= "<tr class=rowcontent>";
				$stream .= "<td></td>";
				$stream .= "<td align=right><b>" . $_SESSION['lang']['total'] . " " . $tempMtUang . "</b></td>";
				$stream .= "<td align=right><b>" . number_format($tsawal, 2) . "</b></td>";
				$stream .= "<td align=right><b>" . number_format($tkmm, 2) . "</b></td>";
				$stream .= "<td align=right><b>" . number_format($tkkk, 2) . "</b></td>";
				$stream .= "<td align=right><b>" . number_format($tsalakk, 2) . "</td>";
				$stream .= "</tr>";
			}
		}
		$stream .= "
		 </tbody>
			 </table>";

		if ($tipe == 'excel') {
			$tglSkrg = date("Ymd");
			$nop_ = "Summary KasBank";
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
							parent.window.alert('Cannot convert to excel format');
							</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
							window.location='tempExcel/" . $nop_ . ".xls';
							</script>";
				}
				fclose($handle);
			}
		} else if ($tipe == 'pdf') {
			$dompdf = new Dompdf();
			$dompdf->loadHtml($stream);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$dompdf->stream("form survey", array("Attachment" => 0));
		} else {

			echo $stream;
		}
		break;

	case 'previewkas':
		if ($tipe == 'excel' or $tipe == 'pdf') {
			$border = 1;
			$bgclr = "bgcolor= #7FFFD4";
		} else {
			$border = 0;
			$bgclr = "";
		}
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $unit . "'");
		$stream .= "Laporan Summary Kas Bank / Rekening<br>";
		$stream .= "" . $unit . " - " . $nmorg[$unit] . "<br>";
		$stream .= "" . $_SESSION['lang']['tanggal'] . "  " . $_SESSION['lang']['novoucher'] . " " . tanggalnormal($tglvoc1) . " s/d " . tanggalnormal($tglvoc2) . "<br>";
		$stream .= "" . $_SESSION['lang']['tanggalinput'] . " " . tanggalnormal($tglinput1) . " s/d " . tanggalnormal($tglinput2) . "<br><br>";
		$stream .= "<table class=sortable cellspacing=1 border='" . $border . "' width=100%>";
		$stream .= "
			<thead>
				<tr class=rowheader>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['nourut'] . "</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['tanggalinput'] . "</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['tanggal'] . "<br>" . $_SESSION['lang']['novoucher'] . "</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['noakun'] . "</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['noakun'] . "</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['namaakun'] . "<br>Detail</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['namaakun'] . "<br>Detail</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['notransaksi'] . "</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['novoucher'] . "</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['tipetransaksi'] . " I</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['tipetransaksi'] . " II</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['matauang'] . "</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['namabank'] . "</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['rekening'] . "</td>

					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['noaruskas'] . "</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['nama'] . "<br>" . $_SESSION['lang']['noaruskas'] . "</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['jumlah'] . "</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['kodesupplier'] . "</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['nik'] . "</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['detail'] . "</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['keterangan'] . "</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['noinvoice'] . "</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['nodok'] . "</td>
					<td align='center' " . $bgclr . ">" . $_SESSION['lang']['status'] . "</td>
				</tr>
			</thead>
		 <tbody>";

		$unit = checkPostGet('unit', '');
		$noakun = checkPostGet('noakun', '');
		$tglvoc1 = tanggalsystemn(checkPostGet('tglvoc1', ''));
		$tglvoc2 = tanggalsystemn(checkPostGet('tglvoc2', ''));
		$tglinput1 = tanggalsystemn(checkPostGet('tglinput1', ''));
		$tglinput2 = tanggalsystemn(checkPostGet('tglinput2', ''));
		$posting = checkPostGet('posting', '');

		if ($tglvoc1 == '--') {
			$tglvoc1 = '';
		}
		if ($tglvoc2 == '--') {
			$tglvoc2 = '';
		}
		if ($tglinput1 == '--') {
			$tglinput1 = '';
		}
		if ($tglinput2 == '--') {
			$tglinput2 = '';
		}

		if (($tglvoc1 != '' and $tglvoc2 == '') or ($tglvoc1 == '' and $tglvoc2 != '')) {
			exit("Warning:Tanggal Vocer tidak boleh salah satu kosong");
		}

		if (($tglinput1 != '' and $tglinput2 == '') or ($tglinput1 == '' and $tglinput2 != '')) {
			exit("Warning:Tanggal Inpute tidak boleh salah satu kosong");
		}

		if ($noakun == '') {
			exit("Warning:Noakun Masih Kosong");
		}

		if ($unit == '') {
			exit("Warning:Unit Masih Kosong");
		}

		$where = "";
		if ($tglvoc1 != '') {
			$where .= " and tanggal between '" . $tglvoc1 . "' and '" . $tglvoc2 . "' ";
		}

		if ($tglinput1 != '') {
			$where .= " and tanggalinput between '" . $tglinput1 . "' and '" . $tglinput2 . "' ";
		}

		if ($unit != '') {
			$where .= " and kodeorg='" . $unit . "'";
		}
		if ($posting != '') {
			$where .= " and posting='" . $posting . "'";
		}
		if ($noakun != '') {
			$where .= " and noakun2a='" . $noakun . "'";
		}
		if ($pembayaran != '') {
			$where .= " and pembayaran='" . $pembayaran . "'";
		}
		if ($tipetransaksi != '') {
			$where .= " and tipetransaksi='" . $tipetransaksi . "'";
		}

		$nmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
		$kdrekening = makeOption($dbname, 'keu_5akunbank_vw', 'noakun,rekening');
		$nmbankrekening = makeOption($dbname, 'keu_5akunbank_vw', 'noakun,namabank');
		$nmaruskas = makeOption($dbname, 'keu_5aruskas', 'noaruskas,nama_aruskas');
		$nmsupplier = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');

		$optposting[0] = 'Belum Diajukan';
		$optposting[1] = 'Disetujui';
		$optposting[3] = 'Ditolak';
		$optposting[9] = 'Proses Persetujuan';

		$str = "select * from " . $dbname . ".keu_kasbankdtht_vw where 1=1 " . $where;
		// echo $str;
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			@$no += 1;

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align='center'>" . $no . "</td>";
			$stream .= "<td align='left'>" . tanggalnormal($bar['tanggalinput']) . "</td>";
			$stream .= "<td align='left'>" . tanggalnormal($bar['tanggal']) . "</td>";
			$stream .= "<td align='left'>" . $bar['noakun2a'] . "</td>";
			$stream .= "<td align='left'>" . $nmakun[$bar['noakun2a']] . "</td>";
			$stream .= "<td align='left'>" . $bar['noakun'] . "</td>";
			$stream .= "<td align='left'>" . $nmakun[$bar['noakun']] . "</td>";
			$stream .= "<td align='left'>" . $bar['notransaksi'] . "</td>";
			$stream .= "<td align='left'>" . $bar['novoucher'] . "</td>";
			$stream .= "<td align='center'>" . $bar['tipetransaksi'] . "</td>";
			$stream .= "<td align='center'>" . $bar['kode'] . "</td>";
			$stream .= "<td align='center'>" . $bar['matauang'] . "</td>";
			$stream .= "<td align='left'>" . $nmbankrekening[$bar['rekening']] . "</td>";
			$stream .= "<td align='left'>" . $kdrekening[$bar['rekening']] . "</td>";
			$stream .= "<td align='left'>" . $bar['noaruskas'] . "</td>";
			$stream .= "<td align='left'>" . $nmaruskas[$bar['noaruskas']] . "</td>";
			if ($bar['tipetransaksi'] == 'K') {
				$stream .= "<td align='right'>" . number_format($bar['jumlah'], 2) . "</td>";
			} else {
				$stream .= "<td align='right'>" . number_format(($bar['jumlah'] * -1), 2) . "</td>";
			}

			$stream .= "<td align='left'>" . $nmsupplier[$bar['kodesupplier']] . "</td>";
			$stream .= "<td align='left'>" . $bar['nik'] . "</td>";
			$stream .= "<td align='left'>" . $bar['keterangan2'] . "</td>";
			$stream .= "<td align='left'>" . $bar['keterangan'] . "</td>";

			$stream .= "<td align='left'>" . $bar['keterangan1'] . "</td>";
			$stream .= "<td align='left'>" . $bar['nodok'] . "</td>";
			$stream .= "<td align='left'>" . $optposting[$bar['posting']] . "</td>";
		}

		$stream .= "
		 </tbody>
			 </table>";

		if ($tipe == 'excel') {
			$tglSkrg = date("Ymd");
			$nop_ = "Laporan_Kas";
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
							parent.window.alert('Cannot convert to excel format');
							</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
							window.location='tempExcel/" . $nop_ . ".xls';
							</script>";
				}
				fclose($handle);
			}
		} else if ($tipe == 'pdf') {
			$dompdf = new Dompdf();
			$dompdf->loadHtml($stream);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$dompdf->stream("form survey", array("Attachment" => 0));
		} else {

			echo $stream;
		}
		break;
}
