<?php
# CABANG PPP dari keu_slave_3selisihKurs.php (Proses Akhir Bulan > 11. Selisih Kurs). Dipanggil otomatis oleh js/keu_3posting.js (tujuanProses)
# hanya untuk unit yang induknya PPP; unit lain tetap memakai keu_slave_3selisihKurs.php. Perubahan khusus PPP tandai "#PPP".
# Rumus selisih sama dengan file asli. Perbedaan: kurs yang dipakai adalah kurs yang berlaku pada akhir periode dan akhir periode sebelumnya
# (file asli mengambil baris tanggal 1/akhir bulan dari periode mana pun), kurs yang tidak ada ditolak (bukan kurs nol), periode tutup buku
# ditolak, jurnal dibuat dalam satu transaksi dengan jumlah 2 desimal, input divalidasi, tampilan dan rumusan dirapikan.
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');
require_once('lib/zAlokasiGajiPPP.php');

$proses = isset($_GET['proses']) ? $_GET['proses'] : '';
$param = $_POST;
#PPP: proses, unit dan periode divalidasi sebelum dipakai di query
$pesanParam = validasiParamKursPPP($param, $proses);
if ($pesanParam != '') {
	exit($pesanParam);
}
$tmpPeriod = explode('-', $param['periode']);
$tahunbulan = implode("", $tmpPeriod);
$maxDay = (int)date('t', strtotime($param['periode'] . '-01'));

$tipeorganisasi = makeOption($dbname, "organisasi", "kodeorganisasi,tipe");
$tipeLokasiTugas = isset($tipeorganisasi[$_SESSION['empl']['lokasitugas']]) ? $tipeorganisasi[$_SESSION['empl']['lokasitugas']] : '';
if ($tipeLokasiTugas != 'HOLDING' && $tipeLokasiTugas != 'KANWIL') {
	exit('<label hidden>Warning</label> Untuk Selisih Kurs, hanya lokasi tugas Holding (HO) yang bisa lakukan Proses');
}
if (!isset($tipeorganisasi[$param['kodeorg']])) {
	exit('Warning : Unit ' . htmlspecialchars($param['kodeorg']) . ' tidak ditemukan.');
}

$hk = hitungSelisihKursPPP($param['kodeorg'], $param['periode']);
if ($hk['pesan'] != '') {
	exit($hk['pesan']);
}

