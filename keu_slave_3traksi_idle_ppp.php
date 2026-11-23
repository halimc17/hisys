<?php
# CABANG PPP dari keu_slave_3traksi_idle.php (salinan awal, belum ada perbedaan). Dipanggil otomatis oleh js/keu_3posting.js (tujuanProses)
# hanya untuk unit yang induknya PPP; unit lain tetap memakai keu_slave_3traksi_idle.php. Perubahan khusus PPP tandai "#PPP".
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');
require_once('lib/zAlokasiGajiPPP.php'); #PPP: fungsi validasi format parameter

$kodeorg  = checkPostGet('kodeorg', '');
$periode  = checkPostGet('periode', '');
$method = checkPostGet('method', '');
#PPP: format unit dan periode divalidasi sebelum dipakai di query
$pesanParam = validasiParamGajiPPP(array('kodeorg' => $kodeorg, 'periode' => $periode));
if ($pesanParam != '') {
	exit($pesanParam);
}

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

	global $kodeorg, $periode, $tgmulai, $tgsampai, $dtKegiatan, $dtNoAkun, $dtOrganisasi, $dbname;

	// ambil data karyawan
	$dtKry = getDtKry($kodeorg, $periode);

	// ambil data absensi
	$dtAbsensi = getDtAbsensi($kodeorg, $tgmulai, $tgsampai, $dtKry);

	// akun kredit dari parameter jurnal (VHCG7 = upah, VHCG8 = premi), ditampilkan di informasi proses
	$pj = getKodeJurUpahPremiIdle();
	$nmAkunKredit = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun like '2%'");
	$teksAkun = function ($noakun) use ($nmAkunKredit) {
		return $noakun == '' ? '(belum diatur di Parameter Jurnal)' : $noakun . ' - ' . (isset($nmAkunKredit[$noakun]) ? $nmAkunKredit[$noakun] : '');
	};
	$kreditUpah = isset($pj['VHCG7']['noakunkredit']) ? $pj['VHCG7']['noakunkredit'] : '';
	$kreditPremi = isset($pj['VHCG8']['noakunkredit']) ? $pj['VHCG8']['noakunkredit'] : '';

	// detail karyawan untuk tampilan: histori periode (versi B) kalau ada, kalau tidak data master
	$infoKar = array();
	if (count($dtAbsensi) > 0) {
		$in = "'" . implode("','", array_map('addslashes', array_keys($dtAbsensi))) . "'";
		foreach (fetchData("select karyawanid,kodejabatan,kodegolongan,tipekaryawan from " . $dbname . ".datakaryawan_hist
				where karyawanid in (" . $in . ") and periodegaji='" . addslashes($periode) . "' and version_type='B' and lokasitugas='" . addslashes($kodeorg) . "'") as $barK) {
			$infoKar[$barK['karyawanid']] = $barK;
		}
		foreach (fetchData("select karyawanid,kodejabatan,kodegolongan,tipekaryawan from " . $dbname . ".datakaryawan where karyawanid in (" . $in . ")") as $barK) {
			if (!isset($infoKar[$barK['karyawanid']])) {
				$infoKar[$barK['karyawanid']] = $barK;
			}
		}
	}
	$nmJabatan = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
	$nmGolongan = makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');
	$nmTipeKar = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');

	// mulai bentuk tabel: kolom disamakan dengan list Gaji Karyawan (langkah 1)
	$thead = "
		<thead>
			<tr class='rowheader'>
				<th>No</th>
				<th>Periode</th>
				<th>NIK</th>
				<th>Nama Karyawan</th>
				<th>Jabatan</th>
				<th>Golongan</th>
				<th>Tipe Karyawan</th>
				<th>Divisi</th>
				<th>Komponen</th>
				<th>Jumlah</th>
				<th>Jurnal</th>
				<th>Akun Debet</th>
				<th>Sumber Akun</th>
			</tr>
		</thead>
	";

	$tbody = "";
	$no = 0;
	$jmlKar = 0;
	$sumUpah = 0;
	$sumPremi = 0;
	$barisNol = 0;
	$barisIsi = 0;

	// foreach karyawan
	foreach ($dtAbsensi as $karyawanid => $dtAkun) {
		$tampilKar = false;
		$kr = isset($dtKry[$karyawanid]) ? $dtKry[$karyawanid] : array('nama' => '', 'nik' => '', 'subbagian' => '');
		$ik = isset($infoKar[$karyawanid]) ? $infoKar[$karyawanid] : array();
		$jabatanTampil = (isset($ik['kodejabatan']) && isset($nmJabatan[$ik['kodejabatan']])) ? $nmJabatan[$ik['kodejabatan']] : '';
		$golonganTampil = (isset($ik['kodegolongan']) && isset($nmGolongan[$ik['kodegolongan']])) ? $nmGolongan[$ik['kodegolongan']] : '';
		$tipeKarTampil = (isset($ik['tipekaryawan']) && isset($nmTipeKar[$ik['tipekaryawan']])) ? $nmTipeKar[$ik['tipekaryawan']] : '';
		$subTampil = $kr['subbagian'] . ' - ' . (isset($dtOrganisasi[$kr['subbagian']]) ? $dtOrganisasi[$kr['subbagian']] : '');

		// foreach noakun
		foreach ($dtAkun as $noakun => $detaildt) {

			$upah = (float)$detaildt['upah'];
			$premi = (float)$detaildt['premi'];
			$sumUpah += $upah;
			$sumPremi += $premi;
			if (($upah + $premi) <= 0) {
				#absensi tanpa upah/premi (mis. hari Minggu): tidak dijurnalkan, jadi tidak ditampilkan
				$barisNol++;
				continue;
			}
			if (!$tampilKar) {
				$jmlKar++;
				$tampilKar = true;
			}
			$akunTampil = $noakun . ' - ' . (isset($dtNoAkun[$noakun]) ? $dtNoAkun[$noakun] : '');

			// satu baris untuk upah (VHCG7) dan satu baris untuk premi (VHCG8), hanya yang bernilai
			foreach (array('Upah' => array($upah, 'VHCG7'), 'Premi' => array($premi, 'VHCG8')) as $namaKomponen => $isi) {
				if ($isi[0] <= 0) {
					continue;
				}
				$no++;
				$barisIsi++;
				$tbody .= "
				<tr class='rowcontent' id='row{$no}'>
					<td>{$no}</td>
					<td>" . htmlspecialchars($periode) . "</td>
					<td>" . htmlspecialchars($kr['nik']) . "</td>
					<td>" . htmlspecialchars(strtoupper($kr['nama'])) . "</td>
					<td>" . htmlspecialchars($jabatanTampil) . "</td>
					<td>" . htmlspecialchars($golonganTampil) . "</td>
					<td>" . htmlspecialchars($tipeKarTampil) . "</td>
					<td>" . htmlspecialchars($subTampil) . "</td>
					<td>" . $namaKomponen . " Idle</td>
					<td align='right'>" . number_format($isi[0], 2) . "</td>
					<td align='center'>" . $isi[1] . "</td>
					<td>" . htmlspecialchars($akunTampil) . "</td>
					<td>Absensi</td>
				</tr>
				";
			}
		}
	}

	if ($barisIsi == 0) {
		$tbody = "<tr class='rowcontent'><td colspan='13' align='center'>Tidak ada upah atau premi idle yang perlu dijurnalkan pada periode ini.</td></tr>";
	}

	$tfoot = "
		<tr class='rowcontent'>
			<td colspan='9'>Total Upah</td>
			<td align='right'>" . number_format($sumUpah, 2) . "</td>
			<td colspan='3'></td>
		</tr>
		<tr class='rowcontent'>
			<td colspan='9'>Total Premi</td>
			<td align='right'>" . number_format($sumPremi, 2) . "</td>
			<td colspan='3'></td>
		</tr>
		<tr class='rowcontent'>
			<td colspan='9'>Total</td>
			<td align='right'>" . number_format($sumUpah + $sumPremi, 2) . "</td>
			<td colspan='3'></td>
		</tr>
	";
	$info = "
		<fieldset style='margin-bottom:8px'><legend><b>Informasi Proses</b></legend>
		<div style='display:flex;flex-wrap:wrap;align-items:stretch;gap:6px;font-size:12px'>
			<div style='flex:1 1 190px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>1. Ambil karyawan</b><br>Karyawan unit " . htmlspecialchars($kodeorg) . " yang subbagiannya <b>Traksi</b> atau <b>Bengkel</b> pada periode " . htmlspecialchars($periode) . ".</div>
			<div style='align-self:center;font-size:18px'>&rarr;</div>
			<div style='flex:1 1 190px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>2. Ambil absensi</b><br>Upah dan premi absensi periode ini yang akunnya <b>7xxxxxx</b> (biaya), dikelompokkan per karyawan dan akun.</div>
			<div style='align-self:center;font-size:18px'>&rarr;</div>
			<div style='flex:1 1 250px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>3. Buat dua jurnal</b><br><b>Upah</b> (VHCG7) dan <b>Premi</b> (VHCG8). <b>Debet</b> akun dari absensi (kolom No Akun), <b>Kredit</b> " . ($kreditUpah == $kreditPremi ? htmlspecialchars($teksAkun($kreditUpah)) : "upah: " . htmlspecialchars($teksAkun($kreditUpah)) . "; premi: " . htmlspecialchars($teksAkun($kreditPremi))) . ".</div>
		</div>
		<div style='font-size:12px;margin-top:6px;line-height:1.5'>
			&bull; Terdapat <b>{$jmlKar}</b> karyawan dengan <b>{$barisIsi}</b> baris yang dijurnalkan" . ($barisNol > 0 ? ". Absensi tanpa upah/premi (mis. hari Minggu) sebanyak <b>{$barisNol}</b> baris tidak ditampilkan karena tidak dijurnalkan" : "") . ".<br>
			&bull; Ini bagian <i>idle</i> dari absensi Traksi/Bengkel. Biaya gaji Traksi/Bengkel lainnya diproses di langkah <i>3. Gaji Karyawan Tidak Langsung - (Traksi,Bengkel)</i>.<br>
			<span style='background:#fff3cd;padding:1px 6px;border:1px solid #e0c36a'><b>Perhatian:</b> Process ulang untuk periode yang sama menghapus jurnal idle periode itu lalu membuatnya lagi.</span>
		</div>
		</fieldset>
	";

	return $tab = "
		<style>
			.tombolIdlePPP { display:inline-block; padding:7px 22px; margin:0 8px 8px 0; border-radius:6px; font-size:13px; font-weight:bold; cursor:pointer; }
			#btnproses.tombolIdlePPP { background:#2e7d32; color:#fff; border:1px solid #1b5e20; }
			#btnproses.tombolIdlePPP:hover { background:#388e3c; }
			#btnproses.tombolIdlePPP:disabled { background:#9e9e9e; border-color:#757575; color:#eee; cursor:not-allowed; }
			#btnexcel.tombolIdlePPP { background:#fff; color:#2e7d32; border:1px solid #2e7d32; }
			#btnexcel.tombolIdlePPP:hover { background:#e8f5e9; }
		</style>
		{$info}
		<div>
			<button class='tombolIdlePPP' onclick=prosesAlokasiTraksiIdle('savedata','{$kodeorg}','{$periode}') id=btnproses title='Buat jurnal upah dan premi idle'>&#9654; Process</button>
			<button class='tombolIdlePPP' onclick=exportTableToExcel() id=btnexcel title='Unduh tabel ini ke Excel'>&#11015; Excel</button>
		</div>
		<table class='sortable' cellspacing='1' cellpadding='5' border=0 id=mytable>
			{$thead}
			<tbody>
				{$tbody}
				{$tfoot}
			</tbody>
		</table>
	";
}

