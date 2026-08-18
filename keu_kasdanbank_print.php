<?php
ini_set('display_errors', 0);
ini_set("session.auto_start", 0);
error_reporting(0);
session_start();
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');
include_once('lib/terbilang.php');
include_once('lib/terbilang.php');


require_once('dompdf/autoload.inc.php');

use Dompdf\Dompdf;

$proses = $_GET['proses'];
$param = $_GET;
$urlefil = checkPostGet('urlefil', '0');

$nmMt =  makeOption($dbname, 'setup_matauang', 'kode,matauang');
$nmorg =  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmakun =  makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
$nmket =  makeOption($dbname, 'keu_5keterangan', 'id_ket,keterangan');
$nmbank =  makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank');


$nmaruskas = makeOption($dbname, 'keu_5aruskas', 'noaruskas,nama_aruskas');

$str = "select * from " . $dbname . ".log_5supplier";
$res = fetchdata($str);
foreach ($res as $bar) {
	$nmsupplier[$bar['supplierid']] = $bar['namasupplier'];
}



#= approval karyawan
$str = "select * from " . $dbname . ".datakaryawan  where karyawanid in (select karyawanid from " . $dbname . ".approval where jenispersetujuan='KASBANK') ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$nmkaryawan[$bar['karyawanid']] = $bar['namakaryawan'];
}