switch ($proses) {
	case 'list':
		$kodeJurnal = "KRS01";
		$resAkun = fetchData(selectQuery($dbname, 'keu_5parameterjurnal', "noakundebet,noakunkredit", "kodeaplikasi='KURS' and jurnalid='" . $kodeJurnal . "'"));
		$akunUntung = isset($resAkun[0]['noakunkredit']) ? $resAkun[0]['noakunkredit'] : '';
		$akunRugi = isset($resAkun[0]['noakundebet']) ? $resAkun[0]['noakundebet'] : '';
		$rpF = function ($v) {
			return number_format($v, 2);
		};
		$kursF = function ($v) {
			return number_format($v, 4);
		};

		$tab = "
			<fieldset style='margin-bottom:8px'><legend><b>Informasi dan Rumusan Proses</b></legend>
			<div style='display:flex;flex-wrap:wrap;align-items:stretch;gap:6px;font-size:12px'>
				<div style='flex:1 1 230px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>1. Ambil kas/bank valas</b><br>Mutasi akun kas/bank bermata uang asing unit " . htmlspecialchars($param['kodeorg']) . " periode " . htmlspecialchars($param['periode']) . ": <b>jumlah valas</b> (masuk &minus; keluar) dan <b>nilai rupiah</b> menurut kurs transaksi.</div>
				<div style='align-self:center;font-size:18px'>&rarr;</div>
				<div style='flex:1 1 360px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>2. Hitung selisih kurs per akun</b><br>
					Selisih = ( <b>saldo awal Rp</b> &divide; kurs akhir bulan lalu + <b>mutasi valas</b> ) &times; kurs akhir bulan &minus; ( <b>saldo awal Rp</b> + <b>mutasi Rp</b> ).<br>
					&bull; Kurs yang dipakai: kurs yang <b>berlaku pada akhir bulan</b> (" . $hk['akhir'] . ") dan <b>akhir bulan lalu</b> (" . $hk['akhirLalu'] . "), yaitu kurs terbaru sampai tanggal itu di Setup Mata Uang Rate.<br>
					&bull; Hasil dibulatkan 2 desimal.</div>
				<div style='align-self:center;font-size:18px'>&rarr;</div>
				<div style='flex:1 1 260px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>3. Buat jurnal KRS01</b><br>Satu jurnal tanggal " . $hk['akhir'] . ". <b>Selisih positif</b> (untung): debet akun kas/bank, kredit akun <b>" . htmlspecialchars($akunUntung) . "</b>. <b>Selisih negatif</b> (rugi): kredit akun kas/bank, debet akun <b>" . htmlspecialchars($akunRugi) . "</b>. Jurnal dibuat belum diposting.</div>
			</div>
			<div style='font-size:12px;margin-top:6px;line-height:1.5'>
				&bull; Total selisih kurs <b>" . $rpF($hk['total']) . "</b> pada " . count($hk['baris']) . " akun.<br>
				<span style='background:#fff3cd;padding:1px 6px;border:1px solid #e0c36a'><b>Perhatian:</b> Process ulang menghapus jurnal KRS01 unit ini pada periode itu lalu membuatnya lagi. Kalau periode akuntansi sudah tutup buku, prosesnya ditolak.</span>
			</div>";
		foreach ($hk['peringatan'] as $msg) {
			$tab .= "<div style='font-size:12px;margin-top:4px;color:#b00020'><b>Perhatian:</b> " . htmlspecialchars($msg) . "</div>";
		}
		$tab .= "</fieldset>
			<style>
				.tombolKursPPP { display:inline-block; padding:7px 22px; margin:0 8px 8px 0; border-radius:6px; font-size:13px; font-weight:bold; cursor:pointer; }
				#btnproses.tombolKursPPP { background:#2e7d32; color:#fff; border:1px solid #1b5e20; }
				#btnproses.tombolKursPPP:hover { background:#388e3c; }
				#btnproses.tombolKursPPP:disabled { background:#9e9e9e; border-color:#757575; color:#eee; cursor:not-allowed; }
				#btnexcel.tombolKursPPP { background:#fff; color:#2e7d32; border:1px solid #2e7d32; }
				#btnexcel.tombolKursPPP:hover { background:#e8f5e9; }
			</style>
			<div>
				<button class='tombolKursPPP' id=btnproses title='Buat jurnal selisih kurs' " . (empty($hk['baris']) ? "disabled" : "onclick=postKurs()") . ">&#9654; Process</button>
				<button class='tombolKursPPP' onclick=exportTableToExcel() id=btnexcel title='Unduh tabel ini ke Excel'>&#11015; Excel</button>
			</div>
			<input type=hidden id=kodeorgKurs value='" . htmlspecialchars($param['kodeorg'], ENT_QUOTES) . "'>
			<input type=hidden id=periodeKurs value='" . htmlspecialchars($param['periode'], ENT_QUOTES) . "'>
			<table class=sortable cellpadding=5 cellspacing=1 border=0 id=mytable><tbody>
			<tr class=rowheader><th colspan=11 align=left>A. Kurs yang dipakai</th></tr>
			<tr class=rowheader><th>Mata Uang</th><th colspan=2>Kurs Akhir Bulan Lalu</th><th colspan=2>Tanggal Kurs</th><th colspan=2>Kurs Akhir Bulan Ini</th><th colspan=2>Tanggal Kurs</th><th colspan=2></th></tr>";
		if (empty($hk['kurs'])) {
			$tab .= "<tr class=rowcontent><td colspan=11 align=center>Tidak ada transaksi kas/bank bermata uang asing pada periode ini.</td></tr>";
		}
		foreach ($hk['kurs'] as $mu => $k) {
			$kl = $hk['kursLalu'][$mu];
			$tab .= "<tr class=rowcontent><td>" . htmlspecialchars($mu) . "</td><td colspan=2 align=right>" . $kursF($kl[1]) . "</td><td colspan=2>" . htmlspecialchars($kl[0]) . "</td><td colspan=2 align=right>" . $kursF($k[1]) . "</td><td colspan=2>" . htmlspecialchars($k[0]) . "</td><td colspan=2></td></tr>";
		}
		$tab .= "<tr class=rowheader><th colspan=11 align=left>B. Perhitungan selisih kurs per akun kas/bank</th></tr>
			<tr class=rowheader><th>Mata Uang</th><th>No Akun</th><th>Nama Akun</th><th>Saldo Awal (Rp)</th><th>Saldo Awal Valas</th><th>Mutasi Valas</th><th>Saldo Akhir Valas</th><th>Mutasi (Rp)</th><th>Saldo Akhir Rp Menurut Buku</th><th>Saldo Akhir Rp Menurut Kurs</th><th>Selisih Kurs (Rp)</th></tr>";
		foreach ($hk['baris'] as $b) {
			$tab .= "<tr class=rowcontent>
				<td>" . htmlspecialchars($b['matauang']) . "</td>
				<td>" . htmlspecialchars($b['noakun']) . "</td>
				<td>" . htmlspecialchars($b['namaakun']) . "</td>
				<td align=right>" . $rpF($b['sawal']) . "</td>
				<td align=right>" . $rpF($b['sawalValas']) . "</td>
				<td align=right>" . $rpF($b['mutasiValas']) . "</td>
				<td align=right>" . $rpF($b['akhirValas']) . "</td>
				<td align=right>" . $rpF($b['mutasiRp']) . "</td>
				<td align=right>" . $rpF($b['akhirRpBuku']) . "</td>
				<td align=right>" . $rpF($b['akhirRpSeharusnya']) . "</td>
				<td align=right>" . $rpF($b['selisih']) . "</td>
			</tr>";
		}
		if (!empty($hk['baris'])) {
			$tab .= "<tr class=rowcontent style='font-weight:bold'><td colspan=10>Total</td><td align=right>" . $rpF($hk['total']) . "</td></tr>";
		}
		$tab .= "</tbody></table>";
		echo $tab;
		break;

	case 'post':
		if (empty($hk['baris'])) {
			exit("Warning: Tidak ada transaksi kas/bank bermata uang asing pada periode " . $param['periode'] . " untuk unit " . htmlspecialchars($param['kodeorg']) . ", tidak ada yang diproses.");
		}
		#PPP: periode akuntansi diperiksa SEBELUM menghapus jurnal lama
		$resPer = fetchData("select periode from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $param['kodeorg'] . "' and tutupbuku=0 and periode='" . $param['periode'] . "'");
		if (count($resPer) < 1) {
			exit("Error: Accounting has closed transaction of  " . $param['periode']);
		}

		// Init
		$dataRes['header'] = array();
		$dataRes['detail'] = array();

		// Default Segment
		$defSegment = colDefaultValue($dbname, 'keu_5segment', 'kodesegment');

		// Kode Jurnal
		$kodeJurnal = "KRS01";

		// No Jurnal
		$nojurnal = $tahunbulan . $maxDay . "/" . $param['kodeorg'] . "/" . $kodeJurnal .
			"/00001";

		// Get Kurs Parameter Jurnal
		$qAkun = selectQuery(
			$dbname,
			'keu_5parameterjurnal',
			"noakundebet,noakunkredit",
			"kodeaplikasi='KURS' and jurnalid='" . $kodeJurnal . "'"
		);
		$resAkun = fetchData($qAkun);
		if (empty($resAkun)) exit("Warning: Parameter Akun untuk jurnalid " . $kodeJurnal .
			" belum ada\nSilahkan hubungi IT dengan melampirkan pesan error ini");

		// Data Header
		$dataRes['header'] = array(
			'nojurnal' => $nojurnal,
			'kodejurnal' => $kodeJurnal,
			'tanggal' => $tahunbulan . $maxDay,
			'tanggalentry' => date('Ymd'),
			'posting' => '0',
			'totaldebet' => '0',
			'totalkredit' => '0',
			'amountkoreksi' => '0',
			'noreferensi' => $kodeJurnal,
			'autojurnal' => '1',
			'matauang' => 'IDR',
			'kurs' => '1',
			'revisi' => '0'
		);

		// Data Detail (satu pasang baris per akun yang punya selisih; selisih nol tidak dijurnalkan)
		$noUrut = 1;
		foreach ($hk['baris'] as $b) {
			$jumlah = $b['selisih'];
			if ($jumlah == 0) {
				continue;
			}
			$mtUang = $b['matauang'];
			$akun = $b['noakun'];

			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $tahunbulan . $maxDay,
				'nourut' => $noUrut,
				'noakun' => $akun,
				'keterangan' => 'Selisih Kurs ' . $mtUang . ' untuk ' . $param['kodeorg'] .
					' per ' . $param['periode'],
				'jumlah' => $jumlah,
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $param['kodeorg'],
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => '',
				'kodecustomer' => '',
				'kodesupplier' => '',
				'noreferensi' => $kodeJurnal,
				'noaruskas' => '',
				'kodevhc' => '',
				'nodok' => '',
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => $defSegment
			);
			$noUrut++;

			if ($jumlah < 0) {
				$tmpAkun = $resAkun[0]['noakundebet'];
			} else {
				$tmpAkun = $resAkun[0]['noakunkredit'];
			}

			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $tahunbulan . $maxDay,
				'nourut' => $noUrut,
				'noakun' => $tmpAkun,
				'keterangan' => 'Selisih Kurs ' . $mtUang . ' untuk ' . $param['kodeorg'] .
					' per ' . $param['periode'],
				'jumlah' => $jumlah * (-1),
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $param['kodeorg'],
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => '',
				'kodecustomer' => '',
				'kodesupplier' => '',
				'noreferensi' => $kodeJurnal,
				'noaruskas' => '',
				'kodevhc' => '',
				'nodok' => '',
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => $defSegment
			);
			$noUrut++;
		}

		#PPP: seluruh proses dalam satu transaksi: gagal di mana pun -> semua dibatalkan
		$jalankanPPP = function ($query) use ($owlPDO) {
			$hasil = $owlPDO->exec($query);
			if ($hasil === false) {
				$err = $owlPDO->errorInfo();
				throw new Exception(isset($err[2]) ? $err[2] : 'perintah database gagal');
			}
			return $hasil;
		};
		try {
			$owlPDO->beginTransaction();
			// Delete Jurnal jika sudah ada
			$jalankanPPP(deleteQuery($dbname, 'keu_jurnalht', "nojurnal='" . $nojurnal . "'"));
			if (!empty($dataRes['detail'])) {
				// Insert Jurnal Header dan Detail
				$jalankanPPP(insertQuery($dbname, 'keu_jurnalht', $dataRes['header']));
				foreach ($dataRes['detail'] as $key => $dataDet) {
					$jalankanPPP(insertQuery($dbname, 'keu_jurnaldt', $dataDet));
				}
			}
			$owlPDO->commit();
		} catch (Exception $e) {
			$owlPDO->rollBack();
			echo "Error, " . addslashes($e->getMessage());
			exit;
		}
		break;
}
