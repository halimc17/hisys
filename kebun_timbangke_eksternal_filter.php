<?php
#filter list Hasil Timbang TBS ke Eksternal, dipakai list, Excel, dan PDF supaya hasilnya selalu sama
#hasil: where = tambahan "and ..." untuk query pabrik_timbangan, info = keterangan filter untuk kop, error = pesan bila filter tidak valid
function tkeFilter($p)
{
	global $dbname;
	$g = function ($k) use ($p) {
		return isset($p[$k]) ? trim($p[$k]) : '';
	};
	$where = '';
	$info = array();
	$error = '';

	if ($g('nosbpCr') != '') {
		$where .= " and nospb like '%" . addslashes($g('nosbpCr')) . "%'";
		$info[] = 'SPB: ' . $g('nosbpCr');
	}
	if ($g('spbpabriksrc') != '') {
		$where .= " and spbpabrik like '%" . addslashes($g('spbpabriksrc')) . "%'";
		$info[] = 'SPB Pabrik: ' . $g('spbpabriksrc');
	}
	if ($g('supirsrc') != '') {
		$where .= " and supir like '%" . addslashes($g('supirsrc')) . "%'";
		$info[] = 'Supir: ' . $g('supirsrc');
	}
	if ($g('pabriktujuansrc') != '') {
		$where .= " and pabriktujuan='" . addslashes($g('pabriktujuansrc')) . "'";
		$rCus = fetchData("select namacustomer from " . $dbname . ".pmn_4customer where kodecustomer='" . addslashes($g('pabriktujuansrc')) . "'");
		$info[] = 'Tujuan Pabrik: ' . (count($rCus) > 0 ? $rCus[0]['namacustomer'] : $g('pabriktujuansrc'));
	}
	if ($g('tahuntanamsrc') != '') {
		if ($g('tahuntanamsrc') != 'Kosong') {
			$where .= " and tahuntanam like '%" . addslashes($g('tahuntanamsrc')) . "%'";
		} else {
			$where .= " and tahuntanam=''";
		}
		$info[] = 'Tahun Tanam: ' . $g('tahuntanamsrc');
	}
	if ($g('periodesrc') != '' && preg_match('/^\d{4}-\d{2}$/', $g('periodesrc'))) {
		$where .= " and left(tanggal,7)='" . $g('periodesrc') . "'";
		$info[] = 'Periode: ' . $g('periodesrc');
	}
	if ($g('tgl_cari') != '' && $g('tgl_cari_sampai') == '') {
		$where .= " and tanggal = '" . tanggalsystemn($g('tgl_cari')) . "'";
		$info[] = 'Tanggal: ' . $g('tgl_cari');
	} elseif ($g('tgl_cari_sampai') != '') {
		$where .= " and tanggal>='" . tanggalsystemn($g('tgl_cari')) . "' and tanggal<='" . tanggalsystemn($g('tgl_cari_sampai')) . " 23:59:59' ";
		$info[] = 'Tanggal: ' . $g('tgl_cari') . ' s/d ' . $g('tgl_cari_sampai');
	}
	if ($g('tgl_cari_sampai') != '' && $g('tgl_cari') == '') {
		$error = "warning : Jika tanggal sampai terisi maka tanggal dari nya harus terisi!!! ";
	}

	return array('where' => $where, 'info' => implode(' | ', $info), 'error' => $error);
}
