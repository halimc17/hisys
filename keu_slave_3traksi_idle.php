<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');

$kodeorg  = checkPostGet('kodeorg', '');
$periode  = checkPostGet('periode', '');
$method = checkPostGet('method', '');

$arrKodeJurnal = [
	'upah' => 'VHCG7',
	'premi' => 'VHCG8'
];

list($tgmulai, $tgsampai, $periodeFix) = getPeriodeAkuntansi($kodeorg, $periode);
if ($tgmulai == '' || $tgsampai == '') exit("Error: Accounting period is not registered");

// kamus no akun
$dtNoAkun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun like '7%'");

// kamus organisasi
$dtOrganisasi = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "tipe IN ('TRAKSI','WORKSHOP')");

switch ($method) {
	case 'list':
		// bentuk table nya 
		$table = generateList();
		echo $table;
		break;

	case 'savedata':
		saveData();
		break;

	default:
		echo "NO VALID METHOD! CONTACT ADMINISTRATOR!";
		break;
}

function generateList()
{

	global $kodeorg, $periode, $tgmulai, $tgsampai, $dtKegiatan, $dtNoAkun, $dtOrganisasi;


	// ambil data karyawan
	$dtKry = getDtKry($kodeorg, $periode);

	// ambil data absensi
	$dtAbsensi = getDtAbsensi($kodeorg, $tgmulai, $tgsampai, $dtKry);

	// echo "<pre>" . var_export($dtAbsensi, true) . "</pre>";


	// mulai bentuk tabel 
	$thead = "
		<thead>
			<tr class='rowheader'>
				<th>No</th>
				<th>Karyawan</th>
				<th>NIK</th>
				<th>Sub Bagian</th>
				<th>No Akun</th>
				<th>Nama Akun</th>
				<th>Upah</th>		
				<th>Premi</th>		
				<th>Total</th>		
			</tr>
		</thead>
	";

	$tbody = "";
	$no = 0;

	// foreach karyawan
	foreach ($dtAbsensi as $karyawanid => $dtAkun) {
		$no++;

		// foreach noakun
		foreach ($dtAkun as $noakun => $detaildt) {

			$tmpTotal = number_format($detaildt['upah'], 2);
			$tmpPremi = number_format($detaildt['premi'], 2);
			$tmpAllTotal = number_format($detaildt['upah'] + $detaildt['premi'], 2);


			$nmKry = strtoupper($dtKry[$karyawanid]['nama']);

			$tbody .= "
				<tr class='rowcontent'>
					<td align='center'>{$no}</td>
					<td align='left'>{$nmKry}</td>
					<td align='left'>{$dtKry[$karyawanid]['nik']}</td>
					<td align='left'>{$dtKry[$karyawanid]['subbagian']} - {$dtOrganisasi[$dtKry[$karyawanid]['subbagian']]}</td>
					<td align='center'>{$noakun}</td>
					<td align='center'>{$dtNoAkun[$noakun]}</td>
					<td align='right'>{$tmpTotal}</td>
					<td align='right'>{$tmpPremi}</td>
					<td align='right'>{$tmpAllTotal}</td>
				</tr>
			";
		}
	}

	return $tab = "
		<button class=mybutton onclick=prosesAlokasiTraksiIdle('savedata','{$kodeorg}','{$periode}') id=btnproses>Process</button>
		<table class='sortable' cellspacing='1' cellpadding='5' border=0>
			{$thead}
			<tbody>
				{$tbody}
			</tbody>
		</table>
	";
}

