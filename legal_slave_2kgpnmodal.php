<?php
//@Copy nangkoelframework
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');
require_once('lib/fpdf.php');

#============================================================
# LKPM - Laporan Kegiatan Penanaman Modal (cetak)
#============================================================

$method = checkPostGet('method', '');
$ptsrc = checkPostGet('ptsrc', '');
$periodedari = tanggalsystemn(checkPostGet('periodedari', ''));
$periodesampai = tanggalsystemn(checkPostGet('periodesampai', ''));

#--- pengamanan parameter ---
if ($ptsrc === 'undefined' || $ptsrc === 'null') {
	$ptsrc = '';
}
if ($ptsrc !== '' && !preg_match('/^[A-Za-z0-9]{1,10}$/', $ptsrc)) {
	exit("Warning : Parameter PT tidak valid.");
}
foreach (array('periodedari', 'periodesampai') as $kTgl) {
	if ($$kTgl !== '' && $$kTgl !== '--' && !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $$kTgl)) {
		exit("Warning : Format tanggal tidak valid.");
	}
}

#------------------------------------------------------------
# Helper
#------------------------------------------------------------
function kgpnR($v)
{
	return number_format((float)$v, 2, ',', '.');
}

#angka tanpa desimal (dipakai PDF, mengikuti format LKPM pemerintah)
function kgpnR0($v)
{
	return number_format((float)$v, 0, ',', '.');
}

function kgpnTglLaporan($tgl)
{
	if ($tgl == '' || $tgl == '0000-00-00' || $tgl == '--') {
		return '-';
	}
	return tanggalnormal($tgl);
}

