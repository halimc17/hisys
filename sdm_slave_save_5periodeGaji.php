<?
// ini_set('display_errors',1);
// error_reporting(1);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method = isset($_POST['method']) ? $_POST['method'] : '';
$kodeorg = trim(isset($_POST['kodeorg']) ? $_POST['kodeorg'] : '');
#insert/update mengirim metodepenggajian, tutup/buka/delete mengirim jenisgaji
$jenis = trim(isset($_POST['metodepenggajian']) ? $_POST['metodepenggajian'] : '');
if ($jenis == '' && isset($_POST['jenisgaji'])) {
	$jenis = trim($_POST['jenisgaji']);
}
$periode = trim(isset($_POST['periode']) ? $_POST['periode'] : '');
$tglmulaiinput = trim(isset($_POST['tanggalmulai']) ? $_POST['tanggalmulai'] : '');
$tglsampaiinput = trim(isset($_POST['tanggalsampai']) ? $_POST['tanggalsampai'] : '');

$unitboleh = getOrgDetail(1);
$arr = getOrgDetail(2);
@$whereunitlist = " and kodeorg in (" . $arr . ")";

#filter pencarian: hanya nilai yang valid yang dipakai
$unitcari = isset($_POST['unitcari']) ? trim($_POST['unitcari']) : '';
$periodecari = isset($_POST['periodecari']) ? trim($_POST['periodecari']) : '';
$statcari = isset($_POST['statcari']) ? trim($_POST['statcari']) : '';
if ($unitcari != '' && isset($unitboleh[$unitcari])) {
	$whereunitlist .= " and kodeorg='" . addslashes($unitcari) . "'";
}
if (preg_match('/^\d{4}-\d{2}$/', $periodecari)) {
	$whereunitlist .= " and periode='" . $periodecari . "'";
}
if ($statcari === '0' || $statcari === '1') {
	$whereunitlist .= " and sudahproses='" . $statcari . "'";
}

#validasi data periode gaji, pesan berawalan "Warning" supaya tampil sebagai peringatan di halaman
function valPeriodeGaji($kodeorg, $periode, $jenis)
{
	global $unitboleh;
	if ($kodeorg == '' || $periode == '' || $jenis == '') {
		exit("Warning : Unit kerja, metode penggajian, dan periode wajib diisi.");
	}
	if (!isset($unitboleh[$kodeorg])) {
		exit("Warning : Unit kerja tidak valid.");
	}
	if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periode)) {
		exit("Warning : Format periode harus YYYY-MM.");
	}
	if ($jenis != 'H' && $jenis != 'B') {
		exit("Warning : Metode penggajian tidak valid.");
	}
}

#tanggal input berformat dd-mm-yyyy, hasilnya yyyy-mm-dd; harus valid dan di dalam bulan periode
function valTanggalPeriode($periode, $mulaiinput, $sampaiinput)
{
	$hasil = array();
	foreach (array('mulai' => $mulaiinput, 'sampai' => $sampaiinput) as $k => $v) {
		if (!preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $v, $m) || !checkdate((int)$m[2], (int)$m[1], (int)$m[3])) {
			exit("Warning : Tanggal " . $k . " tidak valid, gunakan format dd-mm-yyyy.");
		}
		$hasil[$k] = $m[3] . '-' . $m[2] . '-' . $m[1];
	}
	if ($hasil['sampai'] < $hasil['mulai']) {
		exit("Warning : Tanggal sampai tidak boleh lebih kecil dari tanggal mulai.");
	}
	if (substr($hasil['mulai'], 0, 7) != $periode || substr($hasil['sampai'], 0, 7) != $periode) {
		exit("Warning : Tanggal mulai dan sampai harus berada di bulan periode " . $periode . ".");
	}
	return $hasil;
}

#status periode gaji: null kalau belum ada, '0' terbuka, '1' ditutup
function statusPeriodeGaji($kodeorg, $periode, $jenis)
{
	global $dbname;
	$res = fetchData("select sudahproses from " . $dbname . ".sdm_5periodegaji where kodeorg='" . addslashes($kodeorg) . "' and periode='" . addslashes($periode) . "' and jenisgaji='" . addslashes($jenis) . "'");
	return count($res) == 0 ? null : (string)$res[0]['sudahproses'];
}