function saveData()
{
	global $kodeorg, $dbname, $periode, $tgmulai, $tgsampai, $owlPDO, $arrKodeJurnal;

	// cek apakah kodeparameter jurnal sudah di setup 
	$getParamJurnal = getKodeJurUpahPremiIdle();

	if (!$getParamJurnal || count($getParamJurnal) == 0) {
		exit("Error: Kode parameter jurnal belum di setup! contact administrator");
	}

	// ambil noakun nya 
	$dtAkunKredit = [
		'upah' => $getParamJurnal['VHCG7']['noakunkredit'],
		'premi' => $getParamJurnal['VHCG8']['noakunkredit']
	];

	// ambil data karyawan
	$dtKry = getDtKry($kodeorg, $periode);

	// ambil data absensi
	$dtAbsensi = getDtAbsensi($kodeorg, $tgmulai, $tgsampai, $dtKry);
	if (empty($dtAbsensi)) {
		exit("Warning: Tidak ada data absensi untuk periode {$periode} di lokasi {$kodeorg}");
	}
	$dataRes = [];

	try {
		$owlPDO->beginTransaction();

		$defSegment = colDefaultValue($dbname, 'keu_5segment', 'kodesegment');

		if (count($dtAbsensi) > 0) {

			$deleteAllTrans = deleteAllDataTraksiIdle($kodeorg, $periode);
			if (!$deleteAllTrans) {
				exit("warning! : Gagal menghapus data sebelumnya, Silahkan hubungi Administrator");
			}
			
			// mulai bentuk header nya 
			foreach ($arrKodeJurnal as $kd => $kdjurnal) {
				$tmpKD = strtoupper($kd);
				$orgpt = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='{$kodeorg}'")[$kodeorg];

				// prepare jurnal
				# Get Journal Counter
				$queryJ = selectQuery(
					$dbname,
					'keu_5kelompokjurnal',
					'nokounter',
					"kodeorg='" . $orgpt . "' and kodekelompok='" . $kdjurnal . "' and kodeunit='" . trim($kodeorg) . "' and periode='" . $periode . "' "
				);
				$tmpKonter = fetchData($queryJ);
				$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

				# Transform No Jurnal dari No Transaksi
				$nojurnal = str_replace("-", "", $tgsampai) . "/" . trim($kodeorg) . "/" . $kdjurnal . "/" . $konter;

				// mulai bentuk detail nya 
				$nourut = 0;
				$totalJumlah[$kd] = 0;

				// foreach karyawan
				foreach ($dtAbsensi as $karyawanid => $dtAkun) {

					// foreach noakun
					foreach ($dtAkun as $noakun => $dt) {
						if ($dt[$kd] <= 0) {
							continue; // skip jika tidak ada upah atau premi
						}

						$totalJumlah[$kd] += $dt[$kd];

						// prepare detail jurnal
						$nourut++;
						// debet
						$dataRes[$kd]['detail'][] = [
							'nojurnal' => $nojurnal,
							'tanggal' => $tgsampai,
							'nourut' => $nourut,
							'noakun' => $noakun,
							'keterangan' => "Alokasi {$tmpKD} Traksi IDLE Periode:{$periode} Unit:{$kodeorg} - {$dtKry[$karyawanid]['nama']} ({$dtKry[$karyawanid]['nik']})",
							'jumlah' => $dt[$kd],
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => $kodeorg,
							'kodekegiatan' => '',
							'kodeasset' => '',
							'kodebarang' => '',
							'nik' => $karyawanid,
							'kodecustomer' => '',
							'kodesupplier' => '',
							'noreferensi' => "ALK_{$tmpKD}_TRAKSI_IDLE",
							'noaruskas' => '',
							'kodevhc' => '',
							'nodok' => '',
							'kodeblok' => '',
							'revisi' => '0',
							'kodesegment' => $defSegment
						];

						$nourut++;
						// kredit
						$dataRes[$kd]['detail'][] = [
							'nojurnal' => $nojurnal,
							'tanggal' => $tgsampai,
							'nourut' => $nourut,
							'noakun' => $dtAkunKredit[$kd],
							'keterangan' => "Alokasi {$tmpKD} Traksi IDLE Periode:{$periode} Unit:{$kodeorg} - {$dtKry[$karyawanid]['nama']} ({$dtKry[$karyawanid]['nik']})",
							'jumlah' => ($dt[$kd] * -1),
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => $kodeorg,
							'kodekegiatan' => '',
							'kodeasset' => '',
							'kodebarang' => '',
							'nik' => $karyawanid,
							'kodecustomer' => '',
							'kodesupplier' => '',
							'noreferensi' => "ALK_{$tmpKD}_TRAKSI_IDLE",
							'noaruskas' => '',
							'kodevhc' => '',
							'nodok' => '',
							'kodeblok' => '',
							'revisi' => '0',
							'kodesegment' => $defSegment
						];
					}
				}

				if ($totalJumlah[$kd] <= 0) {
					continue; // skip jika tidak ada upah atau premi
				}
				// prepare header jurnal
				$dataRes[$kd]['header'] = [
					'nojurnal' => $nojurnal,
					'kodejurnal' => $kdjurnal,
					'tanggal' => $tgsampai,
					'tanggalentry' => date('Ymd'),
					'posting' => 1,
					'totaldebet' => $totalJumlah[$kd],
					'totalkredit' => -1 * $totalJumlah[$kd],
					'amountkoreksi' => '0',
					'noreferensi' => "ALK_{$tmpKD}_TRAKSI_IDLE",
					'autojurnal' => '1',
					'matauang' => 'IDR',
					'kurs' => '1',
					'revisi' => '0'
				];

				// insert header jurnal
				$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes[$kd]['header']);
				$owlPDO->exec($insHead);

				// insert detail jurnal
				foreach ($dataRes[$kd]['detail'] as $row) {
					$insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
					$owlPDO->exec($insDet);
				}

				# Header and Detail inserted
				#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
				$updJurnal = updateQuery(
					$dbname,
					'keu_5kelompokjurnal',
					array('nokounter' => $konter),
					"kodeorg='" . $orgpt . "' and kodekelompok='" . $kdjurnal . "' and kodeunit='" . trim($kodeorg) . "' and periode='" . $periode . "'  "
				);
				$owlPDO->exec($updJurnal);
			}
		}

		$owlPDO->commit();
	} catch (Exception $e) {
		$owlPDO->rollBack();
	}
}

