<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$method = checkPostGet('method', '');
$param  = $_POST;

#akses sesuai detail unit user (sama seperti halaman lain): unit = lokasi tugas + detail unit user, PT = induk unit-unit tersebut
$unitAkses = getOrgDetail(1);
$ptAkses   = getOrgDetail(3);

function cekPT($pt)
{
	global $ptAkses;
	if ($pt == '' || !isset($ptAkses[$pt])) {
		exit("Warning : Anda tidak punya akses ke PT " . $pt . ".");
	}
}

function cekUnit($unit)
{
	global $unitAkses;
	if ($unit == '' || !isset($unitAkses[$unit])) {
		exit("Warning : Anda tidak punya akses ke unit " . $unit . ".");
	}
}

#nilai harus ada di tabel referensi
function adaDi($tabel, $kolom, $nilai)
{
	global $dbname;
	$res = fetchData("select " . $kolom . " from " . $dbname . "." . $tabel . " where " . $kolom . "='" . addslashes($nilai) . "' limit 1");
	return count($res) > 0;
}

#rupiah: koma ribuan dibuang, kosong dianggap 0, harus angka tidak negatif
function valRupiah($v)
{
	$v = str_replace(',', '', trim($v));
	if ($v === '') {
		return '0';
	}
	if (!preg_match('/^[0-9]+(\.[0-9]{1,4})?$/', $v)) {
		exit("Warning : Nilai rupiah tidak valid, isi dengan angka tidak negatif.");
	}
	return $v;
}

#baris plafon berdasarkan kode, sekaligus cek PT dan unitnya boleh diakses
#(baris lama dengan "unit" KHT/PKWT bukan organisasi, jadi cukup dicek PT-nya)
function barisPlafon($kode)
{
	global $dbname;
	if (!preg_match('/^[0-9]+$/', (string)$kode)) {
		exit("Warning : Kode data tidak valid.");
	}
	$res = fetchData("select * from " . $dbname . ".sdm_5plafondinas where kode='" . $kode . "'");
	if (count($res) == 0) {
		exit("Warning : Data tidak ditemukan.");
	}
	cekPT($res[0]['pt']);
	if (adaDi('organisasi', 'kodeorganisasi', $res[0]['unit'])) {
		cekUnit($res[0]['unit']);
	}
	return $res[0];
}

