<?php
# CABANG PPP dari keu_slave_3bibitpnkemn.php (Proses Akhir Bulan > 9. Alokasi bibit PN ke MN). Dipanggil otomatis oleh js/keu_3posting.js
# (tujuanProses) hanya untuk unit yang induknya PPP; unit lain tetap memakai keu_slave_3bibitpnkemn.php. Perubahan khusus PPP tandai "#PPP".
# Rumus harga rata-rata dan isi jurnal sama dengan file asli. Perbedaan: periode dicek dulu, tidak ada jurnal kosong bila tidak ada transaksi,
# penyebut nol ditolak, keterangan bibit tidak menumpuk saat proses ulang, input divalidasi, tampilan dan rumusan dirapikan.
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');
require_once('lib/zAlokasiGajiPPP.php');

$param = $_POST;
#PPP: proses bisa datang dari POST (proses jurnal) atau tidak ada (list); unit dan periode divalidasi sebelum dipakai di query
$proses = isset($param['proses']) ? $param['proses'] : 'list';
$pesanParam = validasiParamGajiPPP($param);
if ($pesanParam == '' && (!isset($param['kodeorg']) || $param['kodeorg'] === '' || !isset($param['periode']) || $param['periode'] === '')) {
	$pesanParam = 'Warning : Unit dan periode wajib diisi.';
}
if ($pesanParam == '' && !preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])$/', $param['periode'])) {
	$pesanParam = 'Warning : Parameter periode tidak valid.';
}
if ($pesanParam == '' && !in_array($proses, array('', 'list', 'prosesjurnal'), true)) {
	$pesanParam = 'Warning : Parameter proses tidak valid.';
}
if ($pesanParam != '') {
	exit($pesanParam);
}
$kodeorg   = $param['kodeorg'];
$tahunbulan = implode("", explode('-', $param['periode']));
$induk     = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
if (!isset($induk[$kodeorg])) {
	exit('Warning : Unit ' . htmlspecialchars($kodeorg) . ' tidak ditemukan.');
}

