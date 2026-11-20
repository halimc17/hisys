<?php
# CABANG PPP dari keu_slave_3asuransi.php (Proses Akhir Bulan > 7. Transaksi Berulang/Recurring). Dipanggil otomatis oleh js/keu_3posting.js
# (tujuanProses) hanya untuk unit yang induknya PPP; unit lain tetap memakai keu_slave_3asuransi.php. Perubahan khusus PPP tandai "#PPP".
# Daftar transaksi dan jumlah jurnal sama dengan file asli (dihitung oleh hitungTransaksiRutinPPP, fungsi yang sama dengan proses simpan).
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');
require_once('lib/zAlokasiGajiPPP.php');

$proses = isset($_GET['proses']) ? $_GET['proses'] : '';
$param = $_POST;
#PPP: proses, unit dan periode (bulan 01-12) divalidasi sebelum dipakai di query
$pesanParam = validasiParamRutinPPP($param, $proses);
if ($pesanParam != '') {
	exit($pesanParam);
}

$dtRutin = hitungTransaksiRutinPPP($param['kodeorg'], $param['periode']);
$data = $dtRutin['baris'];
$bermasalah = $dtRutin['bermasalah'];

switch ($proses) {
	case 'list':
		$nmAkun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun like '1%' or noakun like '2%' or noakun like '7%' or noakun like '8%'");
		$barisTab = "";
		$no = 0;
		$tTotal = 0;
		foreach ($data as $row) {
			$no++;
			$tTotal += $row['jumlahjurnal'];
			$barisTab .= "<tr class=rowcontent id='row" . $no . "'>
				<td>" . htmlspecialchars($row['notransaksi']) . "</td>
				<td>" . htmlspecialchars($row['kodeorg']) . "</td>
				<td>" . htmlspecialchars($row['keterangan']) . "</td>
				<td>" . tanggalnormal($row['tanggalmulai']) . "</td>
				<td>" . tanggalnormal($row['tanggalselesai']) . "</td>
				<td>" . (($row['tanggalstop'] == '0000-00-00' || $row['tanggalstop'] == '') ? '' : tanggalnormal($row['tanggalstop'])) . "</td>
				<td align=right>" . hidezerodecimal($row['harga_barang'], 2) . "</td>
				<td align=right>" . hidezerodecimal($row['tenor'], 2) . "</td>
				<td align=right>" . hidezerodecimal($row['rpperbulan'], 2) . "</td>
				<td align=right>" . hidezerodecimal($row['perbulan'], 2) . "</td>
				<td align=right>" . hidezerodecimal($row['nilaisisa'], 2) . "</td>
				<td align=right>" . hidezerodecimal($row['jumlahjurnal'], 2) . "</td>
				<td>" . htmlspecialchars($row['noakun_debet'] . (isset($nmAkun[$row['noakun_debet']]) ? ' - ' . $nmAkun[$row['noakun_debet']] : '')) . "</td>
				<td>" . htmlspecialchars($row['noakun_kredit'] . (isset($nmAkun[$row['noakun_kredit']]) ? ' - ' . $nmAkun[$row['noakun_kredit']] : '')) . "</td>
			</tr>";
		}

		$tab = "
			<fieldset style='margin-bottom:8px'><legend><b>Informasi Proses</b></legend>
			<div style='display:flex;flex-wrap:wrap;align-items:stretch;gap:6px;font-size:12px'>
				<div style='flex:1 1 220px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>1. Ambil transaksi berulang</b><br>Transaksi aktif unit " . htmlspecialchars($param['kodeorg']) . " yang masa berlakunya mencakup periode " . htmlspecialchars($param['periode']) . ". Yang sudah dihentikan (stop) tidak ikut.</div>
				<div style='align-self:center;font-size:18px'>&rarr;</div>
				<div style='flex:1 1 230px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>2. Hitung per bulan</b><br>Nilai transaksi dibagi tenor (dibulatkan ke bawah). Pada bulan terakhir ditambah <b>sisa pembulatan</b> supaya total sama dengan nilai transaksi.</div>
				<div style='align-self:center;font-size:18px'>&rarr;</div>
				<div style='flex:1 1 230px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>3. Buat jurnal per transaksi</b><br>Satu jurnal <b>SAS01</b> per transaksi: <b>Debet</b> akun debet transaksi, <b>Kredit</b> akun kredit transaksi.</div>
			</div>
			<div style='font-size:12px;margin-top:6px;line-height:1.5'>
				&bull; Terdapat <b>" . $no . "</b> transaksi dengan total jurnal <b>" . hidezerodecimal($tTotal, 2) . "</b>.<br>
				<span style='background:#fff3cd;padding:1px 6px;border:1px solid #e0c36a'><b>Perhatian:</b> Process ulang untuk periode yang sama menghapus semua jurnal SAS01 unit ini pada periode itu lalu membuatnya lagi. Kalau periode akuntansi sudah tutup buku, tidak ada yang dihapus.</span>
			</div>";
		foreach ($bermasalah as $msg) {
			$tab .= "<div style='font-size:12px;margin-top:4px;color:#b00020'><b>Perhatian:</b> " . htmlspecialchars($msg) . ". Perbaiki dulu di menu Transaksi Berulang; Process dinonaktifkan.</div>";
		}
		$tab .= "</fieldset>
			<style>
				.tombolRutinPPP { display:inline-block; padding:7px 22px; margin:0 8px 8px 0; border-radius:6px; font-size:13px; font-weight:bold; cursor:pointer; }
				#btnproses.tombolRutinPPP { background:#2e7d32; color:#fff; border:1px solid #1b5e20; }
				#btnproses.tombolRutinPPP:hover { background:#388e3c; }
				#btnproses.tombolRutinPPP:disabled { background:#9e9e9e; border-color:#757575; color:#eee; cursor:not-allowed; }
				#btnexcel.tombolRutinPPP { background:#fff; color:#2e7d32; border:1px solid #2e7d32; }
				#btnexcel.tombolRutinPPP:hover { background:#e8f5e9; }
			</style>
			<div>
				<button class='tombolRutinPPP' id=btnproses title='Buat jurnal untuk semua baris di bawah' " . ((empty($data) || !empty($bermasalah)) ? "disabled" : "onclick=postasuransi()") . ">&#9654; Process</button>
				<button class='tombolRutinPPP' onclick=exportTableToExcel() id=btnexcel title='Unduh tabel ini ke Excel'>&#11015; Excel</button>
			</div>
			<table class=sortable cellpadding=5 cellspacing=1 id=mytable><thead><tr class=rowheader>
				<th align=center>" . $_SESSION['lang']['notransaksi'] . "</th>
				<th align=center>" . $_SESSION['lang']['kodeorg'] . "</th>
				<th align=center>" . $_SESSION['lang']['keterangan'] . "</th>
				<th align=center>" . $_SESSION['lang']['tanggalmulai'] . "</th>
				<th align=center>" . $_SESSION['lang']['tanggalselesai'] . "</th>
				<th align=center>" . $_SESSION['lang']['tanggal'] . " Stop</th>
				<th align=center>" . $_SESSION['lang']['rp'] . "</th>
				<th align=center>" . $_SESSION['lang']['tenor'] . "</th>
				<th align=center>" . $_SESSION['lang']['rp'] . "/" . $_SESSION['lang']['bulan'] . "</th>
				<th align=center>" . $_SESSION['lang']['rp'] . "/" . $_SESSION['lang']['bulan'] . "<br>" . $_SESSION['lang']['jurnal'] . "</th>
				<th align=center>" . $_SESSION['lang']['sisa'] . " " . $_SESSION['lang']['pembulatan'] . "</th>
				<th align=center>" . $_SESSION['lang']['jurnal'] . " " . $_SESSION['lang']['periode'] . "<br>" . htmlspecialchars($param['periode']) . "</th>
				<th align=center>Akun Debet</th>
				<th align=center>Akun Kredit</th>
			</tr></thead><tbody>";
		if ($no == 0) {
			$tab .= "<tr class=rowcontent><td colspan=14>" . $_SESSION['lang']['tidakditemukan'] . "</td></tr>";
		} else {
			$tab .= $barisTab . "<tr class=rowcontent>
				<td colspan=11>" . $_SESSION['lang']['total'] . "</td>
				<td align=right>" . hidezerodecimal($tTotal, 2) . "</td>
				<td colspan=2></td>
			</tr>";
		}
		$tab .= "</tbody></table>";
		echo $tab;
		break;

	case 'post':
		#PPP: tidak ada transaksi aktif / ada transaksi bermasalah -> tidak ada yang dihapus
		if (!empty($bermasalah)) {
			exit('Warning: ' . implode('; ', $bermasalah) . '. Tidak ada jurnal yang diproses.');
		}
		if (empty($data)) {
			exit('Warning: Tidak ada transaksi berulang aktif pada periode ' . $param['periode'] . ', tidak ada yang diproses.');
		}
		#PPP: periode akuntansi diperiksa SEBELUM menghapus jurnal lama
		$resPer = fetchData("select periode from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $param['kodeorg'] . "' and tutupbuku=0 and periode='" . $param['periode'] . "'");
		if (count($resPer) < 1) {
			exit("Error: Accounting has closed transaction of  " . $param['periode']);
		}

		$tglJurnl = $param['periode'] . "-28";
		$dtTgl = explode("-", $param['periode']);
		$awalJrn = $dtTgl[0] . $dtTgl[1] . "28";
		$awal = 1;
		$kodejurnal = "SAS01";

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

			// bersihkan all jurnal SAS bulan ini
			$jalankanPPP("delete from " . $dbname . ".keu_jurnalht where nojurnal like '%" . $param['kodeorg'] . "/" . $kodejurnal . "%' and tanggal='" . $tglJurnl . "' and kodejurnal = '" . $kodejurnal . "'  ");

			foreach ($data as $row) {
				$notrans = $awalJrn . "/" . $param['kodeorg'] . "/" . $kodejurnal . "/" . addZero($awal, 3);
				$jumlah = $row['jumlahjurnal'];
				$ket = addslashes($row['keterangan']);
				$noTr = addslashes($row['notransaksi']);

				$jalankanPPP("insert into " . $dbname . ".keu_jurnalht (nojurnal,kodejurnal,totaldebet,totalkredit,tanggal,tanggalentry, autojurnal,noreferensi,matauang,kurs) values ('" . $notrans . "','" . $kodejurnal . "','" . $jumlah . "','" . -($jumlah) . "','" . $tglJurnl . "','" . date('Ymd') . "','1','" . $noTr . "','IDR','1')");

				$jalankanPPP("insert into " . $dbname . ".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,kodevhc,kodesupplier,nodok)
					values ('" . $notrans . "','" . $tglJurnl . "','1','" . addslashes($row['noakun_debet']) . "','Jurnal Otomatis " . $ket . "','" . $jumlah . "','IDR','1','" . $param['kodeorg'] . "','" . $noTr . "','" . addslashes($row['kodevhc']) . "','" . addslashes($row['supplierid']) . "','" . $noTr . "')");

				$jalankanPPP("insert into " . $dbname . ".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,kodevhc,kodesupplier,nodok)
					values ('" . $notrans . "','" . $tglJurnl . "','2','" . addslashes($row['noakun_kredit']) . "','Jurnal Otomatis " . $ket . "','" . -($jumlah) . "','IDR','1','" . $param['kodeorg'] . "','" . $noTr . "','" . addslashes($row['kodevhc']) . "','" . addslashes($row['supplierid']) . "','" . $noTr . "')");
				$awal++;
			}
			$owlPDO->commit();
		} catch (Exception $e) {
			$owlPDO->rollBack();
			echo "Error, " . addslashes($e->getMessage());
			exit;
		}

		break;
}