/** Output Format **/
switch ($proses) {

	case 'pdfpalmakebun':

		exit('warning');

		# Inisialisasi
		$tab = "";
		$subHeader = "";
		$tipesub = "";


		# Logo
		$arrHead	= setheadreport('', $param['kodeorg']);
		$path		= $arrHead['logopalma'];

		# Style
		$borderht = 0;
		$borderdt = 1;
		$fontsizeht = 'font-size:18px;';
		$fontsizedt = 'font-size:14px;';

		$styledotted = "<style>
							.dotted-underline {
								border-bottom: 1px dotted black;
								display: inline;
							}
						</style>";

		# Make Option
		$indukmo = makeOption($dbname, "organisasi", "kodeorganisasi,induk");
		$kodeptmo = makeOption($dbname, "organisasi", "kodeorganisasi,namaorganisasi", "tipe='PT'");
		$namaakunmo = makeOption($dbname, "keu_5akun", "noakun,namaakun");
		$nmkaryawan = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
		$nxkaryawan = makeOption($dbname, 'datakaryawan', 'karyawanid,nik');

		# Select Data
		$sql = "SELECT * FROM $dbname.keu_kasbankht WHERE notransaksi='" . $param['notransaksi'] . "'";
		$resht = fetchData($sql, "OBJECT")[0];

		# Data Result
		$kodeorganisasiht 	= $resht->kodeorg;
		$kodeptht			= $kodeptmo[$indukmo[$resht->kodeorg]];
		$tipetransaksiht	= $resht->tipetransaksi;
		$rekeninght 		= $resht->rekening;
		$noakunht			= $resht->noakun;
		$aruskasht			= $resht->noaruskas;
		$keteranganht		= $resht->keterangan;
		$jumlahht			= $resht->jumlah;
		$tanggalht			= $resht->tanggal;
		$dibuatoleh			= $nmkaryawan[$resht->createby];
		$noreferensiht		= $resht->noreferensi;


		#===============================================#
		# CEK APAKAH AUTOKB DARI NOREFRENSI
		#===============================================#
		if ($noreferensiht != '') {
			$sqlref = "SELECT autokb FROM {$dbname}.keu_kasbankht WHERE notransaksi='" . $noreferensiht . "'";
			$resref = fetchData($sqlref, "OBJECT")[0];

			# Data Result
			$autokbref = $resref->autokb;
		}
		#===============================================#
		# END CEK APAKAH AUTOKB DARI NOREFRENSI
		#===============================================#


		if ($tipetransaksiht == 'K') {
			$headtipesub = "PERMINTAAN";
			$tipesub = "KELUAR";
		} else {
			$headtipesub = "PENERIMAAN";
			$tipesub = "MASUK";
		}

		// if($rekeninght == '') {
		// 	$subHeader = "BANK ".$tipesub;
		// } else {
		// 	$subHeader = "KAS ".$tipesub;
		// }

		$subHeader = $headtipesub . " UANG " . $tipesub;

		# Approval
		if ($tipetransaksiht == 'M' && $noreferensiht != '' && ($autokbref == '1' or $autokbref == 1)) { // Cek jika tipe Masuk, dan auto kb 1, ikutkan PDFnya
			$notransaksiref = $noreferensiht;
			$whrnotransaksi = "AND notransaksi='" . $notransaksiref . "'";
		} else {
			$whrnotransaksi = "AND notransaksi='" . $param['notransaksi'] . "'";
		}

		$sql = "SELECT * FROM {$dbname}.approval WHERE 5=5 {$whrnotransaksi}";
		$res = fetchData($sql, "OBJECT");

		if (count($res) > 0) {
			$nos = 0;
			foreach ($res as $v) {
				$nos++;
				$arrdataapp[$v->level][$nos][$v->karyawanid] = ['id' => $v->karyawanid, 'status' => $v->status];
			}
		}

		# Detail
		$sql = "SELECT * FROM $dbname.keu_kasbankdt WHERE notransaksi='" . $param['notransaksi'] . "' AND keterangan3 NOT IN ('PAJAKPPNKAS', 'PAJAKPPHKAS')";
		$resdt = fetchData($sql);

		$tab .= "<style>
		th, td {
			word-wrap: break-word;
			overflow-wrap: break-word;
		}
	  
		.column-wrap {
			max-width: 40%;
		}
	  </style>";


		$tab .= "<table border='" . $borderht . "' style='width:100%;'>";
		$tab .= "<tr>";
		$tab .= "<td rowspan=4 align=center style='width:180px;'><img src='" . $path . "' style='width:180px;height:100px;' /></td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td align=center><b style='font-size: 16px;'>" . $kodeptht . "</b></td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td align=center><u><b style='" . $fontsizedt . "'>" . $subHeader . "</b></u></td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td align=center><span style='" . $fontsizedt . "' class='dotted-underline'>Nomor : " . $param['notransaksi'] . "</span></td>";
		$tab .= "</tr>";

		# Spasi
		$tab .= "<tr><td style='height:20px;'></td></tr>";

		$tab .= "</table>";

		$tab .= "<table width=100%>";
		# Tanggal
		$tab .= "<tr>";
		$tab .= "<td width=48.5%>";
		$tab .= "<table border='" . $borderht . "' style='width:100%'>";
		$tab .= "<tr>";
		$tab .= "<td width=10% style='" . $fontsizedt . "'>Tgl</td>";
		$tab .= "<td width=5% align=center style='" . $fontsizedt . "' align=right>:</td>";
		$tab .= "<td width=30% style='" . $fontsizedt . "'>" . $tanggalht . "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";
		$tab .= "</td>";

		# Slide kanan
		$tab .= "<td width=48.5%>";
		$tab .= "<table border='" . $borderht . "' style='width:100%'>";
		$tab .= "<tr>";
		$tab .= "<td width=10% align=left style='" . $fontsizedt . "'>Departemen</td>";
		$tab .= "<td width=5% align=center style='" . $fontsizedt . "'>:</td>";
		$tab .= "<td width=30% align=left style='" . $fontsizedt . "'>-</td>";
		$tab .= "</tr>";
		$tab .= "</table>";
		$tab .= "</td>";
		$tab .= "</tr>";

		# Keterangan
		$tab .= "<tr>";
		$tab .= "<td width=48.5%>";
		$tab .= "<table border='" . $borderht . "' style='width:100%'>";
		$tab .= "<tr>";
		$tab .= "<td width=10% style='" . $fontsizedt . ";vertical-align:top!important;'>Ket</td>";
		$tab .= "<td width=5% align=center style='" . $fontsizedt . ";vertical-align:top!important;' align=right>:</td>";
		$tab .= "<td width=30% style='" . $fontsizedt . "'>" . $keteranganht . "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";
		$tab .= "</td>";

		# Slide kanan
		$tab .= "<td width=48.5%>";
		$tab .= "<table border='" . $borderht . "' style='width:100%'>";
		$tab .= "<tr>";
		$tab .= "<td width=10% align=left style='" . $fontsizedt . "'>Unit Kerja</td>";
		$tab .= "<td width=5% align=center style='" . $fontsizedt . "'>:</td>";
		$tab .= "<td width=30% align=left style='" . $fontsizedt . "'>" . $kodeorganisasiht . "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";
		$tab .= "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";

		# Spasi
		$tab .= "<br/><br/>";

		# =========================================================== #
		# TABEL DETAIL
		# =========================================================== #
		$tab .= "<table border='" . $borderdt . "' style='width:100%' cellpadding=3 cellspacing=0>";
		$tab .= "<thead>";
		$tab .= "<tr>";
		$tab .= "<td align=center style='" . $fontsizedt . ";width:5%;' rowspan=2><b>No.</b></td>";
		$tab .= "<td align=center style='" . $fontsizedt . ";width:12%;' rowspan=2><b>No. Akun</b></td>";
		$tab .= "<td align=center style='" . $fontsizedt . "' rowspan=2><b>Nama Akun</b></td>";
		$tab .= "<td align=center style='" . $fontsizedt . "' rowspan=2><b>Keterangan</b></td>";
		$tab .= "<td align=center style='" . $fontsizedt . "' colspan=3><b>Nominal</b></td>";
		$tab .= "</tr>";

		$tab .= "<tr>";
		$tab .= "<td align=center style='" . $fontsizedt . ";width:7%;'><b>Curr</b></td>";
		$tab .= "<td align=center style='" . $fontsizedt . ";width:15%;'><b>Debet</b></td>";
		$tab .= "<td align=center style='" . $fontsizedt . ";width:15%;'><b>Kredit</b></td>";
		$tab .= "</tr>";
		$tab .= "</thead>";

		$tab .= "<tbody>";

		# Dari Header
		$i = 1;
		$data[$i] = array(
			'nomor' => @$i,
			'noakun' => @$noakunht,
			'namaakun' => @$namaakunmo[$noakunht],
			'keterangan2' => $keteranganht,
			'curr' => 'Rp.',
			'debet' => 0,
			'kredit' => 0,
		);

		if (@$tipetransaksiht == 'M') {
			$data[$i]['debet'] = $jumlahht;
			$totalDebet += $jumlahht;
		} else {
			$data[$i]['kredit'] = $jumlahht;
			$totalKredit += $jumlahht;
		}

		$i++;

		# Dari Detail
		foreach ($resdt as $row) {
			$strdt = "select * from " . $dbname . ".keu_tagihanht where noinvoice='" . $row['keterangan1'] . "' ";
			$resinv = fetchdata($strdt);

			$strdt = "select * from " . $dbname . ".vhc_5master_hist where kodevhc='" . $row['kodevhc'] . "' ";
			$resdt = fetchdata($strdt);
			@$nopol = $resdt[0]['nopol'];
			@$namavhc = $resdt[0]['detailvhc'];
			@$nmdepart = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $row['departemen'] . "'");
			$data[$i] = array(
				'nomor' => $i,
				'noakun' => $row['noakun'],
				'namaakun' => $namaakunmo[$row['noakun']] . "",
				'keterangan2' => $row['keterangan2'],
				'curr' => 'Rp.',
				'debet' => 0,
				'kredit' => 0
			);
			//	'hutangunit1'=>$optHutangUnit[$row['hutangunit1']]
			if (@$tipetransaksiht == 'M' and $row['jumlah'] > 0) {
				$data[$i]['kredit'] = $row['jumlah'];
				$totalKredit += $row['jumlah'];
			} else if (@$tipetransaksiht == 'K' and $row['jumlah'] < 0) {
				$data[$i]['kredit'] = $row['jumlah'] * -1;
				$totalKredit += $row['jumlah'] * -1;
			} else if (@$tipetransaksiht == 'M' and $row['jumlah'] < 0) {
				$data[$i]['debet'] = $row['jumlah'] * -1;
				$totalDebet += $row['jumlah'] * -1;
			} else {
				$data[$i]['debet'] = $row['jumlah'];
				$totalDebet += $row['jumlah'];
			}
			$i++;
		}

		// nyusun berdasarkan debet dulu, abis itu baru kredit. by dz
		if (!empty($data)) foreach ($data as $c => $key) {
			$sort_debet[] = $key['debet'];
			$sort_kredit[] = $key['kredit'];
		}

		// sort
		if (!empty($data)) array_multisort($sort_debet, SORT_DESC, $sort_kredit, SORT_ASC, $data);

		if (count($row) <= 0) {
			unset($data);
		}

		if (count($row) <= 0 && count($data) <= 0) {
			$tab .= "<tr>";
			$tab .= "<td colspan=7 align=center>Tidak Ada Data Detail</td>";
			$tab .= "</tr>";
		} else {

			// nyusun ulang nomor setelah disort by debet
			$nyomor = 0;
			foreach ($data as $key => $row) {
				$nyomor += 1;
				$tab .= "<tr>";
				foreach ($row as $key => $cont) {
					if ($key == 'nomor') {
						$tab .= "<td style='" . $fontsizedt . "' align=center name=col" . $nyomor . "[]>" . $nyomor . "</td>";
					} else {
						if ($key == 'debet' or $key == 'kredit') {
							$tab .= "<td style='" . $fontsizedt . "' align=right name=col" . $nyomor . "[]>" . hidezerodecimal($cont, 0) . "</td>";
						} else  if ($key == 'noakun') {
							$tab .= "<td style='" . $fontsizedt . "' name=col" . $nyomor . "[]>" . $cont . "<br>" . @$nmaruskas[$cont] . "</td>";
						} else  if ($key == 'namaakun') {
							$tab .= "<td style='" . $fontsizedt . "' name=col" . $nyomor . "[]>" . $cont . "<br>" . @$namaakunmo[$cont] . "</td>";
						} else if ($key == 'curr') {
							$tab .= "<td style='" . $fontsizedt . "' align=center name=col" . $nyomor . "[]><b>" . $cont . "</b></td>";
						} else  if ($key == 'hutangunit1') {
							if ($cont == 0) {
								$tab .= "<td style='" . $fontsizedt . "' align=center name=col" . $nyomor . "[]>Tidak</td>";
							} else if ($cont == 1) {
								$tab .= "<td style='" . $fontsizedt . "' align=center name=col" . $nyomor . "[]>Ya</td>";
							} else {
								$tab .= "<td style='" . $fontsizedt . "' name=col" . $nyomor . "[]></td>";
							}
						} else  if ($key == 'pemilikhutang1') {
							$tab .= "<td style='" . $fontsizedt . "' name=col" . $nyomor . "[]>" . $cont . "</td>";
						} else {
							$tab .= "<td style='" . $fontsizedt . "' name=col" . $nyomor . "[]>" . $cont . "</td>";
						}
					}
				}
				$tab .= "</tr>";
			}
		}

		# Tabel Row Total
		$tab .= "<tr>";
		$tab .= "<td style='" . $fontsizedt . "' align=center colspan=4><b>Total</b></td>";
		$tab .= "<td style='" . $fontsizedt . "' align=center><b>Rp.</b></td>";
		$tab .= "<td style='" . $fontsizedt . "' align=right><b>" . hidezerodecimal($totalDebet) . "</b></td>";
		$tab .= "<td style='" . $fontsizedt . "' align=right><b>" . hidezerodecimal($totalKredit) . "</b></td>";
		$tab .= "</tr>";

		$tab .= "</tbody>";
		$tab .= "</table>";
		# =========================================================== #
		# END - TABEL DETAIL
		# =========================================================== #

		# Spasi
		$tab .= "<br/><br/>";

		# =========================================================== #
		# =========================================================== #
		# TABEL TTD - REFACTORED TO DYNAMIC GRID
		# =========================================================== #
		$ttd_items = [];
		$ttd_items[] = ['label' => 'Dibuat,', 'name' => $dibuatoleh, 'signed' => true];

		if (count($arrdataapp) > 0) {
			$approvers = [];
			foreach ($arrdataapp as $level => $data) {
				foreach ($data as $nomor => $datax) {
					foreach ($datax as $karyid => $val) {
						$approvers[] = $val;
					}
				}
			}

			$countApp = count($approvers);
			foreach ($approvers as $idx => $app) {
				$label = ($idx == $countApp - 1) ? 'Disetujui,' : 'Diperiksa,';
				$ttd_items[] = ['label' => $label, 'name' => $nmkaryawan[$app['id']], 'signed' => ($app['status'] == '1')];
			}
		}

		$ttd_chunks = array_chunk($ttd_items, 4);
		foreach ($ttd_chunks as $chunk) {
			$chunkSize = count($chunk);
			$widthPerc = ($chunkSize / 4) * 100;
			$tab .= "<table border='" . $borderdt . "' width='" . $widthPerc . "%' cellpadding=4 cellspacing=0 style='margin-bottom:10px; margin-left:auto; margin-right:auto; border-collapse: collapse;'>";
			$tab .= "<thead><tr>";
			foreach ($chunk as $item) {
				$tab .= "<th style='width:" . (100 / $chunkSize) . "%;" . $fontsizedt . "' align=center><b>" . $item['label'] . "</b></th>";
			}
			$tab .= "</tr></thead>";

			$tab .= "<tbody><tr>";
			foreach ($chunk as $item) {
				$tab .= "<td style='height:70px;text-align:center;color:gray;font-size:14px'><i>" . ($item['signed'] && $item['name'] != '' ? 'ELECTRONICALLY SIGNED BY' : '') . "</i></td>";
			}
			$tab .= "</tr><tr>";
			foreach ($chunk as $item) {
				$tab .= "<td style='height:20px!important;" . $fontsizedt . "' align=center>" . $item['name'] . "</td>";
			}
			$tab .= "</tr></tbody></table>";
		}
		# =========================================================== #
		# END - TTD
		# =========================================================== #


		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		if ($urlefil == '0') {
			$dompdf->stream("PrintKASBANK_" . $param['notransaksi'], array("Attachment" => 0));
		} else {
			file_put_contents($urlefil, $dompdf->output());
		}
		break;

	case 'pdfpalma':

		# Inisialisasi
		$tab = "";
		$subHeader = "";
		$tipesub = "";

		# Style
		$borderht = 0;
		$borderdt = 1;
		$fontsizeht = 'font-size:18px;';
		$fontsizedt = 'font-size:14px;';

		$styledotted = "<style>
							.dotted-underline {
								border-bottom: 1px dotted black;
								display: inline;
							}
						</style>";

		# Make Option
		$indukmo = makeOption($dbname, "organisasi", "kodeorganisasi,induk");
		$kodeptmo = makeOption($dbname, "organisasi", "kodeorganisasi,namaorganisasi", "tipe='PT'");
		$namaakunmo = makeOption($dbname, "keu_5akun", "noakun,namaakun");
		$nmkaryawan = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
		$nxkaryawan = makeOption($dbname, 'datakaryawan', 'karyawanid,nik');

		# Select Data
		$sql = "SELECT * FROM $dbname.keu_kasbankht WHERE notransaksi='" . $param['notransaksi'] . "'";
		$resht = fetchData($sql, "OBJECT")[0];
		$mataUang = ucwords($resht->matauang);
		$simbolMataUang = makeOption($dbname, "setup_matauang", "kode,simbol", "kode='{$resht->matauang}'")[$resht->matauang];

		# Logo
		$arrHead	= setheadreport($indukmo[$param['kodeorg']], $param['kodeorg']);
		$path		= $arrHead['logo'];

		# Data Result
		$kodeorganisasiht 	= $resht->kodeorg;
		$kodeptht			= $kodeptmo[$indukmo[$resht->kodeorg]];
		$tipetransaksiht	= $resht->tipetransaksi;
		$rekeninght 		= $resht->rekening;
		$noakunht			= $resht->noakun;
		$aruskasht			= $resht->noaruskas;
		$keteranganht		= $resht->keterangan;
		$jumlahht			= $resht->jumlah;
		$tanggalht			= $resht->tanggal;
		$dibuatoleh			= $nmkaryawan[$resht->createby];
		$noreferensiht		= $resht->noreferensi;

		# Approval
		$str = "select karyawanid,status,tanggal,max(level) from " . $dbname . ".approval where notransaksi='" . $param['notransaksi'] . "' group by level order by level desc limit 1";
		$resdisetujui = fetchdata($str);
		$statusdisetujui = $resdisetujui[0]['status'];

		if ($resdisetujui[0]['status'] == '1') {
			$aprvkarpenyetuju = $nmkaryawan[$resdisetujui[0]['karyawanid']];
			// $expaprvtgl = explode("-",substr($res[0]['tanggal'],0,10));
			// $bulanaprv=$expaprvtgl[1];
			// $aprvtgl="Date : ".$expaprvtgl[2].' '.getnmbln($bulanaprv).' '.$expaprvtgl[0];
		}

		$str = "select karyawanid,status,tanggal,max(level-1) from " . $dbname . ".approval where notransaksi='" . $param['notransaksi'] . "' and status='1' order by level desc limit 1";
		$resdisetujui = fetchdata($str);
		if ($resdisetujui[0]['status'] == '1') {
			$aprvkardiperiksa = $nmkaryawan[$resdisetujui[0]['karyawanid']];
		}

		#===============================================#
		# CEK APAKAH AUTOKB DARI NOREFRENSI
		#===============================================#
		if ($noreferensiht != '') {
			$sqlref = "SELECT autokb FROM {$dbname}.keu_kasbankht WHERE notransaksi='" . $noreferensiht . "'";
			$resref = fetchData($sqlref, "OBJECT")[0];

			# Data Result
			$autokbref = $resref->autokb;
		}
		#===============================================#
		# END CEK APAKAH AUTOKB DARI NOREFRENSI
		#===============================================#


		if ($tipetransaksiht == 'K') {
			$headtipesub = "PERMINTAAN";
			$tipesub = "KELUAR";
		} else {
			$headtipesub = "PENERIMAAN";
			$tipesub = "MASUK";
		}

		// if($rekeninght == '') {
		// 	$subHeader = "BANK ".$tipesub;
		// } else {
		// 	$subHeader = "KAS ".$tipesub;
		// }

		$subHeader = $headtipesub . " UANG " . $tipesub;

		# Approval
		if ($tipetransaksiht == 'M' && $noreferensiht != '' && ($autokbref == '1' or $autokbref == 1)) { // Cek jika tipe Masuk, dan auto kb 1, ikutkan PDFnya
			$notransaksiref = $noreferensiht;
			$whrnotransaksi = "AND notransaksi='" . $notransaksiref . "'";
		} else {
			$whrnotransaksi = "AND notransaksi='" . $param['notransaksi'] . "'";
		}

		$sql = "SELECT * FROM {$dbname}.approval WHERE 5=5 {$whrnotransaksi}";
		$res = fetchData($sql, "OBJECT");

		if (count($res) > 0) {
			$nos = 0;
			foreach ($res as $v) {
				$nos++;
				$arrdataapp[$v->level][$nos][$v->karyawanid] = ['id' => $v->karyawanid, 'status' => $v->status];
			}
		}

		# Detail
		$sql = "SELECT * FROM $dbname.keu_kasbankdt WHERE notransaksi='" . $param['notransaksi'] . "' AND keterangan3 NOT IN ('PAJAKPPNKAS', 'PAJAKPPHKAS')";
		$resdt = fetchData($sql);

		$tab .= "<style>
		th, td {
			word-wrap: break-word;
			overflow-wrap: break-word;
		}
	  
		.column-wrap {
			max-width: 40%;
		}
	  </style>";


		$tab .= "<table border='" . $borderht . "' style='width:100%;'>";
		$tab .= "<tr>";
		$tab .= "<td rowspan=4 align=center style='width:180px;'><img src='" . $path . "' style='width:180px;height:100px;' /></td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td align=center><b style='font-size: 16px;'>" . $kodeptht . "</b></td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td align=center><u><b style='" . $fontsizedt . "'>" . $subHeader . "</b></u></td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td align=center><span style='" . $fontsizedt . "' class='dotted-underline'>Nomor : " . $param['notransaksi'] . "</span></td>";
		$tab .= "</tr>";

		# Spasi
		$tab .= "<tr><td style='height:20px;'></td></tr>";

		$tab .= "</table>";

		$tab .= "<table width=100%>";
		# Tanggal
		$tab .= "<tr>";
		$tab .= "<td width=48.5%>";
		$tab .= "<table border='" . $borderht . "' style='width:100%'>";
		$tab .= "<tr>";
		$tab .= "<td width=10% style='" . $fontsizedt . "'>Tgl</td>";
		$tab .= "<td width=5% align=center style='" . $fontsizedt . "' align=right>:</td>";
		$tab .= "<td width=30% style='" . $fontsizedt . "'>" . $tanggalht . "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";
		$tab .= "</td>";

		# Slide kanan
		$tab .= "<td width=48.5%>";
		$tab .= "<table border='" . $borderht . "' style='width:100%'>";
		$tab .= "<tr>";
		$tab .= "<td width=10% align=left style='" . $fontsizedt . "'>Departemen</td>";
		$tab .= "<td width=5% align=center style='" . $fontsizedt . "'>:</td>";
		$tab .= "<td width=30% align=left style='" . $fontsizedt . "'>-</td>";
		$tab .= "</tr>";
		$tab .= "</table>";
		$tab .= "</td>";
		$tab .= "</tr>";

		# Keterangan
		$tab .= "<tr>";
		$tab .= "<td width=48.5%>";
		$tab .= "<table border='" . $borderht . "' style='width:100%'>";
		$tab .= "<tr>";
		$tab .= "<td width=10% style='" . $fontsizedt . ";vertical-align:top!important;'>Ket</td>";
		$tab .= "<td width=5% align=center style='" . $fontsizedt . ";vertical-align:top!important;' align=right>:</td>";
		$tab .= "<td width=30% style='" . $fontsizedt . "'>" . $keteranganht . "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";
		$tab .= "</td>";

		# Slide kanan
		$tab .= "<td width=48.5%>";
		$tab .= "<table border='" . $borderht . "' style='width:100%'>";
		$tab .= "<tr>";
		$tab .= "<td width=10% align=left style='" . $fontsizedt . "'>Unit Kerja</td>";
		$tab .= "<td width=5% align=center style='" . $fontsizedt . "'>:</td>";
		$tab .= "<td width=30% align=left style='" . $fontsizedt . "'>" . $kodeorganisasiht . "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";
		$tab .= "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";

		# Spasi
		$tab .= "<br/><br/>";

		# =========================================================== #
		# TABEL DETAIL
		# =========================================================== #
		$tab .= "<table border='" . $borderdt . "' style='width:100%' cellpadding=3 cellspacing=0>";
		$tab .= "<thead>";
		$tab .= "<tr>";
		$tab .= "<td align=center style='" . $fontsizedt . ";width:5%;' rowspan=2><b>No.</b></td>";
		$tab .= "<td align=center style='" . $fontsizedt . ";width:12%;' rowspan=2><b>No. Akun</b></td>";
		$tab .= "<td align=center style='" . $fontsizedt . "' rowspan=2><b>Nama Akun</b></td>";
		$tab .= "<td align=center style='" . $fontsizedt . "' rowspan=2><b>Keterangan</b></td>";
		$tab .= "<td align=center style='" . $fontsizedt . "' colspan=3><b>Nominal</b></td>";
		$tab .= "</tr>";

		$tab .= "<tr>";
		$tab .= "<td align=center style='" . $fontsizedt . ";width:7%;'><b>Curr</b></td>";
		$tab .= "<td align=center style='" . $fontsizedt . ";width:15%;'><b>Debet</b></td>";
		$tab .= "<td align=center style='" . $fontsizedt . ";width:15%;'><b>Kredit</b></td>";
		$tab .= "</tr>";
		$tab .= "</thead>";

		$tab .= "<tbody>";

		# Dari Header
		$i = 1;
		$data[$i] = array(
			'nomor' => @$i,
			'noakun' => @$noakunht,
			'namaakun' => @$namaakunmo[$noakunht],
			'keterangan2' => $keteranganht,
			'curr' => $simbolMataUang,
			'debet' => 0,
			'kredit' => 0,
		);

		if (@$tipetransaksiht == 'M') {
			$data[$i]['debet'] = $jumlahht;
			$totalDebet += $jumlahht;
		} else {
			$data[$i]['kredit'] = $jumlahht;
			$totalKredit += $jumlahht;
		}

		$i++;

		# Dari Detail
		foreach ($resdt as $row) {
			$strdt = "select * from " . $dbname . ".keu_tagihanht where noinvoice='" . $row['keterangan1'] . "' ";
			$resinv = fetchdata($strdt);

			$strdt = "select * from " . $dbname . ".vhc_5master_hist where kodevhc='" . $row['kodevhc'] . "' ";
			$resdt = fetchdata($strdt);
			@$nopol = $resdt[0]['nopol'];
			@$namavhc = $resdt[0]['detailvhc'];
			@$nmdepart = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $row['departemen'] . "'");
			$data[$i] = array(
				'nomor' => $i,
				'noakun' => $row['noakun'],
				'namaakun' => $namaakunmo[$row['noakun']] . "",
				'keterangan2' => $row['keterangan2'],
				'curr' => $simbolMataUang,
				'debet' => 0,
				'kredit' => 0
			);
			//	'hutangunit1'=>$optHutangUnit[$row['hutangunit1']]
			if (@$tipetransaksiht == 'M' and $row['jumlah'] > 0) {
				$data[$i]['kredit'] = $row['jumlah'];
				$totalKredit += $row['jumlah'];
			} else if (@$tipetransaksiht == 'K' and $row['jumlah'] < 0) {
				$data[$i]['kredit'] = $row['jumlah'] * -1;
				$totalKredit += $row['jumlah'] * -1;
			} else if (@$tipetransaksiht == 'M' and $row['jumlah'] < 0) {
				$data[$i]['debet'] = $row['jumlah'] * -1;
				$totalDebet += $row['jumlah'] * -1;
			} else {
				$data[$i]['debet'] = $row['jumlah'];
				$totalDebet += $row['jumlah'];
			}
			$i++;
		}

		// nyusun berdasarkan debet dulu, abis itu baru kredit. by dz
		if (!empty($data)) foreach ($data as $c => $key) {
			$sort_debet[] = $key['debet'];
			$sort_kredit[] = $key['kredit'];
		}

		// sort
		if (!empty($data)) array_multisort($sort_debet, SORT_DESC, $sort_kredit, SORT_ASC, $data);

		if (count($row) <= 0) {
			unset($data);
		}

		if (count($row) <= 0 && count($data) <= 0) {
			$tab .= "<tr>";
			$tab .= "<td colspan=7 align=center>Tidak Ada Data Detail</td>";
			$tab .= "</tr>";
		} else {

			// nyusun ulang nomor setelah disort by debet
			$nyomor = 0;
			foreach ($data as $key => $row) {
				$nyomor += 1;
				$tab .= "<tr>";
				foreach ($row as $key => $cont) {
					if ($key == 'nomor') {
						$tab .= "<td style='" . $fontsizedt . "' align=center name=col" . $nyomor . "[]>" . $nyomor . "</td>";
					} else {
						if ($key == 'debet' or $key == 'kredit') {
							$tab .= "<td style='" . $fontsizedt . "' align=right name=col" . $nyomor . "[]>" . hidezerodecimal($cont, 0) . "</td>";
						} else  if ($key == 'noakun') {
							$tab .= "<td style='" . $fontsizedt . "' name=col" . $nyomor . "[]>" . $cont . "<br>" . @$nmaruskas[$cont] . "</td>";
						} else  if ($key == 'namaakun') {
							$tab .= "<td style='" . $fontsizedt . "' name=col" . $nyomor . "[]>" . $cont . "<br>" . @$namaakunmo[$cont] . "</td>";
						} else if ($key == 'curr') {
							$tab .= "<td style='" . $fontsizedt . "' align=center name=col" . $nyomor . "[]><b>" . $cont . "</b></td>";
						} else  if ($key == 'hutangunit1') {
							if ($cont == 0) {
								$tab .= "<td style='" . $fontsizedt . "' align=center name=col" . $nyomor . "[]>Tidak</td>";
							} else if ($cont == 1) {
								$tab .= "<td style='" . $fontsizedt . "' align=center name=col" . $nyomor . "[]>Ya</td>";
							} else {
								$tab .= "<td style='" . $fontsizedt . "' name=col" . $nyomor . "[]></td>";
							}
						} else  if ($key == 'pemilikhutang1') {
							$tab .= "<td style='" . $fontsizedt . "' name=col" . $nyomor . "[]>" . $cont . "</td>";
						} else {
							$tab .= "<td style='" . $fontsizedt . "' name=col" . $nyomor . "[]>" . $cont . "</td>";
						}
					}
				}
				$tab .= "</tr>";
			}
		}

		# Tabel Row Total
		$tab .= "<tr>";
		$tab .= "<td style='" . $fontsizedt . "' align=center colspan=4><b>Total</b></td>";
		$tab .= "<td style='" . $fontsizedt . "' align=center><b>" . $simbolMataUang . "</b></td>";
		$tab .= "<td style='" . $fontsizedt . "' align=right><b>" . hidezerodecimal($totalDebet) . "</b></td>";
		$tab .= "<td style='" . $fontsizedt . "' align=right><b>" . hidezerodecimal($totalKredit) . "</b></td>";
		$tab .= "</tr>";

		$tab .= "</tbody>";
		$tab .= "</table>";
		# =========================================================== #
		# END - TABEL DETAIL
		# =========================================================== #

		# TERBILANG
		$tab .= "<p>Terbilang : <b><i>" . terbilang($totalDebet, 3) . " {$mataUang}</i></b></p>";
		# END TERBILANG

		# Spasi
		$tab .= "<br/><br/>";

		# =========================================================== #
		# =========================================================== #
		# TABEL TTD - REFACTORED TO DYNAMIC GRID
		# =========================================================== #
		$ttd_items = [];
		$ttd_items[] = [
			'label' => 'Dibuat,',
			'name' => $dibuatoleh,
			'signed' => true
		];

		if (count($arrdataapp) > 0) {
			$approvers = [];
			foreach ($arrdataapp as $level => $data) {
				foreach ($data as $nomor => $datax) {
					foreach ($datax as $karyid => $val) {
						$approvers[] = $val;
					}
				}
			}

			$countApp = count($approvers);
			foreach ($approvers as $idx => $app) {
				$label = ($idx == $countApp - 1) ? 'Disetujui,' : 'Diperiksa,';
				$ttd_items[] = [
					'label' => $label,
					'name' => $nmkaryawan[$app['id']],
					'signed' => ($app['status'] == '1')
				];
			}
		}

		$ttd_chunks = array_chunk($ttd_items, 4);
		foreach ($ttd_chunks as $chunk) {
			$chunkSize = count($chunk);
			$widthPerc = ($chunkSize / 4) * 100;
			// $tab .= "<table border='" . $borderdt . "' style='width:100%' cellpadding=3 cellspacing=0>";
			$tab .= "<table border='" . $borderdt . "' width=" . $widthPerc . "% cellpadding=3 cellspacing=0 ";
			$tab .= "<thead><tr>";
			foreach ($chunk as $item) {
				$tab .= "<th style='width:" . (100 / $chunkSize) . "%;" . $fontsizedt . "' align=center><b> " . $item['label'] . "</b></th>";
			}
			$tab .= "</tr></thead>";

			$tab .= "<tbody><tr>";
			foreach ($chunk as $item) {
				$tab .= "<td style='height:70px;text-align:center;font-size:14px'><i><font color=gray>" . ($item['signed'] && $item['name'] != '' ? 'ELECTRONICALLY SIGNED BY' : '') . "</font></i></td>";
			}
			$tab .= "</tr><tr>";
			foreach ($chunk as $item) {
				$tab .= "<td style='height:20px!important;text-align:center;" . $fontsizedt . "'>" . $item['name'] . "</td>";
			}
			$tab .= "</tr></tbody></table>";
		}
		# =========================================================== #
		# END - TTD
		# =========================================================== #


		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		if ($urlefil == '0') {
			$dompdf->stream("PrintKASBANK_" . $param['notransaksi'], array("Attachment" => 0));
		} else {
			file_put_contents($urlefil, $dompdf->output());
		}
		break;


	case 'pdfkasir':

		// $str = "select * from ".$dbname.".keu_kasbankht where nocek='".$nocekx."'";
		$str = "select * from " . $dbname . ".keu_kasbankht where notransaksi='" . $param['notransaksi'] . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$tanggalht = trim($bar['tanggal']);
			$novoucherht = trim($bar['novoucher']);
			$noakunht = trim($bar['noakun']);
			$rekeninght = trim($bar['rekening']);
			$cgttuht = trim($bar['cgttu']);
			$nocekht = trim($bar['nocek']);
			$jumlahht += trim($bar['jumlah']);
			$tipetransaksiht = trim($bar['tipetransaksi']);
			$kodeorght = trim($bar['kodeorg']);
			$serahx = $bar['bayarkepada'];
			$notransaksi = $bar['notransaksi'];
			$keterangan = $bar['keterangan'];

			$namabankht = trim($bar['namabank']);
			$rekeningextht = trim($bar['rekeningext']);
			$anrekeningextht = trim($bar['anrekeningext']);
			$mataUang = ucwords($bar['matauang']);
			$simbolMataUang = makeOption($dbname, "setup_matauang", "kode,simbol", "kode='{$bar['matauang']}'")[$bar['matauang']];
		}

		if ($anrekeningextht != '') {
			$anrekeningextht = $anrekeningextht;
			$serahkiri = $anrekeningextht;
			$anrekeningextht .= ' / ';
		}
		if ($namabankht != '') {
			$namabankht = $namabankht . ' / ';
		}
		if ($rekeningextht != '') {
			$rekeningextht = $rekeningextht;
		}

		$anbankx = makeOption($dbname, 'log_5rekbank', 'rekening,an', "rekening='" . $rekeningextht . "'");
		$kdbankx = makeOption($dbname, 'log_5rekbank', 'rekening,idbank', "rekening='" . $rekeningextht . "'");
		$nmbankx = makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank');
		$serah = $anrekeningextht . $namabankht . $rekeningextht;

		if ($rekeningextht != '') {
			$tanpabank = $nmbankx[$kdbankx[$rekeningextht]];
			$tanpabank = str_replace("Bank ", "", $tanpabank);
			$tanpabank = str_replace("BANK ", "", $tanpabank);
			$serah = $anbankx[$rekeningextht] . ' / ' . $tanpabank . ' / ' . $rekeningextht;
		}

		$serahkiri = $anbankx[$rekeningextht];

		#= jika tidak ada supplier maka ambil bayarkepada

		if ($serah == '') {
			$serah = $serahx;
		}

		if ($serahkiri == '') {
			$serahkiri = $serahx;
		}

		$str = "select * from " . $dbname . ".organisasi where kodeorganisasi='" . $kodeorght . "'";
		// echo $str;
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$wilayahkota = $bar['wilayahkota'];

		$str = "select * from " . $dbname . ".keu_5akunbank where noakun='" . $rekeninght . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$norekeningsetup = $bar['rekening'];
		$kodebanksetup = $bar['namabank'];
		$atasnamasetup = $bar['atasnama'];
		$cabang = $bar['cabang'];

		// $serah=$atasnamasetup."-".$norekeningsetup;


		#= data dt
		$str = "select * from " . $dbname . ".keu_kasbankdt where notransaksi='" . $notransaksi . "' and kodesupplier!=''";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$kodesupplier = $bar['kodesupplier'];

		#= supplier
		$str = "select * from " . $dbname . ".log_5supplier where supplierid='" . $kodesupplier . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$namasupplier = $bar['namasupplier'];

		# Make Option
		$indukPdfKasir = makeOption($dbname, "organisasi", "kodeorganisasi,induk");
		$namaorganisasiPdfKasir = makeOption($dbname, "organisasi", "kodeorganisasi,namaorganisasi");

		# Logo Palma
		$path = setheadreport($indukPdfKasir[$kodeorght], $kodeorght);

		# Enum
		$arrpembayaran = getEnum($dbname, 'keu_kasbankht', 'cgttu');
		foreach ($arrpembayaran as $key => $val):
			$pemb .= $val . "/";
		endforeach;

		# Keterangan
		$subHeader = '';
		if ($tipetransaksiht == 'K') {
			$subHeader = 'BUKTI PEMBAYARAN';
			$subFooter = 'Penerima';
			$dkdo = 'Dibayarkan kepada';
		} else {
			$subHeader = 'BUKTI PENERIMAAN';
			$subFooter = 'Yang Menyerahkan';
			$dkdo = 'Diterima Oleh';
		}


		# Style
		$tab = "
			<style>
				.f12 {
					font-size: 12px;
				}
				.f14 {
					font-size: 14px;
				}
				.f16 {
					font-size: 16px;
				}
				.f20 {
					font-size: 20px;
				}
			</style>
		";

		$tab .= "<table width=100% cellpadding=0 cellspacing=0>";
		$tab .= "<thead>";
		$tab .= "<tr>";
		$tab .= "<table width=100% cellpadding=0 cellspacing=0 border=1>";
		$tab .= "<tr>";
		$tab .= "<td style='width:20%' rowspan=3><img src='" . $path['logo'] . "' style='width:180px;height:100px;' /></td>";
		$tab .= "<td style='width:75%' align=center><b class='f16'>" . $namaorganisasiPdfKasir[$indukPdfKasir[$kodeorght]] . "</b></td>";
		$tab .= "</tr>";

		$tab .= "<tr>";
		$tab .= "<td style='width:75%' align=center><b class='f16'><u>" . $subHeader . "</u></b></td>";
		$tab .= "</tr>";

		$tab .= "<tr>";
		if ($novoucherht != '') {
			$tab .= "<td style='width:75%' align=center>Nomor : " . $novoucherht . "</td>";
		} else {
			$tab .= "<td style='width:75%' align=center>Nomor : ..............................</td>";
		}
		$tab .= "</tr>";
		$tab .= "</table>";
		$tab .= "</tr>";
		$tab .= "</thead>";
		$tab .= "</table>";

		$tab .= "<br/>";
		$tab .= "<br/>";

		$tab .= "<table width=100% cellpadding=1 cellspacing=0>";
		$tab .= "<thead>";
		$tab .= "<tr>";
		# Page 1
		$tab .= "<table width=100% cellpadding=3 cellspacing=0 border=0>";
		$tab .= "<tr>";
		$tab .= "<td style='width:48.5%' align=left>";
		# Page 2-1
		$tab .= "<table border=0 width=100%; cellpadding=3 cellspacing=1>";
		# Dibayarkan
		$tab .= "<tr>";
		$tab .= "<td width=40% align=left>" . $dkdo . "</td>";
		$tab .= "<td width=5% align=center>:</td>";
		$tab .= "<td width=45% align=left>" . $serahx . "</td>";
		$tab .= "</tr>";
		# Notransaksi
		$tab .= "<tr>";
		$tab .= "<td width=40% align=left>" . $_SESSION['lang']['notransaksi'] . "</td>";
		$tab .= "<td width=5% align=center>:</td>";
		$tab .= "<td width=45% align=left>" . $notransaksi . "</td>";
		$tab .= "</tr>";
		# Keterangan
		$tab .= "<tr>";
		$tab .= "<td width=40% align=left>" . $_SESSION['lang']['keterangan'] . "</td>";
		$tab .= "<td width=5% align=center>:</td>";
		$tab .= "<td width=45% align=left>" . $keterangan . "</td>";
		$tab .= "</tr>";
		# Tunai/Cek
		$tab .= "<tr>";
		$tab .= "<td width=40% align=left>Dibayar Dengan</td>";
		$tab .= "<td width=5% align=center>:</td>";
		$tab .= "<td width=45% align=left>" . $cgttuht . "</td>";
		$tab .= "</tr>";
		# Nominal
		$tab .= "<tr>";
		$tab .= "<td width=40% align=left>Nominal</td>";
		$tab .= "<td width=5% align=center>:</td>";
		$tab .= "<td width=45% align=left>" . $simbolMataUang . " " . hidezerodecimal($jumlahht, 2) . "</td>";
		$tab .= "</tr>";
		# Terbilang
		$tab .= "<tr>";
		$tab .= "<td width=40% style='vertical-align:top!important;' align=left>" . $_SESSION['lang']['terbilang'] . "</td>";
		$tab .= "<td width=5% style='vertical-align:top!important;' align=center>:</td>";
		$tab .= "<td width=45% align=left>" . terbilang($jumlahht, 3) . " " . $mataUang . "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";

		$tab .= "</td>";

		$tab .= "<td style='width:45.5%' align=left>";
		# Page 2-2
		$tab .= "<table border=0 width=100%; cellpadding=3 cellspacing=1>";
		# Tanggal
		$tab .= "<tr>";
		$tab .= "<td width=40% align=center>" . $_SESSION['lang']['tanggal'] . "</td>";
		$tab .= "<td width=5% align=center>:</td>";
		$tab .= "<td width=45% align=left>" . date("d M Y", strtotime($tanggalht)) . "</td>";
		$tab .= "</tr>";
		# Notransaksi
		$tab .= "<tr style='color:#fff;'>";
		$tab .= "<td width=40% align=left>" . $_SESSION['lang']['notransaksi'] . "</td>";
		$tab .= "<td width=5% align=center>:</td>";
		$tab .= "<td width=45% align=justify>" . $notransaksi . "</td>";
		$tab .= "</tr>";
		# Keterangan
		$tab .= "<tr style='color:#fff;'>";
		$tab .= "<td width=40% align=left>" . $_SESSION['lang']['keterangan'] . "</td>";
		$tab .= "<td width=5% align=center>:</td>";
		$tab .= "<td width=45% align=justify>" . $keterangan . "</td>";
		$tab .= "</tr>";
		# Tunai/Cek
		$tab .= "<tr style='color:#fff;'>";
		$tab .= "<td width=40% align=left>Tunai/Cek</td>";
		$tab .= "<td width=5% align=center>:</td>";
		$tab .= "<td width=45% align=justify>" . $cgttuht . "</td>";
		$tab .= "</tr>";
		# Nominal
		$tab .= "<tr style='color:#fff;'>";
		$tab .= "<td width=40% align=left>Nominal</td>";
		$tab .= "<td width=5% align=center>:</td>";
		$tab .= "<td width=45% align=justify>" . hidezerodecimal($jumlahht, 2) . "</td>";
		$tab .= "</tr>";
		# Terbilang
		$tab .= "<tr style='color:#fff;'>";
		$tab .= "<td width=40% style='vertical-align:top!important;' align=left>" . $_SESSION['lang']['terbilang'] . "</td>";
		$tab .= "<td width=5% style='vertical-align:top!important;' align=center>:</td>";
		$tab .= "<td width=45% align=left>" . terbilang($jumlahht, 3) . "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";

		$tab .= "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";
		$tab .= "</tr>";
		$tab .= "</thead>";
		$tab .= "</table>";

		$tab .= "<br/>";

		#==================#
		# TANDA TANGAN
		#==================#
		$tab .= "<table width=50% border=1 cellpadding=3 cellspacing=0 align=right>";
		$tab .= "<tbody>";
		$tab .= "<tr class=rowcontent>";
		$tab .= "<td width=40.5% style=height:120px;></td>";
		$tab .= "<td width=40.5% style=height:120px;></td>";
		$tab .= "</tr>";
		$tab .= "<tr class=rowcontent>";
		$tab .= "<td width=40.5% align=center><b>Kasir</b></td>";
		$tab .= "<td width=40.5% align=center><b>" . $subFooter . "</b></td>";
		$tab .= "</tr>";
		$tab .= "</tbody>";
		$tab .= "</table>";
		#==================#
		# END TANDA TANGAN
		#==================#


		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		if ($urlefil == '0') {
			$dompdf->stream("PrintKASBANK_" . $param['notransaksi'], array("Attachment" => 0));
		} else {
			file_put_contents($urlefil, $dompdf->output());
		}
		break;

	case 'pdfvoucher':
		$no = 0;
		$dtnotransaksi = '';
		$str = "select * from " . $dbname . ".keu_kasbankht where novoucher='" . $param['novoucher'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$no++;
			$listnotransaksi[$bar['notransaksi']] = $bar['notransaksi'];
			if ($no == 1) {
				$dtnotransaksi .= $bar['notransaksi'] . '<br>';
			} else {
				$dtnotransaksi .= '  ' . $bar['notransaksi'] . '<br>';
			}
			$tanggalht = $bar['tanggal'];
			$noakunht = $bar['noakun'];
			$rekeninght = $bar['rekening'];
			$cgttuht = $bar['cgttu'];
			$nocekht = $bar['nocek'];
			@$jumlahht += $bar['jumlah'];
			$tipetransaksiht = $bar['tipetransaksi'];
			$kodeorght = $bar['kodeorg'];
			$bayarkepada = $bar['bayarkepada'];
		}



		$str = "select * from " . $dbname . ".keu_5akunbank where noakun='" . $rekeninght . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$norekeningsetup = $bar['rekening'];
			$kodebanksetup = $bar['namabank'];
			$atasnamasetup = $bar['atasnama'];
		}

		#= data dt

		$str = "select * from " . $dbname . ".keu_kasbankdt where notransaksi='" . $param['notransaksi'] . "' and kodesupplier!=''";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$kodesupplier = $bar['kodesupplier'];
		}

		#= supplier
		$str = "select * from " . $dbname . ".log_5supplier where supplierid='" . $kodesupplier . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$namasupplier = $bar['namasupplier'];
		}



		#=Tanggal Terima & Tanggal Jatuh Tempo
		// $str="select * from ".$dbname.".keu_tagihanht a left join ".$dbname.".keu_kasbankdt b on a.noinvoice=b.keterangan1 
		// where b.notransaksi in ('".$param['notransaksi']."') ";
		$str = "select a.tanggal,a.jatuhtempo,a.noinvoice,a.reksupplier from " . $dbname . ".keu_tagihanht a left join " . $dbname . ".keu_kasbankdt b on a.noinvoice=b.keterangan1 
				where b.notransaksi in ('" . implode("','", $listnotransaksi) . "') ";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			@$tglterima[$bar['noinvoice']] = tanggalnormal($bar['tanggal']);
			@$tgljthtmpo[$bar['noinvoice']] = tanggalnormal($bar['jatuhtempo']);
			$reksupplier = $bar['reksupplier'];
		}


		$cellpadding = 2;

		$tab = "<style>
				@page {
					margin-top: 25px;
					margin-left: 30px;
					margin-right: 30px;
					margin-bottom: 50px;
				}
				body {
					font-family: Tahoma, Verdana, Segoe, sans-serif;
				}
				
				footer {
					position: fixed; 
					bottom: -20px; 
					left: 0px; 
					right: 0px;
					height: 50px; 
				}
				
			</style>";
		// $tab = '';


		if ($tipetransaksiht == 'M') {
			$namavoucher = 'BUKTI MASUK';
		} else {
			$namavoucher = 'BUKTI PENGELUARAN';
		}

		$cellpadding = 1;
		$fontsize = '10px';
		$tab .= "<table width=115% style='font-size:" . $fontsize . "' border=0 cellpadding=" . $cellpadding . ">";
		$tab .= "<tr>";
		$tab .= "<td colspan=4><b>" . $nmorg[$kodeorght] . "</b></td>";
		$tab .= "<td colspan=2 align=left><b>" . $namavoucher . "</b></td>";
		$tab .= "</tr>";

		$tab .= "<tr>";
		if ($tipetransaksiht == 'M') {
			$tab .= "<td style='width:30px'>Terima Dari</td>";
			$tab .= "<td style='width:200px' colspan=3>: " . $bayarkepada . "</td>";
		} else {
			$tab .= "<td style='width:30px'>" . $_SESSION['lang']['bayarke'] . "</td>";
			$tab .= "<td style='width:200px' colspan=3>: " . $bayarkepada . "</td>";
		}
		$tab .= "<td>" . $_SESSION['lang']['novoucher'] . "</td>";
		$tab .= "<td>: " . $param['novoucher'] . "</td>";
		$tab .= "</tr>";

		$tab .= "<tr>";
		if ($reksupplier != '') {
			$str = "select * from " . $dbname . ".log_5rekbank where rekening='" . $reksupplier . "'";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$an = $bar['an'];
				$namabank = $bar['bank'];
			}
			$tab .= "<td style='width:30px'>" . $_SESSION['lang']['rekening'] . "</td>";
			$tab .= "<td style='width:200px' colspan=3>: " . $namabank . " " . $reksupplier . "</td>";
		} else {
			$tab .= "<td colspan=4></td>";
		}
		$tab .= "<td style='width:30px'>" . $_SESSION['lang']['tanggal'] . "</td>";
		$tab .= "<td style='width:30px'>: " . tanggalnormal($tanggalht) . "</td>";
		$tab .= "</tr>";

		$tab .= "<tr>";
		if ($reksupplier != '') {
			$tab .= "<td style='width:30px'>" . $_SESSION['lang']['atasnama'] . "</td>";
			$tab .= "<td style='width:200px' colspan=3>: " . $an . "</td>";
		} else {
			$tab .= "<td colspan=4></td>";
		}
		$tab .= "<td style='width:30px;valign=top' >" . $_SESSION['lang']['noreferensi'] . "</td>";
		$tab .= "<td style='width:30px'>: " . $dtnotransaksi . "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";

		$tab .= "<br>";

		$cellpadding = 2;
		$cellspacing = 0;

		// if($tipetransaksiht=='M'){
		// 	$width='width:450px';
		// }else{
		// 	$width='';
		// }


		// exit("Error:$width");

		$tab .= "<table width='100%' style='font-size:" . $fontsize . ";' border=0 cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . ">";
		$tab .= "<thead>";
		$tab .= "<tr>";
		$tab .= "<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>" . $_SESSION['lang']['nourut'] . "</td>";
		$tab .= "<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>" . $_SESSION['lang']['noakun'] . "</td>";
		$tab .= "<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>" . $_SESSION['lang']['nodok'] . "</td>";
		$tab .= "<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>" . $_SESSION['lang']['tglterima'] . "</td>";
		$tab .= "<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>" . $_SESSION['lang']['tanggaljatuhtempo'] . "</td>";
		$tab .= "<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>" . $_SESSION['lang']['keterangan'] . "</td>";
		$tab .= "<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>" . $_SESSION['lang']['control'] . "</td>";
		$tab .= "<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>" . $_SESSION['lang']['tipe'] . "</td>";
		$tab .= "<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>" . $_SESSION['lang']['jumlah'] . "</td>";
		$tab .= "</tr>";
		$tab .= "</thead>";

		$tab .= "<tr>";
		$tab .= "<td colspan=9>&nbsp;</td>";
		$tab .= "</tr>";
		$optketerangan =  makeOption($dbname, 'keu_5keterangan', 'id_ket,keterangan');
		#= query pakai kasbankdt


		$tab .= "<tbody>";
		$str = "select * from " . $dbname . ".keu_kasbankdt where  notransaksi in ('" . implode("','", $listnotransaksi) . "') AND keterangan3 NOT IN ('PAJAKPPNKAS', 'PAJAKPPHKAS')";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($kodept[$bar['pemilikhutang1']] == $kodept[$kodeorght]) $jenisinduk = 'intra';
			else $jenisinduk = 'inter';

			$whereNocaco = "jenis='" . $jenisinduk . "' and kodeorg='" . $bar['pemilikhutang1'] . "'";
			$query = selectQuery($dbname, 'keu_5caco', 'akunpiutang', $whereNocaco);
			$noKon = fetchData($query);
			$noakuncaco = $noKon[0]['akunpiutang'];
			// exit("Error:".$bar['pemilikhutang1']._.$kodeorght);

			if ($bar['pemilikhutang1'] != '') {
				if ($bar['pemilikhutang1'] != $kodeorght) {
					$bar['noakun'] = $noakuncaco;
				}
			}


			$tipe = '';
			if ($bar['keterangan1'] != '') {
				if ($tipetransaksiht == 'K') {
					$tipe = 'AP';
				} else {
					$tipe = 'AR';
				}
			}

			@$no += 1;
			$tab .= "<tr>";
			$tab .= "<td style=font-size:0.9em align=center valign=top>" . $no . "</td>";
			$tab .= "<td style=font-size:0.9em align=center valign=top>" . $bar['noakun'] . "</td>";
			$tab .= "<td style=font-size:0.9em align=center valign=top>" . $bar['keterangan1'] . "</td>";
			$tab .= "<td style=font-size:0.9em align=center valign=top>" . $tglterima[$bar['keterangan1']] . "</td>";
			$tab .= "<td style=font-size:0.9em align=center valign=top>" . $tgljthtmpo[$bar['keterangan1']] . "</td>";
			$tab .= "<td style=font-size:0.9em align=left valign=top>" . $nmakun[$bar['noakun']] . "<br>" . $bar['keterangan2'] . "</td>";
			$tab .= "<td style=font-size:0.9em align=center valign=top>" . $bar['nodok'] . "</td>";
			$tab .= "<td style=font-size:0.9em align=center valign=top>" . $tipe . "</td>";
			$tab .= "<td style=font-size:0.9em align=right valign=top>" . number_format($bar['jumlah'], 2) . "</td>";
			$tab .= "</tr>";
			@$totaldt += $bar['jumlah'];
		}
		$tab .= "</tbody>";
		// $tab.="</table>";
		// $tab.="<table style='font-size:".$fontsize."' width=100% border=1 cellpadding=".$cellpadding." cellspacing=".$cellspacing.">";	

		$tab .= "<tr>";
		$tab .= "<td colspan=9 height=50px>&nbsp;</td>";
		$tab .= "</tr>";

		$tab .= "<tr width=60%>";
		$tab .= "<td colspan=8 align=right style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;'>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['jumlah'] . ":</td>";
		$tab .= "<td  align=right style='width:10px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;'>" . number_format($totaldt, 2) . "</td>";
		$tab .= "</tr>";

		$tab .= "<tr>";
		$tab .= "<td align=left colspan=3 valign=top>" . $noakunht . "<br>" . $nmakun[$noakunht] . "</td>";
		$tab .= "<td align=left  valign=top>" . $nmbank[$kodebanksetup] . "&nbsp;&nbsp;" . $atasnamasetup . "&nbsp;&nbsp;" . $norekeningsetup . "</td>";
		$isi = "";
		if ($nocekht != '') {
			$isi = "" . $_SESSION['lang']['nomor'] . " : " . $nocekht . "";
		}

		$tab .= "<td align=left valign=top>" . $_SESSION['lang']['tipe'] . " : " . $cgttuht . "<br>" . $isi . "</td>";
		$tab .= "<td align=right colspan=3 valign=top>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['jumlah'] . "</td>";
		$tab .= "<td  align=right valign=top>" . number_format($jumlahht, 2) . "</td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td colspan=5 align=right valign=right><td colspan=4 >Terbilang : (" . ucwords(terbilang($jumlahht, 2)) . ")</td>";
		$tab .= "</tr>";

		$tab .= "<tr>";
		$tab .= "<td colspan=9>&nbsp;</td>";
		$tab .= "</tr>";
		$tab .= "</table>";

		$tab .= "<table style='width:100%;font-size:" . $fontsize . "' border=0 cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . ">";
		$tab .= "<tr>";
		$tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . "</td>";
		$tab .= "<td style='width:50px'>&nbsp;</td>";
		$tab .= "<td align=center>" . $_SESSION['lang']['diperiksaoleh'] . "</td>";
		$tab .= "<td style='width:50px'>&nbsp;</td>";
		$tab .= "<td align=center>" . $_SESSION['lang']['dibuatoleh'] . "</td>";
		$tab .= "<td style='width:50px'>&nbsp;</td>";
		$tab .= "<td align=center>" . $_SESSION['lang']['penerima'] . "</td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td style='height:50px;border-bottom:0.5px solid #000000;'>&nbsp;</td>";
		$tab .= "<td align=center></td>";
		$tab .= "<td style='border-bottom:0.5px solid #000000;'>&nbsp;</td>";
		$tab .= "<td align=center></td>";
		$tab .= "<td style='border-bottom:0.5px solid #000000;'>&nbsp;</td>";
		$tab .= "<td align=center></td>";
		$tab .= "<td style='border-bottom:0.5px solid #000000;'>&nbsp;</td>";
		$tab .= "</tr>";
		$tab .= "</table>";


		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		// $a=array(0,0,2150,1390);
		// $dompdf->setPaper('A5', 'landscape');
		// $dompdf->setPaper($a, 'landscape');
		$dompdf->render();
		if ($urlefil == '0') {
			$dompdf->stream("PrintKASBANK_" . $param['notransaksi'], array("Attachment" => 0));
		} else {
			file_put_contents($urlefil, $dompdf->output());
		}
		break;

	/*
	case'pdfvoucherlama':
	
		$str = "select * from ".$dbname.".keu_kasbankht where novoucher='".$param['novoucher']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			@$no+=1;
			$tanggalht=$bar['tanggal'];
			$novoucherht=$bar['novoucher'];
			$noakunht=$bar['noakun'];
			$rekeninght=$bar['rekening'];
			$cgttuht=$bar['cgttu'];
			$nocekht=$bar['nocek'];
			@$jumlahht+=$bar['jumlah'];
			$tipetransaksiht=$bar['tipetransaksi'];
			$kodeorght=$bar['kodeorg'];
			$bayarkepadaht=$bar['bayarkepada'];
			$kodematauanght=$bar['matauang'];
			$pembayaran=$bar['pembayaran'];
			$listnotransaksi.=$bar['notransaksi']." ";
			$arrnotransaksi[$bar['notransaksi']]=$bar['notransaksi'];
		}
		
		
		if($pembayaran=='0'){
			exit("Nomor voucher belum ada, silahkan lakukan transaksi kasir");
		}
		
		
		if($no=='' || $no==0){
			exit("Warning:Nomor transaksi ini belum ada nomor vouchernya");
		}
		
		$str = "select * from ".$dbname.".setup_matauang where kode='".$kodematauanght."'";
		// echo $str;exit();
		$res=fetchdata($str);
		foreach($res as $bar){
			$matauanght=$bar['matauang'];
		}
		
		$str = "select * from ".$dbname.".keu_5akunbank_vw where noakun='".$rekeninght."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$norekeningsetup=$bar['rekening'];
			$kodebanksetup=$bar['namabank'];
			$atasnamasetup=$bar['atasnama'];
		}

		
		#= cari max row detail
		// $str = "select count(*) as maxrow from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."'";		
		#= pengeluaran di grup by
		$maxrowdt=0;
		if($tipetransaksiht=='M'){
			$str = "select * from ".$dbname.".keu_kasbankdt where notransaksi in ('".implode("','",$arrnotransaksi)."')";					
		} else {
			$str = "select sum(jumlah) as jumlah,keterangan2,kodesupplier,noaruskas,noakun from ".$dbname.".keu_kasbankdt where
					notransaksi in ('".implode("','",$arrnotransaksi)."') group by noaruskas,noakun,kodesupplier";	
		}
		$res=fetchdata($str);
		foreach($res as $bar){
			$maxrowdt++;
		}

		$cellpadding=2;	
		$varmaxrow=12;
		$tab="<style>
			@page {
				margin-top: 25px;
				margin-left: 30px;
				margin-right: 50px;
				margin-bottom: 50px;
			}
			body {
				font-family: Tahoma, Verdana, Segoe, sans-serif;
			}
			footer {
				position: fixed; 
				bottom: -20px; 
				left: 0px; 
				right: 0px;
				height: 50px; 
			}
		</style>";
			
		$expltran=explode('/',$param['notransaksi']);
		$jenistransaksiht=$expltran[2];
		if($tipetransaksiht=='M'){
			if($jenistransaksiht=='BM'){
				$namavoucher='BUKTI PENERIMAAN BANK';
			}else{
				$namavoucher='BUKTI PENERIMAAN KAS';
			}

		}else{
			if($jenistransaksiht=='BK'){
				$namavoucher='BUKTI PENGELUARAN BANK';
			}else{
				$namavoucher='BUKTI PENGELUARAN KAS';
			}
		}			
		
		$cellpadding=0;
		$cellspacing=1;
		$fontsize='10px';
		$tab.="<table width=100% style='font-size:".$fontsize."' border=0 cellpadding=".$cellpadding." cellspacing=".$cellspacing.">";	
			$tab.="<tr>";
				$tab.="<td  style='width:475px'><b>".$nmorg[$kodeorght]."</b></td>"; 
					$tab.="<td><b>".$_SESSION['lang']['novoucher']."</b></td>"; 
				$tab.="<td><b>: ".$novoucherht."</b></td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
			
				$tab.="<td></td>"; 
				
				$tab.="<td><b>".$_SESSION['lang']['tanggal']."</b></td>"; 
				$tab.="<td><b>: ".tglnmbln($tanggalht,'','')."</b></td>"; 
			
			$tab.="</tr>";

			$tab.="<tr>";
				$tab.="<td colspan=3>&nbsp;</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td colspan=3 style='font-size:15px;' align=center><b>".$namavoucher."</b></td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		$tab.="<br>";
		
		
		#========================= penerimaan
		if($tipetransaksiht=='M'){
			$tab.="<table width=100% style='font-size:".$fontsize."' border=0 cellpadding=".$cellpadding." cellspacing=".$cellspacing.">";	
				$tab.="<tr>";
					$tab.="<td>Diterima dari</td>"; 
					$tab.="<td align=center>:</td>"; 
					$tab.="<td style='width:575px'>".$bayarkepadaht."</td>"; 
				$tab.="</tr>";
				$tab.="<tr>";
					$tab.="<td>".$_SESSION['lang']['jumlah']."</td>"; 
					$tab.="<td align=center>:</td>"; 
					$tab.="<td>".number_format($jumlahht,2)."</td>"; 
				$tab.="</tr>";
				$tab.="<tr>";
					$tab.="<td valign=top>".$_SESSION['lang']['terbilang']."</td>"; 
					$tab.="<td align=center valign=top>:</td>"; 
					$tab.="<td valign=top><i>".terbilang($jumlahht,3)." Rupiah</i></td>"; 
				$tab.="</tr>";
			$tab.="</table>";
			
			$tab.="<br>";
			$cellspacing=0;
			$cellpadding=2;
			$tab.="<table width=100% style='font-size:".$fontsize."' border=0 cellpadding=".$cellpadding." cellspacing=".$cellspacing.">";	
				$tab.="<tr>";
					$tab.="<td align=center colspan=3 style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'><b>".$_SESSION['lang']['noakun']."</b></td>"; 
					$tab.="<td align=center colspan=8 style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'><b>".$_SESSION['lang']['keterangan']." ".$_SESSION['lang']['transaksi']."</b></td>"; 
				$tab.="</tr>";
				
				$nodt=0;
				$str = "select * from ".$dbname.".keu_kasbankdt where notransaksi in ('".implode("','",$arrnotransaksi)."')";				
				$res=fetchdata($str);
				foreach($res as $bar){
					$nodt++;
					if($nodt==$maxrowdt and $maxrowdt>=$varmaxrow){
						$styledt="style='border-top:0px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'";
					}else{
						$styledt="style='border-top:0px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0px solid #000000;'";			
					}
					$tab.="<tr>";
						$tab.="<td ".$styledt."  colspan=3 align=center>".$bar['noakun']."</td>"; 
						$tab.="<td ".$styledt." colspan=8>".$bar['keterangan2']."</td>"; 
					$tab.="</tr>";
				}
				if($maxrowdt<$varmaxrow){
					$height=(($varmaxrow-$maxrowdt)*10);
					$tab.="<tr>";
						$tab.="<td style='height:".$height."px;border-top:0px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'  colspan=3 ></td>"; 
						$tab.="<td style='border-top:0px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;' colspan=8></td>"; 
					$tab.="</tr>";
				}
				
				$tab.="<tr>";
					$tab.="<td>".$_SESSION['lang']['bank']."</td>"; 
					$tab.="<td align=center style='width:20px'>:</td>"; 
					$tab.="<td>".$kodebanksetup."</td>"; 
					$tab.="<td style='width:100px'>&nbsp;</td>"; 
					$tab.="<td>A/C</td>"; 
					$tab.="<td align=center style='width:20px'>:</td>"; 
					$tab.="<td>".$norekeningsetup."</td>"; 
					$tab.="<td style='width:100px'>&nbsp;</td>"; 
					$tab.="<td>".$_SESSION['lang']['jumlah']."</td>"; 
					$tab.="<td align=center style='width:20px'>:</td>"; 
					$tab.="<td align=right>".number_format($jumlahht,2)."</td>"; 
				$tab.="</tr>";
			$tab.="</table>";
			$tab.="<br>";
		}
		
		#========================= pengeluaran
			
		$cellpadding=1;
		$cellspacing=0;
		
		
		if($tipetransaksiht=='K'){
		$tab.="<table width=100% style='font-size:".$fontsize."' border=0 cellpadding=".$cellpadding." cellspacing=".$cellspacing.">";	
			
				$tab.="<tr>";
					$tab.="<td style='width:120px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0px solid #000000;border-bottom:0px solid #000000;'></td>"; 
					$tab.="<td align=center style='width:20px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;border-bottom:0px solid #000000;'></td>"; 
					$tab.="<td align=center'  style='width:200px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0px solid #000000;'></td>"; 
					$tab.="<td style='width:120px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0px solid #000000;border-bottom:0px solid #000000;'>".$_SESSION['lang']['norekeningbank']."</td>"; 
					$tab.="<td align=center style='width:20px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;border-bottom:0px solid #000000;'>:</td>"; 
					$tab.="<td style='width:200px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0px solid #000000;'>".$kodebanksetup." : ".$norekeningsetup."</td>"; // style='width:105px'
				$tab.="</tr>";
				
				$tab.="<tr>";
					$tab.="<td style='width:120px;border-top:0px solid #000000;border-left:0.5px solid #000000;border-right:0px solid #000000;border-bottom:0px solid #000000;'>".$_SESSION['lang']['dibayar']." ".$_SESSION['lang']['kepada']."</td>"; 
					$tab.="<td align=center>:</td>"; 
					$tab.="<td style='width:200px;border-top:0px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0px solid #000000;'>".$bayarkepadaht."</td>"; 
					$tab.="<td style='border-top:0px solid #000000;border-left:0.5px solid #000000;border-right:0px solid #000000;border-bottom:0px solid #000000;'>No. Cheque/Giro/Transfer</td>"; 
					$tab.="<td align=center style='width:20px'>:</td>"; 
					$tab.="<td style='border-top:0px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0px solid #000000;'>".$nocekht."</td>"; // style='width:105px'
				$tab.="</tr>";
				$tab.="<tr>";
					$tab.="<td style='width:120px;border-top:0px solid #000000;border-left:0.5px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'>Dibebankan ke pusat biaya</td>"; 
					$tab.="<td align=center style='width:20px;border-top:0px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'>:</td>"; 
					$tab.="<td align=center'  style='width:200px;border-top:0px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:120px;border-top:0px solid #000000;border-left:0.5px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'>".$_SESSION['lang']['tanggal']."</td>"; 
					$tab.="<td align=center style='width:20px;border-top:0px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'>:</td>"; 
					$tab.="<td style='width:200px;border-top:0px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".tglnmbln($tanggalht,'','')."</td>"; // style='width:105px'
				$tab.="</tr>";
			$tab.="</table>";
			
			$tab.="<table width=100% style='font-size:".$fontsize."' border=0 cellpadding=2 cellspacing=0>";	
				$tab.="<tr><td></td></tr>";
			$tab.="</table>";
			
			$cellspacing=0;
			$cellpadding=2;
			$tab.="<table width=100% style='font-size:".$fontsize."' border=0 cellpadding=".$cellpadding." cellspacing=".$cellspacing.">";	
				$tab.="<tr>";
					$tab.="<td align=center colspan=2 style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'><b>".$_SESSION['lang']['uraian']."</b></td>"; 
					$tab.="<td align=center style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'><b>".$_SESSION['lang']['jumlah']." (".$matauanght.")</b></td>"; 
				$tab.="</tr>";
				
				
				$nodt=0;
				$str = "select sum(jumlah) as jumlah,keterangan3,kodesupplier,noaruskas,noakun,keterangan2 from ".$dbname.".keu_kasbankdt where
					notransaksi in ('".implode("','",$arrnotransaksi)."') group by noaruskas,noakun,kodesupplier order by nourut asc";
// echo $str;exit("Error:");					
				$res=fetchdata($str);
				foreach($res as $bar){

					if($bar['kodesupplier']!=''){
						$keterangandt=" ".$nmaruskas[$bar['noaruskas']].", ".$_SESSION['lang']['supplier']." : ".$nmsupplier[$bar['kodesupplier']]." ";
					}else{
						$keterangandt=" ".$bar['keterangan2']." ";
					}
					
					$nodt++;
					if($nodt==$maxrowdt and $maxrowdt>=$varmaxrow){
						$styledt1="style='width:500px;border-top:0px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'";
						$styledt2="style='width:125px;border-top:0px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'";
					}else{
						$styledt1="style='width:500px;border-top:0px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0px solid #000000;'";			
						$styledt2="style='width:125px;border-top:0px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0px solid #000000;'";			
					}
					$tab.="<tr>";
						$tab.="<td ".$styledt1." colspan=2 >".$keterangandt."</td>"; 
						$tab.="<td ".$styledt2." align=right>".number_format($bar['jumlah'],2)."</td>"; 
					$tab.="</tr>";
				}
				if($maxrowdt<$varmaxrow){
					$height=(($varmaxrow-$maxrowdt)*10);
					$tab.="<tr>";
						$tab.="<td style='height:".$height."px;border-top:0px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'  colspan=2 ></td>"; 
						$tab.="<td style='border-top:0px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'></td>"; 
					$tab.="</tr>";
				}
				$tab.="<tr>";
					$tab.="<td style='height:15px;'>".$_SESSION['lang']['terbilang']." : ".terbilang($jumlahht,3)."</td>"; 
					$tab.="<td align=right style='height:15px;'>".$_SESSION['lang']['jumlah']." :</td>"; 
					$tab.="<td align=right style='height:15px;border-top:0px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($jumlahht,2)."</td>"; 
				$tab.="</tr>";
				
				$tab.="<tr>";
					$tab.="<td colspan=3 style='height:20px;' valign=bottom>Lampirkan bukti-bukti pembayarannya :</td>"; 
				$tab.="</tr>";
			$tab.="</table>";
			
		}
		$tab.="<table style='width:100%;font-size:".$fontsize."' border=1 cellpadding=".$cellpadding." cellspacing=".$cellspacing.">";	
			$tab.="<tr>";
				$tab.="<td align=center>".$_SESSION['lang']['dibuatoleh']."</td>";
				$tab.="<td align=center>".$_SESSION['lang']['diperiksaoleh']."</td>"; 
				$tab.="<td align=center>".$_SESSION['lang']['persetujuan']."</td>"; 
				$tab.="<td align=center>".$_SESSION['lang']['dibukukan']."</td>"; 
				$tab.="<td align=center>".$_SESSION['lang']['penerima']."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td align=center style='height:50px'></td>"; 
				$tab.="<td align=center></td>"; 				
				$tab.="<td align=center></td>"; 
				$tab.="<td align=center></td>"; 
				$tab.="<td align=center></td>"; 
			$tab.="</tr>";
		$tab.="</table>";	
		
		$tab.="<table style='width:100%;font-size:".$fontsize."' border=0 cellpadding=".$cellpadding." cellspacing=".$cellspacing.">";	
			$tab.="<tr>";
				$tab.="<td align=left>".$_SESSION['lang']['notransaksi']." : ".$listnotransaksi."</td>";
			$tab.="</tr>";
		$tab.="</table>";	
		
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		// $dompdf->setPaper('A4', 'portrait');
		$dompdf->setPaper('A5', 'landscape');
		$dompdf->render();
		$dompdf->stream($table,array("Attachment"=>0));	
		
	
	break;
	*/

	case 'pdfnew':

		/*
		$str = "select * from ".$dbname.".keu_kasbankht where notransaksi='".$param['notransaksi']."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$novoucherht=$bar['novoucher'];
			$noreferensiht=$bar['noreferensi'];
		*/

		$str = "select * from " . $dbname . ".keu_kasbankht where notransaksi='" . $param['notransaksi'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$novoucherht = $bar['novoucher'];
			$listnotransaksi[$bar['notransaksi']] = $bar['notransaksi'];
			$tanggalht = $bar['tanggal'];
			$noakunht = $bar['noakun'];
			$rekeninght = $bar['rekening'];
			$cgttuht = $bar['cgttu'];
			$nocekht = $bar['nocek'];
			@$jumlahht += $bar['jumlah'];
			$tipetransaksiht = $bar['tipetransaksi'];
			$kodeorght = $bar['kodeorg'];
			$bayarkepada = $bar['bayarkepada'];
		}

		$str = "select * from " . $dbname . ".keu_5akunbank where noakun='" . $rekeninght . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$norekeningsetup = $bar['rekening'];
			$kodebanksetup = $bar['namabank'];
			$atasnamasetup = $bar['atasnama'];
		}

		#= data dt

		$str = "select * from " . $dbname . ".keu_kasbankdt where notransaksi='" . $param['notransaksi'] . "' and kodesupplier!=''";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$kodesupplier = $bar['kodesupplier'];
		}

		#= supplier
		$str = "select * from " . $dbname . ".log_5supplier where supplierid='" . $kodesupplier . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$namasupplier = $bar['namasupplier'];
		}

		#=Tanggal Terima & Tanggal Jatuh Tempo
		$str = "select a.tanggal,a.jatuhtempo,a.noinvoice,a.reksupplier,a.kodesupplier, a.noinvoicesupplier from " . $dbname . ".keu_tagihanht a left join " . $dbname . ".keu_kasbankdt b on a.noinvoice=b.keterangan1  where b.notransaksi in ('" . $param['notransaksi'] . "') ";
		// echo $str;exit();
		$res = fetchdata($str);
		foreach ($res as $bar) {
			@$tglterima[$bar['noinvoice']] = tanggalnormal($bar['tanggal']);
			@$tgljthtmpo[$bar['noinvoice']] = tanggalnormal($bar['jatuhtempo']);
			@$noinvoicesupplier[$bar['noinvoice']] = $bar['noinvoicesupplier'];
			$reksupplier = $bar['reksupplier'];
		}
		// echo"<pre>";
		// print_r($tglterima);
		// echo"</pre>";

		// exit("Error:$width");
		$cellpadding = 2;

		$tab = "<style>
				@page {
					margin-top: 25px;
					margin-left: 30px;
					margin-right: 30px;
					margin-bottom: 50px;
				}
				body {
					font-family: Tahoma, Verdana, Segoe, sans-serif;
				}
				
				footer {
					position: fixed; 
					bottom: -20px; 
					left: 0px; 
					right: 0px;
					height: 50px; 
				}
				
			</style>";
		// $tab = '';


		if ($tipetransaksiht == 'M') {
			$namavoucher = 'BUKTI MASUK';
		} else {
			$namavoucher = 'BUKTI PENGELUARAN';
		}

		$cellpadding = 1;
		$fontsize = '10px';
		$tab .= "<table width=115% style='font-size:" . $fontsize . "' border=0 cellpadding=" . $cellpadding . ">";
		$tab .= "<tr>";
		$tab .= "<td colspan=4><b>" . $nmorg[$kodeorght] . "</b></td>";
		$tab .= "<td colspan=2 align=left><b>" . $namavoucher . "</b></td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		// $tab.="<td colspan=4></td>";  

		if ($tipetransaksiht == 'M') {
			$tab .= "<td style='width:30px'>Terima Dari</td>";
			$tab .= "<td style='width:200px' colspan=3>: " . $bayarkepada . "</td>";
		} else {
			$tab .= "<td style='width:30px'>" . $_SESSION['lang']['bayarke'] . "</td>";
			$tab .= "<td style='width:200px' colspan=3>: " . $bayarkepada . "</td>";
		}
		$tab .= "<td>" . $_SESSION['lang']['novoucher'] . "</td>";
		$tab .= "<td>: " . $novoucherht . "</td>";
		$tab .= "</tr>";

		$tab .= "<tr>";
		if ($reksupplier != '') {
			$str = "select * from " . $dbname . ".log_5rekbank where rekening='" . $reksupplier . "'";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$an = $bar['an'];
				$namabank = $bar['bank'];
			}
			$tab .= "<td style='width:30px'>" . $_SESSION['lang']['rekening'] . "</td>";
			$tab .= "<td style='width:200px' colspan=3>: " . $namabank . " " . $reksupplier . "</td>";
		} else {
			$tab .= "<td colspan=4></td>";
		}
		$tab .= "<td style='width:30px'>" . $_SESSION['lang']['tanggal'] . "</td>";
		$tab .= "<td style='width:30px'>: " . tanggalnormal($tanggalht) . "</td>";
		$tab .= "</tr>";

		$tab .= "<tr>";
		if ($reksupplier != '') {
			$tab .= "<td style='width:30px'>" . $_SESSION['lang']['atasnama'] . "</td>";
			$tab .= "<td style='width:200px' colspan=3>: " . $an . "</td>";
		} else {
			$tab .= "<td colspan=4></td>";
		}
		$tab .= "<td style='width:30px'>" . $_SESSION['lang']['noreferensi'] . "</td>";
		$tab .= "<td style='width:30px'>: " . $param['notransaksi'] . "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";

		$tab .= "<br>";

		$cellpadding = 2;
		$cellspacing = 0;

		// if($tipetransaksiht=='M'){
		// 	$width='width:450px';
		// }else{
		// 	$width='';
		// }



		$tab .= "<table width='100%' style='font-size:" . $fontsize . ";' border=0 cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . ">";
		$tab .= "<thead>";
		$tab .= "<tr>";
		$tab .= "<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>" . $_SESSION['lang']['nourut'] . "</td>";
		$tab .= "<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>" . $_SESSION['lang']['noakun'] . "</td>";
		$tab .= "<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>" . $_SESSION['lang']['nodok'] . "</td>";
		$tab .= "<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>" . $_SESSION['lang']['tglterima'] . "</td>";
		$tab .= "<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>" . $_SESSION['lang']['tanggaljatuhtempo'] . "</td>";
		$tab .= "<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>" . $_SESSION['lang']['keterangan'] . "</td>";
		$tab .= "<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>" . $_SESSION['lang']['control'] . "</td>";
		$tab .= "<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>" . $_SESSION['lang']['tipe'] . "</td>";
		$tab .= "<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>" . $_SESSION['lang']['jumlah'] . "</td>";
		$tab .= "</tr>";
		$tab .= "</thead>";

		$tab .= "<tr>";
		$tab .= "<td colspan=9>&nbsp;</td>";
		$tab .= "</tr>";
		$optketerangan =  makeOption($dbname, 'keu_5keterangan', 'id_ket,keterangan');
		#= query pakai kasbankdt
		// echo $str;exit();

		$tab .= "<tbody>";
		$str = "select * from " . $dbname . ".keu_kasbankdt where notransaksi='" . $param['notransaksi'] . "' AND keterangan3 NOT IN ('PAJAKPPNKAS', 'PAJAKPPHKAS')";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($kodept[$bar['pemilikhutang1']] == $kodept[$kodeorght]) $jenisinduk = 'intra';
			else $jenisinduk = 'inter';

			$whereNocaco = "jenis='" . $jenisinduk . "' and kodeorg='" . $bar['pemilikhutang1'] . "'";
			$query = selectQuery($dbname, 'keu_5caco', 'akunpiutang', $whereNocaco);
			$noKon = fetchData($query);
			$noakuncaco = $noKon[0]['akunpiutang'];
			// exit("Error:".$bar['pemilikhutang1']._.$kodeorght);

			if ($bar['pemilikhutang1'] != '') {
				if ($bar['pemilikhutang1'] != $kodeorght) {
					$bar['noakun'] = $noakuncaco;
				}
			}


			$tipe = '';
			if ($bar['keterangan1'] != '') {
				if ($tipetransaksiht == 'K') {
					$tipe = 'AP';
				} else {
					$tipe = 'AR';
				}
			}

			@$no += 1;
			$tab .= "<tr>";
			$tab .= "<td style=font-size:0.9em align=center valign=top>" . $no . "</td>";
			$tab .= "<td style=font-size:0.9em align=center valign=top>" . $bar['noakun'] . "</td>";
			$tab .= "<td style=font-size:0.9em align=center valign=top>" . $bar['keterangan1'] . " <b><i>" . $noinvoicesupplier[$bar['keterangan1']] . "</b></i></td>";
			$tab .= "<td style=font-size:0.9em align=center valign=top>" . $tglterima[$bar['keterangan1']] . "</td>";
			$tab .= "<td style=font-size:0.9em align=center valign=top>" . $tgljthtmpo[$bar['keterangan1']] . "</td>";
			$tab .= "<td style=font-size:0.9em align=left valign=top>" . $nmakun[$bar['noakun']] . "<br>" . $bar['keterangan2'] . "</td>";
			$tab .= "<td style=font-size:0.9em align=center valign=top>" . $bar['nodok'] . "</td>";
			$tab .= "<td style=font-size:0.9em align=center valign=top>" . $tipe . "</td>";
			$tab .= "<td style=font-size:0.9em align=right valign=top>" . number_format($bar['jumlah'], 2) . "</td>";
			$tab .= "</tr>";
			@$totaldt += $bar['jumlah'];
		}
		$tab .= "</tbody>";
		// $tab.="</table>";
		// $tab.="<table style='font-size:".$fontsize."' width=100% border=1 cellpadding=".$cellpadding." cellspacing=".$cellspacing.">";	

		$tab .= "<tr>";
		$tab .= "<td colspan=9 height=50px>&nbsp;</td>";
		$tab .= "</tr>";

		$tab .= "<tr width=60%>";
		$tab .= "<td colspan=8 align=right style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;'>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['jumlah'] . ":</td>";
		$tab .= "<td  align=right style='width:10px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;'>" . number_format($totaldt, 2) . "</td>";
		$tab .= "</tr>";

		$tab .= "<tr border>";
		$tab .= "<td align=left colspan=3 valign=top>" . $noakunht . "<br>" . $nmakun[$noakunht] . "</td>";
		$tab .= "<td align=left  valign=top>" . $nmbank[$kodebanksetup] . "&nbsp;&nbsp;" . $atasnamasetup . "&nbsp;&nbsp;" . $norekeningsetup . "</td>";
		$isi = "";
		if ($nocekht != '') {
			$isi = "" . $_SESSION['lang']['nomor'] . " : " . $nocekht . "";
		}

		$tab .= "<td align=left valign=top>" . $_SESSION['lang']['tipe'] . " : " . $cgttuht . "<br>" . $isi . "</td>";
		$tab .= "<td align=right colspan=3 valign=top>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['jumlah'] . "</td>";
		$tab .= "<td  align=right valign=top>" . number_format($jumlahht, 2) . "</td>";

		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td colspan=5 align=right valign=right><td colspan=4 >Terbilang : (" . ucwords(terbilang($jumlahht, 2)) . ")</td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td colspan=9>&nbsp;</td>";
		$tab .= "</tr>";
		$tab .= "</table>";

		$tab .= "<table style='width:100%;font-size:" . $fontsize . "' border=0 cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . ">";
		$tab .= "<tr>";
		$tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . "</td>";
		$tab .= "<td style='width:50px'>&nbsp;</td>";
		$tab .= "<td align=center>" . $_SESSION['lang']['diperiksaoleh'] . "</td>";
		$tab .= "<td style='width:50px'>&nbsp;</td>";
		$tab .= "<td align=center>" . $_SESSION['lang']['dibuatoleh'] . "</td>";
		$tab .= "<td style='width:50px'>&nbsp;</td>";
		$tab .= "<td align=center>" . $_SESSION['lang']['penerima'] . "</td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td style='height:50px;border-bottom:0.5px solid #000000;'>&nbsp;</td>";
		$tab .= "<td align=center></td>";
		$tab .= "<td style='border-bottom:0.5px solid #000000;'>&nbsp;</td>";
		$tab .= "<td align=center></td>";
		$tab .= "<td style='border-bottom:0.5px solid #000000;'>&nbsp;</td>";
		$tab .= "<td align=center></td>";
		$tab .= "<td style='border-bottom:0.5px solid #000000;'></td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td>&nbsp;</td>";
		$tab .= "<td align=center></td>";
		$tab .= "<td>&nbsp;</td>";
		$tab .= "<td align=center></td>";
		$tab .= "<td>&nbsp;</td>";
		$tab .= "<td align=center></td>";
		$tab .= "<td align=center>" . $bayarkepada . "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";


		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		// $a=array(0,0,2150,1390);
		// $dompdf->setPaper('A5', 'landscape');
		// $dompdf->setPaper($a, 'landscape');
		$dompdf->render();
		if ($urlefil == '0') {
			$dompdf->stream("PrintKASBANK_" . $param['notransaksi'], array("Attachment" => 0));
		} else {
			file_put_contents($urlefil, $dompdf->output());
		}
		break;


	case 'excel':
		break;
	case 'html':


		/** Report Prep **/
		$cols = array();

		#=============================== Header ======================================= keu_kasbankht
		$whereH = "notransaksi='" . $param['notransaksi'] .
			"' and kodeorg='" . $param['kodeorg'] .
			"' and noakun='" . $param['noakun'] .
			"' and tipetransaksi='" . $param['tipetransaksi'] . "'";
		$queryH = selectQuery($dbname, 'keu_kasbankht', '*', $whereH);
		$resH = fetchData($queryH);


		# Get Nama Pembuat
		$userId = makeOption(
			$dbname,
			'datakaryawan',
			'karyawanid,namakaryawan',
			"karyawanid='" . $resH[0]['userid'] . "'"
		);
		# Get Nama Akun Hutang
		$namaakunhutang = makeOption(
			$dbname,
			'keu_5akun',
			'noakun,namaakun',
			"noakun='" . $resH[0]['noakunhutang'] . "'"
		);
		#Get tipe Lokasi Tugas
		$tipeLokasiTugas = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');

		#=============================== Detail =======================================
		# Data
		$col1 = 'noakun,jumlah,noaruskas,matauang,kode,keterangan2,hutangunit1,pemilikhutang1';
		$cols = array('nourut', 'noakun', 'namaakun', 'noaruskas', 'debet', 'kredit');
		$colshtml = array('nourut', 'noakun', 'namaakun', 'noaruskas', 'debet', 'kredit', 'keterangan', 'hutangunit1', 'pemilikhutang1');
		//$col1 = 'noakun,jumlah,noaruskas,matauang,kode,hutangunit1';
		//$cols = array('nomor','noakun','namaakun','matauang','debet','kredit','hutangunit');
		$where = "notransaksi='" . $param['notransaksi'] .
			"' and kodeorg='" . $param['kodeorg'] .
			"' and noakun2a='" . $param['noakun'] .
			"' and tipetransaksi='" . $param['tipetransaksi'] . "' AND keterangan3 NOT IN ('PAJAKPPNKAS', 'PAJAKPPHKAS')";
		$query = selectQuery($dbname, 'keu_kasbankdt', $col1, $where);
		$res = fetchData($query);

		# Data Empty
		if (empty($res)) {
			echo 'Data Empty';
			exit;
		}

		# Options
		$whereAkun = "noakun in (";
		$whereAkun .= "'" . $resH[0]['noakun'] . "'";
		$whereAkun .= ",'" . $resH[0]['noakunhutang'] . "'"; // tambahin kamus nama akun hutangunit 
		foreach ($res as $key => $row) {
			$whereAkun .= ",'" . $row['noakun'] . "'";
		}
		$whereAkun .= ")";
		$optAkun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', $whereAkun);
		$optHutangUnit = array('0' => 'Tidak', '1' => 'Ya');

		# Data Show
		$data = array();

		#================================ Prep Data ===================================
		# Total
		$totalDebet = 0;
		$totalKredit = 0;

		# Dari Header
		$i = 1;
		$data[$i] = array(
			'nomor' => @$i,
			'noakun' => @$resH[0]['noakun'],
			'namaakun' => @$optAkun[$resH[0]['noakun']],
			'noaruskas' => @$resH[0]['noaruskas'],
			'debet' => 0,
			'kredit' => 0,
			'keterangan' => $resH[0]['keterangan'],
			'hutangunit1' => '99',
			'pemilikhutang1' => '',
		);

		if ($param['tipetransaksi'] == 'M') {
			$data[$i]['debet'] = $resH[0]['jumlah'];
			$totalDebet += $resH[0]['jumlah'];
		} else {
			$data[$i]['kredit'] = $resH[0]['jumlah'];
			$totalKredit += $resH[0]['jumlah'];
		}

		// if(substr($resH[0]['noakun'],0,5)<='1111101')
		if ($resH[0]['noakun'] <= '1111101') {
			if ($resH[0]['tipetransaksi'] == 'K') {
				$title = strtoupper($_SESSION['lang']['bank'] . " (" . $_SESSION['lang']['keluar'] . ")");
			} else {
				$title = strtoupper($_SESSION['lang']['bank'] . " (" . $_SESSION['lang']['masuk'] . ")");
			}
		} else {
			if ($resH[0]['tipetransaksi'] == 'K') {
				$title = strtoupper($_SESSION['lang']['kas'] . " (" . $_SESSION['lang']['keluar'] . ")");
			} else {
				$title = strtoupper($_SESSION['lang']['kas'] . " (" . $_SESSION['lang']['masuk'] . ")");
			}
		}


		$i++;

		# Dari Detail
		foreach ($res as $row) {
			$data[$i] = array(
				'nomor' => $i,
				'noakun' => $row['noakun'],
				'namaakun' => $optAkun[$row['noakun']],
				'noaruskas' => $row['noaruskas'],
				'debet' => 0,
				'kredit' => 0,
				'keterangan3' => $row['keterangan3'],
				'hutangunit1' => $row['hutangunit1'],
				'pemilikhutang1' => $row['pemilikhutang1'],
			);
			//	'hutangunit1'=>$optHutangUnit[$row['hutangunit1']]
			if ($param['tipetransaksi'] == 'M' and $row['jumlah'] > 0) {
				$data[$i]['kredit'] = $row['jumlah'];
				$totalKredit += $row['jumlah'];
			} else if ($param['tipetransaksi'] == 'K' and $row['jumlah'] < 0) {
				$data[$i]['kredit'] = $row['jumlah'] * -1;
				$totalKredit += $row['jumlah'] * -1;
			} else if ($param['tipetransaksi'] == 'M' and $row['jumlah'] < 0) {
				$data[$i]['debet'] = $row['jumlah'] * -1;
				$totalDebet += $row['jumlah'] * -1;
			} else {
				$data[$i]['debet'] = $row['jumlah'];
				$totalDebet += $row['jumlah'];
			}
			$i++;
		}

		// nyusun berdasarkan debet dulu, abis itu baru kredit. by dz
		if (!empty($data)) foreach ($data as $c => $key) {
			$sort_debet[] = $key['debet'];
			$sort_kredit[] = $key['kredit'];
		}

		// sort
		if (!empty($data)) array_multisort($sort_debet, SORT_DESC, $sort_kredit, SORT_ASC, $data);

		$align = explode(",", "R,R,L,L,R,R,L,L");
		$length = explode(",", "7,12,35,10,18,18,10");
		$titleDetail = 'Detail';


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
		$tab = "<link rel=stylesheet type=text/css href=style/" . $gen . ">";
		$tab .= "<fieldset><legend>" . $title . "</legend>";
		$tab .= "<table cellpadding=1 cellspacing=1 border=0 width=100% class=sortable><tbody class=rowcontent>";
		$tab .= "<tr><td>" . $_SESSION['lang']['kodeorganisasi'] . "</td><td> :</td><td> " . $_SESSION['empl']['lokasitugas'] . "</td></tr>";
		$tab .= "<tr><td>" . $_SESSION['lang']['notransaksi'] . "</td><td> :</td><td>" . $param['notransaksi'] . "</td></tr>";
		$tab .= "<tr><td>" . $_SESSION['lang']['novoucher'] . "</td><td> :</td><td>" . $resH[0]['novoucher'] . "</td></tr>";
		$tab .= "<tr><td>" . $_SESSION['lang']['cgttu'] . "</td><td> :</td><td> " . $resH[0]['cgttu'] . "</td></tr>";
		$tab .= "<tr><td>" . $_SESSION['lang']['terbilang'] . "</td><td> :</td><td> " . terbilang($resH[0]['jumlah'], 2) .
			' rupiah' . "</td></tr>";
		if ($resH[0]['hutangunit'] == 1) {
			$tab .= "<tr><td>" . $_SESSION['lang']['hutangunit'] . "</td><td> :</td><td> " . 'Unit payable Account ' . $resH[0]['pemilikhutang'] . ' : ' . $namaakunhutang[$resH[0]['noakunhutang']] . "</td></tr>";
		}
		$tab .= "</tbody></table><br />";

		$tab .= "<table cellpadding=1 cellspacing=1 border=0 width=100% class=sortable><thead><tr class=rowheader>";



		foreach ($colshtml as $column) {
			if ($column == 'hutangunit1') {
				$tab .= "<td align=center>" . $_SESSION['lang']['hutangunit'] . "</td>";
			} else if ($column == 'pemilikhutang1') {
				$tab .= "<td align=center>" . $_SESSION['lang']['pemilikhutang'] . "</td>";
			} else {
				$tab .= "<td align=center>" . $_SESSION['lang'][$column] . "</td>";
			}
		}
		$tab .= "</tr></thead><tbody class=rowcontent>";

		// echo"<pre>";
		// print_r($data);
		// echo"</pre>";
		// nyusun ulang nomor setelah disort by debet. dz
		$nyomor = 0;
		foreach ($data as $key => $row) {
			$nyomor += 1;
			$tab .= "<tr>";
			foreach ($row as $key => $cont) {
				if ($key == 'nomor') {
					$tab .= "<td align=center>" . $nyomor . "</td>";
				} else {
					if ($key == 'debet' or $key == 'kredit') {
						$tab .= "<td align=right>" . number_format($cont, 0) . "</td>";
					} else  if ($key == 'noaruskas') {
						$tab .= "<td>" . $cont . "<br>" . $nmaruskas[$cont] . "</td>";
					} else  if ($key == 'hutangunit1') {
						if ($cont == 0) {
							$tab .= "<td align=center>Tidak</td>";
						} else if ($cont == 1) {
							$tab .= "<td align=center>Ya</td>";
						} else {
							$tab .= "<td></td>";
						}
					} else  if ($key == 'pemilikhutang1') {
						$tab .= "<td>" . $cont . "</td>";
					} else {
						$tab .= "<td>" . $cont . "</td>";
					}
				}
			}
			$tab .= "</tr>";
		}
		$tab .= "<tr><td colspan=4 align=center>Total</td><td align=right>" . number_format($totalDebet, 0) . "</td>
					<td align=right>" . number_format($totalKredit, 0) . "</td>
					<td colspan=3></td></tr>";
		$tab .= "</tbody></table> <br /><br />";


		$tab .= "Daftar Persetujuan";
		$tab .= "<table cellpadding=1 cellspacing=1 border=0 width=100% class=sortable><tbody class=rowcontent>";
		$tab .= "<thead><tr>
			
				<td>" . $_SESSION['lang']['level'] . "</td>
				<td>" . $_SESSION['lang']['karyawanid'] . "</td>
				<td>" . $_SESSION['lang']['status'] . "</td>
				<td>" . $_SESSION['lang']['keterangan'] . "</td>
				<td>" . $_SESSION['lang']['tanggal'] . "</td>
			</tr></thead>";

		$optposting = array('' => $_SESSION['lang']['pilihdata'], '0' => 'Belum Diajukan', '1' => 'Disetujui', '2' => 'Ditolak', '3' => 'Dikoreksi', '9' => 'Proses Persetujuan');
		//0; belum proses; 1:disetujui;3:dikoreksi;2:ditolak;9:proses pengajuan
		$str = "select * from " . $dbname . ".approval where notransaksi='" . $param['notransaksi'] . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$tab .= "<tr class=rowcontent>";

			$tab .= "<td align=center>" . $bar['level'] . "</td>";
			$tab .= "<td>" . $nmkaryawan[$bar['karyawanid']] . "</td>";
			$tab .= "<td>" . $optposting[$bar['status']] . "</td>";
			$tab .= "<td>" . $bar['komentar'] . "</td>";
			$tab .= "<td>" . tanggalnormal(substr($bar['tanggal'], 0, 10)) . " " . substr($bar['tanggal'], 11, 8) . "</td>";
			$tab .= "</tr>";
		}
		$tab .= "</tbody></table> <br />";


		$tab .= "File Upload Kas/Bank";
		$tab .= "<table border=0 cellspacing=1 class=sortable>
			<thead>
			<tr style='font-weight:bold'>
				<td align='center'>No.</td>
				<td align='center'>File Type</td>
				<td align='center'>Kriteria</td>
				<td align='center'>Filename</td>
				<td align='center'>Action</td>
			</tr>
			</thead>
			<tbody id='listfile'>";


		#= ambil data noinvice	

		$path   = "fileupload/keu_kasbank/";
		$form .= "<td><a href='" . $path . $bar['namafile'] . "' download>" . $bar['namafile'] . "</td>";
		$str = "select * from " . $dbname . ".listfileupload where notransaksi='" . $param['notransaksi'] . "'";
		$res = fetchdata($str);
		foreach ($res as $key => $val) {
			@$icon = seticonfile($val['formaticon']);
			$no++;
			$tab .= "<tr id='ppDetailTable' class=rowcontent>
						<td style='text-align:center'>" . $no . "</td>";
			$tab .= "<td align='center'><img src=" . $icon . " class=resicon></a></td>";
			$tab .= "<td style='text-align:left'>" . getcriterianame($val['kriteriaefil']) . "</td>
						<td style='text-align:left'>" . $val['namafile'] . "</td>
						<td align=center>
							<a href='" . $path . $val['namafile'] . "' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
			$tab . "	</td>
					</tr>";
		}
		$tab .= "</tbody>
		</table><br>";

		$tab .= "File Upload Invoice AP";
		$tab .= "<table border=0 cellspacing=1 class=sortable>
			<thead>
			<tr style='font-weight:bold'>
				<td align='center'>No.</td>
				<td align='center'>Invoice</td>
				<td align='center'>File Type</td>
				<td align='center'>Kriteria</td>
				<td align='center'>Filename</td>
				<td align='center'>Action</td>
			</tr>
			</thead>
			<tbody id='listfilesview'>";


		/*
			#= ambil data noinvice	
			$tempnamafile='';
			$strinv = "select keterangan1 from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."'";
			$resinv = $owlPDO->query($strinv) or die(print " Gagal: " . PDOException::getMessage());
			$resinv->setFetchMode(PDO::FETCH_ASSOC);
			while($barinv=$resinv->fetch()){
				
			$str="select * from ".$dbname.".listfileupload where notransaksi='".$barinv['keterangan1']."'";
			$res=fetchdata($str);
				foreach($res as $key=>$val){
					if($val['namafile']!=$tempnamafile){
						@$icon = seticonfile($val['formaticon']);
						$no++;
						$tab.="<tr id='ppDetailTable' class=rowcontent>
							<td style='text-align:center'>".$no."</td><td style='text-align:center'>".$barinv['keterangan1']."</td>";
						$tab.="<td align='center'><img src=".$icon." class=resicon></a></td>";
						$tab.="<td style='text-align:left'>".getcriterianame($val['kriteriaefil'])."</td>
							<td style='text-align:left'>".$val['namafile']."</td>
							<td align=center>
								<a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
						$tab."	</td>
						</tr>";
						$tempnamafile=$val['namafile'];
					}
				}	
			}
			*/


		#= ambil data noinvice	
		$path   = "fileupload/keu_tagihanht/";
		$tempnamafile = '';
		$strinv = "select keterangan1 from " . $dbname . ".keu_kasbankdt where notransaksi='" . $param['notransaksi'] . "'";
		$resinv = $owlPDO->query($strinv) or die(print " Gagal: " . PDOException::getMessage());
		$resinv->setFetchMode(PDO::FETCH_ASSOC);
		while ($barinv = $resinv->fetch()) {
			$arrnoinvoice[$barinv['keterangan1']] = $barinv['keterangan1'];
		}


		@$carrnoinvoice = count($arrnoinvoice);

		if ($carrnoinvoice > 0) {
			$str = "select * from " . $dbname . ".listfileupload where notransaksi in ('" . implode("','", $arrnoinvoice) . "')";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$lsformaticon[$bar['namafile']] = $bar['formaticon'];
				$lskriteriaefil[$bar['namafile']] = $bar['kriteriaefil'];
				// $lsnamafile[$bar['namafile']]=$bar['namafile'];
				$arrnamafile[$bar['namafile']] = $bar['namafile'];
				$lsnoinvoice[$bar['namafile']] = $bar['notransaksi'];
			}


			foreach ($arrnamafile as $lsnamafile) {
				@$icon = seticonfile($lsformaticon[$lsnamafile]);
				$no++;
				$tab .= "<tr id='ppDetailTable' class=rowcontent>
						<td style='text-align:center'>" . $no . "</td>";
				$tab .= "<td style='text-align:center'>" . $lsnoinvoice[$lsnamafile] . "</td>";
				$tab .= "<td align='center'><img src=" . $icon . " class=resicon></a></td>";
				$tab .= "<td style='text-align:left'>" . getcriterianame($lskriteriaefil[$lsnamafile]) . "</td>
						<td style='text-align:left'>" . $lsnamafile . "</td>
						<td align=center>
							<a href='" . $path . $lsnamafile . "' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
				$tab . "	</td>
					</tr>";
			}
		}





		$tab .= "</tbody>
		</table>";



		echo $tab;

		break;
	default:
		break;
}