function saveData()
{
	global $kodeorg, $dbname, $periode, $tgmulai, $tgsampai, $owlPDO, $arrKodeJurnal;

	// cek apakah kodeparameter jurnal sudah di setup 
	$getParamJurnal = getKodeJurUpahPremiIdle();

	if (!$getParamJurnal || !isset($getParamJurnal['VHCG7']) || !isset($getParamJurnal['VHCG8'])) {
		exit("Error: Kode parameter jurnal VHCG7 (upah) dan VHCG8 (premi) belum lengkap di setup! contact administrator");
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
				$owlPDO->rollBack();
				exit("Error: Gagal menghapus data sebelumnya, Silahkan hubungi Administrator");
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
				if (empty($tmpKonter)) {
					#tanpa baris counter nomor jurnal tidak bisa disimpan dan nomor bisa ganda
					throw new Exception("Counter nomor jurnal " . $kdjurnal . " unit " . trim($kodeorg) . " periode " . $periode . " belum ada");
				}
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
		if ($owlPDO->inTransaction()) {
			$owlPDO->rollBack();
		}
		exit("Error, " . addslashes($e->getMessage()));
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
	$str = "SELECT 	karyawanid, noakun, sum(premi) as premi, sum(insentif) as insentif, 
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
		if (!isset($dtAbsensi[$dt['karyawanid']][$dt['noakun']])) {
			$dtAbsensi[$dt['karyawanid']][$dt['noakun']] = array('upah' => 0, 'premi' => 0);
		}
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
