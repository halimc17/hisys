<?php
//@Copy nangkoelframework
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

#============================================================
# Master Mapping COA - LKPM Kegiatan Penanaman Modal
# jenis : 1 = Modal Tetap, 2 = Modal Kerja
#============================================================

$method = checkPostGet('method', '');
$noakun = checkPostGet('noakun', '');
$jenis = checkPostGet('jenis', '');
$keterangan = checkPostGet('keterangan', '');
$aktif = checkPostGet('aktif', '');
$jenissrc = checkPostGet('jenissrc', '');
$aktifsrc = checkPostGet('aktifsrc', '');
$carisrc = checkPostGet('carisrc', '');

$userid = $_SESSION['standard']['userid'];
$now = date('Y-m-d H:i:s');

#--- pengamanan parameter ---
foreach (array('noakun', 'jenissrc', 'aktifsrc') as $p) {
	$v = $$p;
	if ($v === 'undefined' || $v === 'null') {
		$$p = '';
	}
}
if ($noakun !== '' && !preg_match('/^[0-9]{1,16}$/', $noakun)) {
	exit("Warning : Nomor akun tidak valid.");
}
if ($jenis !== '' && $jenis !== '1' && $jenis !== '2') {
	exit("Warning : Jenis tidak valid.");
}
if ($aktif !== '' && $aktif !== '0' && $aktif !== '1') {
	exit("Warning : Status tidak valid.");
}
if ($jenissrc !== '' && $jenissrc !== '1' && $jenissrc !== '2') {
	exit("Warning : Filter jenis tidak valid.");
}
if ($aktifsrc !== '' && $aktifsrc !== '0' && $aktifsrc !== '1') {
	exit("Warning : Filter status tidak valid.");
}

#------------------------------------------------------------
# Helper
#------------------------------------------------------------

#nama akun dari bagan akun HHO
function coaNamaAkun($noakun)
{
	global $owlPDO, $dbname;
	$kol = ($_SESSION['language'] == 'EN') ? 'namaakun1' : 'namaakun';
	$stmt = $owlPDO->prepare("select " . $kol . " from " . $dbname . ".keu_5akun where noakun=? and kodeorg='HHO'");
	$stmt->execute(array($noakun));
	return (string)$stmt->fetchColumn();
}

#apakah akun ada di bagan akun (akun daun)
function coaAkunAda($noakun)
{
	global $owlPDO, $dbname;
	$stmt = $owlPDO->prepare("select count(*) from " . $dbname . ".keu_5akun where noakun=? and kodeorg='HHO' and level>=5");
	$stmt->execute(array($noakun));
	return ((int)$stmt->fetchColumn()) > 0;
}

#apakah akun sudah terdaftar di mapping
function coaSudahAda($noakun)
{
	global $owlPDO, $dbname;
	$stmt = $owlPDO->prepare("select count(*) from " . $dbname . ".lgl_kgpnmodal_5coa where noakun=?");
	$stmt->execute(array($noakun));
	return ((int)$stmt->fetchColumn()) > 0;
}

function coaJenisTeks($j)
{
	return ($j == 1) ? 'Modal Tetap' : (($j == 2) ? 'Modal Kerja' : '-');
}

#filter grid
function coaWhereList()
{
	global $jenissrc, $aktifsrc, $carisrc;
	$where = "";
	if ($jenissrc != '') {
		$where .= " and jenis='" . $jenissrc . "' ";
	}
	if ($aktifsrc != '') {
		$where .= " and aktif='" . $aktifsrc . "' ";
	}
	if ($carisrc != '') {
		$cari = str_replace(array("'", "\\"), "", $carisrc);
		$where .= " and (noakun like '%" . $cari . "%' or namaakun like '%" . $cari . "%') ";
	}
	return $where;
}

