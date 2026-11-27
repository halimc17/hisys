<?php
#Ringkasan hasil Proses Gaji PPP (Bulanan, Harian, Gaji Kecil Bulanan, Gaji Kecil), ditampilkan setelah tombol Proses selesai.
#Hanya menghitung dari data yang baru disimpan ($readyData); tidak menulis ke database.
#Teks hasil sengaja tidak memuat kata "gagal", "error" atau "warning" supaya dianggap sukses oleh isSaveResponse() di js/generic.js.
#Penambah dan pengurang dihitung sama dengan tampilan list: komponen BPJS bagian perusahaan (70,71,72,73,80) tidak ikut Total Penambah.
function ringkasanProsesGajiPPP($o)
{
	$h = function ($s) {
		return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
	};
	$rp = function ($v) {
		return number_format((float)$v, 0, ',', '.');
	};
	$comp = isset($o['comp']) && is_array($o['comp']) ? $o['comp'] : array();
	$nakomp = isset($o['nakomp']) && is_array($o['nakomp']) ? $o['nakomp'] : array();
	$namakar = isset($o['namakar']) && is_array($o['namakar']) ? $o['namakar'] : array();
	$subKary = isset($o['subbagian']) && is_array($o['subbagian']) ? $o['subbagian'] : array();
	$daftar = isset($o['karyawan']) && is_array($o['karyawan']) ? $o['karyawan'] : array();
	$data = isset($o['readyData']) && is_array($o['readyData']) ? $o['readyData'] : array();
	$bpjsPerusahaan = array('70', '71', '72', '73', '80');

	$perKary = array(); #karyawanid => array(plus, min)
	$perKomp = array(); #idkomponen => array(n => array karyawan, total)
	$baris = 0;
	foreach ($data as $r) {
		if (!isset($r['karyawanid']) || $r['karyawanid'] === '' || !isset($r['jumlah']) || $r['jumlah'] == 0 || $r['jumlah'] === '') {
			continue;
		}
		$k = $r['karyawanid'];
		$c = (string)$r['idkomponen'];
		$jml = (float)$r['jumlah'];
		$peng = isset($comp[$c]) ? (float)$comp[$c] : 1;
		$baris++;
		if (!isset($perKary[$k])) {
			$perKary[$k] = array('plus' => 0, 'min' => 0);
		}
		if ($peng < 0) {
			$perKary[$k]['min'] += $jml;
		} elseif (!in_array($c, $bpjsPerusahaan, true)) {
			$perKary[$k]['plus'] += $jml;
		}
		if (!isset($perKomp[$c])) {
			$perKomp[$c] = array('n' => array(), 'total' => 0, 'peng' => $peng);
		}
		$perKomp[$c]['n'][$k] = true;
		$perKomp[$c]['total'] += $jml;
	}

	$totPlus = $totMin = $minus = 0;
	$perSub = array();
	$jmlKary = count($daftar) > 0 ? count($daftar) : count($perKary);
	foreach ($perKary as $k => $d) {
		$sub = isset($subKary[$k]) && trim((string)$subKary[$k]) !== '' ? trim((string)$subKary[$k]) : 'KANTOR';
		$bersih = $d['plus'] - $d['min'];
		$totPlus += $d['plus'];
		$totMin += $d['min'];
		if (round($bersih, 2) < 0) {
			$minus++;
		}
		if (!isset($perSub[$sub])) {
			$perSub[$sub] = array('orang' => 0, 'plus' => 0, 'min' => 0);
		}
		$perSub[$sub]['orang']++;
		$perSub[$sub]['plus'] += $d['plus'];
		$perSub[$sub]['min'] += $d['min'];
	}
	ksort($perSub);
	$totBersih = $totPlus - $totMin;

	#catatan untuk operator
	$cat = array();
	if (!empty($o['negatif']) || $minus > 0) {
		$cat[] = "<b>" . $minus . " karyawan bergaji di bawah 0</b>. Periksa sebelum melanjutkan ke Proses Akhir Bulan.";
	}
	if (!empty($o['hke']) && function_exists('hkePTAktif') && function_exists('getHKEPPP')) {
		if (hkePTAktif('PPP', $o['periode'])) {
			$hke = getHKEPPP('PPP', $o['periode']);
			$cat[] = "HKE PT PPP periode " . $h($o['periode']) . " dipakai" . ($hke !== null ? ": <b>" . (int)$hke . " hari</b>" : "") . ". Upah per hari = dasar upah / HKE.";
		} else {
			$cat[] = "HKE belum aktif untuk periode ini: upah per hari memakai gaji pokok / 25.";
		}
	}
	if (!empty($o['gajikecil'])) {
		$cat[] = (int)$o['gajikecil'] . " karyawan memakai hasil Proses Gaji Kecil (komponen BPJS dan gaji kecil dibawa dari sana).";
	}
	if (isset($perKomp['132']) && isset($perKomp['133'])) {
		$cat[] = "Lebih HK: <b>" . count($perKomp['132']['n']) . " karyawan</b> (Rp " . $rp($perKomp['132']['total']) . "). Dicatat sebagai Lebih HK (+) dan Koreksi Lebih HK (-), netto nol.";
	}
	if (isset($perKomp['37'])) {
		$cat[] = "Potongan absensi: " . count($perKomp['37']['n']) . " karyawan, total Rp " . $rp($perKomp['37']['total']) . ".";
	}
	$ket = isset($o['keterangan']) && is_array($o['keterangan']) ? $o['keterangan'] : array();
	foreach ($ket as $k2) {
		$cat[] = $k2;
	}

	$oleh = isset($_SESSION['standard']['username']) ? $_SESSION['standard']['username'] : '';
	$sel = "padding:3px 8px;border-bottom:1px solid #cfd8e3;";
	$kartu = function ($judul, $nilai, $warna) {
		return "<div style='flex:1 1 130px;min-width:130px;background:#fff;border:1px solid #b6c6d8;border-top:3px solid " . $warna . ";border-radius:6px;padding:6px 10px'>"
			. "<div style='font-size:11px;color:#555'>" . $judul . "</div><div style='font-size:16px;font-weight:bold'>" . $nilai . "</div></div>";
	};

	$out = "<div style='font-family:Segoe UI,Arial,sans-serif;font-size:13px;line-height:1.4'>";
	$out .= "<div style='background:#e3f4e1;border:1px solid #9bd08f;border-radius:6px;padding:8px 12px;margin-bottom:8px'>"
		. "<b style='font-size:15px'>Proses Gaji " . $h($o['judul']) . " selesai</b><br>"
		. "Unit <b>" . $h($o['kodeorg']) . "</b> &middot; Periode <b>" . $h($o['periode']) . "</b> &middot; " . date('d-m-Y H:i:s')
		. ($oleh !== '' ? " &middot; oleh " . $h($oleh) : "") . "</div>";
	$out .= "<div style='display:flex;gap:8px;flex-wrap:wrap;margin-bottom:8px'>"
		. $kartu('Karyawan diproses', $jmlKary, '#4a90c2')
		. $kartu('Baris komponen tersimpan', $baris, '#4a90c2')
		. $kartu('Total Penambah', 'Rp ' . $rp($totPlus), '#3f9d4a')
		. $kartu('Total Pengurang', 'Rp ' . $rp($totMin), '#c9742a')
		. $kartu('Gaji Bersih', 'Rp ' . $rp($totBersih), '#1f5f99')
		. "</div>";

	if (count($cat) > 0) {
		$out .= "<div style='background:#fff8e1;border:1px solid #e6cf7a;border-radius:6px;padding:6px 12px;margin-bottom:8px'><b>Catatan</b><ul style='margin:4px 0 0 18px;padding:0'>";
		foreach ($cat as $c1) {
			$out .= "<li>" . $c1 . "</li>";
		}
		$out .= "</ul></div>";
	}

	$out .= "<div style='font-weight:bold;margin:6px 0 3px'>Per subbagian</div>";
	$out .= "<table style='border-collapse:collapse;width:100%;background:#fff'><tr style='background:#dfe6ee'>"
		. "<th style='" . $sel . "text-align:left'>Subbagian</th><th style='" . $sel . "'>Orang</th><th style='" . $sel . "'>Penambah</th><th style='" . $sel . "'>Pengurang</th><th style='" . $sel . "'>Gaji Bersih</th></tr>";
	foreach ($perSub as $sub => $d) {
		$out .= "<tr><td style='" . $sel . "'>" . $h($sub) . "</td><td style='" . $sel . "text-align:right'>" . $d['orang'] . "</td>"
			. "<td style='" . $sel . "text-align:right'>" . $rp($d['plus']) . "</td><td style='" . $sel . "text-align:right'>" . $rp($d['min']) . "</td>"
			. "<td style='" . $sel . "text-align:right'><b>" . $rp($d['plus'] - $d['min']) . "</b></td></tr>";
	}
	$out .= "<tr style='background:#C3DAF9;font-weight:bold'><td style='" . $sel . "'>TOTAL</td><td style='" . $sel . "text-align:right'>" . $jmlKary . "</td>"
		. "<td style='" . $sel . "text-align:right'>" . $rp($totPlus) . "</td><td style='" . $sel . "text-align:right'>" . $rp($totMin) . "</td>"
		. "<td style='" . $sel . "text-align:right'>" . $rp($totBersih) . "</td></tr></table>";

	#komponen ringkas: penambah dan pengurang berdampingan, hanya $batasKomp terbesar per kolom, sisanya digabung "Lainnya"
	$batasKomp = 6;
	$kolom = array('plus' => array(), 'min' => array());
	$beban = array('n' => array(), 'total' => 0);
	foreach ($perKomp as $c => $d) {
		if (in_array((string)$c, $bpjsPerusahaan, true)) {
			foreach ($d['n'] as $kk => $v) {
				$beban['n'][$kk] = true;
			}
			$beban['total'] += $d['total'];
			continue;
		}
		$jenis = ($d['peng'] < 0) ? 'min' : 'plus';
		$kolom[$jenis][] = array('nama' => isset($nakomp[$c]) ? $nakomp[$c] : ('Komponen ' . $c), 'n' => count($d['n']), 'total' => $d['total']);
	}
	$tabelKomp = function ($judul, $baris) use ($h, $rp, $sel, $batasKomp) {
		usort($baris, function ($a, $b) {
			return $b['total'] <=> $a['total'];
		});
		$t = "<table style='border-collapse:collapse;width:100%;background:#fff'><tr style='background:#dfe6ee'>"
			. "<th style='" . $sel . "text-align:left'>" . $judul . "</th><th style='" . $sel . "'>Orang</th><th style='" . $sel . "'>Total (Rp)</th></tr>";
		$lain = array('n' => 0, 'total' => 0, 'jml' => 0);
		foreach ($baris as $i => $b) {
			if ($i < $batasKomp) {
				$t .= "<tr><td style='" . $sel . "'>" . $h($b['nama']) . "</td><td style='" . $sel . "text-align:right'>" . $b['n'] . "</td><td style='" . $sel . "text-align:right'>" . $rp($b['total']) . "</td></tr>";
			} else {
				$lain['jml']++;
				$lain['total'] += $b['total'];
				$lain['n'] = max($lain['n'], $b['n']);
			}
		}
		if ($lain['jml'] > 0) {
			$t .= "<tr style='color:#555'><td style='" . $sel . "'>Lainnya (" . $lain['jml'] . " komponen)</td><td style='" . $sel . "text-align:right'>&le; " . $lain['n'] . "</td><td style='" . $sel . "text-align:right'>" . $rp($lain['total']) . "</td></tr>";
		}
		if (count($baris) == 0) {
			$t .= "<tr><td colspan=3 style='" . $sel . "color:#777'>-</td></tr>";
		}
		return $t . "</table>";
	};
	$out .= "<div style='font-weight:bold;margin:10px 0 3px'>Komponen terbesar</div>";
	$out .= "<div style='display:flex;gap:10px;flex-wrap:wrap;align-items:flex-start'>"
		. "<div style='flex:1 1 280px;min-width:260px'>" . $tabelKomp('Penambah', $kolom['plus']) . "</div>"
		. "<div style='flex:1 1 280px;min-width:260px'>" . $tabelKomp('Pengurang', $kolom['min']) . "</div></div>";
	if (count($beban['n']) > 0) {
		$out .= "<div style='margin-top:5px;color:#555'>Beban perusahaan (BPJS bagian perusahaan, tidak masuk gaji bersih): Rp " . $rp($beban['total']) . " untuk " . count($beban['n']) . " karyawan.</div>";
	}
	#tombol OK menutup popup (alertify.popup2 dipakai js/sdm_prosesgjbulanan.js dan sejenisnya)
	$out .= "<div style='position:sticky;bottom:0;background:#fff;text-align:right;padding:8px 0 2px;margin-top:8px;border-top:1px solid #cfd8e3'>"
		. "<button type='button' class='mybutton' style='min-width:90px;height:30px' onclick=\"alertify.popup2().destroy();\">OK</button></div>";
	$out .= "</div>";
	return $out;
}
?>