function getKodeJurUpahPremiIdle()
{
	global $dbname, $arrKodeJurnal;

	$dtReturn = [];

	$expKodeJur = implode("','", array_values($arrKodeJurnal));

	$str = "SELECT DISTINCT jurnalid, noakundebet, noakunkredit
					FROM {$dbname}.keu_5parameterjurnal
					WHERE kodeaplikasi = 'VHC'
					and aktif = '1'
					AND jurnalid in ('{$expKodeJur}')
				";
	$getDt = fetchData($str);
	foreach ($getDt as $dt) {
		$dtReturn[$dt['jurnalid']]['noakundebet'] = $dt['noakundebet'];
		$dtReturn[$dt['jurnalid']]['noakunkredit'] = $dt['noakunkredit'];
	}

	return $dtReturn;
}

function deleteAllDataTraksiIdle($kodeorg, $periode)
{
	global $dbname, $owlPDO, $arrKodeJurnal;

	$expKodeJur = implode("','", array_values($arrKodeJurnal));
	// delete data traksi idle jurnal upah dan premi
	$strCekJurnal = "	SELECT DISTINCT nojurnal 
										FROM {$dbname}.keu_jurnaldt_vw 
										WHERE kodeorg = '{$kodeorg}'
											AND periode = '{$periode}'
											AND kodejurnal in ('{$expKodeJur}')
											AND (noreferensi = 'ALK_UPAH_TRAKSI_IDLE' OR noreferensi = 'ALK_PREMI_TRAKSI_IDLE')
									";
	$cekJurnal = fetchdata($strCekJurnal);
	if ($cekJurnal && count($cekJurnal) > 0) {
		try {
			foreach ($cekJurnal as $dt) {
				$str = "DELETE FROM {$dbname}.keu_jurnalht WHERE nojurnal = '{$dt['nojurnal']}'";
				$owlPDO->exec($str);
			}
		} catch (Exception $e) {
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
	}

	return true;
}

function getDtAbsensi($kodeorg, $tgmulai, $tgsampai, $dtKry)
{
	global $dbname;

	$periode = date("Y-m", strtotime($tgmulai));
	// generate query get karyawan nya dulu
	$strKry = generateQueryStrDtKaryawan($kodeorg, $periode, 'explode');


	$dtAbsensi = [];
	$str = "SELECT 	karyawanid, tanggal, noakun, sum(premi) as premi, sum(insentif) as insentif, 
									sum(insentiflibur) as insentiflibur, sum(umr) as umr 
					FROM {$dbname}.sdm_absensidt_vw
					WHERE 1=1
						AND kodeorg LIKE '{$kodeorg}%'
						AND tanggal BETWEEN '{$tgmulai}' AND '{$tgsampai}'
						AND noakun like '7%'
						AND karyawanid IN ({$strKry})
						AND tipekaryawan !='0'
					GROUP BY karyawanid, noakun
					";

	$getDtAbsensi = fetchdata($str);
	$dtAbsensi = [];
	foreach ($getDtAbsensi as $dt) {
		$totalupah = $dt['umr'];
		$totalPremi = $dt['premi'] + $dt['insentif'] + $dt['insentiflibur'];
		$dtAbsensi[$dt['karyawanid']][$dt['noakun']]['upah'] += $totalupah;
		$dtAbsensi[$dt['karyawanid']][$dt['noakun']]['premi'] += $totalPremi;
	}
	return $dtAbsensi;
}

function generateQueryStrDtKaryawan($kodeorg, $periode, $tipe = 'explode')
{
	global $dbname;

	// kalau tipenya explode untuk diambil karyawanid nya aja
	$slc = ($tipe == 'explode') ? " karyawanid " : " nik, namakaryawan, periodegaji, karyawanid, lokasitugas, subbagian ";

	$str = "SELECT DISTINCT {$slc}
					FROM {$dbname}.datakaryawan_hist as a
					INNER JOIN {$dbname}.organisasi as b
						ON a.subbagian=b.kodeorganisasi
						AND b.tipe IN ('TRAKSI','WORKSHOP')
						AND b.induk = '{$kodeorg}'
					WHERE 1=1
						AND lokasitugas='{$kodeorg}'
						AND periodegaji='{$periode}' 
						AND version_type='B'
	";
	return $str;
}

function getDtKry($kodeorg, $periode)
{
	global $dbname;

	$str = generateQueryStrDtKaryawan($kodeorg, $periode, 'all');
	$getDtKry = fetchdata($str);
	$dtKry = [];
	foreach ($getDtKry as $dt) {
		$dtKry[$dt['karyawanid']]['nama'] = $dt['namakaryawan'];
		$dtKry[$dt['karyawanid']]['nik'] = $dt['nik'];
		$dtKry[$dt['karyawanid']]['lokasitugas'] = $dt['lokasitugas'];
		$dtKry[$dt['karyawanid']]['periode'] = $dt['periodegaji'];
		$dtKry[$dt['karyawanid']]['subbagian'] = $dt['subbagian'];
	}
	return $dtKry;
}

function getPeriodeAkuntansi($kodeorg, $periode)
{
	global $owlPDO, $dbname;
	$str = "SELECT tanggalmulai, tanggalsampai, periode 
					FROM $dbname.setup_periodeakuntansi 
					WHERE kodeorg ='$kodeorg' and tutupbuku=0 and periode='$periode'";
	$res = $owlPDO->query($str);
	$res->setFetchMode(PDO::FETCH_OBJ);
	$tgmulai = $tgsampai = $per = '';
	while ($bar = $res->fetch()) {
		$tgmulai = $bar->tanggalmulai;
		$tgsampai = $bar->tanggalsampai;
		$per = $bar->periode;
	}
	return [$tgmulai, $tgsampai, $per];
}