switch ($method) {
	case 'tutup':
		valPeriodeGaji($kodeorg, $periode, $jenis);
		$st = statusPeriodeGaji($kodeorg, $periode, $jenis);
		if ($st === null) {
			exit("Warning : Periode gaji tidak ditemukan.");
		}
		if ($st == '1') {
			exit("Warning : Periode gaji sudah ditutup.");
		}
		$str = "update " . $dbname . ".sdm_5periodegaji set sudahproses='1' where kodeorg='" . $kodeorg . "' and periode='" . $periode . "' and jenisgaji='" . $jenis . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>";
			die();
		}
		break;
	case 'buka':
		valPeriodeGaji($kodeorg, $periode, $jenis);
		$st = statusPeriodeGaji($kodeorg, $periode, $jenis);
		if ($st === null) {
			exit("Warning : Periode gaji tidak ditemukan.");
		}
		if ($st == '0') {
			exit("Warning : Periode gaji masih terbuka.");
		}

		$str = "select periode from " . $dbname . ".setup_periodeakuntansi where periode = '" . $periode . "' and kodeorg = '" . $kodeorg . "' and tutupbuku = 1";
		$res = fetchdata($str);
		$periodeaku = $res[0]['periode'];

		if ($periodeaku != '') {
			exit("Warning : Periode akuntansi sudah ditutup silahkan buka periode akuntansi terlebih dahulu untuk membuka periode gaji");
		}

		## Karena gak ada HO bisa di unpost dari unit
		// if ($_SESSION['empl']['tipelokasitugas']=='KANWIL' or $_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$str = "update " . $dbname . ".sdm_5periodegaji set sudahproses='0' where kodeorg='" . $kodeorg . "' and periode='" . $periode . "' and jenisgaji='" . $jenis . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>";
			die();
		}
		// }else{
		// 	exit("Warning : Untuk melakukan unposting / buka periode gaji silahkan hubungi HR di RO atau HO.");
		// }
		break;
	case 'update':
		valPeriodeGaji($kodeorg, $periode, $jenis);
		$st = statusPeriodeGaji($kodeorg, $periode, $jenis);
		if ($st === null) {
			exit("Warning : Periode gaji tidak ditemukan.");
		}
		if ($st == '1') {
			exit("Warning : Periode gaji sudah ditutup, tidak bisa diubah. Buka periode terlebih dahulu.");
		}
		$tgl = valTanggalPeriode($periode, $tglmulaiinput, $tglsampaiinput);
		$str = "update " . $dbname . ".sdm_5periodegaji set tanggalmulai='" . $tgl['mulai'] . "', tanggalsampai='" . $tgl['sampai'] . "'
		        where kodeorg='" . $kodeorg . "' and periode='" . $periode . "' and jenisgaji='" . $jenis . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>";
			die();
		}
		break;
	case 'insert':
		valPeriodeGaji($kodeorg, $periode, $jenis);
		$tgl = valTanggalPeriode($periode, $tglmulaiinput, $tglsampaiinput);
		if (statusPeriodeGaji($kodeorg, $periode, $jenis) !== null) {
			exit("Warning : Periode gaji " . $periode . " untuk unit " . $kodeorg . " dengan metode " . ($jenis == 'H' ? 'Harian' : 'Bulanan') . " sudah ada, silahkan cek pada daftar.");
		}
		$str = "insert into " . $dbname . ".sdm_5periodegaji
		        (kodeorg,periode,tanggalmulai,tanggalsampai,sudahproses,natura,jenisgaji,kg,harga)
		        values('" . $kodeorg . "','" . $periode . "','" . $tgl['mulai'] . "','" . $tgl['sampai'] . "',0,0,'" . $jenis . "',0,0)";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>";
			die();
		}
		break;
	case 'delete':
		valPeriodeGaji($kodeorg, $periode, $jenis);
		$st = statusPeriodeGaji($kodeorg, $periode, $jenis);
		if ($st === null) {
			exit("Warning : Periode gaji tidak ditemukan.");
		}
		if ($st == '1') {
			exit("Warning : Periode gaji sudah ditutup, tidak bisa dihapus.");
		}
		#periode yang sudah dipakai data gaji, potongan, atau pendapatan lain tidak boleh dihapus
		$arrPakai = array(
			'sdm_gaji' => 'Gaji',
			'sdm_potonganht' => 'Potongan',
			'sdm_pendapatanlainht' => 'Pendapatan Lain',
		);
		foreach ($arrPakai as $tbl => $nm) {
			$cek = fetchData("select 1 from " . $dbname . "." . $tbl . " where kodeorg='" . addslashes($kodeorg) . "' and periodegaji='" . addslashes($periode) . "' limit 1");
			if (count($cek) > 0) {
				exit("Warning : Periode tidak bisa dihapus karena sudah ada data " . $nm . " pada periode " . $periode . " unit " . $kodeorg . ".");
			}
		}
		$str = "delete from " . $dbname . ".sdm_5periodegaji
		        where kodeorg='" . $kodeorg . "' and periode='" . $periode . "' and jenisgaji='" . $jenis . "' and sudahproses=0";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>";
			die();
		}
		break;
	case 'loaddata':
		$str1 = "select *,
		             case jenisgaji when 'H' then '" . $_SESSION['lang']['harian'] . "'
		                 when 'B' then '" . $_SESSION['lang']['bulanan'] . "'
		                 end as ketgroup,
		                 case sudahproses when '1' then '" . $_SESSION['lang']['yes'] . "'
		                 when '0' then '" . $_SESSION['lang']['no'] . "'
		                 end as sts,
		                 case natura when '1' then '" . $_SESSION['lang']['yes'] . "'
		                 when '0' then '" . $_SESSION['lang']['no'] . "'
		                 end as statnatura
		             from " . $dbname . ".sdm_5periodegaji
		                 where 1=1 " . $whereunitlist . "
		                 order by periode desc";
		// echo $str1;
		$res = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar1 = $res->fetch()) {
			echo "<tr class=rowcontent>
			<td align=center>" . $bar1->kodeorg . "</td>
			<td>" . $bar1->ketgroup . "</td>
			<td align=center>" . substr(tanggalnormal($bar1->periode), 1, 7) . "</td>
			<td align=center>" . tanggalnormal($bar1->tanggalmulai) . "</td>
			<td align=center>" . tanggalnormal($bar1->tanggalsampai) . "</td>
			<td align=right  hidden>" . $bar1->kg . "</td>
			<td align=right hidden>" . number_format($bar1->harga) . "</td>
			<td align=center>" . $bar1->sts . "</td>
			<td align=center hidden>" . $bar1->statnatura . "</td>";
			if ($bar1->sudahproses == '0') {
				echo "<td align=center><img src=images/application/application_edit.png class=zImgBtn title=Edit onclick=\"fillField('" . $bar1->kodeorg . "','" . $bar1->jenisgaji . "','" . $bar1->periode . "','" . tanggalnormal($bar1->tanggalmulai) . "','" . tanggalnormal($bar1->tanggalsampai) . "');\"></td>";
				echo "<td align=center><img src=images/application/application_delete.png class=zImgBtn title=Hapus onclick=\"hapus('" . $bar1->kodeorg . "','" . $bar1->jenisgaji . "','" . $bar1->periode . "');\"></td>";
				echo "<td align=center><img src=images/skyblue/posting.png class=zImgBtn height=30 title='Tutup periode' onclick=tutup('" . $bar1->kodeorg . "','" . $bar1->jenisgaji . "','" . $bar1->periode . "')></td>";
			} else {
				echo "<td></td><td></td><td align=center><img src=images/icons/04/16/04.png class=zImgBtn height=30 title='Periode ditutup, klik untuk membuka' onclick=buka('" . $bar1->kodeorg . "','" . $bar1->jenisgaji . "','" . $bar1->periode . "')></td>";
			}

			echo "</tr>";
		}
		break;
	default:
		break;
}
?>