#daftar dokumen LKPM dalam rentang periode
function kgpnDokumenPeriode()
{
	global $owlPDO, $dbname, $ptsrc, $periodedari, $periodesampai;
	if ($ptsrc == '' || $periodedari == '--' || $periodesampai == '--') {
		return array();
	}
	$sql = "select * from " . $dbname . ".lgl_kgpnmodal_ht
			where kodept=? and periodedari>=? and periodesampai<=?
			order by periodesampai, notransaksi";
	$stmt = $owlPDO->prepare($sql);
	$stmt->execute(array($ptsrc, $periodedari, $periodesampai));
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

#jumlah dt satu dokumen, per jenis, dengan tanda mode
function kgpnSumDt($notransaksi, $jenis, $kolom)
{
	global $owlPDO, $dbname;
	if (!in_array($kolom, array('nilaiperiode', 'nilaiakumulasi'))) {
		return 0;
	}
	$sql = "select ifnull(sum(case when mode='KURANGI' then -" . $kolom . " else " . $kolom . " end),0)
			from " . $dbname . ".lgl_kgpnmodal_dt where notransaksi=? and jenis=?";
	$stmt = $owlPDO->prepare($sql);
	$stmt->execute(array($notransaksi, $jenis));
	return (float)$stmt->fetchColumn();
}

#rincian per akun untuk satu dokumen (dipakai Excel & PDF)
function kgpnRincianDt($notransaksi, $jenis)
{
	global $owlPDO, $dbname;
	$sql = "select noakun, uraian, nilaiperiode, nilaiakumulasi, mode
			from " . $dbname . ".lgl_kgpnmodal_dt
			where notransaksi=? and jenis=?
			order by noakun='' desc, noakun, id";
	$stmt = $owlPDO->prepare($sql);
	$stmt->execute(array($notransaksi, $jenis));
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

#susun seluruh data laporan (satu struktur siap cetak)
function kgpnLaporanData()
{
	global $owlPDO, $dbname, $ptsrc, $periodedari, $periodesampai;

	$out = array(
		'ada' => false,
		'ptsrc' => $ptsrc,
		'dokumen' => array(),
		'namaorg' => '',
		'nib' => '',
		'nokegiatanusaha' => '',
		'noizin' => '',
		'kegiatanusaha' => '',
		'bidangusaha' => '',
		'alamatproyek' => '',
		'alamatkantor' => '',
		'mt_periode' => 0,
		'mt_akumulasi' => 0,
		'mk_periode' => 0,
		'mk_akumulasi' => 0,
		'tk' => array(),
		'masalah' => array(),
		'penjelasan' => '',
		'tklokalsetempat' => 0,
		'catatantk' => '',
		'kota' => '',
		'tanggallapor' => '0000-00-00',
		'namapenandatangan' => '',
		'jabatan' => '',
		'notelepon' => '',
		'email' => '',
		'tahun' => '',
		'triwulan' => '',
		'periodedari' => $periodedari,
		'periodesampai' => $periodesampai
	);
	if ($ptsrc == '') {
		return $out;
	}

	#profil perusahaan
	$stmt = $owlPDO->prepare("select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi=?");
	$stmt->execute(array($ptsrc));
	$out['namaorg'] = (string)$stmt->fetchColumn();
	$stmt = $owlPDO->prepare("select * from " . $dbname . ".lgl_kgpnmodal_5perusahaan where kodept=?");
	$stmt->execute(array($ptsrc));
	$prof = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($prof) {
		foreach (array('nib', 'nokegiatanusaha', 'noizin', 'kegiatanusaha', 'bidangusaha', 'alamatproyek', 'alamatkantor') as $f) {
			$out[$f] = $prof[$f];
		}
	}

	$dok = kgpnDokumenPeriode();
	if (count($dok) == 0) {
		return $out;
	}
	$out['ada'] = true;
	$out['dokumen'] = $dok;

	$pertama = $dok[0];
	$terakhir = $dok[count($dok) - 1];

	#periode dijumlahkan, akumulasi diambil dari dokumen terakhir (kumulatif)
	$mtP = 0;
	$mkP = 0;
	foreach ($dok as $d) {
		$mtP += kgpnSumDt($d['notransaksi'], 1, 'nilaiperiode');
		$mkP += kgpnSumDt($d['notransaksi'], 2, 'nilaiperiode');
	}
	$out['mt_periode'] = $mtP;
	$out['mk_periode'] = $mkP;
	$out['mt_akumulasi'] = kgpnSumDt($terakhir['notransaksi'], 1, 'nilaiakumulasi');
	$out['mk_akumulasi'] = kgpnSumDt($terakhir['notransaksi'], 2, 'nilaiakumulasi');

	#tenaga kerja: sebelum dari dokumen pertama, sampai dari dokumen terakhir, tambahan/pengurangan dijumlah
	$tk = array();
	foreach (array('LK', 'PR', 'ASG', 'UNK') as $kat) {
		$tk[$kat] = array('sebelum' => 0, 'tambahan' => 0, 'pengurangan' => 0, 'sampai' => 0);
	}
	$stmtA = $owlPDO->prepare("select kategori,sebelum,tambahan,pengurangan,sampai from " . $dbname . ".lgl_kgpnmodal_tk where notransaksi=?");
	foreach ($dok as $i => $d) {
		$stmtA->execute(array($d['notransaksi']));
		while ($r = $stmtA->fetch(PDO::FETCH_ASSOC)) {
			$kat = strtoupper($r['kategori']);
			if (!isset($tk[$kat])) {
				continue;
			}
			if ($i == 0) {
				$tk[$kat]['sebelum'] = (int)$r['sebelum'];
			}
			$tk[$kat]['tambahan'] += (int)$r['tambahan'];
			$tk[$kat]['pengurangan'] += (int)$r['pengurangan'];
			$tk[$kat]['sampai'] = (int)$r['sampai'];
		}
	}
	$out['tk'] = $tk;

	#permasalahan dari semua dokumen
	$stmtM = $owlPDO->prepare("select * from " . $dbname . ".lgl_kgpnmodal_masalah where notransaksi=? order by urut, id");
	$masalah = array();
	foreach ($dok as $d) {
		$stmtM->execute(array($d['notransaksi']));
		while ($r = $stmtM->fetch(PDO::FETCH_ASSOC)) {
			$masalah[] = $r;
		}
	}
	$out['masalah'] = $masalah;

	#atribut laporan diambil dari dokumen terakhir
	$out['penjelasan'] = $terakhir['penjelasan'];
	$out['tklokalsetempat'] = (int)$terakhir['tklokalsetempat'];
	$out['catatantk'] = $terakhir['catatantk'];
	$out['kota'] = $terakhir['kota'];
	$out['tanggallapor'] = $terakhir['tanggallapor'];
	$out['namapenandatangan'] = $terakhir['namapenandatangan'];
	$out['jabatan'] = $terakhir['jabatan'];
	$out['notelepon'] = $terakhir['notelepon'];
	$out['email'] = $terakhir['email'];
	$out['tahun'] = $terakhir['tahun'];
	$out['triwulan'] = $terakhir['triwulan'];

	return $out;
}

#teks periode triwulan
function kgpnPeriodeTeks($tw)
{
	$arr = array(
		1 => 'Triwulan Pertama ( Januari - Maret )',
		2 => 'Triwulan Kedua ( April - Juni )',
		3 => 'Triwulan Ketiga ( Juli - September )',
		4 => 'Triwulan Keempat ( Oktober - Desember )'
	);
	return isset($arr[(int)$tw]) ? $arr[(int)$tw] : '';
}

#info filter untuk kop
function kgpnInfoFilter()
{
	global $ptsrc, $periodedari, $periodesampai, $dbname, $owlPDO;
	$nm = '';
	if ($ptsrc != '') {
		$stmt = $owlPDO->prepare("select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi=?");
		$stmt->execute(array($ptsrc));
		$nm = (string)$stmt->fetchColumn();
	}
	$info = 'PT: ' . ($ptsrc != '' ? $ptsrc . ($nm != '' ? ' - ' . $nm : '') : 'Semua');
	$info .= ' | Periode: ' . kgpnTglLaporan($periodedari) . ' s/d ' . kgpnTglLaporan($periodesampai);
	return $info;
}

#------------------------------------------------------------
# Routing
#------------------------------------------------------------
switch ($method) {

	#================ PRATINJAU HTML ================
	case 'loaddata':
		$d = kgpnLaporanData();
		if ($d['ptsrc'] == '') {
			exit("Warning : PT wajib dipilih.");
		}
		if (!$d['ada']) {
			echo "<div style='padding:14px;color:#990000;'><b>Belum ada dokumen LKPM</b> untuk PT "
				. htmlspecialchars($d['ptsrc']) . " pada periode "
				. kgpnTglLaporan($periodedari) . " s/d " . kgpnTglLaporan($periodesampai) . ".</div>";
			break;
		}
		$tk = $d['tk'];
		$jmlSeb = $tk['LK']['sebelum'] + $tk['PR']['sebelum'] + $tk['ASG']['sebelum'];
		$jmlTam = $tk['LK']['tambahan'] + $tk['PR']['tambahan'] + $tk['ASG']['tambahan'];
		$jmlPen = $tk['LK']['pengurangan'] + $tk['PR']['pengurangan'] + $tk['ASG']['pengurangan'];
		$jmlSam = $tk['LK']['sampai'] + $tk['PR']['sampai'] + $tk['ASG']['sampai'];

		$h = "<div style='font-family:Arial;font-size:12px;'>";
		$h .= "<div align=center style='font-size:14px;font-weight:bold;'>LAPORAN KEGIATAN PENANAMAN MODAL (LKPM)<br>"
			. "TAHAP OPERASIONAL DAN/ATAU KOMERSIAL TAHUN " . htmlspecialchars($d['tahun']) . "</div>";
		$h .= "<div align=center style='margin:4px 0 10px 0;'>PERIODE: " . htmlspecialchars(kgpnPeriodeTeks($d['triwulan'])) . "</div>";

		#dokumen yang diikutkan
		$h .= "<div style='font-size:10px;color:#555;margin-bottom:8px;'>Dokumen diikutkan: ";
		$arrNo = array();
		foreach ($d['dokumen'] as $dk) {
			$arrNo[] = $dk['notransaksi'];
		}
		$h .= htmlspecialchars(implode(', ', $arrNo)) . "</div>";

		#I. Keterangan perusahaan
		$h .= "<table cellspacing=0 cellpadding=2 style='width:100%;font-size:12px;'>";
		$h .= "<tr><td colspan=3 style='font-weight:bold;background:#DDE6F0;padding:4px;'>I. KETERANGAN PERUSAHAAN</td></tr>";
		$arrI = array(
			'1' => array('Nama Pelaku Usaha/Perusahaan', $d['namaorg']),
			'2' => array('Nomor Induk Berusaha (NIB)', $d['nib']),
			'3' => array('Nomor Kegiatan Usaha', $d['nokegiatanusaha']),
			'4' => array('No Izin', $d['noizin']),
			'5' => array('Kegiatan Usaha', $d['kegiatanusaha']),
			'6' => array('Bidang Usaha', $d['bidangusaha']),
			'7' => array('Alamat lokasi proyek', $d['alamatproyek']),
			'8' => array('Alamat Kantor', $d['alamatkantor'])
		);
		foreach ($arrI as $no => $r) {
			$h .= "<tr><td style='width:30px;vertical-align:top;'>" . $no . "</td>"
				. "<td style='width:250px;vertical-align:top;'>" . $r[0] . "</td>"
				. "<td style='vertical-align:top;'>: " . nl2br(htmlspecialchars($r[1])) . "</td></tr>";
		}
		$h .= "</table>";

		#II. Realisasi
		$h .= "<table cellspacing=0 cellpadding=3 style='width:100%;font-size:12px;margin-top:10px;border-collapse:collapse;' border=1>";
		$h .= "<tr><td colspan=3 style='font-weight:bold;background:#DDE6F0;'>II. REALISASI PENANAMAN MODAL</td></tr>";
		$h .= "<tr style='background:#EFEFEF;font-weight:bold;'><td style='width:40%;'>Investasi</td>"
			. "<td align=right>Tambahan realisasi pada Periode Pelaporan (Rp)</td>"
			. "<td align=right>Total akumulasi realisasi s/d Periode Pelaporan (Rp)</td></tr>";
		$h .= "<tr><td>I. Modal Tetap</td><td align=right>" . kgpnR($d['mt_periode']) . "</td><td align=right>" . kgpnR($d['mt_akumulasi']) . "</td></tr>";
		$h .= "<tr><td>II. Modal Kerja</td><td align=right>" . kgpnR($d['mk_periode']) . "</td><td align=right>" . kgpnR($d['mk_akumulasi']) . "</td></tr>";
		$h .= "<tr style='font-weight:bold;'><td>Jumlah</td><td align=right>" . kgpnR($d['mt_periode'] + $d['mk_periode']) . "</td>"
			. "<td align=right>" . kgpnR($d['mt_akumulasi'] + $d['mk_akumulasi']) . "</td></tr>";
		$h .= "</table>";
		$h .= "<div style='font-size:11px;color:#555;margin-top:3px;'>Perhatian: Apabila perusahaan memiliki lebih dari satu lokasi proyek/bidang usaha, investasi wajib dirinci untuk masing-masing lokasi proyek/bidang usaha.</div>";
		if (trim($d['penjelasan']) != '') {
			$h .= "<div style='margin-top:4px;font-size:12px;'><b>Penjelasan :</b><br>" . nl2br(htmlspecialchars($d['penjelasan'])) . "</div>";
		}

		#III. Tenaga kerja
		$h .= "<table cellspacing=0 cellpadding=3 style='width:100%;font-size:12px;margin-top:10px;border-collapse:collapse;' border=1>";
		$h .= "<tr><td colspan=5 style='font-weight:bold;background:#DDE6F0;'>III. PENGGUNAAN TENAGA KERJA *)</td></tr>";
		$h .= "<tr style='background:#EFEFEF;font-weight:bold;'><td style='width:28%;'>Tenaga Kerja Perusahaan</td>"
			. "<td align=center>Total tenaga kerja sebelum periode pelaporan</td>"
			. "<td align=center>Tambahan tenaga kerja periode pelaporan</td>"
			. "<td align=center>Pengurangan tenaga kerja periode pelaporan</td>"
			. "<td align=center>Total tenaga kerja sampai dengan periode pelaporan</td></tr>";
		$arrTK = array(
			'Indonesia - Laki-laki' => 'LK',
			'Indonesia - Perempuan' => 'PR',
			'Asing' => 'ASG'
		);
		foreach ($arrTK as $lbl => $k) {
			$h .= "<tr><td>" . $lbl . "</td>"
				. "<td align=center>" . $tk[$k]['sebelum'] . " Orang</td>"
				. "<td align=center>" . $tk[$k]['tambahan'] . " Orang</td>"
				. "<td align=center>" . $tk[$k]['pengurangan'] . " Orang</td>"
				. "<td align=center>" . $tk[$k]['sampai'] . " Orang</td></tr>";
		}
		$h .= "<tr style='font-weight:bold;'><td>Jumlah</td>"
			. "<td align=center>" . $jmlSeb . " Orang</td>"
			. "<td align=center>" . $jmlTam . " Orang</td>"
			. "<td align=center>" . $jmlPen . " Orang</td>"
			. "<td align=center>" . $jmlSam . " Orang</td></tr>";
		if ($tk['UNK']['sebelum'] + $tk['UNK']['tambahan'] + $tk['UNK']['pengurangan'] + $tk['UNK']['sampai'] > 0) {
			$h .= "<tr style='font-style:italic;color:#666;'><td>Gender tidak diketahui (data SDM belum lengkap)</td>"
				. "<td align=center>" . $tk['UNK']['sebelum'] . " Orang</td>"
				. "<td align=center>" . $tk['UNK']['tambahan'] . " Orang</td>"
				. "<td align=center>" . $tk['UNK']['pengurangan'] . " Orang</td>"
				. "<td align=center>" . $tk['UNK']['sampai'] . " Orang</td></tr>";
		}
		$h .= "</table>";
		$h .= "<div style='font-size:12px;margin-top:3px;'>Dari total tenaga kerja di atas, tenaga kerja lokal setempat yang diserap sejumlah <b>"
			. $d['tklokalsetempat'] . " Orang</b>"
			. ($d['catatantk'] != '' ? " (" . htmlspecialchars($d['catatantk']) . ")" : "") . "</div>";
		$h .= "<div style='font-size:11px;color:#555;'>*) Tidak termasuk Komisaris dan Direksi, termasuk tenaga kerja kontrak</div>";

		#IV. Permasalahan
		$h .= "<table cellspacing=0 cellpadding=3 style='width:100%;font-size:12px;margin-top:10px;border-collapse:collapse;' border=1>";
		$h .= "<tr><td colspan=4 style='font-weight:bold;background:#DDE6F0;'>IV. PERMASALAHAN YANG DIHADAPI PERUSAHAAN</td></tr>";
		$h .= "<tr style='background:#EFEFEF;font-weight:bold;'><td style='width:15%;'>Tanggal Melaporkan</td>"
			. "<td style='width:22%;'>Jenis Masalah</td><td>Detail Masalah</td>"
			. "<td style='width:25%;'>Keterangan (Kronologis)</td></tr>";
		if (count($d['masalah']) == 0) {
			$h .= "<tr><td colspan=4 align=center style='color:#777;font-style:italic;'>&nbsp;</td></tr>";
		} else {
			foreach ($d['masalah'] as $m) {
				$h .= "<tr><td align=center>" . kgpnTglLaporan($m['tanggal']) . "</td>"
					. "<td>" . htmlspecialchars($m['jenismasalah']) . "</td>"
					. "<td>" . htmlspecialchars($m['detail']) . "</td>"
					. "<td>" . htmlspecialchars($m['keterangan']) . "</td></tr>";
			}
		}
		$h .= "</table>";

		#Pengesahan
		$h .= "<div style='margin-top:14px;font-size:12px;'>Laporan ini disusun dengan sebenarnya</div>";
		$h .= "<table cellspacing=0 cellpadding=2 style='width:100%;font-size:12px;margin-top:6px;'>";
		$h .= "<tr><td style='width:55%;'>" . htmlspecialchars($d['kota']) . ", " . kgpnTglLaporan($d['tanggallapor']) . "</td>"
			. "<td>Direksi/Kuasa Direksi Selaku Penanggung Jawab</td></tr>";
		$h .= "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>";
		$h .= "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>";
		$h .= "<tr><td></td><td>Nama Jelas : " . htmlspecialchars($d['namapenandatangan']) . "</td></tr>";
		$h .= "<tr><td></td><td>Jabatan : " . htmlspecialchars($d['jabatan']) . "</td></tr>";
		$h .= "<tr><td></td><td>No. Telepon : " . htmlspecialchars($d['notelepon']) . "</td></tr>";
		$h .= "<tr><td></td><td>Email : " . htmlspecialchars($d['email']) . "</td></tr>";
		$h .= "</table>";
		$h .= "</div>";

		echo $h;
		break;

	#================ EXCEL ================
	case 'excellist':
		$d = kgpnLaporanData();
		if ($d['ptsrc'] == '') {
			exit("Warning : PT wajib dipilih.");
		}
		if (!$d['ada']) {
			exit("Warning : Belum ada dokumen LKPM pada periode tersebut.");
		}
		$tk = $d['tk'];
		$jmlSeb = $tk['LK']['sebelum'] + $tk['PR']['sebelum'] + $tk['ASG']['sebelum'];
		$jmlTam = $tk['LK']['tambahan'] + $tk['PR']['tambahan'] + $tk['ASG']['tambahan'];
		$jmlPen = $tk['LK']['pengurangan'] + $tk['PR']['pengurangan'] + $tk['ASG']['pengurangan'];
		$jmlSam = $tk['LK']['sampai'] + $tk['PR']['sampai'] + $tk['ASG']['sampai'];

		$s = "<table border=1 cellspacing=0 cellpadding=3>";
		$s .= "<tr><td colspan=3 align=center><b>LAPORAN KEGIATAN PENANAMAN MODAL (LKPM)</b></td></tr>";
		$s .= "<tr><td colspan=3 align=center><b>TAHAP OPERASIONAL DAN/ATAU KOMERSIAL TAHUN " . htmlspecialchars($d['tahun']) . "</b></td></tr>";
		$s .= "<tr><td colspan=3" . " align=center>PERIODE: " . htmlspecialchars(kgpnPeriodeTeks($d['triwulan'])) . "</td></tr>";
		$s .= "<tr><td colspan=3>" . htmlspecialchars(kgpnInfoFilter()) . "</td></tr>";
		$s .= "<tr><td colspan=3>&nbsp;</td></tr>";

		$s .= "<tr><td colspan=3 bgcolor=#DDE6F0><b>I. KETERANGAN PERUSAHAAN</b></td></tr>";
		$arrI = array(
			'Nama Pelaku Usaha/Perusahaan' => $d['namaorg'],
			'Nomor Induk Berusaha (NIB)' => $d['nib'],
			'Nomor Kegiatan Usaha' => $d['nokegiatanusaha'],
			'No Izin' => $d['noizin'],
			'Kegiatan Usaha' => $d['kegiatanusaha'],
			'Bidang Usaha' => $d['bidangusaha'],
			'Alamat lokasi proyek' => $d['alamatproyek'],
			'Alamat Kantor' => $d['alamatkantor']
		);
		foreach ($arrI as $lbl => $val) {
			$s .= "<tr><td>" . $lbl . "</td><td>:</td><td>" . htmlspecialchars($val) . "</td></tr>";
		}
		$s .= "<tr><td colspan=3>&nbsp;</td></tr>";

		$s .= "<tr><td colspan=3 bgcolor=#DDE6F0><b>II. REALISASI PENANAMAN MODAL</b></td></tr>";
		$s .= "<tr bgcolor=#EFEFEF><td><b>Investasi</b></td><td align=right><b>Tambahan realisasi periode (Rp)</b></td><td align=right><b>Total akumulasi s/d periode (Rp)</b></td></tr>";
		$s .= "<tr><td>I. Modal Tetap</td><td align=right>" . round($d['mt_periode'], 2) . "</td><td align=right>" . round($d['mt_akumulasi'], 2) . "</td></tr>";
		$s .= "<tr><td>II. Modal Kerja</td><td align=right>" . round($d['mk_periode'], 2) . "</td><td align=right>" . round($d['mk_akumulasi'], 2) . "</td></tr>";
		$s .= "<tr><td><b>Jumlah</b></td><td align=right><b>" . round($d['mt_periode'] + $d['mk_periode'], 2) . "</b></td><td align=right><b>" . round($d['mt_akumulasi'] + $d['mk_akumulasi'], 2) . "</b></td></tr>";
		if (trim($d['penjelasan']) != '') {
			$s .= "<tr><td>Penjelasan</td><td colspan=2>" . htmlspecialchars($d['penjelasan']) . "</td></tr>";
		}
		$s .= "<tr><td colspan=3>&nbsp;</td></tr>";

		$s .= "<tr><td colspan=5 bgcolor=#DDE6F0><b>III. PENGGUNAAN TENAGA KERJA *)</b></td></tr>";
		$s .= "<tr bgcolor=#EFEFEF><td><b>Tenaga Kerja Perusahaan</b></td><td align=center><b>Sebelum periode</b></td><td align=center><b>Tambahan periode</b></td><td align=center><b>Pengurangan periode</b></td><td align=center><b>Sampai periode</b></td></tr>";
		$arrTK = array('Indonesia - Laki-laki' => 'LK', 'Indonesia - Perempuan' => 'PR', 'Asing' => 'ASG');
		foreach ($arrTK as $lbl => $k) {
			$s .= "<tr><td>" . $lbl . "</td><td align=center>" . $tk[$k]['sebelum'] . "</td><td align=center>" . $tk[$k]['tambahan'] . "</td><td align=center>" . $tk[$k]['pengurangan'] . "</td><td align=center>" . $tk[$k]['sampai'] . "</td></tr>";
		}
		$s .= "<tr><td><b>Jumlah</b></td><td align=center><b>" . $jmlSeb . "</b></td><td align=center><b>" . $jmlTam . "</b></td><td align=center><b>" . $jmlPen . "</b></td><td align=center><b>" . $jmlSam . "</b></td></tr>";
		if ($tk['UNK']['sebelum'] + $tk['UNK']['tambahan'] + $tk['UNK']['pengurangan'] + $tk['UNK']['sampai'] > 0) {
			$s .= "<tr><td><i>Gender tidak diketahui</i></td><td align=center>" . $tk['UNK']['sebelum'] . "</td><td align=center>" . $tk['UNK']['tambahan'] . "</td><td align=center>" . $tk['UNK']['pengurangan'] . "</td><td align=center>" . $tk['UNK']['sampai'] . "</td></tr>";
		}
		$s .= "<tr><td colspan=5>Tenaga kerja lokal setempat yang diserap: " . $d['tklokalsetempat'] . " Orang"
			. ($d['catatantk'] != '' ? " (" . htmlspecialchars($d['catatantk']) . ")" : "") . "</td></tr>";
		$s .= "<tr><td colspan=5>*) Tidak termasuk Komisaris dan Direksi, termasuk tenaga kerja kontrak</td></tr>";
		$s .= "<tr><td colspan=5>&nbsp;</td></tr>";

		$s .= "<tr><td colspan=4 bgcolor=#DDE6F0><b>IV. PERMASALAHAN YANG DIHADAPI PERUSAHAAN</b></td></tr>";
		$s .= "<tr bgcolor=#EFEFEF><td><b>Tanggal Melaporkan</b></td><td><b>Jenis Masalah</b></td><td><b>Detail Masalah</b></td><td><b>Keterangan (Kronologis)</b></td></tr>";
		if (count($d['masalah']) == 0) {
			$s .= "<tr><td colspan=4 align=center>-</td></tr>";
		} else {
			foreach ($d['masalah'] as $m) {
				$s .= "<tr><td>" . kgpnTglLaporan($m['tanggal']) . "</td><td>" . htmlspecialchars($m['jenismasalah']) . "</td><td>" . htmlspecialchars($m['detail']) . "</td><td>" . htmlspecialchars($m['keterangan']) . "</td></tr>";
			}
		}
		$s .= "<tr><td colspan=4>&nbsp;</td></tr>";
		$s .= "<tr><td colspan=2>Laporan ini disusun dengan sebenarnya</td><td colspan=2>Direksi/Kuasa Direksi Selaku Penanggung Jawab</td></tr>";
		$s .= "<tr><td colspan=2>&nbsp;</td><td colspan=2>&nbsp;</td></tr>";
		$s .= "<tr><td colspan=2>" . htmlspecialchars($d['kota']) . ", " . kgpnTglLaporan($d['tanggallapor']) . "</td><td colspan=2>Nama Jelas : " . htmlspecialchars($d['namapenandatangan']) . "</td></tr>";
		$s .= "<tr><td colspan=2></td><td colspan=2>Jabatan : " . htmlspecialchars($d['jabatan']) . "</td></tr>";
		$s .= "<tr><td colspan=2></td><td colspan=2>No. Telepon : " . htmlspecialchars($d['notelepon']) . "</td></tr>";
		$s .= "<tr><td colspan=2></td><td colspan=2>Email : " . htmlspecialchars($d['email']) . "</td></tr>";
		$s .= "</table>";

		$xls = new HtmlExcel();
		$xls->addSheet("LKPM", $s);
		$xls->headers("LKPM_" . $d['ptsrc'] . "_" . date('YmdHis') . ".xls");
		echo $xls->buildFile();
		exit;

	#================ PDF ================
	case 'pdflist':
		$d = kgpnLaporanData();
		if ($d['ptsrc'] == '') {
			exit("Warning : PT wajib dipilih.");
		}
		if (!$d['ada']) {
			exit("Warning : Belum ada dokumen LKPM pada periode tersebut.");
		}

		if (!class_exists('PDFKGPN')) {
			#Format LKPM pemerintah: 4 halaman, satu bagian per halaman.
			#Ukuran kolom mengikuti berkas Contoh LKPM Candi.pdf (A4, margin 19 mm,
			#lebar isi 172 mm, tinggi baris 5,3-5,6 mm).
			class PDFKGPN extends FPDF
			{
				public $d = array();
				public $lineH = 4.2;   #tinggi satu baris teks (mm)
				public $pad = 1.0;     #padding atas/bawah dalam sel (mm)
				public $bottom = 15;   #batas bawah halaman (mm)

				#jumlah baris teks untuk lebar sel tertentu
				function nLines($w, $txt)
				{
					$txt = trim($txt);
					if ($txt === '') {
						return 1;
					}
					$words = explode(' ', $txt);
					$lines = 1;
					$cur = '';
					$max = $w - 2 * $this->pad;
					foreach ($words as $word) {
						$coba = ($cur === '') ? $word : $cur . ' ' . $word;
						if ($this->GetStringWidth($coba) > $max) {
							$lines++;
							$cur = $word;
						} else {
							$cur = $coba;
						}
					}
					return $lines;
				}

				#satu baris tabel berbingkai.
				#$cells = array(array('w'=>lebar,'txt'=>teks,'align'=>L/C/R,'bold'=>bool,'size'=>pt,'fill'=>bool), ...)
				function barisTabel($cells, $hFixed = 0)
				{
					$maxLines = 1;
					foreach ($cells as $c) {
						$sz = isset($c['size']) ? $c['size'] : 8.5;
						$this->SetFont('Arial', !empty($c['bold']) ? 'B' : '', $sz);
						$n = $this->nLines($c['w'], isset($c['txt']) ? $c['txt'] : '');
						if ($n > $maxLines) {
							$maxLines = $n;
						}
					}
					$h = $maxLines * $this->lineH + 2 * $this->pad;
					if ($hFixed > 0) {
						$h = $hFixed;
					}
					if ($h < 5) {
						$h = 5;
					}

					#pindah halaman kalau tidak cukup
					if ($this->GetY() + $h > $this->h - $this->bottom) {
						$this->AddPage();
					}

					$x0 = $this->GetX();
					$y0 = $this->GetY();

					#bingkai
					$x = $x0;
					foreach ($cells as $c) {
						if (!empty($c['fill'])) {
							$this->SetFillColor(221, 230, 240);
							$this->Rect($x, $y0, $c['w'], $h, 'DF');
						} else {
							$this->Rect($x, $y0, $c['w'], $h);
						}
						$x += $c['w'];
					}

					#teks
					$x = $x0;
					foreach ($cells as $c) {
						$sz = isset($c['size']) ? $c['size'] : 8.5;
						$this->SetFont('Arial', !empty($c['bold']) ? 'B' : '', $sz);
						$this->SetXY($x, $y0 + $this->pad);
						$this->MultiCell($c['w'], $this->lineH, isset($c['txt']) ? $c['txt'] : '', 0, isset($c['align']) ? $c['align'] : 'L');
						$x += $c['w'];
					}
					$this->SetXY($x0, $y0 + $h);
				}

				#judul bagian (teks tebal, tanpa bingkai)
				function judulBagian($txt, $y = 0)
				{
					if ($y > 0) {
						$this->SetY($y);
					}
					$this->SetFont('Arial', 'B', 9);
					$this->Cell(0, 5, utf8_decode($txt), 0, 1, 'L');
					$this->Ln(1);
				}

				#catatan kecil
				function catatan($txt, $size = 7.5, $style = '')
				{
					$this->SetFont('Arial', $style, $size);
					$this->MultiCell(0, 4, utf8_decode($txt), 0, 'L');
				}
			}
		}

		$pdf = new PDFKGPN('P', 'mm', 'A4');
		$pdf->d = $d;
		$pdf->SetMargins(19, 19, 19);
		$pdf->SetAutoPageBreak(false);
		$pdf->AliasNbPages();

		#lebar isi = 172 mm (210 - 19 - 19)
		$W = 172;

		#======================== HALAMAN 1 : KETERANGAN PERUSAHAAN ========================
		$pdf->AddPage();

		$pdf->SetFont('Arial', 'B', 11);
		$pdf->Cell(0, 6, 'LAPORAN KEGIATAN PENANAMAN MODAL (LKPM)', 0, 1, 'C');
		$pdf->Cell(0, 6, 'TAHAP OPERASIONAL DAN/ATAU KOMERSIAL', 0, 1, 'C');
		$pdf->Cell(0, 6, 'TAHUN ' . $d['tahun'], 0, 1, 'C');

		$pdf->Ln(7);
		$pdf->SetFont('Arial', '', 9);
		$pdf->Cell(0, 5, 'PERIODE:', 0, 1, 'L');
		$pdf->Cell(0, 5, utf8_decode(kgpnPeriodeTeks($d['triwulan'])), 0, 1, 'L');

		$pdf->Ln(9);
		$pdf->judulBagian('I. KETERANGAN PERUSAHAAN');

		#kolom Bagian I : no 8,5 | label 79 | titik dua 4,2 | nilai 80,3
		$bag1 = array(
			array('1', 'Nama Pelaku Usaha/Perusahaan', $d['namaorg']),
			array('2', 'Nomor Induk Berusaha (NIB)', $d['nib']),
			array('3', 'Nomor Kegiatan Usaha', $d['nokegiatanusaha']),
			array('4', 'No Izin', $d['noizin']),
			array('5', 'Kegiatan Usaha', $d['kegiatanusaha']),
			array('6', 'Bidang Usaha', $d['bidangusaha']),
			array('7', 'Alamat lokasi proyek', $d['alamatproyek']),
			array('8', 'Alamat Kantor', $d['alamatkantor'])
		);
		foreach ($bag1 as $r) {
			$pdf->barisTabel(array(
				array('w' => 8.5, 'txt' => $r[0], 'align' => 'C'),
				array('w' => 79, 'txt' => utf8_decode($r[1]), 'align' => 'L'),
				array('w' => 4.2, 'txt' => ':', 'align' => 'C'),
				array('w' => 80.3, 'txt' => utf8_decode($r[2]), 'align' => 'L')
			), 5.6);
		}

		#======================== HALAMAN 2 : REALISASI PENANAMAN MODAL ========================
		$pdf->AddPage();
		$pdf->judulBagian('II. REALISASI PENANAMAN MODAL');

		#header : 57,5 | 56,8 | 57,7
		$pdf->barisTabel(array(
			array('w' => 57.5, 'txt' => 'Investasi', 'align' => 'C', 'bold' => true, 'size' => 8, 'fill' => true),
			array('w' => 56.8, 'txt' => 'Tambahan realisasi pada Periode Pelaporan (Rp)', 'align' => 'C', 'bold' => true, 'size' => 8, 'fill' => true),
			array('w' => 57.7, 'txt' => 'Total akumulasi realisasi s/d Periode Pelaporan (Rp)', 'align' => 'C', 'bold' => true, 'size' => 8, 'fill' => true)
		), 9.2);

		#baris data : label 53,3 | titik dua 4,2 | 56,8 | 57,7
		$bag2 = array(
			array('I. Modal Tetap', kgpnR0($d['mt_periode']), kgpnR0($d['mt_akumulasi']), false),
			array('II. Modal Kerja', kgpnR0($d['mk_periode']), kgpnR0($d['mk_akumulasi']), false),
			array('Jumlah', kgpnR0($d['mt_periode'] + $d['mk_periode']), kgpnR0($d['mt_akumulasi'] + $d['mk_akumulasi']), true)
		);
		foreach ($bag2 as $r) {
			$pdf->barisTabel(array(
				array('w' => 53.3, 'txt' => utf8_decode($r[0]), 'align' => 'L', 'bold' => $r[3]),
				array('w' => 4.2, 'txt' => ':', 'align' => 'C', 'bold' => $r[3]),
				array('w' => 56.8, 'txt' => $r[1], 'align' => 'R', 'bold' => $r[3]),
				array('w' => 57.7, 'txt' => $r[2], 'align' => 'R', 'bold' => $r[3])
			), 5.3);
		}

		$pdf->Ln(3);
		$pdf->catatan('Perhatian: Apabila perusahaan memiliki lebih dari satu lokasi proyek/bidang usaha, investasi wajib dirinci untuk masing-masing lokasi proyek/bidang usaha.', 7.5, 'I');

		$pdf->Ln(2);
		$pdf->SetFont('Arial', '', 8.5);
		$pdf->Cell(18, 4.5, 'Penjelasan', 0, 0, 'L');
		$pdf->Cell(4, 4.5, ':', 0, 0, 'L');
		$isiPenjelasan = trim($d['penjelasan']);
		if ($isiPenjelasan == '') {
			$isiPenjelasan = '-';
		}
		$pdf->MultiCell($W - 22, 4.5, utf8_decode($isiPenjelasan), 0, 'L');

		#======================== HALAMAN 3 : PENGGUNAAN TENAGA KERJA ========================
		$tk = $d['tk'];
		$jmlSeb = $tk['LK']['sebelum'] + $tk['PR']['sebelum'] + $tk['ASG']['sebelum'];
		$jmlTam = $tk['LK']['tambahan'] + $tk['PR']['tambahan'] + $tk['ASG']['tambahan'];
		$jmlPen = $tk['LK']['pengurangan'] + $tk['PR']['pengurangan'] + $tk['ASG']['pengurangan'];
		$jmlSam = $tk['LK']['sampai'] + $tk['PR']['sampai'] + $tk['ASG']['sampai'];

		$pdf->AddPage();
		$pdf->judulBagian('III. PENGGUNAAN TENAGA KERJA*)');

		#kolom : 51,2 | 4,9 | 28,2 | 28,6 | 31,8 | 27,3  = 172
		$kLbl = 51.2;
		$kTit = 4.9;
		$k1 = 28.2;
		$k2 = 28.6;
		$k3 = 31.8;
		$k4 = 27.3;

		$pdf->barisTabel(array(
			array('w' => $kLbl, 'txt' => 'Tenaga Kerja Perusahaan', 'align' => 'C', 'bold' => true, 'size' => 7.5, 'fill' => true),
			array('w' => $kTit, 'txt' => '', 'align' => 'C', 'bold' => true, 'size' => 7.5, 'fill' => true),
			array('w' => $k1, 'txt' => 'Total tenaga kerja sebelum periode pelaporan', 'align' => 'C', 'bold' => true, 'size' => 7.5, 'fill' => true),
			array('w' => $k2, 'txt' => 'Tambahan tenaga kerja periode pelaporan', 'align' => 'C', 'bold' => true, 'size' => 7.5, 'fill' => true),
			array('w' => $k3, 'txt' => 'Pengurangan tenaga kerja periode pelaporan', 'align' => 'C', 'bold' => true, 'size' => 7.5, 'fill' => true),
			array('w' => $k4, 'txt' => 'Total tenaga kerja sampai dengan periode pelaporan', 'align' => 'C', 'bold' => true, 'size' => 7.5, 'fill' => true)
		), 12.7);

		#baris grup "Indonesia"
		$pdf->barisTabel(array(
			array('w' => $kLbl, 'txt' => 'Indonesia', 'align' => 'C', 'bold' => true, 'size' => 8),
			array('w' => $kTit, 'txt' => '', 'align' => 'C'),
			array('w' => $k1, 'txt' => '', 'align' => 'C'),
			array('w' => $k2, 'txt' => '', 'align' => 'C'),
			array('w' => $k3, 'txt' => '', 'align' => 'C'),
			array('w' => $k4, 'txt' => '', 'align' => 'C')
		), 5.6);

		$barisTK = array(
			array('- Laki - laki', 'LK', false),
			array('- Perempuan', 'PR', false),
			array('Asing', 'ASG', false),
			array('Jumlah', '', true)
		);
		foreach ($barisTK as $r) {
			if ($r[1] == '') {
				$v = array($jmlSeb, $jmlTam, $jmlPen, $jmlSam);
			} else {
				$v = array(
					$tk[$r[1]]['sebelum'],
					$tk[$r[1]]['tambahan'],
					$tk[$r[1]]['pengurangan'],
					$tk[$r[1]]['sampai']
				);
			}
			$algn = ($r[0] == 'Asing') ? 'C' : 'L';
			$pdf->barisTabel(array(
				array('w' => $kLbl, 'txt' => utf8_decode($r[0]), 'align' => $algn, 'bold' => $r[2]),
				array('w' => $kTit, 'txt' => ':', 'align' => 'C', 'bold' => $r[2]),
				array('w' => $k1, 'txt' => $v[0] . ' Orang', 'align' => 'C', 'bold' => $r[2]),
				array('w' => $k2, 'txt' => $v[1] . ' Orang', 'align' => 'C', 'bold' => $r[2]),
				array('w' => $k3, 'txt' => $v[2] . ' Orang', 'align' => 'C', 'bold' => $r[2]),
				array('w' => $k4, 'txt' => $v[3] . ' Orang', 'align' => 'C', 'bold' => $r[2])
			), 5.6);
		}

		#baris "gender tidak diketahui" hanya kalau ada isinya
		if ($tk['UNK']['sebelum'] + $tk['UNK']['tambahan'] + $tk['UNK']['pengurangan'] + $tk['UNK']['sampai'] > 0) {
			$pdf->barisTabel(array(
				array('w' => $kLbl, 'txt' => 'Gender tidak diketahui', 'align' => 'L', 'size' => 7),
				array('w' => $kTit, 'txt' => ':', 'align' => 'C', 'size' => 7),
				array('w' => $k1, 'txt' => $tk['UNK']['sebelum'] . ' Orang', 'align' => 'C', 'size' => 7),
				array('w' => $k2, 'txt' => $tk['UNK']['tambahan'] . ' Orang', 'align' => 'C', 'size' => 7),
				array('w' => $k3, 'txt' => $tk['UNK']['pengurangan'] . ' Orang', 'align' => 'C', 'size' => 7),
				array('w' => $k4, 'txt' => $tk['UNK']['sampai'] . ' Orang', 'align' => 'C', 'size' => 7)
			), 5.6);
		}

		$pdf->Ln(3);
		$ketLokal = 'Dari total tenaga kerja di atas, tenaga kerja lokal setempat yang diserap sejumlah '
			. $d['tklokalsetempat'] . ' Orang';
		if (trim($d['catatantk']) != '') {
			$ketLokal .= ' (' . $d['catatantk'] . ')';
		}
		$pdf->catatan($ketLokal, 8.5, '');
		$pdf->catatan('*) Tidak termasuk Komisaris dan Direksi, termasuk tenaga kerja kontrak', 7.5, 'I');

		#======================== HALAMAN 4 : PERMASALAHAN + PENGESAHAN ========================
		$pdf->AddPage();
		$pdf->judulBagian('IV. PERMASALAHAN YANG DIHADAPI PERUSAHAAN', 16);
		$pdf->Ln(4);

		#kolom : 33,9 | 31,8 | 32,1 | 74,2 = 172
		$m0 = 33.9;
		$m1 = 31.8;
		$m2 = 32.1;
		$m3 = 74.2;

		$pdf->barisTabel(array(
			array('w' => $m0, 'txt' => 'Tanggal Melaporkan', 'align' => 'C', 'bold' => true, 'size' => 7, 'fill' => true),
			array('w' => $m1, 'txt' => 'Jenis Masalah', 'align' => 'C', 'bold' => true, 'size' => 7, 'fill' => true),
			array('w' => $m2, 'txt' => 'Detail Masalah', 'align' => 'C', 'bold' => true, 'size' => 7, 'fill' => true),
			array('w' => $m3, 'txt' => 'Keterangan Permasalahan (Kronologis)', 'align' => 'C', 'bold' => true, 'size' => 7, 'fill' => true)
		), 5.6);

		if (count($d['masalah']) == 0) {
			#baris kosong supaya bentuk tabel tetap terlihat
			$pdf->barisTabel(array(
				array('w' => $m0, 'txt' => '', 'align' => 'C', 'size' => 7),
				array('w' => $m1, 'txt' => '', 'align' => 'L', 'size' => 7),
				array('w' => $m2, 'txt' => '', 'align' => 'L', 'size' => 7),
				array('w' => $m3, 'txt' => '', 'align' => 'L', 'size' => 7)
			), 5.6);
		} else {
			foreach ($d['masalah'] as $m) {
				$pdf->barisTabel(array(
					array('w' => $m0, 'txt' => kgpnTglLaporan($m['tanggal']), 'align' => 'C', 'size' => 7),
					array('w' => $m1, 'txt' => utf8_decode($m['jenismasalah']), 'align' => 'L', 'size' => 7),
					array('w' => $m2, 'txt' => utf8_decode($m['detail']), 'align' => 'L', 'size' => 7),
					array('w' => $m3, 'txt' => utf8_decode($m['keterangan']), 'align' => 'L', 'size' => 7)
				));
			}
		}

		$pdf->Ln(7);
		$pdf->SetFont('Arial', '', 8.5);
		$pdf->Cell(0, 4.5, 'Laporan ini disusun dengan sebenarnya', 0, 1, 'L');

		#blok tanda tangan di sisi kanan (x = 19 + 107 = 126 mm)
		$xSig = 126;
		$wSig = 65;
		$pdf->Ln(9);
		$pdf->SetX($xSig);
		$pdf->Cell($wSig, 4.5, utf8_decode($d['kota'] . ', ' . kgpnTglLaporan($d['tanggallapor'])), 0, 1, 'L');

		$pdf->SetX($xSig);
		$pdf->MultiCell($wSig, 4.5, "Direksi/Kuasa Direksi\nSelaku Penanggung Jawab", 0, 'L');

		$pdf->Ln(7);
		$pdf->SetX($xSig);
		$pdf->Cell($wSig, 4.5, utf8_decode('Nama Jelas : ' . $d['namapenandatangan']), 0, 1, 'L');
		$pdf->SetX($xSig);
		$pdf->Cell($wSig, 4.5, utf8_decode('Jabatan : ' . $d['jabatan']), 0, 1, 'L');
		$pdf->SetX($xSig);
		$pdf->Cell($wSig, 4.5, utf8_decode('No. Telepon : ' . $d['notelepon']), 0, 1, 'L');
		$pdf->SetX($xSig);
		$pdf->Cell($wSig, 4.5, utf8_decode('Email : ' . $d['email']), 0, 1, 'L');

		$pdf->Output('LKPM_' . $d['ptsrc'] . '_' . date('YmdHis') . '.pdf', 'I');
		exit;

	default:
		exit("Warning : Method tidak dikenal.");
}
?>