switch ($method) {

	case 'loadData':
		listData();
		break;

	case 'getunit':
		$pt = checkPostGet('pt', '');
		cekPT($pt);
		$optun = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$res = fetchData("select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where induk='" . addslashes($pt) . "' and length(kodeorganisasi)=4 order by namaorganisasi asc");
		foreach ($res as $bar) {
			if (isset($unitAkses[$bar['kodeorganisasi']])) {
				$optun .= "<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
			}
		}
		echo $optun;
		break;

	case 'insert':
		$pt           = trim(checkPostGet('pt', ''));
		$unit         = trim(checkPostGet('unit', ''));
		$jenis        = trim(checkPostGet('jenis', ''));
		$regional     = trim(checkPostGet('regional', ''));
		$levelkaryawan = trim(checkPostGet('levelkaryawan', ''));
		$tipekaryawan = trim(checkPostGet('tipekaryawan', ''));
		$kodegolongan = trim(checkPostGet('kodegolongan', ''));

		if ($pt == '' || $unit == '' || $jenis == '' || $regional == '') {
			exit("Warning : PT, unit, regional tujuan, dan jenis wajib diisi.");
		}
		cekPT($pt);
		cekUnit($unit);
		$resUnit = fetchData("select induk from " . $dbname . ".organisasi where kodeorganisasi='" . addslashes($unit) . "' and length(kodeorganisasi)=4");
		if (count($resUnit) == 0 || $resUnit[0]['induk'] != $pt) {
			exit("Warning : Unit " . $unit . " bukan unit dari PT " . $pt . ".");
		}
		if (!preg_match('/^[0-9]+$/', $jenis) || !adaDi('sdm_5jenisbiayapjdinas', 'id', $jenis)) {
			exit("Warning : Jenis biaya tidak valid.");
		}
		if ($regional != 'OTH' && !adaDi('sdm_5regionalpjd', 'regional', $regional)) {
			exit("Warning : Regional tujuan tidak valid.");
		}
		if ($tipekaryawan != '' && !adaDi('sdm_5tipekaryawan', 'id', $tipekaryawan)) {
			exit("Warning : Tipe karyawan tidak valid.");
		}
		if ($levelkaryawan != '' && !adaDi('sdm_5levelkaryawan', 'kode', $levelkaryawan)) {
			exit("Warning : Level karyawan tidak valid.");
		}
		if ($kodegolongan != '' && !adaDi('sdm_5golongan', 'kodegolongan', $kodegolongan)) {
			exit("Warning : Golongan tidak valid.");
		}
		$rupiah = valRupiah(checkPostGet('rupiah', ''));

		$kunci = array($pt, $unit, $regional, $levelkaryawan, $tipekaryawan, $kodegolongan, $jenis);
		$cek = $owlPDO->prepare("select kode from " . $dbname . ".sdm_5plafondinas where pt=? and unit=? and regiontujuan=? and levelkaryawan=? and tipekaryawan=? and golongan=? and jenis=?");
		$cek->execute($kunci);
		if ($cek->fetch()) {
			exit("Warning : Data sudah pernah terdaftar sebelumnya.");
		}
		try {
			$ins = $owlPDO->prepare("insert into " . $dbname . ".sdm_5plafondinas (pt,unit,jenis,levelkaryawan,tipekaryawan,golongan,jabatan,regiontujuan,uangmakandriver,tujuan,jumlah,status,updateby,lastupdate) values (?,?,?,?,?,?,0,?,0,0,?,1,?,?)");
			$ins->execute(array($pt, $unit, $jenis, $levelkaryawan, $tipekaryawan, $kodegolongan, $regional, $rupiah, $_SESSION['standard']['userid'], date("Y-m-d H:i:s")));
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;

	case 'update':
		#hanya nilai rupiah yang boleh diubah; kunci data (PT, unit, regional, tipe, level, golongan, jenis) tetap
		$kode = checkPostGet('kode', '');
		barisPlafon($kode);
		$rupiah = valRupiah(checkPostGet('rupiah', ''));
		try {
			$upd = $owlPDO->prepare("update " . $dbname . ".sdm_5plafondinas set jumlah=?, updateby=?, lastupdate=? where kode=?");
			$upd->execute(array($rupiah, $_SESSION['standard']['userid'], date("Y-m-d H:i:s"), $kode));
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;

	case 'delete':
		$kode = checkPostGet('kode', '');
		barisPlafon($kode);
		try {
			$del = $owlPDO->prepare("delete from " . $dbname . ".sdm_5plafondinas where kode=?");
			$del->execute(array($kode));
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;

	default:
		break;
}

function listData()
{
	global $dbname, $owlPDO, $param, $unitAkses, $ptAkses;

	#data dibatasi PT dan unit sesuai akses user; filter dipasang lewat parameter terikat
	$wh   = "";
	$bind = array();
	$pt   = isset($param['pt']) ? trim($param['pt']) : '';
	if ($pt != '') {
		cekPT($pt);
		$wh .= " and pt = ?";
		$bind[] = $pt;
	} else {
		$ptList = array_keys($ptAkses);
		$wh .= " and pt in (" . implode(',', array_fill(0, count($ptList), '?')) . ")";
		$bind = array_merge($bind, $ptList);
	}
	$unitList = array_keys($unitAkses);
	#baris lama dengan "unit" KHT/PKWT (bukan organisasi) ikut tampil bagi yang punya akses PT-nya
	$wh .= " and (unit in (" . implode(',', array_fill(0, count($unitList), '?')) . ") or unit not in (select kodeorganisasi from " . $dbname . ".organisasi))";
	$bind = array_merge($bind, $unitList);

	$filter = array('unit' => 'unit', 'regional' => 'regiontujuan', 'levelkaryawan' => 'levelkaryawan', 'tipekaryawan' => 'tipekaryawan', 'kodegolongan' => 'golongan', 'jenis' => 'jenis');
	foreach ($filter as $p => $kolom) {
		if (isset($param[$p]) && trim($param[$p]) != '') {
			$wh .= " and " . $kolom . " = ?";
			$bind[] = trim($param[$p]);
		}
	}

	$footer  = "";
	$limit   = 15;
	$page    = max(0, (int)checkPostGet('page', 0));
	$colspan = 11;
	$offset  = $page * $limit;

	$cnt = $owlPDO->prepare("select COUNT(*) AS jmlhrow from " . $dbname . ".sdm_5plafondinas where 1=1 " . $wh);
	$cnt->execute($bind);
	$jlhbrs  = (int)$cnt->fetchColumn();
	$totrows = ceil($jlhbrs / $limit);
	if ($totrows == 0) {
		$totrows = 1;
	}

	$isiRow = '';
	for ($er = 1; $er <= $totrows; $er++) {
		$sel     = ($page == $er - 1) ? 'selected' : '';
		$isiRow .= "<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
	}

	$frompage = $offset + 1;
	$topage   = (($page + 1) * $limit > $jlhbrs) ? $jlhbrs : (($page + 1) * $limit);

	$lst = $owlPDO->prepare("select * from " . $dbname . ".sdm_5plafondinas where 1=1 " . $wh . " order by pt asc, unit asc, regiontujuan asc,tipekaryawan asc,levelkaryawan asc,golongan asc,jenis asc limit " . $offset . "," . $limit);
	$lst->execute($bind);
	$qry = $lst->fetchAll(PDO::FETCH_ASSOC);
	if (count($qry) < 1) {
		echo "<tr class=rowcontent><td colspan='" . $colspan . "' style='text-align:center;'>" . $_SESSION['lang']['datanotfound'] . "</td></tr>";
	} else {
		$no    = $offset;
		$nmreg = makeOption($dbname, 'sdm_5regionalpjd', 'regional,nama');
		$nmreg['OTH'] = 'LAIN-LAIN';
		$nmlvlkar = makeOption($dbname, 'sdm_5levelkaryawan', 'kode,nama');
		$nmgol    = makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');
		$nmtpkar  = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
		$nmjns    = makeOption($dbname, 'sdm_5jenisbiayapjdinas', 'id,keterangan');
		$nmorg    = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
		foreach ($qry as $row) {
			$no += 1;
			echo "<tr class=rowcontent>
				<td align=center>" . $no . "</td>
				<td>" . $row['pt'] . " - " . $nmorg[$row['pt']] . " </td>
				<td>" . $row['unit'] . " - " . $nmorg[$row['unit']] . " </td>
				<td>" . $nmreg[$row['regiontujuan']] . "</td>
				<td>" . $nmtpkar[$row['tipekaryawan']] . "</td>
				<td>" . $nmlvlkar[$row['levelkaryawan']] . "</td>
				<td>" . $nmgol[$row['golongan']] . "</td>
				<td>" . $nmjns[$row['jenis']] . "</td>
				<td style='text-align:right;'>" . number_format($row['jumlah'], 2) . "</td>
				<td align=center width=20px><img src='images/skyblue/edit.png' class='zImgBtn' title='Edit' onclick=\"fillfield('" . $row['kode'] . "','" . addslashes($row['pt']) . "','" . addslashes($row['unit']) . "','" . addslashes($row['regiontujuan']) . "','" . addslashes($row['tipekaryawan']) . "','" . addslashes($row['levelkaryawan']) . "','" . addslashes($row['golongan']) . "','" . $row['jenis'] . "','" . $row['jumlah'] . "')\"></td>
				<td align=center width=20px><img src='images/skyblue/delete.png' class='zImgBtn' title='Hapus' onclick=\"deleteData('" . $row['kode'] . "')\"></td>
			</tr>";
		}

		$footer .= "<tr>
						<td colspan=" . $colspan . " align=center>
							" . $frompage . " to " . $topage . " Of " . $jlhbrs . "
						</td>
					</tr>";
		$footer .= "<tr>
						<td colspan=" . $colspan . " align=center>";
		if ($page != 0) {
			$footer .= "<button class=mybutton onclick=loadData(" . ($page - 1) . ");>Prev</button>";
		}
		$footer .= "<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";
		if (($page + 1) != $totrows) {
			$footer .= "<button class=mybutton onclick=loadData(" . ($page + 1) . ");>Next</button>";
		}
		$footer .= "</td></tr>";

		echo $footer;
	}
}
?>
