<?php
# CABANG PPP dari keu_slave_3bibitalokasi.php (Proses Akhir Bulan > 10. Alokasi bibit ke Lapangan). Dipanggil otomatis oleh js/keu_3posting.js
# (tujuanProses) hanya untuk unit yang induknya PPP; unit lain tetap memakai keu_slave_3bibitalokasi.php. Perubahan khusus PPP tandai "#PPP".
# Rumus harga rata-rata dan isi jurnal sama dengan file asli. Perbedaan: rupiah, akun debet/kredit dihitung server (bukan dikirim browser),
# periode akuntansi dicek, akun interco kosong ditolak, counter jurnal M dibetulkan, dropdown kegiatan untuk blok TB diperbaiki,
# penyebut nol ditolak, input divalidasi, tampilan dan rumusan dirapikan.
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');
require_once('lib/zAlokasiGajiPPP.php');

$param     = $_POST;
$proses    = checkPostGet('proses', '');
#PPP: parameter divalidasi sebelum dipakai di query
$pesanParam = validasiParamBibitAlokasiPPP($param, $proses);
if ($pesanParam != '') {
	exit($pesanParam);
}
$kodeorg   = isset($param['kodeorg']) ? $param['kodeorg'] : '';
$induk     = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');

switch ($proses) {
	default:
	case 'list':
		$hs = hitungBibitAlokasiPPP($kodeorg, $param['periode']);
		if ($hs['pesan'] != '') {
			exit($hs['pesan']);
		}
		$rpF = function ($v) {
			return number_format($v, 0);
		};
		$arrjenis = array('TB' => 'Tanam Baru', 'SISIP' => 'Sisip', 'SULAM' => 'Sulam');

		#pilihan kegiatan: kelompok TBM (126...) dan TM (621...), dikelompokkan per akun
		$opsiKeg = array('TBM' => array(), 'TM' => array());
		$namaAkunCache = array();
		foreach (fetchdata("select * from " . $dbname . ".setup_kegiatan where namakegiatan not like '%NON AKTIF%' and (noakun like '621%' or noakun like '126%') order by kodekegiatan") as $bar) {
			$d = substr($bar['kodekegiatan'], 0, 3);
			$kel = ($d == '126') ? 'TBM' : (($d == '621') ? 'TM' : '');
			if ($kel == '') {
				continue;
			}
			$e = substr($bar['kodekegiatan'], 0, 7);
			$opsiKeg[$kel][$e][] = "<option value='" . htmlspecialchars($bar['kodekegiatan'], ENT_QUOTES) . "'>" . htmlspecialchars($bar['kodekegiatan'] . " - " . $bar['namakegiatan']) . "</option>";
		}
		$bangunOpsi = function ($kel) use ($opsiKeg, &$namaAkunCache) {
			$o = '';
			foreach ($opsiKeg[$kel] as $e => $daftar) {
				if (!isset($namaAkunCache[$e])) {
					$namaAkunCache[$e] = getNamaAkun($e);
				}
				$o .= "<optgroup label='" . htmlspecialchars($e . " - " . $namaAkunCache[$e], ENT_QUOTES) . "'>" . implode('', $daftar) . "</optgroup>";
			}
			return $o;
		};
		$pilih = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$opsiTBM = $pilih . $bangunOpsi('TBM');
		$opsiTM = $pilih . $bangunOpsi('TM');
		$opsiSemua = $pilih . $bangunOpsi('TBM') . $bangunOpsi('TM');

		$tab = "
			<fieldset style='margin-bottom:8px'><legend><b>Informasi dan Rumusan Proses</b></legend>
			<div style='display:flex;flex-wrap:wrap;align-items:stretch;gap:6px;font-size:12px'>
				<div style='flex:1 1 220px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>1. Ambil penanaman bibit (PNB)</b><br>Mutasi bibit <b>PNB</b> unit " . htmlspecialchars($kodeorg) . " periode " . htmlspecialchars($param['periode']) . " (bibit ditanam ke lapangan), sudah diposting semua. Ada <b>" . count($hs['transaksi']) . "</b> transaksi, total <b>" . number_format($hs['total']) . "</b> bibit.</div>
				<div style='align-self:center;font-size:18px'>&rarr;</div>
				<div style='flex:1 1 340px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>2. Hitung harga rata-rata (Rp/Sat) per blok asal dan jenis tanam</b><br>
					Rp/Sat = (<b>Biaya</b> &minus; <b>Sudah dialokasi</b>) &divide; <b>Jumlah bibit</b>.<br>
					&bull; <b>Biaya</b> = jumlah jurnal akun pembibitan (rentang akun di Parameter Jurnal PNB sesuai jenis tanam) blok itu sampai periode ini.<br>
					&bull; <b>Sudah dialokasi</b> = jurnal akun kredit parameter blok itu pada periode sebelumnya.<br>
					&bull; <b>Jumlah bibit</b> = saldo bibit blok sampai periode ini + bibit ditanam bulan ini.</div>
				<div style='align-self:center;font-size:18px'>&rarr;</div>
				<div style='flex:1 1 260px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>3. Pilih kegiatan, buat jurnal PNB</b><br><b>Rupiah</b> = jumlah tanam &times; Rp/Sat. <b>Debet</b> akun kegiatan yang Anda pilih (126xxxx untuk TB/TBM, 621xxxx untuk TM) di blok tujuan, <b>Kredit</b> akun pembibitan blok asal. Beda unit: lewat akun <b>interco/intraco</b> (jurnal M). Jurnal dibuat belum diposting.</div>
			</div>
			<div style='font-size:12px;margin-top:6px;line-height:1.5'>
				&bull; Pilih <b>Kegiatan</b> di setiap baris sebelum Process. Akun debet mengikuti kegiatan itu.<br>
				&bull; Rupiah dan akun kredit dihitung ulang oleh server saat Process, jadi tidak tergantung angka yang tampil di layar.<br>
				<span style='background:#fff3cd;padding:1px 6px;border:1px solid #e0c36a'><b>Perhatian:</b> Process ulang menghapus jurnal alokasi bibit unit ini pada periode itu lalu membuatnya lagi. Kalau periode akuntansi sudah tutup buku, prosesnya ditolak.</span>
			</div>";
		if (!empty($hs['blokNol'])) {
			$tab .= "<div style='font-size:12px;margin-top:4px;color:#b00020'><b>Perhatian:</b> Jumlah bibit blok " . htmlspecialchars(implode(', ', $hs['blokNol'])) . " nol, harga rata-rata tidak bisa dihitung. Process dinonaktifkan; periksa mutasi bibit blok tersebut.</div>";
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
				<button class='tombolBibitPPP' id=btnproses title='Buat jurnal PNB untuk semua baris di bawah' " . ((!empty($hs['blokNol']) || empty($hs['baris'])) ? "disabled" : "onclick=savemutasibibittanam('1','" . count($hs['baris']) . "')") . ">&#9654; Process</button>
				<button class='tombolBibitPPP' onclick=exportTableToExcel() id=btnexcel title='Unduh tabel ini ke Excel'>&#11015; Excel</button>
			</div>
			<table class=sortable cellpadding=5 cellspacing=1 border=0 id=mytable><tbody>
			<tr class=rowheader><th colspan=15 align=left>A. Transaksi penanaman bibit (PNB) periode " . htmlspecialchars($param['periode']) . "</th></tr>
			<tr class=rowheader><th>No</th><th>Batch</th><th colspan=2>Dari</th><th colspan=2>Tujuan</th><th colspan=2>Nama Tujuan</th><th>Jenis Tanam</th><th>Tanggal</th><th>Jumlah Bibit</th><th colspan=4></th></tr>";
		$no = 0;
		foreach ($hs['transaksi'] as $bar) {
			$no++;
			$tab .= "<tr class=rowcontent id='row" . $no . "'>
				<td align=center>" . $no . "</td>
				<td>" . htmlspecialchars($bar['batch']) . "</td>
				<td colspan=2>" . htmlspecialchars($bar['kodeorg']) . "</td>
				<td colspan=2>" . htmlspecialchars($bar['afdeling']) . "</td>
				<td colspan=2>" . htmlspecialchars(getNamaOrg($bar['afdeling'])) . "</td>
				<td>" . htmlspecialchars(isset($arrjenis[$bar['jenistanam']]) ? $arrjenis[$bar['jenistanam']] : $bar['jenistanam']) . "</td>
				<td>" . htmlspecialchars($bar['tanggal']) . "</td>
				<td align=right>" . number_format(abs($bar['jumlah'])) . "</td>
				<td colspan=4></td>
			</tr>";
		}
		$tab .= "<tr class=rowcontent style='font-weight:bold'><td colspan=10>Total</td><td align=right>" . number_format($hs['total']) . "</td><td colspan=4></td></tr>
			<tr class=rowheader><th colspan=15 align=left>B. Perhitungan alokasi biaya bibit per blok asal, tujuan dan jenis tanam</th></tr>
			<tr class=rowheader><th>No</th><th>Dari</th><th>Tujuan</th><th>Jenis Tanam</th><th class=kolom-sembunyi>Kegiatan</th><th>Akun Debet</th><th>Akun Kredit</th><th>Biaya s.d. Periode (Rp)</th><th>Sudah Dialokasi (Rp)</th><th>Biaya Belum Dialokasi (Rp)</th><th>Jumlah Bibit</th><th>Jumlah Tanam</th><th>Rp/Sat</th><th>Rupiah</th><th>Jurnal</th></tr>";
		$ttlRp = 0;
		foreach ($hs['baris'] as $b) {
			$no = $b['no'];
			$sts = getBlok($b['tujuan'], 'statusblok');
			if ($sts == 'TB' || $sts == 'TBM') {
				$opsi = $opsiTBM;
			} else if ($sts == 'TM') {
				$opsi = $opsiTM;
			} else {
				$opsi = $opsiSemua; #status blok lain (mis. BBT): semua kegiatan TBM dan TM, tanpa pembatasan akun di proses
			}
			$ttlRp += $b['rupiah'];
			$tab .= "<tr class=rowcontent id=baris" . $no . ">
				<td align=center>" . $no . "</td>
				<td id='kodeorg" . $no . "'>" . htmlspecialchars($b['blok']) . "</td>
				<td id='tujuan" . $no . "'>" . htmlspecialchars($b['tujuan']) . "</td>
				<td id='jenis" . $no . "'>" . htmlspecialchars($b['jenis']) . "</td>
				<td class=kolom-sembunyi><select style=width:250px onchange=getnoakun(" . $no . "); id='kegiatan" . $no . "'>" . $opsi . "</select></td>
				<td id=tempdebet" . $no . "></td>
				<td id='kredit" . $no . "'>" . htmlspecialchars($b['kredit']) . "</td>
				<td align=right>" . $rpF($b['sumX']) . "</td>
				<td align=right>" . $rpF($b['sumH']) . "</td>
				<td align=right>" . $rpF($b['sumX'] - $b['sumH']) . "</td>
				<td align=right>" . number_format($b['sumY']) . "</td>
				<td align=right id='jumlah" . $no . "'>" . $b['jumlah'] . "</td>
				<td align=right>" . number_format($b['sumZ'], 2) . "</td>
				<td align=right id='rupiah" . $no . "'>" . $rpF($b['rupiah']) . "</td>
				<td id='jurnal" . $no . "'></td>
				<td hidden class=kolom-sembunyi><input hidden id='debet" . $no . "'></td>
			</tr>";
		}
		$tab .= "<tr class=rowcontent style='font-weight:bold'><td colspan=13>Total</td><td align=right>" . $rpF($ttlRp) . "</td><td></td></tr>
			</tbody></table>";

		echo $tab;
		break;

	case 'getnoakun':
		$optakun = '';
		$namaakun = '';
		$str = "select * from " . $dbname . ".setup_kegiatan where kodekegiatan='" . addslashes($param['kegiatan']) . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$optakun = $bar['noakun'];
			$namaakun = $bar['noakun'] . " - " . getNamaAkun($bar['noakun']);
		}

		echo $optakun . "####" . $namaakun;
		break;

	case 'prosesjurnal':
		try {
			$owlPDO->beginTransaction();

			#PPP: rupiah, jumlah dan akun dihitung ulang di server dari data (kiriman browser selain blok asal, blok tujuan, jenis tanam dan kegiatan tidak dipakai)
			$hs = hitungBibitAlokasiPPP($kodeorg, $param['periode']);
			if ($hs['pesan'] != '') {
				exit($hs['pesan']);
			}
			$barisIni = null;
			foreach ($hs['baris'] as $bPPP) {
				if ($bPPP['blok'] == $param['blokbbt'] && $bPPP['tujuan'] == $param['bloktnm'] && $bPPP['jenis'] == $param['jenis']) {
					$barisIni = $bPPP;
					break;
				}
			}
			if ($barisIni === null) {
				exit("Error: Kombinasi blok asal " . $param['blokbbt'] . ", tujuan " . $param['bloktnm'] . ", jenis tanam " . $param['jenis'] . " tidak ada dalam daftar alokasi periode " . $param['periode']);
			}
			if (isset($hs['blokNol'][$barisIni['blok']])) {
				exit("Error: Jumlah bibit blok " . $barisIni['blok'] . " nol, harga rata-rata tidak bisa dihitung. Periksa mutasi bibit blok tersebut.");
			}
			$resKeg = fetchdata("select noakun from " . $dbname . ".setup_kegiatan where kodekegiatan='" . addslashes($param['kegiatan']) . "'");
			if (empty($resKeg) || $resKeg[0]['noakun'] == '') {
				exit("Error: Kegiatan " . $param['kegiatan'] . " tidak ditemukan atau tidak punya akun.");
			}
			$param['debet'] = $resKeg[0]['noakun'];
			$param['kredit'] = $barisIni['kredit'];
			$param['rupiah'] = $barisIni['rupiah'];
			$param['jumlah'] = $barisIni['jumlah'];

			$biaya = str_replace(",", "", $param['rupiah']);
			$param['jumlah'] = str_replace(",", "", $param['jumlah']);
			$kodeJurnal  = 'PNB';

			$noreff    = "ALK_" . $kodeJurnal . "_" . $param['kodeorg'] . "_" . str_replace("-", "", $param['periode']);
			$bibitan   = substr(getNamaOrg($param['blokbbt'], 'induk'), 0, 4);
			$kebun     = substr(getNamaOrg($param['bloktnm'], 'induk'), 0, 4);
			$ptkebun   = getNamaOrg($kebun, 'induk');
			$ptbibitan = getNamaOrg($bibitan, 'induk');
			$segment   = '0000000001';
			$kodeorg = $kebun;
			$param['kodeorg'] = $kebun;

			$kodeTrans   = $param['jenis'];

			if ($param['bloktnm'] == '') {
				throw new PDOException("Blok tujuan tidak boleh kosong.");
			}
			if ($kebun == '') {
				throw new PDOException("Kode organisasi tujuan tidak boleh kosong.");
			}

			#PPP: periode akuntansi unit tujuan (dan unit bibitan bila beda) harus terdaftar dan belum tutup buku
			$tgmulai = '';
			$tgsampai = '';
			$periode = '';
			$tanggal = '';
			$str = "select * from " . $dbname . ".setup_periodeakuntansi where kodeorg ='" . addslashes($kodeorg) . "' and tutupbuku=0 and periode='" . $param['periode'] . "' ";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$tgsampai = $bar['tanggalsampai'];
				$tgmulai = $bar['tanggalmulai'];
				$periode = $bar['periode'];
				$tanggal = $bar['tanggalsampai'];
			}
			if ($tanggal == '') {
				throw new PDOException("Periode akuntansi " . $param['periode'] . " unit " . $kodeorg . " belum terdaftar atau sudah ditutup.");
			}
			if ($bibitan != $kebun) {
				$resPb = fetchdata("select periode from " . $dbname . ".setup_periodeakuntansi where kodeorg ='" . addslashes($bibitan) . "' and tutupbuku=0 and periode='" . $param['periode'] . "' ");
				if (count($resPb) < 1) {
					throw new PDOException("Periode akuntansi " . $param['periode'] . " unit " . $bibitan . " belum terdaftar atau sudah ditutup.");
				}
			}

			include_once('lib/zJournal.php');
			$cJournal = new zJournal();

			// Get Kelompok Jurnal
			$sql = "SELECT nokounter FROM " . $dbname . ".keu_5kelompokjurnal WHERE kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' and kodekelompok ='" . $kodeJurnal . "'";
			$resJurnal = fetchData($sql);
			if (empty($resJurnal)) {
				throw new PDOException("Counter Jurnal " . $kodeJurnal . " belum ada.");
			}
			$counter = $resJurnal[0]['nokounter'];
			$nojurnal = $cJournal->genNoJournal($tanggal, substr($param['kodeorg'], 0, 4), $kodeJurnal, $counter);

			$defSegment = colDefaultValue($dbname, 'keu_5segment', 'kodesegment');

			if ($param['kredit'] == '') {
				throw new PDOException("Akun kredit tidak boleh kosong.");
			}
			if ($param['debet'] == '') {
				throw new PDOException("Akun debet tidak boleh kosong.");
			}

			$kelakun = substr($param['debet'], 0, 3);
			$status  = getBlok($param['bloktnm'], 'statusblok');
			if ($status == 'TB' and $kelakun != '126') {
				throw new PDOException("Status Blok TB, noakun harus 126xxxx.");
			}
			if ($status == 'TBM' and $kelakun != '126') {
				throw new PDOException("Status Blok TBM, noakun harus 126xxxx.");
			}
			if ($status == 'TM' and $kelakun != '621') {
				throw new PDOException("Status Blok TM, noakun harus 621xxxx.");
			}

			if ($param['currRow'] == '1') {
				$query = "delete from " . $dbname . ".keu_jurnalht where noreferensi ='" . $noreff . "' and tanggal='" . $tanggal . "'";
				$owlPDO->exec($query);
			}

			$dataRes = array('header' => array(), 'detail' => array());
			$dataResRk = array('header' => array(), 'detail' => array());
			$nojurnal2 = '';
			$noUrut = 0;
			$noUrut2 = 0;

			if ($kebun != $bibitan) {
				if ($ptkebun != $ptbibitan) {
					$jenis = "inter";
				} else {
					$jenis = "intra";
				}

				$aknPt  = makeOption($dbname, 'keu_5caco', 'kodeorg,akunpiutang', "kodeorg='" . addslashes($kebun) . "' and jenis='" . $jenis . "'");
				$aknHtg = makeOption($dbname, 'keu_5caco', 'kodeorg,akunhutang', "kodeorg='" . addslashes($bibitan) . "' and jenis='" . $jenis . "'");
				#PPP: akun interco/intraco harus ada (dulu jurnal dibuat dengan akun kosong)
				if (!isset($aknPt[$kebun]) || $aknPt[$kebun] == '' || !isset($aknHtg[$bibitan]) || $aknHtg[$bibitan] == '') {
					throw new PDOException("Akun " . $jenis . "co piutang unit " . $kebun . " atau hutang unit " . $bibitan . " belum diatur di menu Keuangan - Setup - COA Intra/Interco.");
				}

				$dataRes['header'] = array(
					'nojurnal'     => $nojurnal,
					'kodejurnal'   => $kodeJurnal,
					'tanggal'      => $tanggal,
					'tanggalentry' => date('Ymd'),
					'posting'      => '0',
					'totaldebet'   => $biaya,
					'totalkredit'  => $biaya,
					'amountkoreksi' => '0',
					'noreferensi'  => $noreff,
					'autojurnal'   => '1',
					'matauang'     => 'IDR',
					'kurs'         => '1',
					'revisi'       => '0'
				);
				$noUrut++;
				#debet disisi penerima bibit
				$dataRes['detail'][] = array(
					'nojurnal'    => $nojurnal,
					'tanggal'     => $tanggal,
					'nourut'      => $noUrut,
					'noakun'      => $param['debet'],
					'keterangan'  => 'Alokasi ' . abs($param['jumlah']) . ' bibit dari ' . $param['blokbbt'] . " ke " . $param['bloktnm'],
					'jumlah'      => $biaya,
					'matauang'    => 'IDR',
					'kurs'        => '1',
					'kodeorg'     => $kebun,
					'kodekegiatan' => $param['kegiatan'],
					'kodeasset'   => '',
					'kodebarang'  => '',
					'nik'         => '',
					'kodecustomer' => '',
					'kodesupplier' => '',
					'noreferensi' => $noreff,
					'noaruskas'   => '',
					'kodevhc'     => '',
					'nodok'       => $param['blokbbt'],
					'kodeblok'    => $param['bloktnm'],
					'revisi'      => '0',
					'kodesegment' => $defSegment
				);

				$noUrut++;
				# Detail (Kredit)  disisi penerima bibit
				$dataRes['detail'][] = array(
					'nojurnal'    => $nojurnal,
					'tanggal'     => $tanggal,
					'nourut'      => $noUrut,
					'noakun'      => $aknHtg[$bibitan],
					'keterangan'  => 'Alokasi ' . abs($param['jumlah']) . ' bibit dari ' . $param['blokbbt'] . " ke " . $param['bloktnm'],
					'jumlah'      => $biaya * (-1),
					'matauang'    => 'IDR',
					'kurs'        => '1',
					'kodeorg'     => $kebun,
					'kodekegiatan' => '',
					'kodeasset'   => '',
					'kodebarang'  => '',
					'nik'         => '',
					'kodecustomer' => '',
					'kodesupplier' => '',
					'noreferensi' => $noreff,
					'noaruskas'   => '',
					'kodevhc'     => '',
					'nodok'       => '',
					'kodeblok'    => '',
					'revisi'      => '0',
					'kodesegment' => $segment
				);

				# Get Journal Counter
				$queryJ = selectQuery($dbname, 'keu_5kelompokjurnal', 'nokounter', "kodeorg='" . $ptbibitan . "' and kodekelompok='M' and kodeunit='" . $bibitan . "' and periode='" . $param['periode'] . "'");
				$tmpKonter2 = fetchData($queryJ);
				if (count($tmpKonter2) == 0) {
					throw new PDOException("Kelompok Jurnal M untuk kodeorg " . $ptbibitan . ", kodeunit : " . $bibitan . ", periode : " . $param['periode'] . " silahkan tambah melalui menu : Keuangan - Setup - Kelompok Jurnal.");
				}
				$konter    = addZero($tmpKonter2[0]['nokounter'] + 1, 3);

				# Transform No Jurnal dari No Transaksi
				$tmpNoJurnal = str_replace('-', '', $tanggal);
				$nojurnal2   = $tmpNoJurnal . "/" . $bibitan . "/M/" . $konter;

				#cek apakah nomor ini sudah ada di keu_jurnalht atau belum ???
				$str = "SELECT * FROM " . $dbname . ".`keu_jurnalht` WHERE nojurnal='" . $nojurnal2 . "'";
				$res = fetchdata($str);
				if (count($res) > 0) {
					$str = "select max(convert(substring_index(nojurnal,'/',-1),unsigned integer)) as nomor from keu_jurnalht where kodejurnal='M' and nojurnal like '%" . $bibitan . "%' and tanggal like '" . $param['periode'] . "%'";
					$res = fetchdata($str);
					$konter = addZero($res[0]['nomor'] + 1, 3);
					$nojurnal2 = $tmpNoJurnal . "/" . $bibitan . "/M/" . $konter;
				}

				#1. Data Header RK
				$dataResRk['header'] = array(
					'nojurnal'     => $nojurnal2,
					'kodejurnal'   => 'M',
					'tanggal'      => $tanggal,
					'tanggalentry' => date('Ymd'),
					'posting'      => '1',
					'totaldebet'   => '0',
					'totalkredit'  => '0',
					'amountkoreksi' => '0',
					'noreferensi'  => $noreff,
					'autojurnal'   => '1',
					'matauang'     => 'IDR',
					'kurs'         => '1',
					'revisi'       => '0'
				);

				$noUrut2++;
				$dataResRk['detail'][] = array(
					'nojurnal'    => $nojurnal2,
					'tanggal'     => $tanggal,
					'nourut'      => $noUrut2,
					'noakun'      => $aknPt[$kebun],
					'keterangan'  => 'Alokasi ' . abs($param['jumlah']) . ' bibit dari ' . $param['blokbbt'] . " ke " . $param['bloktnm'],
					'jumlah'      => $biaya,
					'matauang'    => 'IDR',
					'kurs'        => '1',
					'kodeorg'     => $bibitan,
					'kodekegiatan' => '',
					'kodeasset'   => '',
					'kodebarang'  => '',
					'nik'         => '',
					'kodecustomer' => '',
					'kodesupplier' => '',
					'noreferensi' => $noreff,
					'noaruskas'   => '',
					'kodevhc'     => '',
					'nodok'       => '',
					'kodeblok'    => $param['bloktnm'],
					'revisi'      => '0',
					'kodesegment' => $segment
				);

				$noUrut2++;
				$dataResRk['detail'][] = array(
					'nojurnal'    => $nojurnal2,
					'tanggal'     => $tanggal,
					'nourut'      => $noUrut2,
					'noakun'      => $param['kredit'],
					'keterangan'  => 'Alokasi ' . abs($param['jumlah']) . ' bibit dari ' . $param['blokbbt'] . " ke " . $param['bloktnm'],
					'jumlah'      => ($biaya) * (-1),
					'matauang'    => 'IDR',
					'kurs'        => '1',
					'kodeorg'     => $bibitan,
					'kodekegiatan' => '',
					'kodeasset'   => '',
					'kodebarang'  => '',
					'nik'         => '',
					'kodecustomer' => '',
					'kodesupplier' => '',
					'noreferensi' => $noreff,
					'noaruskas'   => '',
					'kodevhc'     => '',
					'nodok'       => $param['bloktnm'],
					'kodeblok'    => $param['blokbbt'],
					'revisi'      => '0',
					'kodesegment' => $defSegment
				);

				$qHeader = insertQuery($dbname, 'keu_jurnalht', $dataResRk['header']);
				$owlPDO->exec($qHeader);

				foreach ($dataResRk['detail'] as $key => $dataDet) {
					$queryD = insertQuery($dbname, 'keu_jurnaldt', $dataDet);
					$owlPDO->exec($queryD);
				}

				#PPP: counter kelompok M diisi dengan nomor M yang baru dipakai (dulu diisi counter PNB + 1)
				$queryKonter = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => intval($konter)), "kodeorg='" . $ptbibitan . "' and kodekelompok='M' and kodeunit='" . $bibitan . "' and periode='" . $param['periode'] . "'");
				$owlPDO->exec($queryKonter);
			} else {

				#BUKAN RK
				// Jurnal Header
				$dataRes['header'] = array(
					'nojurnal'     => $nojurnal,
					'kodejurnal'   => $kodeJurnal,
					'tanggal'      => $tanggal,
					'tanggalentry' => date('Ymd'),
					'posting'      => '0',
					'totaldebet'   => $biaya,
					'totalkredit'  => $biaya,
					'amountkoreksi' => '0',
					'noreferensi'  => $noreff,
					'autojurnal'   => '1',
					'matauang'     => 'IDR',
					'kurs'         => '1',
					'revisi'       => '0'
				);

				$no = 0;
				#debet
				$no++;
				$dataRes['detail'][] = array(
					'nojurnal'    => $nojurnal,
					'tanggal'     => $tanggal,
					'nourut'      => $no,
					'noakun'      => $param['debet'],
					'keterangan'  => 'Alokasi ' . abs($param['jumlah']) . ' bibit dari ' . $param['blokbbt'] . " ke " . $param['bloktnm'],
					'jumlah'      => $biaya,
					'matauang'    => 'IDR',
					'kurs'        => '1',
					'kodeorg'     => $kebun,
					'kodekegiatan' => $param['kegiatan'],
					'kodeasset'   => '',
					'kodebarang'  => '',
					'nik'         => '',
					'kodecustomer' => '',
					'kodesupplier' => '',
					'noreferensi' => $noreff,
					'noaruskas'   => '',
					'kodevhc'     => '',
					'nodok'       => $param['blokbbt'],
					'kodeblok'    => $param['bloktnm'],
					'revisi'      => '0',
					'kodesegment' => $defSegment
				);

				#kredit
				$no++;
				$dataRes['detail'][] = array(
					'nojurnal'    => $nojurnal,
					'tanggal'     => $tanggal,
					'nourut'      => $no,
					'noakun'      => $param['kredit'],
					'keterangan'  => 'Alokasi ' . abs($param['jumlah']) . ' bibit dari ' . $param['blokbbt'] . " ke " . $param['bloktnm'],
					'jumlah'      => ($biaya) * (-1),
					'matauang'    => 'IDR',
					'kurs'        => '1',
					'kodeorg'     => $kebun,
					'kodekegiatan' => '',
					'kodeasset'   => '',
					'kodebarang'  => '',
					'nik'         => '',
					'kodecustomer' => '',
					'kodesupplier' => '',
					'noreferensi' => $noreff,
					'noaruskas'   => '',
					'kodevhc'     => '',
					'nodok'       => $param['bloktnm'],
					'kodeblok'    => $param['blokbbt'],
					'revisi'      => '0',
					'kodesegment' => $defSegment
				);
			} #tutup jika RK

			$qHeader = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
			$owlPDO->exec($qHeader);

			foreach ($dataRes['detail'] as $key => $dataDet) {
				$queryD = insertQuery($dbname, 'keu_jurnaldt', $dataDet);
				$owlPDO->exec($queryD);
			}

			$queryKonter = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $counter + 1), "kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "'");
			$owlPDO->exec($queryKonter);

			if ($nojurnal2 != '') {
				echo $nojurnal2 . ", " . $nojurnal;
			} else {
				echo $nojurnal;
			}
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Errorcode, " . addslashes($e->getMessage());
			die();
		}
		break;
}