#------------------------------------------------------------
# Routing
#------------------------------------------------------------
switch ($method) {

	#--------------------------------------------------------
	# Tambah akun ke mapping
	#--------------------------------------------------------
	case 'insert':
		if ($noakun == '') {
			exit("Warning : Nomor akun wajib dipilih.");
		}
		if (!coaAkunAda($noakun)) {
			exit("Warning : Nomor akun " . $noakun . " tidak ada di bagan akun (atau bukan akun daun).");
		}
		if (coaSudahAda($noakun)) {
			exit("Warning : Nomor akun " . $noakun . " sudah terdaftar di mapping. Gunakan tombol Ubah.");
		}
		if ($jenis != '1' && $jenis != '2') {
			exit("Warning : Jenis wajib dipilih (Modal Tetap / Modal Kerja).");
		}
		if ($aktif == '') {
			$aktif = '1';
		}
		if (strlen($keterangan) > 60) {
			exit("Warning : Keterangan maksimal 60 karakter.");
		}
		$nama = coaNamaAkun($noakun);
		$sql = "insert into " . $dbname . ".lgl_kgpnmodal_5coa
				(noakun,namaakun,jenis,keterangan,aktif,createby,createtime,updateby,updatetime)
				values (?,?,?,?,?,?,?,?,?)";
		try {
			$stmt = $owlPDO->prepare($sql);
			$stmt->execute(array($noakun, $nama, (int)$jenis, $keterangan, (int)$aktif, $userid, $now, $userid, $now));
		} catch (PDOException $e) {
			exit("Warning : Gagal menyimpan. " . $e->getMessage());
		}
		echo "OK";
		break;

	#--------------------------------------------------------
	# Ubah jenis / keterangan / status akun
	#--------------------------------------------------------
	case 'update':
		if ($noakun == '') {
			exit("Warning : Nomor akun wajib dipilih.");
		}
		if (!coaSudahAda($noakun)) {
			exit("Warning : Nomor akun " . $noakun . " belum terdaftar di mapping.");
		}
		if ($jenis != '1' && $jenis != '2') {
			exit("Warning : Jenis wajib dipilih (Modal Tetap / Modal Kerja).");
		}
		if ($aktif == '') {
			$aktif = '1';
		}
		if (strlen($keterangan) > 60) {
			exit("Warning : Keterangan maksimal 60 karakter.");
		}
		#nama akun disegarkan dari bagan akun supaya tetap sinkron
		$nama = coaNamaAkun($noakun);
		$sql = "update " . $dbname . ".lgl_kgpnmodal_5coa
				   set namaakun=?, jenis=?, keterangan=?, aktif=?, updateby=?, updatetime=?
				 where noakun=?";
		try {
			$stmt = $owlPDO->prepare($sql);
			$stmt->execute(array($nama, (int)$jenis, $keterangan, (int)$aktif, $userid, $now, $noakun));
		} catch (PDOException $e) {
			exit("Warning : Gagal mengubah. " . $e->getMessage());
		}
		echo "OK";
		break;

	#--------------------------------------------------------
	# Aktifkan / nonaktifkan akun (tanpa hapus data)
	#--------------------------------------------------------
	case 'toggle':
		if ($noakun == '') {
			exit("Warning : Nomor akun wajib dipilih.");
		}
		if (!coaSudahAda($noakun)) {
			exit("Warning : Nomor akun " . $noakun . " belum terdaftar di mapping.");
		}
		$sql = "update " . $dbname . ".lgl_kgpnmodal_5coa
				   set aktif=case when aktif=1 then 0 else 1 end, updateby=?, updatetime=?
				 where noakun=?";
		try {
			$stmt = $owlPDO->prepare($sql);
			$stmt->execute(array($userid, $now, $noakun));
		} catch (PDOException $e) {
			exit("Warning : Gagal mengubah status. " . $e->getMessage());
		}
		echo "OK";
		break;

	#--------------------------------------------------------
	# Hapus akun dari mapping
	#--------------------------------------------------------
	case 'delete':
		if ($noakun == '') {
			exit("Warning : Nomor akun wajib dipilih.");
		}
		if (!coaSudahAda($noakun)) {
			exit("Warning : Nomor akun " . $noakun . " belum terdaftar di mapping.");
		}
		try {
			$stmt = $owlPDO->prepare("delete from " . $dbname . ".lgl_kgpnmodal_5coa where noakun=?");
			$stmt->execute(array($noakun));
		} catch (PDOException $e) {
			exit("Warning : Gagal menghapus. " . $e->getMessage());
		}
		echo "OK";
		break;

	#--------------------------------------------------------
	# Daftar mapping (grid)
	#--------------------------------------------------------
	case 'loaddata':
		$where = coaWhereList();

		#ringkasan jumlah per jenis
		$sqlR = "select
					ifnull(sum(case when jenis=1 then 1 else 0 end),0) jmlmt,
					ifnull(sum(case when jenis=2 then 1 else 0 end),0) jmlmk,
					ifnull(sum(case when aktif=1 then 1 else 0 end),0) jmlaktif,
					count(*) jmltotal
				from " . $dbname . ".lgl_kgpnmodal_5coa where 1=1 " . $where;
		$stmtR = $owlPDO->query($sqlR);
		$ring = $stmtR->fetch(PDO::FETCH_ASSOC);

		$tab = "";
		$tab .= "<div style='padding:2px 0 6px 0;font-size:11px;color:#333;'>";
		$tab .= "Modal Tetap: <b>" . $ring['jmlmt'] . "</b> akun &nbsp;|&nbsp; ";
		$tab .= "Modal Kerja: <b>" . $ring['jmlmk'] . "</b> akun &nbsp;|&nbsp; ";
		$tab .= "Aktif: <b>" . $ring['jmlaktif'] . "</b> &nbsp;|&nbsp; ";
		$tab .= "Total: <b>" . $ring['jmltotal'] . "</b> akun";
		$tab .= "</div>";

		$tab .= "<table class=sortable cellspacing=1 border=0 width=100%>
			 <thead>
			 <tr class=rowheader>
				<td align=center style='width:40px;'>" . $_SESSION['lang']['nourut'] . "</td>
				<td align=center style='width:100px;'>" . $_SESSION['lang']['noakun'] . "</td>
				<td align=center>" . $_SESSION['lang']['namaakun'] . "</td>
				<td align=center style='width:110px;'>Jenis</td>
				<td align=center style='width:130px;'>" . $_SESSION['lang']['keterangan'] . "</td>
				<td align=center style='width:80px;'>" . $_SESSION['lang']['status'] . "</td>
				<td align=center style='width:90px;'>" . $_SESSION['lang']['action'] . "</td>
			 </tr>
			 </thead>
			 <tbody>";

		$sql = "select noakun,namaakun,jenis,keterangan,aktif
				from " . $dbname . ".lgl_kgpnmodal_5coa
				where 1=1 " . $where . "
				order by jenis, noakun";
		$res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no = 0;
		while ($bar = $res->fetch()) {
			$no++;
			$xx = ($no % 2 == 1) ? " style=background-color:#F5EEF8 " : "";
			$tab .= "<tr class=rowcontent " . $xx . ">";
			$tab .= "<td align=center>" . $no . "</td>";
			$tab .= "<td align=center>" . htmlspecialchars($bar['noakun']) . "</td>";
			$tab .= "<td>" . htmlspecialchars($bar['namaakun']) . "</td>";
			$tab .= "<td align=center>" . coaJenisTeks($bar['jenis']) . "</td>";
			$tab .= "<td>" . htmlspecialchars($bar['keterangan']) . "</td>";
			if ($bar['aktif'] == 1) {
				$tab .= "<td align=center><span style='color:#006600;'>Aktif</span></td>";
			} else {
				$tab .= "<td align=center><span style='color:#990000;'>Nonaktif</span></td>";
			}
			$aksi = "<img src=images/application/application_edit.png class=resicon title='Ubah' "
				. "onclick=\"fillField('" . $bar['noakun'] . "','" . $bar['jenis'] . "','"
				. str_replace(array("'", '"', "\r", "\n"), array("", "", " ", " "), $bar['keterangan']) . "','"
				. $bar['aktif'] . "');\"> ";
			$aksi .= "<img src=images/application/application_delete.png class=resicon title='Hapus' "
				. "onclick=\"hapusBaris('" . $bar['noakun'] . "');\"> ";
			$aksi .= "<img src=images/icons/" . ($bar['aktif'] == 1 ? 'lightbulb.png' : 'lightbulb_off.png') . " class=resicon title='"
				. ($bar['aktif'] == 1 ? 'Nonaktifkan' : 'Aktifkan') . "' "
				. "onclick=\"toggleBaris('" . $bar['noakun'] . "');\">";
			$tab .= "<td align=center>" . $aksi . "</td>";
			$tab .= "</tr>";
		}
		if ($no == 0) {
			$tab .= "<tr class=rowcontent><td colspan=7 align=center>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		}
		$tab .= "</tbody></table>";
		echo $tab;
		break;

	default:
		exit("Warning : Method tidak dikenal.");
}
?>