try {
	$owlPDO->beginTransaction();

	#1. ambil periode akuntansi
	$tgmulai = '';
	$tgsampai = '';
	$periode = '';
	$tanggal = '';
	$str = "select * from " . $dbname . ".setup_periodeakuntansi where kodeorg ='" . $kodeorg . "' and tutupbuku=0 and periode='" . $param['periode'] . "' ";
	$res = fetchdata($str);
	foreach ($res as $bar) {
		$tgsampai = $bar['tanggalsampai'];
		$tgmulai = $bar['tanggalmulai'];
		$periode = $bar['periode'];
		$tanggal = $bar['tanggalsampai'];
	}
	#PPP: periode harus terdaftar dan belum tutup buku (dulu dilanjutkan dengan tanggal kosong)
	if ($tgmulai == '' || $tgsampai == '') {
		exit("Error: Accounting period " . $param['periode'] . " is not registered or has been closed");
	}

	$noblok = array();
	$nobloktujuan = array();
	$nobatch = array();
	$subttl = array();
	$total = 0;
	$barisTpb = array();

	$str = "select * from " . $dbname . ".bibitan_mutasi where kodeorg like '" . $kodeorg . "%' and tanggal>='" . $tgmulai . "' and tanggal<='" . $tgsampai . "' and kodetransaksi='TPB'";
	$res = fetchdata($str);
	foreach ($res as $bar) {
		if ($bar['post'] == '0') {
			exit("Error : Ada transaksi yang belum diposting.");
		} else {
			$noblok[$bar['kodeorg']] = $bar['kodeorg'];
			$nobloktujuan[$bar['kodeorg']][$bar['tujuan']] = $bar['tujuan'];
			$nobatch[$bar['kodeorg']][$bar['tujuan']] = $bar['batch'];
			$barisTpb[] = $bar;
			$total += abs($bar['jumlah']);
			if (!isset($subttl[$bar['kodeorg']][$bar['tujuan']])) $subttl[$bar['kodeorg']][$bar['tujuan']] = 0;
			$subttl[$bar['kodeorg']][$bar['tujuan']] += abs($bar['jumlah']);
		}
	}
	#PPP: tidak ada transaksi transplanting (TPB) -> tidak ada yang diproses (dulu membuat header jurnal kosong)
	if (empty($barisTpb)) {
		exit("Warning: Tidak ada transaksi transplanting (TPB) pada periode " . $param['periode'] . " untuk unit " . htmlspecialchars($kodeorg) . ", tidak ada yang diproses.");
	}

	// Get Parameter Jurnal
	$kodeTrans       = 'TPB';
	$kodeJurnal      = $kodeTrans;

	$sql = "SELECT noakundebet,noakunkredit,sampaidebet,sampaikredit FROM " . $dbname . ".keu_5parameterjurnal WHERE kodeorg='" . (isset($induk[$induk[$kodeorg]]) ? addslashes($induk[$induk[$kodeorg]]) : '') . "' and jurnalid='" . $kodeJurnal . "' and kodeaplikasi ='" . $kodeTrans . "'";
	$resPj = fetchData($sql);
	$paramJurnal = isset($resPj[0]) ? $resPj[0] : array();
	if (empty($paramJurnal)) {
		throw new PDOException("Parameter Jurnal untuk " . $kodeJurnal . " belum ada");
	}

	$inBlok = "'" . implode("','", array_map('addslashes', $noblok)) . "'";

	// Get Jurnal
	$sumX = array();
	$qJurnal = "SELECT kodeblok, SUM(jumlah) as nilai FROM " . $dbname . ".keu_jurnaldt WHERE LEFT(tanggal,7)<='" . $param['periode'] . "' and noakun >= '" . addslashes($paramJurnal['sampaidebet']) . "' and noakun <='" . addslashes($paramJurnal['sampaikredit']) . "' and kodeorg='" . $kodeorg . "' and kodeblok in (" . $inBlok . ") group by kodeblok";
	foreach (fetchData($qJurnal) as $bar) {
		if (!isset($sumX[$bar['kodeblok']])) $sumX[$bar['kodeblok']] = 0;
		$sumX[$bar['kodeblok']] += $bar['nilai'];
	}

	// Get Jurnal sudah dialokasi
	$sumH = array();
	$qJurnal = "SELECT kodeblok, SUM(jumlah) as nilai FROM " . $dbname . ".keu_jurnaldt WHERE LEFT(tanggal,7)<'" . $param['periode'] . "' and noakun = '" . addslashes($paramJurnal['noakunkredit']) . "' and kodeorg='" . $kodeorg . "' and kodeblok in (" . $inBlok . ") group by kodeblok";
	foreach (fetchData($qJurnal) as $bar) {
		if (!isset($sumH[$bar['kodeblok']])) $sumH[$bar['kodeblok']] = 0;
		$sumH[$bar['kodeblok']] += abs($bar['nilai']);
	}

	// Get Saldo Bibit
	$sumM = array();
	$qBibit = "SELECT kodeorg, SUM(jumlah) as nilai FROM " . $dbname . ".bibitan_mutasi WHERE kodeorg in (" . $inBlok . ") and LEFT(tanggal,7)<='" . $param['periode'] . "' group by kodeorg";
	foreach (fetchData($qBibit) as $bar) {
		if (!isset($sumM[$bar['kodeorg']])) $sumM[$bar['kodeorg']] = 0;
		$sumM[$bar['kodeorg']] += $bar['nilai'];
	}

	// Get TPB bulan ini
	$sumN = array();
	$qBibit = "SELECT kodeorg, SUM(jumlah) as nilai FROM " . $dbname . ".bibitan_mutasi WHERE kodeorg in (" . $inBlok . ") and kodetransaksi='TPB' and LEFT(tanggal,7)='" . $param['periode'] . "' group by kodeorg";
	foreach (fetchData($qBibit) as $bar) {
		if (!isset($sumN[$bar['kodeorg']])) $sumN[$bar['kodeorg']] = 0;
		$sumN[$bar['kodeorg']] += abs($bar['nilai']);
	}

	// Harga Rata2
	$sumZ = array();
	$sumY = array();
	$blokNol = array();
	foreach ($noblok as $blok) {
		$sM = isset($sumM[$blok]) ? $sumM[$blok] : 0;
		$sN = isset($sumN[$blok]) ? $sumN[$blok] : 0;
		$sX = isset($sumX[$blok]) ? $sumX[$blok] : 0;
		$sH = isset($sumH[$blok]) ? $sumH[$blok] : 0;
		$sumY[$blok] = $sM + $sN;
		if ($sumY[$blok] != 0) {
			$sumZ[$blok] = ($sX - $sH) / $sumY[$blok];
		} else {
			$sumZ[$blok] = 0;
			$blokNol[] = $blok; #PPP: jumlah bibit nol -> harga rata-rata tidak bisa dihitung (dulu INF/pembagian nol)
		}
	}

	#perhitungan rupiah per kombinasi blok asal - tujuan (rumus sama dengan file asli)
	$biaya = 0;
	$biayadt = array();
	$barisHit = array();
	foreach ($nobloktujuan as $blok => $v1) {
		foreach ($v1 as $tujuan) {
			$jml = abs($subttl[$blok][$tujuan]);
			$rupiahHit = round($jml * $sumZ[$blok]);
			$biaya += $rupiahHit;
			if (!isset($biayadt[$blok][$tujuan])) $biayadt[$blok][$tujuan] = 0;
			$biayadt[$blok][$tujuan] += $rupiahHit;
			$barisHit[] = array($blok, $tujuan, $jml, $sumZ[$blok], $jml * $sumZ[$blok]);
		}
	}

	if ($proses == 'prosesjurnal' && !empty($blokNol)) {
		exit("Error: Jumlah bibit blok " . implode(', ', $blokNol) . " nol (saldo bibit + TPB bulan ini), harga rata-rata tidak bisa dihitung. Periksa mutasi bibit blok tersebut.");
	}

	switch ($proses) {
		default:
		case 'list':
			$rpF = function ($v) {
				return number_format($v, 0);
			};
			$tab = "
				<fieldset style='margin-bottom:8px'><legend><b>Informasi dan Rumusan Proses</b></legend>
				<div style='display:flex;flex-wrap:wrap;align-items:stretch;gap:6px;font-size:12px'>
					<div style='flex:1 1 230px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>1. Ambil transplanting (TPB)</b><br>Mutasi bibit <b>TPB</b> (pindah dari pre nursery ke main nursery) unit " . htmlspecialchars($kodeorg) . " periode " . htmlspecialchars($param['periode']) . ", sudah diposting semua. Ada <b>" . count($barisTpb) . "</b> transaksi, total <b>" . number_format($total) . "</b> bibit.</div>
					<div style='align-self:center;font-size:18px'>&rarr;</div>
					<div style='flex:1 1 330px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>2. Hitung harga rata-rata (Rp/Sat) per blok asal</b><br>
						Rp/Sat = (<b>Biaya</b> &minus; <b>Sudah dialokasi</b>) &divide; <b>Jumlah bibit</b>.<br>
						&bull; <b>Biaya</b> = jumlah jurnal akun " . htmlspecialchars($paramJurnal['sampaidebet']) . " s/d " . htmlspecialchars($paramJurnal['sampaikredit']) . " blok itu sampai periode " . htmlspecialchars($param['periode']) . ".<br>
						&bull; <b>Sudah dialokasi</b> = jurnal akun kredit " . htmlspecialchars($paramJurnal['noakunkredit']) . " blok itu pada periode sebelumnya.<br>
						&bull; <b>Jumlah bibit</b> = saldo bibit blok sampai periode ini + TPB bulan ini (saldo sebelum TPB).</div>
					<div style='align-self:center;font-size:18px'>&rarr;</div>
					<div style='flex:1 1 260px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>3. Buat jurnal TPB</b><br><b>Rupiah</b> = jumlah transplanting &times; Rp/Sat (dibulatkan). Satu jurnal <b>TPB</b>: <b>Debet</b> akun " . htmlspecialchars($paramJurnal['noakundebet']) . " blok tujuan, <b>Kredit</b> akun " . htmlspecialchars($paramJurnal['noakunkredit']) . " blok asal. Jurnal dibuat belum diposting.</div>
				</div>
				<div style='font-size:12px;margin-top:6px;line-height:1.5'>
					&bull; Total biaya yang dialokasikan <b>Rp " . $rpF($biaya) . "</b>.<br>
					<span style='background:#fff3cd;padding:1px 6px;border:1px solid #e0c36a'><b>Perhatian:</b> Process ulang menghapus jurnal TPB unit ini pada periode itu lalu membuatnya lagi.</span>
				</div>";
			if (!empty($blokNol)) {
				$tab .= "<div style='font-size:12px;margin-top:4px;color:#b00020'><b>Perhatian:</b> Jumlah bibit blok " . htmlspecialchars(implode(', ', $blokNol)) . " nol, harga rata-rata tidak bisa dihitung. Process dinonaktifkan; periksa mutasi bibit blok tersebut.</div>";
			}
			$tab .= "</fieldset>
				<style>
					.tombolBibitPPP { display:inline-block; padding:7px 22px; margin:0 8px 8px 0; border-radius:6px; font-size:13px; font-weight:bold; cursor:pointer; }
					#btnproses.tombolBibitPPP { background:#2e7d32; color:#fff; border:1px solid #1b5e20; }
					#btnproses.tombolBibitPPP:hover { background:#388e3c; }
					#btnproses.tombolBibitPPP:disabled { background:#9e9e9e; border-color:#757575; color:#eee; cursor:not-allowed; }
					#btnexcel.tombolBibitPPP { background:#fff; color:#2e7d32; border:1px solid #2e7d32; }
					#btnexcel.tombolBibitPPP:hover { background:#e8f5e9; }
				</style>
				<div>
					<button class='tombolBibitPPP' id=btnproses title='Buat jurnal TPB' " . (!empty($blokNol) ? "disabled" : "onclick=savemutasibibit()") . ">&#9654; Process</button>
					<button class='tombolBibitPPP' onclick=exportTableToExcel() id=btnexcel title='Unduh tabel ini ke Excel'>&#11015; Excel</button>
				</div>
				<table class=sortable cellpadding=5 cellspacing=1 border=0 id=mytable><tbody>
				<tr class=rowheader><th colspan=12 align=left>A. Transaksi transplanting (TPB) periode " . htmlspecialchars($param['periode']) . "</th></tr>
				<tr class=rowheader><th>No</th><th>Batch</th><th colspan=2>Dari</th><th colspan=2>Tujuan</th><th>Tanggal</th><th>Jumlah Bibit</th><th colspan=4></th></tr>";
			$no = 0;
			foreach ($barisTpb as $bar) {
				$no++;
				$tab .= "<tr class=rowcontent id='row" . $no . "'>
					<td align=center>" . $no . "</td>
					<td>" . htmlspecialchars($bar['batch']) . "</td>
					<td colspan=2>" . htmlspecialchars($bar['kodeorg']) . "</td>
					<td colspan=2>" . htmlspecialchars($bar['tujuan']) . "</td>
					<td>" . htmlspecialchars($bar['tanggal']) . "</td>
					<td align=right>" . number_format(abs($bar['jumlah'])) . "</td>
					<td colspan=4></td>
				</tr>";
			}
			$tab .= "<tr class=rowcontent style='font-weight:bold'><td colspan=7>Total</td><td align=right>" . number_format($total) . "</td><td colspan=4></td></tr>
				<tr class=rowheader><th colspan=12 align=left>B. Perhitungan alokasi biaya bibit per blok asal dan tujuan</th></tr>
				<tr class=rowheader><th>No</th><th>Dari</th><th>Tujuan</th><th>Akun Debet</th><th>Akun Kredit</th><th>Biaya s.d. Periode (Rp)</th><th>Sudah Dialokasi (Rp)</th><th>Biaya Belum Dialokasi (Rp)</th><th>Jumlah Bibit</th><th>Jumlah Transplanting</th><th>Rp/Sat</th><th>Rupiah</th></tr>";
			$no = 0;
			$ttlRp = 0;
			foreach ($nobloktujuan as $blok => $v1) {
				foreach ($v1 as $tujuan) {
					$no++;
					$sX = isset($sumX[$blok]) ? $sumX[$blok] : 0;
					$sH = isset($sumH[$blok]) ? $sumH[$blok] : 0;
					$jml = abs($subttl[$blok][$tujuan]);
					$rp = $jml * $sumZ[$blok];
					$ttlRp += $rp;
					$tab .= "<tr class=rowcontent>
						<td align=center>" . $no . "</td>
						<td id='kodeorg" . $no . "'>" . htmlspecialchars($blok) . "</td>
						<td id='tujuan" . $no . "'>" . htmlspecialchars($tujuan) . "</td>
						<td id='debet" . $no . "'>" . htmlspecialchars($paramJurnal['noakundebet']) . " - " . htmlspecialchars(getNamaAkun($paramJurnal['noakundebet'])) . "</td>
						<td id='kredit" . $no . "'>" . htmlspecialchars($paramJurnal['noakunkredit']) . " - " . htmlspecialchars(getNamaAkun($paramJurnal['noakunkredit'])) . "</td>
						<td align=right>" . $rpF($sX) . "</td>
						<td align=right>" . $rpF($sH) . "</td>
						<td align=right>" . $rpF($sX - $sH) . "</td>
						<td align=right>" . number_format($sumY[$blok]) . "</td>
						<td align=right id='jumlah" . $no . "'>" . $jml . "</td>
						<td align=right>" . number_format($sumZ[$blok], 2) . "</td>
						<td align=right id='rupiah" . $no . "'>" . $rpF($rp) . "</td>
					</tr>";
				}
			}
			$tab .= "<tr class=rowcontent style='font-weight:bold'><td colspan=11>Total</td><td align=right>" . $rpF($ttlRp) . "</td></tr>
				</tbody></table>";
			echo $tab;
			break;
		case 'prosesjurnal':
			include_once('lib/zJournal.php');
			$cJournal = new zJournal();

			// Get Kelompok Jurnal
			$sql = "SELECT nokounter FROM " . $dbname . ".keu_5kelompokjurnal WHERE kodeunit='" . $kodeorg . "' and periode='" . $param['periode'] . "' and kodekelompok ='" . $kodeJurnal . "'";
			$resJurnal = fetchData($sql);
			if (empty($resJurnal)) {
				throw new PDOException("Counter Jurnal belum ada.");
			}
			$counter = $resJurnal[0]['nokounter'];
			$nojurnal = $cJournal->genNoJournal($tanggal, substr($kodeorg, 0, 4), $kodeJurnal, $counter);

			$defSegment = colDefaultValue($dbname, 'keu_5segment', 'kodesegment');

			$query = "delete from " . $dbname . ".keu_jurnalht where nojurnal like '" . substr($nojurnal, 0, 18) . "%' and tanggal='" . $tanggal . "' and nojurnal like '%/" . $kodeorg . "/%' and nojurnal like '%/" . $kodeJurnal . "/%' and kodejurnal='" . $kodeJurnal . "'";
			$owlPDO->exec($query);

			// Jurnal Header
			$dataRes = array('header' => array(), 'detail' => array());
			$dataRes['header'] = array(
				'nojurnal'     => $nojurnal,
				'kodejurnal'   => $kodeJurnal,
				'tanggal'      => $tanggal,
				'tanggalentry' => date('Ymd'),
				'posting'      => '0',
				'totaldebet'   => $biaya,
				'totalkredit'  => $biaya,
				'amountkoreksi' => '0',
				'noreferensi'  => null,
				'autojurnal'   => '1',
				'matauang'     => 'IDR',
				'kurs'         => '1',
				'revisi'       => '0'
			);

			$no = 0;
			foreach ($nobloktujuan as $blok => $v1) {
				foreach ($v1 as $tujuan) {
					#debet
					$no++;
					$dataRes['detail'][] = array(
						'nojurnal'    => $nojurnal,
						'tanggal'     => $tanggal,
						'nourut'      => $no,
						'noakun'      => $paramJurnal['noakundebet'],
						'keterangan'  => 'Transplanting ' . abs($subttl[$blok][$tujuan]) . ' bibit dari ' . $blok . " ke " . $tujuan,
						'jumlah'      => $biayadt[$blok][$tujuan],
						'matauang'    => 'IDR',
						'kurs'        => '1',
						'kodeorg'     => substr($blok, 0, 4),
						'kodekegiatan' => '',
						'kodeasset'   => '',
						'kodebarang'  => '',
						'nik'         => '',
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => $nobatch[$blok][$tujuan],
						'noaruskas'   => '',
						'kodevhc'     => '',
						'nodok'       => $nobatch[$blok][$tujuan],
						'kodeblok'    => $tujuan,
						'revisi'      => '0',
						'kodesegment' => $defSegment
					);

					#kredit
					$no++;
					$dataRes['detail'][] = array(
						'nojurnal'    => $nojurnal,
						'tanggal'     => $tanggal,
						'nourut'      => $no,
						'noakun'      => $paramJurnal['noakunkredit'],
						'keterangan'  => 'Transplanting ' . abs($subttl[$blok][$tujuan]) . ' bibit dari ' . $blok . " ke " . $tujuan,
						'jumlah'      => ($biayadt[$blok][$tujuan]) * (-1),
						'matauang'    => 'IDR',
						'kurs'        => '1',
						'kodeorg'     => substr($blok, 0, 4),
						'kodekegiatan' => '',
						'kodeasset'   => '',
						'kodebarang'  => '',
						'nik'         => '',
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => $nobatch[$blok][$tujuan],
						'noaruskas'   => '',
						'kodevhc'     => '',
						'nodok'       => $nobatch[$blok][$tujuan],
						'kodeblok'    => $blok,
						'revisi'      => '0',
						'kodesegment' => $defSegment
					);
				}
			}

			$qHeader = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
			$owlPDO->exec($qHeader);

			foreach ($dataRes['detail'] as $key => $dataDet) {
				$queryD = insertQuery($dbname, 'keu_jurnaldt', $dataDet);
				$owlPDO->exec($queryD);
			}

			$queryKonter = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $counter + 1), "kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $kodeorg . "' and periode='" . $param['periode'] . "'");
			$owlPDO->exec($queryKonter);

			#PPP: catatan nojurnal pada keterangan mutasi bibit diganti (bukan ditambah) saat proses ulang, dan keterangan di-escape
			$str = "select * from " . $dbname . ".bibitan_mutasi where kodeorg like '" . $kodeorg . "%' and tanggal>='" . $tgmulai . "' and tanggal<='" . $tgsampai . "' and kodetransaksi='TPB'";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$ketDasar = preg_replace('/(, nojurnal : [^,]*)+$/', '', $bar['keterangan']);
				$queryU = "update " . $dbname . ".bibitan_mutasi set keterangan='" . addslashes($ketDasar . ", nojurnal : " . $nojurnal) . "' where kodetransaksi='" . addslashes($bar['kodetransaksi']) . "' and batch='" . addslashes($bar['batch']) . "' and kodeorg='" . addslashes($bar['kodeorg']) . "' and tujuan='" . addslashes($bar['tujuan']) . "' and post='1' and tanggal='" . addslashes($bar['tanggal']) . "'";
				$owlPDO->exec($queryU);
			}

			echo $nojurnal;
			break;
	}
	#execute
	$owlPDO->commit();
} catch (PDOException $e) {
	$owlPDO->rollback();
	echo "Errorcode, " . addslashes($e->getMessage());
	die();
}
