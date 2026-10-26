<?php
require_once('master_validation.php');
require_once('lib/zLib.php');


$method = checkPostGet('method', '');
$param = $_POST;
if (count($param) == 0) {
	$param = $_GET;
}

switch ($method) {
	case 'preview':
		$str = "select *  from " . $dbname . ".sdm_ho_component";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$idcomp[$bar['id']] = $bar['name'];
			$plus[$bar['id']] = $bar['plus'];
		}

		$str = "select *  from " . $dbname . ".bgt_regional_assignment";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nmreg[$bar['kodeunit']] = $bar['subregional'];
		}

		$str = "select *  from " . $dbname . ".sdm_5jabatan";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nmjab[$bar['kodejabatan']] = $bar['namajabatan'];
		}
		$str = "select *  from " . $dbname . ".sdm_5tipekaryawan";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$tipekar[$bar['id']] = $bar['tipe'];
		}
		$str = "select *  from " . $dbname . ".sdm_5departemen";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nmdept[$bar['kode']] = $bar['nama'];
		}
		$str = "select *  from " . $dbname . ".sdm_5golongan";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nmgol[$bar['kodegolongan']] = $bar['namagolongan'];
		}

		$str = "select *  from " . $dbname . ".sdm_5pendidikan";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nmpdd[$bar['levelpendidikan']] = $bar['pendidikan'];
		}
		$str = "select *  from " . $dbname . ".sdm_5suku";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nmsuku[$bar['idsuku']] = $bar['namasuku'];
		}
		$str = "select *  from " . $dbname . ".provinsi";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$prov[$bar['id']] = $bar['provinsi'];
		}
		$str = "select *  from " . $dbname . ".kabupaten";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$kab[$bar['id']] = $bar['kabupaten'];
		}
		$str = "select *  from " . $dbname . ".keu_5daftarbank";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nmbank[$bar['kodebank']] = $bar['namabank'];
		}
		$kab["LOKAL"] = "LOKAL";


		$datatipe = array();
		$strx = "select * from " . $dbname . ".sdm_5tipekaryawan where aktif='1'";
		//echo $strx;
		$resn = fetchdata($strx);
		foreach ($resn as $bar) {
			$datatipe[$bar['id']] = $bar['tipe'];
		}

		$datacountmax = intval(count($datatipe));
		$datacount = intval(count($datatipe) / 2);



		$tab .= "<table id=mytable class='sortable nowrap' cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['nourut']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['nik']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['namakaryawan']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['jabatan']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['kodegolongan']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['tipekaryawan']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['status'] . " " . $_SESSION['lang']['karyawan']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['regional']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['pt']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['lokasitugas']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['divisi']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['departemen']) . "</th>
				<th style='text-align:center;'>SUB " . strtoupper($_SESSION['lang']['departemen']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['poh']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['noktp']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['nokk']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['pendidikan']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['alokasi']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['statuspajak']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['statusperkawinan']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['tanggalmenikah']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['jumlahanak']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['tanggalmasuk']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['tanggalpengangkatan']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['tanggalselesai']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['tempatlahir']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['tanggallahir']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['umur']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['umur']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['warganegara']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['jeniskelamin']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['agama']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['golongandarah']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['provinsi']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['kabupaten'] . " / " . $_SESSION['lang']['kota']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['kecamatan']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['desa'] . " / " . $_SESSION['lang']['kelurahan']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['kodepos']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['alamat']) . " KTP</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['telp']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['nohp']) . " (1)</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['nohp']) . " (2)</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['notelepondarurat']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['email']) . " PRIBADI</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['email']) . " KANTOR</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['rekening']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['norekeningbank']) . "</th>
				<th style='text-align:center;'>A/N REKENING BANK</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['sistemgaji']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['npwp']) . "</th>
				<th style='text-align:center;'>BPJS KES</th>
				<th style='text-align:center;'>BPJS TK</th>
				<th style='text-align:center;'>BPJS JP</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['jumlahtanggunganbpjs']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['suku']) . "</th>
				<th style='text-align:center;'>SIM</th>
				<th style='text-align:center;'>KODE CATU</th>
				<th style='text-align:center;'>NIK SEBELUMNYA</th>
			</tr>
		</thead>
		<tbody >";



		if ($param['periode'] == '') {
			exit("Warning : Periode harus dipilih.");
		}
		if (!preg_match('/^\d{4}-\d{2}$/', $param['periode'])) {
			exit("Warning : Periode tidak valid.");
		}
		$fTipe = isset($param['tipekaryawan']) ? trim($param['tipekaryawan']) : '';
		$fOrg = isset($param['kodeorg']) ? trim($param['kodeorg']) : '';
		$fTgl = isset($param['tgl1']) ? trim($param['tgl1']) : '';
		if ($fTipe != '' && !ctype_digit($fTipe)) {
			exit("Warning : Tipe karyawan tidak valid.");
		}
		$unitAkses = getOrgDetail(2);
		if ($fOrg != '' && (!preg_match('/^[A-Za-z0-9_]+$/', $fOrg) || strpos($unitAkses, "'" . $fOrg . "'") === false)) {
			exit("Warning : Unit tidak valid atau di luar hak akses Anda.");
		}
		if ($fTgl != '' && !preg_match('/^\d{2}-\d{2}-\d{4}$/', $fTgl)) {
			exit("Warning : Format tanggal tidak valid.");
		}
		$fTglDb = $fTgl != '' ? tanggalsystemn($fTgl) : '';

		$where = "";
		if ($fTipe != '') {
			$where .= " and tipekaryawan='" . $fTipe . "'";
		}
		if ($fOrg != '') {
			$where .= " and lokasitugas='" . $fOrg . "'";
		} else {
			$where .= " and lokasitugas in (" . $unitAkses . ")";
		}
		$exAdmin = " and namakaryawan not like '%ADMINISTRATOR%'";

		## Data bulanan (snapshot B) periode yang dipilih
		$res = fetchdata("select * from " . $dbname . ".datakaryawan_hist where 1=1 " . $where . " and version_type='B' and periodegaji='" . $param['periode'] . "'" . $exAdmin);
		$unitSnapshot = array();
		foreach ($res as $bar) {
			$unitSnapshot[addslashes($bar['lokasitugas'])] = 1;
		}
		## Periode berjalan (belum ada snapshot lengkap): unit tanpa snapshot memakai data karyawan saat ini
		if ($param['periode'] >= date('Y-m')) {
			$whereNow = $where;
			if (!empty($unitSnapshot)) {
				$whereNow .= " and lokasitugas not in ('" . implode("','", array_keys($unitSnapshot)) . "')";
			}
			$resNow = fetchdata("select * from " . $dbname . ".datakaryawan where 1=1 " . $whereNow . $exAdmin);
			$res = array_merge($res, $resNow);
		}
		usort($res, function ($a, $b) {
			return strcasecmp($a['namakaryawan'], $b['namakaryawan']);
		});

		$idKec = $idDes = array();
		foreach ($res as $bar) {
			if ($bar['kecamatan'] != '') {
				$idKec[addslashes($bar['kecamatan'])] = 1;
			}
			if ($bar['desa'] != '') {
				$idDes[addslashes($bar['desa'])] = 1;
			}
		}
		$kec = $des = array();
		if (!empty($idKec)) {
			$rk = fetchdata("select * from " . $dbname . ".kecamatan where idkec in ('" . implode("','", array_keys($idKec)) . "')");
			foreach ($rk as $bar) {
				$kec[$bar['idkec']] = $bar['kecamatan'];
			}
		}
		if (!empty($idDes)) {
			$rd = fetchdata("select * from " . $dbname . ".desa where iddes in ('" . implode("','", array_keys($idDes)) . "')");
			foreach ($rd as $bar) {
				$des[$bar['iddes']] = $bar['desa'];
			}
		}

		$optjk = array("L" => "Laki - Laki", "P" => "Perempuan");
		$nmalokasi = array("0" => "UNIT", "1" => "UMUM");
		$country   = readCountry("./config/country.lst");
		for ($x = 0; $x < count($country); $x++) {
			$nmnegara[$country[$x][2]] = $country[$x][0];
		}

		$periodeLalu = periodelalu($param['periode']);
		$optNIKLAMA = makeOption($dbname, 'datakaryawan_hist', 'karyawanid,nik', " version_type='B' and periodegaji='" . $periodeLalu . "' ");

		$data = array();
		foreach ($res as $bar) {
			$no++;
			if ($bar['tanggalmasuk'] == "0000-00-00") {
				$bar['tanggalmasuk'] = "";
			}
			if ($bar['tanggalpengangkatan'] == "0000-00-00") {
				$bar['tanggalpengangkatan'] = "";
			}
			if ($bar['tanggalkeluar'] == "0000-00-00") {
				$bar['tanggalkeluar'] = "";
			}
			if ($bar['tanggallahir'] == "0000-00-00") {
				$bar['tanggallahir'] = "";
			}
			if ($bar['tanggalmenikah'] == "0000-00-00") {
				$bar['tanggalmenikah'] = "";
			}

			if ($bar['noktp'] != '') {
				$bar['noktp'] = "'" . $bar['noktp'];
			}
			if ($bar['no_keluarga'] != '') {
				$bar['no_keluarga'] = "'" . $bar['no_keluarga'];
			}
			if ($bar['subbagian'] != '') {
				$subbagian = $bar['subbagian'] . " - " . getNamaOrg($bar['subbagian']);
			} else {
				$subbagian = "";
			}

			$years = $umurtext = '';
			if ($bar['tanggallahir'] != '' && strtotime($bar['tanggallahir']) !== false) {
				$selisih = date_diff(date_create($bar['tanggallahir']), date_create(date('Y-m-d')));
				$years = $selisih->y;
				$umurtext = $selisih->y . " tahun, " . $selisih->m . " bulan, " . $selisih->d . " hari";
			}

			$data[] = array(
				$no,
				strtoupper($bar['nik']),
				strtoupper($bar['namakaryawan']),
				strtoupper($nmjab[$bar['kodejabatan']]),
				strtoupper($nmgol[$bar['kodegolongan']]),
				strtoupper($tipekar[$bar['tipekaryawan']]),
				strtoupper($bar['statuskaryawan']),
				strtoupper($nmreg[$bar['lokasitugas']]),
				strtoupper(getNamaOrg($bar['kodeorganisasi'])),
				strtoupper(getNamaOrg($bar['lokasitugas'])),
				strtoupper($subbagian),
				strtoupper($nmdept[$bar['bagian']]),
				strtoupper($nmdept[$bar['subdept']]),
				strtoupper($kab[$bar['lokasipenerimaan']]),
				strtoupper(strval($bar['noktp'])),
				strtoupper($bar['no_keluarga']),
				strtoupper($nmpdd[$bar['levelpendidikan']]),
				strtoupper($nmalokasi[$bar['alokasi']]),
				strtoupper($bar['statuspajak']),
				strtoupper($bar['statusperkawinan']),
				strtoupper($bar['tanggalmenikah']),
				strtoupper($bar['jumlahanak']),
				strtoupper($bar['tanggalmasuk']),
				strtoupper($bar['tanggalpengangkatan']),
				strtoupper($bar['tanggalkeluar']),
				strtoupper($bar['tempatlahir']),
				strtoupper($bar['tanggallahir']),
					$years,
					$umurtext,
				strtoupper($nmnegara[$bar['warganegara']]),
				strtoupper($optjk[$bar['jeniskelamin']]),
				strtoupper($bar['agama']),
				strtoupper($bar['golongandarah']),
				strtoupper($prov[$bar['provinsi']]),
				strtoupper($kab[$bar['kabupaten']]),
				strtoupper($kec[$bar['kecamatan']]),
				strtoupper($des[$bar['desa']]),
				strtoupper($bar['kodepos']),
				strtoupper($bar['alamataktif']),
				strtoupper($bar['noteleponrumah']),
				strtoupper($bar['nohp']),
				strtoupper($bar['nohp2']),
				strtoupper($bar['notelepondarurat']),
				$bar['email'],
				$bar['emailkantor'],
				strtoupper($nmbank[$bar['namabank']]),
				strtoupper($bar['norekeningbank']),
				$bar['pemilikrekening'],
				strtoupper($bar['sistemgaji']),
				strtoupper($bar['npwp']),
				strtoupper($bar['bpjs']),
				strtoupper($bar['jms']),
				strtoupper($bar['pensiun']),
				strtoupper($bar['jumlahtanggungan']),
				strtoupper($nmsuku[$bar['suku']]),
				strtoupper($bar['sim']),
				strtoupper($bar['kodecatu']),
				strtoupper($optNIKLAMA[$bar['karyawanid']])
			);
		}

		$tab .= "</tbody>
		<tfoot>
		</tfoot>
		</table>";

		## Info jumlah karyawan aktif / tidak aktif per tipe (menurut tanggal status bila diisi)
		$jmlAktif = $jmlTidak = array();
		foreach ($res as $bar) {
			$keluar = $bar['tanggalkeluar'];
			$sudahKeluar = ($keluar != '' && $keluar != '0000-00-00');
			if ($fTglDb != '') {
				$sudahKeluar = $sudahKeluar && $keluar <= $fTglDb;
			}
			if ($sudahKeluar) {
				$jmlTidak[$bar['tipekaryawan']] = (isset($jmlTidak[$bar['tipekaryawan']]) ? $jmlTidak[$bar['tipekaryawan']] : 0) + 1;
			} else {
				$jmlAktif[$bar['tipekaryawan']] = (isset($jmlAktif[$bar['tipekaryawan']]) ? $jmlAktif[$bar['tipekaryawan']] : 0) + 1;
			}
		}
		$kolomTipe = array();
		foreach ($datatipe as $idTipe => $namaTipe) {
			$a = isset($jmlAktif[$idTipe]) ? $jmlAktif[$idTipe] : 0;
			$t = isset($jmlTidak[$idTipe]) ? $jmlTidak[$idTipe] : 0;
			if ($a + $t > 0) {
				$kolomTipe[$idTipe] = array($namaTipe, $a, $t);
			}
		}
		$thInfo = "<th>STATUS</th>";
		$trAktif = "<td>Aktif</td>";
		$trTidak = "<td>Tidak Aktif</td>";
		$trTotal = "<td><b>Total</b></td>";
		$sumA = $sumT = 0;
		foreach ($kolomTipe as $kt) {
			$thInfo .= "<th>" . htmlspecialchars($kt[0]) . "</th>";
			$trAktif .= "<td align=right>" . $kt[1] . "</td>";
			$trTidak .= "<td align=right>" . $kt[2] . "</td>";
			$trTotal .= "<td align=right><b>" . ($kt[1] + $kt[2]) . "</b></td>";
			$sumA += $kt[1];
			$sumT += $kt[2];
		}
		$thInfo .= "<th>TOTAL</th>";
		$trAktif .= "<td align=right><b>" . $sumA . "</b></td>";
		$trTidak .= "<td align=right><b>" . $sumT . "</b></td>";
		$trTotal .= "<td align=right><b>" . ($sumA + $sumT) . "</b></td>";
		$info = "<fieldset style='float:left;'><legend>Info</legend><table class='sortable' cellspacing='1' cellpadding='3' border='0'><thead><tr class=rowheader>" . $thInfo . "</tr></thead><tbody><tr class=rowcontent>" . $trAktif . "</tr><tr class=rowcontent>" . $trTidak . "</tr><tr class=rowcontent>" . $trTotal . "</tr></tbody></table></fieldset><div style='clear:both;'></div>";

		## teks di-escape (data dirender DataTables sebagai HTML); byte non-UTF-8 diganti agar JSON tidak gagal
		foreach ($data as $k => $row) {
			foreach ($row as $i => $v) {
				if (is_string($v)) {
					$data[$k][$i] = htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
				}
			}
		}
		echo $info . $tab . "####" . json_encode($data);
		break;

	case 'excel':
		$nop = "csbm.xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet('csbm', $tab);
		$xls->headers($nop);
		echo $xls->buildFile();
		break;
}

function nantozero($e, $i = 0)
{
	if (is_nan($e)) {
		$e = 0;
	} else {
		$e = $e;
	}
	return number_format($e, $i);
}

function numb_format($a, $d = 0)
{
	$n = hidezerodecimal($a, $d);
	#$n = number_format($a,$d);
	if ($n == '0' or $n == '') {
		$n = "";
	} else {
		$n = $n;
	}
	return $n;
}
function bagi($e, $i)
{
	if ($i != '' and $i != '0') {
		$n = $e / $i;
	} else {
		$n = 0;
	}
	return $n;
}
