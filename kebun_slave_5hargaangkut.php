<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
require_once('dompdf/PHPExcel/IOFactory.php');
require('lib/xlsxwriter.class.php');

use Dompdf\Dompdf;
use Mpdf\Tag\P;

$method        = checkPostGet('method', '');
$unit          = checkPostGet('unit', '');
$blok          = checkPostGet('blok', '');
$divisi        = checkPostGet('divisi', '');
$kegiatan      = checkPostGet('kegiatan', '');
$find_divisi   = checkPostGet('find_divisi', '');
$find_blok     = checkPostGet('find_blok', '');
$find_tt       = checkPostGet('find_tt', '');
$tahuntanam    = checkPostGet('tahuntanam', '');
$muat_tphpks1   = checkPostGet('muat_tphpks1', '');
$muat_tphpks2   = checkPostGet('muat_tphpks2', '');
$muat_tphpks3   = checkPostGet('muat_tphpks3', '');
$muat_rampks   = checkPostGet('muat_rampks', '');
$muat_tphpks5   = checkPostGet('muat_tphpks5', '');
$muat_tphpks6   = checkPostGet('muat_tphpks6', '');
$muat_tphpks7   = checkPostGet('muat_tphpks7', '');
$angkut_tphpks1 = checkPostGet('angkut_tphpks1', '');
$angkut_tphpks2 = checkPostGet('angkut_tphpks2', '');
$angkut_tphpks3 = checkPostGet('angkut_tphpks3', '');
$angkut_rampks = checkPostGet('angkut_rampks', '');
$angkut_tphpks5 = checkPostGet('angkut_tphpks5', '');
$angkut_tphpks6 = checkPostGet('angkut_tphpks6', '');
$angkut_tphpks7 = checkPostGet('angkut_tphpks7', '');
$muat_tphpks1   = str_replace(",", "", $muat_tphpks1);
$muat_tphpks2   = str_replace(",", "", $muat_tphpks2);
$muat_tphpks3   = str_replace(",", "", $muat_tphpks3);
$muat_rampks   = str_replace(",", "", $muat_rampks);
$muat_tphpks5   = str_replace(",", "", $muat_tphpks5);
$muat_tphpks6   = str_replace(",", "", $muat_tphpks6);
$muat_tphpks7   = str_replace(",", "", $muat_tphpks7);
$angkut_tphpks1 = str_replace(",", "", $angkut_tphpks1);
$angkut_tphpks2 = str_replace(",", "", $angkut_tphpks2);
$angkut_tphpks3 = str_replace(",", "", $angkut_tphpks3);
$angkut_rampks = str_replace(",", "", $angkut_rampks);
$angkut_tphpks5 = str_replace(",", "", $angkut_tphpks5);
$angkut_tphpks6 = str_replace(",", "", $angkut_tphpks6);
$angkut_tphpks7 = str_replace(",", "", $angkut_tphpks7);
$pkstujuan     = checkPostGet('pkstujuan', '');
$jenisvhc      = checkPostGet('jenisvhc', '');
$pkstujuanht   = checkPostGet('pkstujuanht', '');
$jnskendht     = checkPostGet('jnskendht', '');
$namafee       = checkPostGet('namafee', '');
$jenisfeex     = checkPostGet('jenisfeex', '');
$jenisfee      = checkPostGet('jenisfee', '');
$rpfee         = checkPostGet('rpfee', '');
$keyfee        = checkPostGet('key', '');
$nofee         = checkPostGet('no', '');
$namafile      = checkPostGet('namafile', '');
$tanggalberlaku = checkPostGet('tanggalberlaku', '');
$rpfee        = str_replace(",", "", $rpfee);

if (count($_POST) > 0) {
	$param = $_POST;
} else {
	$param = $_GET;
}

$sql = "SELECT * FROM " . $dbname . ".keu_5akun";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
	$optakun[$bar['noakun']] = $bar['namaakun'];
}

$sql = "SELECT * FROM " . $dbname . ".setup_kegiatan";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
	$optakun[$bar['kodekegiatan']] = $bar['namakegiatan'];
}

$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmvhc = makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc');
$nmvhc['GLOBAL'] = 'GLOBAL';
switch ($method) {
	case 'getblok':
		$opt = "<option value=''></option>";
		$sql = "SELECT * FROM " . $dbname . ".organisasi where tipe ='BLOK' and kodeorganisasi like '" . $find_divisi . "%'";
		$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $qry->fetch()) {
			$opt .= "<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . "</option>";
		}

		$opttt = "<option value=''></option>";
		$sql = "SELECT distinct tahuntanam FROM " . $dbname . ".setup_blok where kodeorg like '" . $find_divisi . "%' order by tahuntanam asc";
		$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $qry->fetch()) {
			$opttt .= "<option value=" . $bar['tahuntanam'] . ">" . $bar['tahuntanam'] . "</option>";
		}

		echo $opt . "####" . $opttt;
		break;
	case 'getfindtt':
		$opttt = "<option value=''></option>";
		$sql = "SELECT distinct tahuntanam FROM " . $dbname . ".setup_blok where kodeorg like '" . $find_blok . "%' order by tahuntanam asc";
		$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $qry->fetch()) {
			$opttt .= "<option value=" . $bar['tahuntanam'] . ">" . $bar['tahuntanam'] . "</option>";
		}
		echo $opttt;
		break;
	case 'gettahuntanam':
		$opt = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		$sql = "SELECT * FROM " . $dbname . ".organisasi where tipe ='AFDELING' and kodeorganisasi like '" . $unit . "%'";
		$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $qry->fetch()) {
			$opt .= "<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $nmorg[$bar['kodeorganisasi']] . "</option>";
		}

		if ($unit != '') {
			$wh = " and kodeorg like '" . $unit . "%'";
		}
		if ($divisi != '') {
			$wh = " and kodeorg like '" . $divisi . "%'";
		}


		$opttt = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		$sql = "SELECT distinct tahuntanam FROM " . $dbname . ".setup_blok where 1=1 " . $wh . " order by tahuntanam asc";
		$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $qry->fetch()) {
			$opttt .= "<option value=" . $bar['tahuntanam'] . ">" . $bar['tahuntanam'] . "</option>";
		}
		if ($param['tahuntanam'] != '') {
			$wh .= " and tahuntanam = '" . $param['tahuntanam'] . "'";
		}

		$blok = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		$sql = "SELECT distinct indukblok FROM " . $dbname . ".setup_blok where 1=1 " . $wh . " order by kodeorg asc";
		$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $qry->fetch()) {
			$nminduk = makeOption($dbname, 'organisasi', 'indukblok,namaindukblok', "indukblok='" . $bar['indukblok'] . "'");
			$blok .= "<option value=" . $bar['indukblok'] . ">" . $bar['indukblok'] . " - " . $nminduk[$bar['indukblok']] . "</option>";
		}

		$tanggalberlaku = tanggalsystemn($tanggalberlaku);
		$periode = substr($tanggalberlaku, 0, 7);


		$namasupp = array();
		$optsupp = "<option value=''>" . $_SESSION['lang']['default'] . "</option>";
		$sql = "SELECT a.* FROM " . $dbname . ".log_spkht a 
		left join " . $dbname . ".lgl_pengajuanspkht b on a.nopengajuan=b.notransaksi 
		where a.posting='0' and b.close='0' and b.jenis='ANGKUTTBS' and a.kodeorg='" . $unit . "' and substr(a.dari,1,7)<='" . $periode . "' and substr(a.sampai,1,7)>='" . $periode . "' order by a.notransaksi asc";
		$res = fetchdata($sql);

		foreach ($res as $bar) {
			$namasupp = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid='" . $bar['koderekanan'] . "'");

			$optsupp .= "<option value='" . $bar['notransaksi'] . "'>" . $bar['notransaksi'] . " - " . $namasupp[$bar['koderekanan']] . "</option>";
		}


		echo $opt . "####" . $opttt . "####" . $blok . "####" . $optsupp;
		break;
	case 'previewdetail':
		if ($unit == '') {
			exit("Warning : Unit harus diisi.");
		}
		$wh = "";
		$whdt = "";
		if ($divisi != '') {
			$wh .= " and kodeorg like '" . $divisi . "%'";
		}
		if ($tahuntanam != '') {
			$wh .= " and tahuntanam = '" . $tahuntanam . "'";
		}
		if ($param['blok'] != '') {
			$wh .= " and indukblok = '" . $param['blok'] . "'";
		}

		if ($pkstujuanht != '') {
			$whdt .= " and pkstujuan = '" . $pkstujuanht . "'";
		}
		if ($jnskendht != '') {
			$whdt .= " and jenisvhc = '" . $jnskendht . "'";
		}
		$whopt = '';
		if ($param['blok'] != '') {
			$whopt .= " and blok = '" . $param['blok'] . "'";
		}
		$whdt .= " and tanggalberlaku = '" . tanggalsystemn($param['tanggalberlaku']) . "'";
		$whdt .= " and nospk = '" . $param['nospk'] . "'";


		if ($param['nospk'] != '') {
			$sql = "SELECT *
            FROM " . $dbname . ".kebun_5hargaangkut
            WHERE nospk = '" . $param['nospk'] . "'
            AND pkstujuan = '" . $pkstujuanht . "'
            AND jenisvhc = '" . $jnskendht . "'
            AND tanggalberlaku = '" . tanggalsystemn($param['tanggalberlaku']) . "'
            AND posting IN ('1','9')";

			if ($param['blok'] != '') {
				$sql .= " AND blok = '" . $param['blok'] . "'";
			}

			$res = fetchdata($sql);

			if (count($res) > 0) {
				exit("Error : Data NO SPK sudah disetujui atau masih dalam proses persetujuan.");
			}
		}


		//exit("error".$sql);



		$optpks = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$sql = "SELECT * FROM " . $dbname . ".organisasi where tipe='PABRIK' and namaorganisasi not like '%BULKING%'";
		$res = fetchdata($sql);
		foreach ($res as $bar) {
			if ($bar['kodeorganisasi'] == $pkstujuanht) {
				$i = "selected";
			} else {
				$i = "";
			}
			$optpks .= "<option value=" . $bar['kodeorganisasi'] . " " . $i . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
		}

		$where = " and a.kodecustomer not in ('BPJ','KBP','KSP','SDK','SNIP')";
		$iPks = "select distinct b.* from " . $dbname . ".pmn_4komoditi a left join " . $dbname . ".pmn_4customer b
			ON a.kodecustomer=b.kodecustomer where a.kodebarang='40000003'  and b.kodecustomer is not null " . $where . "";
		$nPks = $owlPDO->query($iPks) or die(print " Gagal: " . PDOException::getMessage());
		$nPks->setFetchMode(PDO::FETCH_ASSOC);
		while ($dPks = $nPks->fetch()) {
			if ($pkstujuanht == $dPks['kodecustomer']) {
				$select = "selected=selected";
			} else {
				$select = "";
			}
			$optpks .= "<option " . $select . " value='" . $dPks['kodecustomer'] . "'>" . $dPks['namacustomer'] . "</option>";
		}


		$optjns = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$optjns .= "<option value='GLOBAL' selected>GLOBAL</option>";
		$sql = "SELECT * FROM " . $dbname . ".vhc_5jenisvhc";
		$res = fetchdata($sql);
		foreach ($res as $bar) {
			if ($bar['jenisvhc'] == $jnskendht) {
				$i = "selected";
			} else {
				$i = "";
			}
			$optjns .= "<option value=" . $bar['jenisvhc'] . " " . $i . ">" . $bar['jenisvhc'] . " - " . $bar['namajenisvhc'] . "</option>";
		}

		$str = "select * from " . $dbname . ".kebun_5hargaangkut where 1=1 " . $whopt . " " . $whdt . "";
		$res = fetchdata($str);
		foreach ($res as $val) {
			if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks1') {
				$isimtp1 = $val['kodekeg'];
			}
			if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks2') {
				$isimtp2 = $val['kodekeg'];
			}
			if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks3') {
				$isimtp3 = $val['kodekeg'];
			}
			if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks4') {
				$isimrp = $val['kodekeg'];
			}
			if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks5') {
				$isimtp5 = $val['kodekeg'];
			}
			if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks6') {
				$isimtp6 = $val['kodekeg'];
			}
			if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks7') {
				$isimtp7 = $val['kodekeg'];
			}

			if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks1') {
				$isiatp1 = $val['kodekeg'];
			}
			if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks2') {
				$isiatp2 = $val['kodekeg'];
			}
			if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks3') {
				$isiatp3 = $val['kodekeg'];
			}
			if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks4') {
				$isiarp = $val['kodekeg'];
			}
			if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks5') {
				$isiatp5 = $val['kodekeg'];
			}
			if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks6') {
				$isiatp6 = $val['kodekeg'];
			}
			if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks7') {
				$isiatp7 = $val['kodekeg'];
			}
		}

		// if($isimtp1==''){$isimtp1='611010224';}
		// if($isimtp2==''){$isimtp2='611010224';}
		// if($isimtp3==''){$isimtp3='611010224';}
		// if($isimrp==''){$isimrp='611010225';}
		// if($isiatp1==''){$isiatp1='611010308';}
		// if($isiatp2==''){$isiatp2='611010308';}
		// if($isiatp3==''){$isiatp3='611010308';}
		// if($isiarp==''){$isiarp='611010309';}

		$optmtp1 = $optmtp2 = $optmtp3 = $optmtp4 = $optmtp5 = $optmtp6 = $optmtp7 = $optmrp = $optatp1 = $optatp2 = $optatp3 = $optatp4 = $optatp5 = $optatp6 = $optatp7 = $optarp = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "select * from " . $dbname . ".setup_kegiatan where noakun like '611%'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($isimtp1 == $bar['kodekegiatan']) {
				$optmtp1 .= "<option value=" . $bar['kodekegiatan'] . " selected>" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			} else {
				$optmtp1 .= "<option value=" . $bar['kodekegiatan'] . ">" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			}
			if ($isimtp2 == $bar['kodekegiatan']) {
				$optmtp2 .= "<option value=" . $bar['kodekegiatan'] . " selected>" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			} else {
				$optmtp2 .= "<option value=" . $bar['kodekegiatan'] . ">" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			}
			if ($isimtp3 == $bar['kodekegiatan']) {
				$optmtp3 .= "<option value=" . $bar['kodekegiatan'] . " selected>" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			} else {
				$optmtp3 .= "<option value=" . $bar['kodekegiatan'] . ">" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			}
			if ($isimrp == $bar['kodekegiatan']) {
				$optmtp4 .= "<option value=" . $bar['kodekegiatan'] . " selected>" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			} else {
				$optmtp4 .= "<option value=" . $bar['kodekegiatan'] . ">" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			}
			if ($isimtp5 == $bar['kodekegiatan']) {
				$optmtp5 .= "<option value=" . $bar['kodekegiatan'] . " selected>" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			} else {
				$optmtp5 .= "<option value=" . $bar['kodekegiatan'] . ">" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			}
			if ($isimtp6 == $bar['kodekegiatan']) {
				$optmtp6 .= "<option value=" . $bar['kodekegiatan'] . " selected>" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			} else {
				$optmtp6 .= "<option value=" . $bar['kodekegiatan'] . ">" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			}
			if ($isimtp7 == $bar['kodekegiatan']) {
				$optmtp7 .= "<option value=" . $bar['kodekegiatan'] . " selected>" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			} else {
				$optmtp7 .= "<option value=" . $bar['kodekegiatan'] . ">" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			}

			if ($isiatp1 == $bar['kodekegiatan']) {
				$optatp1 .= "<option value=" . $bar['kodekegiatan'] . " selected>" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			} else {
				$optatp1 .= "<option value=" . $bar['kodekegiatan'] . ">" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			}
			if ($isiatp2 == $bar['kodekegiatan']) {
				$optatp2 .= "<option value=" . $bar['kodekegiatan'] . " selected>" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			} else {
				$optatp2 .= "<option value=" . $bar['kodekegiatan'] . ">" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			}
			if ($isiatp3 == $bar['kodekegiatan']) {
				$optatp3 .= "<option value=" . $bar['kodekegiatan'] . " selected>" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			} else {
				$optatp3 .= "<option value=" . $bar['kodekegiatan'] . ">" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			}
			if ($isiarp == $bar['kodekegiatan']) {
				$optatp4 .= "<option value=" . $bar['kodekegiatan'] . " selected>" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			} else {
				$optatp4 .= "<option value=" . $bar['kodekegiatan'] . ">" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			}
			if ($isiatp5 == $bar['kodekegiatan']) {
				$optatp5 .= "<option value=" . $bar['kodekegiatan'] . " selected>" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			} else {
				$optatp5 .= "<option value=" . $bar['kodekegiatan'] . ">" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			}
			if ($isiatp6 == $bar['kodekegiatan']) {
				$optatp6 .= "<option value=" . $bar['kodekegiatan'] . " selected>" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			} else {
				$optatp6 .= "<option value=" . $bar['kodekegiatan'] . ">" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			}
			if ($isiatp7 == $bar['kodekegiatan']) {
				$optatp7 .= "<option value=" . $bar['kodekegiatan'] . " selected>" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			} else {
				$optatp7 .= "<option value=" . $bar['kodekegiatan'] . ">" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
			}
		}

		$tab .= "<tr class=rowcontent>";
		$tab .= "<td valign=top align=center colspan=6></td>";
		$tab .= "<td valign=top align=center><select class=select2 id=muattphpks1 style=\"width:150px;\">" . $optmtp1 . "</select></td>";
		$tab .= "<td valign=top align=center><select class=select2 id=muattphpks2 style=\"width:150px;\">" . $optmtp2 . "</select></td>";
		$tab .= "<td valign=top align=center><select class=select2 id=muattphpks3 style=\"width:150px;\">" . $optmtp3 . "</select></td>";
		$tab .= "<td valign=top align=center><select class=select2 id=muatramppks style=\"width:150px;\">" . $optmtp4 . "</select></td>";
		$tab .= "<td valign=top align=center><select class=select2 id=muattphpks5 style=\"width:150px;\">" . $optmtp5 . "</select></td>";
		$tab .= "<td valign=top align=center><select class=select2 id=muattphpks6 style=\"width:150px;\">" . $optmtp6 . "</select></td>";
		$tab .= "<td valign=top align=center><select class=select2 id=muattphpks7 style=\"width:150px;\">" . $optmtp7 . "</select></td>";
		$tab .= "<td valign=top align=center><select class=select2 id=angkuttphpks1 style=\"width:150px;\">" . $optatp1 . "</select></td>";
		$tab .= "<td valign=top align=center><select class=select2 id=angkuttphpks2 style=\"width:150px;\">" . $optatp2 . "</select></td>";
		$tab .= "<td valign=top align=center><select class=select2 id=angkuttphpks3 style=\"width:150px;\">" . $optatp3 . "</select></td>";
		$tab .= "<td valign=top align=center><select class=select2 id=angkutramppks style=\"width:150px;\">" . $optatp4 . "</select></td>";
		$tab .= "<td valign=top align=center><select class=select2 id=angkuttphpks5 style=\"width:150px;\">" . $optatp5 . "</select></td>";
		$tab .= "<td valign=top align=center><select class=select2 id=angkuttphpks6 style=\"width:150px;\">" . $optatp6 . "</select></td>";
		$tab .= "<td valign=top align=center><select class=select2 id=angkuttphpks7 style=\"width:150px;\">" . $optatp7 . "</select></td>";
		$tab .= "<td valign=top align=center></td>";
		$tab .= "</tr>";

		$str = "select * from " . $dbname . ".setup_blok where kodeorg like '" . $unit . "%' " . $wh . " and tahuntanam != '0' ";
		$res = fetchdata($str);
		foreach ($res as $val) {
			$blokbesar[$val['indukblok']] = $val['indukblok'];
		}
		$jlh = count($res);
		if ($jlh == 0) {
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td colspan=12 align=center>" . $_SESSION['lang']['datanotfound'] . "</td>";
			$tab .= "</tr>";
			echo $tab;
			exit();
		}
		$no = 0;
		foreach ($blokbesar as $blk) {
			$stri = "select * from " . $dbname . ".kebun_5hargaangkut where blok = '" . $blk . "' " . $whdt . "";
			$isi = fetchdata($stri);
			$isimtp1 = $isimtp2 = $isimtp3 = $isimrp = $isimtp5 = $isimtp6 = $isimtp7 = $isiatp1 = $isiatp2 = $isiatp3 = $isiarp = $isiatp5 = $isiatp6 = $isiatp7 = '0';
			foreach ($isi as $val) {
				if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks1') {
					$isimtp1 = $val['harga'];
				}
				if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks2') {
					$isimtp2 = $val['harga'];
				}
				if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks3') {
					$isimtp3 = $val['harga'];
				}
				if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks4') {
					$isimrp = $val['harga'];
				}
				if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks5') {
					$isimtp5 = $val['harga'];
				}
				if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks6') {
					$isimtp6 = $val['harga'];
				}
				if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks7') {
					$isimtp7 = $val['harga'];
				}


				if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks1') {
					$isiatp1 = $val['harga'];
				}
				if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks2') {
					$isiatp2 = $val['harga'];
				}
				if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks3') {
					$isiatp3 = $val['harga'];
				}
				if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks4') {
					$isiarp = $val['harga'];
				}
				if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks5') {
					$isiatp5 = $val['harga'];
				}
				if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks6') {
					$isiatp6 = $val['harga'];
				}
				if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks7') {
					$isiatp7 = $val['harga'];
				}
			}
			$tatan = array();
			$str = "select * from " . $dbname . ".setup_blok where kodeorg like '" . $blk . "%' and tahuntanam != '0'";
			$res = fetchdata($str);
			foreach ($res as $v) {
				$tatan[$v['tahuntanam']] = $v['tahuntanam'];
				$luas[$v['indukblok']] += $v['luasareaproduktif'];
			}
			$nminduk = makeOption($dbname, 'organisasi', 'indukblok,namaindukblok', "indukblok='" . $blk . "'");
			$no += 1;
			$tab .= "<tr class=rowcontent id=tr_" . $no . ">";
			$tab .= "<td valign=top align=center>" . $no . "</td>";
			$tab .= "<td valign=top align=center hidden id=blok_" . $no . ">" . $blk . "</td>";
			$tab .= "<td valign=top align=center >" . $nminduk[$blk] . "</td>";
			$tab .= "<td valign=top align=center>";
			foreach ($tatan as $value) {
				$tab .= $value . "<br>";
			}
			$tab .= "</td>";
			$tab .= "<td valign=top align=right>" . number_format($luas[$blk], 2) . "</td>";
			$tab .= "<td valign=top align=center><select disabled id=pkstujuan" . $no . " style=\"width:100px;\">" . @$optpks . "</select></td>";
			$tab .= "<td valign=top align=center><select id=jenisvhc" . $no . " disabled style=\"width:80px;\">" . @$optjns . "</select></td>";
			$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('muat_tphpks1_" . $no . "',2)\" type=text id=muat_tphpks1_" . $no . " nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$isimtp1) . "></td>";
			$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('muat_tphpks2_" . $no . "',2)\" type=text id=muat_tphpks2_" . $no . " nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$isimtp2) . "></td>";
			$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('muat_tphpks3_" . $no . "',2)\" type=text id=muat_tphpks3_" . $no . " nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$isimtp3) . "></td>";
			$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('muat_rampks_" . $no . "',2)\" type=text id=muat_rampks_" . $no . " nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$isimrp) . "></td>";
			$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('muat_tphpks5_" . $no . "',2)\" type=text id=muat_tphpks5_" . $no . " nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$isimtp5) . "></td>";
			$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('muat_tphpks6_" . $no . "',2)\" type=text id=muat_tphpks6_" . $no . " nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$isimtp6) . "></td>";
			$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('muat_tphpks7_" . $no . "',2)\" type=text id=muat_tphpks7_" . $no . " nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$isimtp7) . "></td>";
			$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('angkut_tphpks1_" . $no . "',2)\" type=text id=angkut_tphpks1_" . $no . " nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$isiatp1) . "></td>";
			$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('angkut_tphpks2_" . $no . "',2)\" type=text id=angkut_tphpks2_" . $no . " nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$isiatp2) . "></td>";
			$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('angkut_tphpks3_" . $no . "',2)\" type=text id=angkut_tphpks3_" . $no . " nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$isiatp3) . "></td>";
			$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('angkut_rampks_" . $no . "',2)\" type=text id=angkut_rampks_" . $no . " nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$isiarp) . "></td>";
			$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('angkut_tphpks5_" . $no . "',2)\" type=text id=angkut_tphpks5_" . $no . " nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$isiatp5) . "></td>";
			$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('angkut_tphpks6_" . $no . "',2)\" type=text id=angkut_tphpks6_" . $no . " nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$isiatp6) . "></td>";
			$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('angkut_tphpks7_" . $no . "',2)\" type=text id=angkut_tphpks7_" . $no . " nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$isiatp7) . "></td>";
			$tab .= "<td valign=top><button class=mybutton onclick=savedetail(" . $no . ")>" . $_SESSION['lang']['save'] . "</button></td>";
			$tab .= "</tr>";
		}

		$tab .= "<tr class=rowcontent><input hidden id=method value=insert>";
		$tab .= "<td colspan=21 align=right><button class=mybutton onclick=saveAll(" . $no . ")>" . $_SESSION['lang']['saveall'] . "</button></td>";
		$tab .= "</tr>";
		#exit("error asdasdsa");
		echo $tab;
		break;
	case 'edit':
		$isimtp1 = $isimtp2 = $isimtp3 = $isimrp = $isiatp1 = $isiatp2 = $isiatp3 = $isiarp = '';
		$whdt .= " and jenisvhc = '" . $param['jenisvhc'] . "'";
		$whdt .= " and pkstujuan = '" . $param['pkstujuan'] . "'";
		$whdt .= " and tanggalberlaku = '" . $param['tanggalberlaku'] . "'";
		$str = "select * from " . $dbname . ".kebun_5hargaangkut where blok = '" . $param['blok'] . "' " . $whdt . "";
		$isi = fetchdata($str);
		foreach ($isi as $val) {
			if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks1') {
				$isimtp1 = $val['harga'];
			}
			if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks2') {
				$isimtp2 = $val['harga'];
			}
			if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks3') {
				$isimtp3 = $val['harga'];
			}
			if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks4') {
				$isimrp = $val['harga'];
			}
			if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks1') {
				$isiatp1 = $val['harga'];
			}
			if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks2') {
				$isiatp2 = $val['harga'];
			}
			if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks3') {
				$isiatp3 = $val['harga'];
			}
			if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks4') {
				$isiarp = $val['harga'];
			}
		}


		break;

	case 'insert':
		try {
			$owlPDO->beginTransaction();
			$param['tanggalberlaku'] = tanggalsystemn($param['tanggalberlaku']);
			if ($param['tanggalberlaku'] == '--') {
				throw new PDOException("Tanggal berlaku wajib diisi.");
			}

			if ($muat_tphpks1 == '') {
				$muat_tphpks1 = 0;
			}
			if ($muat_tphpks2 == '') {
				$muat_tphpks2 = 0;
			}
			if ($muat_tphpks3 == '') {
				$muat_tphpks3 = 0;
			}
			if ($muat_rampks == '') {
				$muat_rampks = 0;
			}
			if ($muat_tphpks5 == '') {
				$muat_tphpks5 = 0;
			}
			if ($muat_tphpks6 == '') {
				$muat_tphpks6 = 0;
			}
			if ($muat_tphpks7 == '') {
				$muat_tphpks7 = 0;
			}
			if ($angkut_tphpks1 == '') {
				$angkut_tphpks1 = 0;
			}
			if ($angkut_tphpks2 == '') {
				$angkut_tphpks2 = 0;
			}
			if ($angkut_tphpks3 == '') {
				$angkut_tphpks3 = 0;
			}
			if ($angkut_rampks == '') {
				$angkut_rampks = 0;
			}
			if ($angkut_tphpks5 == '') {
				$angkut_tphpks5 = 0;
			}
			if ($angkut_tphpks6 == '') {
				$angkut_tphpks6 = 0;
			}
			if ($angkut_tphpks7 == '') {
				$angkut_tphpks7 = 0;
			}

			$arrdata['muat_tphpks1'] = array('harga' => $muat_tphpks1, 'kegiatan' => $param['keg_muat_tphpks1']);
			$arrdata['muat_tphpks2'] = array('harga' => $muat_tphpks2, 'kegiatan' => $param['keg_muat_tphpks2']);
			$arrdata['muat_tphpks3'] = array('harga' => $muat_tphpks3, 'kegiatan' => $param['keg_muat_tphpks3']);
			$arrdata['muat_tphpks4'] = array('harga' => $muat_rampks, 'kegiatan' => $param['keg_muat_rampks']);
			$arrdata['muat_tphpks5'] = array('harga' => $muat_tphpks5, 'kegiatan' => $param['keg_muat_tphpks5']);
			$arrdata['muat_tphpks6'] = array('harga' => $muat_tphpks6, 'kegiatan' => $param['keg_muat_tphpks6']);
			$arrdata['muat_tphpks7'] = array('harga' => $muat_tphpks7, 'kegiatan' => $param['keg_muat_tphpks7']);
			$arrdata['angkut_tphpks1'] = array('harga' => $angkut_tphpks1, 'kegiatan' => $param['keg_angkut_tphpks1']);
			$arrdata['angkut_tphpks2'] = array('harga' => $angkut_tphpks2, 'kegiatan' => $param['keg_angkut_tphpks2']);
			$arrdata['angkut_tphpks3'] = array('harga' => $angkut_tphpks3, 'kegiatan' => $param['keg_angkut_tphpks3']);
			$arrdata['angkut_tphpks4'] = array('harga' => $angkut_rampks, 'kegiatan' => $param['keg_angkut_rampks']);
			$arrdata['angkut_tphpks5'] = array('harga' => $angkut_tphpks5, 'kegiatan' => $param['keg_angkut_tphpks5']);
			$arrdata['angkut_tphpks6'] = array('harga' => $angkut_tphpks6, 'kegiatan' => $param['keg_angkut_tphpks6']);
			$arrdata['angkut_tphpks7'] = array('harga' => $angkut_tphpks7, 'kegiatan' => $param['keg_angkut_tphpks7']);


			if ($jenisvhc != '' and $pkstujuan != '') {
				$str = "select * from " . $dbname . ".kebun_5hargaangkut where blok = '" . $blok . "' and pkstujuan='" . $pkstujuan . "' and jenisvhc='" . $jenisvhc . "'  and tanggalberlaku='" . $param['tanggalberlaku'] . "' and nospk='" . $param['nospk'] . "'";
				$res = fetchdata($str);
				if (count($res) > 0) {
					throw new PDOException("Data sudah ada.");
					$data = array();
					foreach ($res as $val) {
						if ($val['posting'] == '9') {
							throw new PDOException("Data sedang dalam proses persetujuan.");
						} else {
							$hargalama[$val['blok']][$val['jenis']][$val['tujuan']][$val['kodekeg']][$val['pkstujuan']][$val['jenisvhc']] = $val['harga'];
							$data = array(
								'blok'          => $val['blok'],
								'jenis'         => $val['jenis'],
								'tujuan'        => $val['tujuan'],
								'kodekeg'       => $val['kodekeg'],
								'pkstujuan'     => $val['pkstujuan'],
								'jenisvhc'      => $val['jenisvhc'],
								'harga'         => $val['harga'],
								'tanggalberlaku' => $val['tanggalberlaku'],
								'updateby'      => $_SESSION['standard']['userid'],
								'lastupdate'    => date("Y-m-d H:i:s"),
								'posting'       => $val['posting'],
								'nospk'         => $val['nospk']
							);

							$str = insertQuery($dbname, 'kebun_5hargaangkut_hist', $data, array_keys($data));
							$owlPDO->exec($str);
						}
					}
				}


				$data = array();
				$str = "select * from " . $dbname . ".kebun_5hargaangkut_additional where blok = '" . $blok . "' and pkstujuan='" . $pkstujuan . "' and jenisvhc='" . $jenisvhc . "' and nospk='" . $param['nospk'] . "'";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$data[] = rangeTanggal($bar['tglawal'], $bar['tglakhir']);
				}

				$lsdate = array();
				foreach ($data as $key => $v1) {
					foreach ($v1 as $date) {
						if ($date >= $param['tanggalberlaku']) {
							$lsdate[substr($date, 8, 2)] = substr($date, 8, 2);
						}
					}
				}

				$prdx = tanggalnormal($date);

				if (count($lsdate) > 0) {
					throw new PDOException("Tambahan biaya tanggal : " . implode(", ", $lsdate) . "" . substr($prdx, 2, 8) . " sudah ada.<br>Harga baru bisa diinputkan dengan tanggal berlaku diatas tanggal " . tanggalnormal($date) . "");
				}

				$baru = $lama = 0;
				foreach ($arrdata as $val => $bar) {
					if ($bar['kegiatan'] == '') {
						throw new PDOException("Kode kegiatan wajib diisi.");
					}
					$isi = explode("_", $val);

					$lama += $hargalama[$blok][$isi[0]][$isi[1]][$bar['kegiatan']][$pkstujuan][$jenisvhc];
					$baru += $bar['harga'];

					if ($hargalama[$blok][$isi[0]][$isi[1]][$bar['kegiatan']][$pkstujuan][$jenisvhc] == $bar['harga'] and $bar['harga'] > 0 and $hargalama[$blok][$isi[0]][$isi[1]][$bar['kegiatan']][$pkstujuan][$jenisvhc] > 0) {
						throw new PDOException("Harga lama dan harga baru tidak boleh sama.<br>Harga Lama " . $hargalama[$blok][$isi[0]][$isi[1]][$bar['kegiatan']][$pkstujuan][$jenisvhc] . "<br>Harga Baru " . $bar['harga'] . "");
					}
					if ($bar['harga'] != '' || $bar['harga'] != 0) {
						$data = array();
						$data = array(
							'blok'          => $blok,
							'jenis'         => $isi[0],
							'tujuan'        => $isi[1],
							'harga'         => $bar['harga'],
							'kodekeg'       => $bar['kegiatan'],
							'jenisvhc'      => $jenisvhc,
							'pkstujuan'     => $pkstujuan,
							'tanggalberlaku' => $param['tanggalberlaku'],
							'nospk'         => $param['nospk'],
							'posting'       => '0',
							'updateby'      => $_SESSION['standard']['userid']
						);

						$cols = array();
						foreach ($data as $key => $row) {
							$cols[] = $key;
						}
						$str = insertQuery($dbname, 'kebun_5hargaangkut', $data, $cols);

						$owlPDO->exec($str);
					}
				}


				if ($lama == $baru and $baru > 0 and $lama > 0) {
					throw new PDOException("Harga lama dan harga baru tidak boleh sama.");
				}
			}

			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Warning, " . addslashes($e->getMessage());
			die();
		}
		break;
	case 'delete':
		$str = "delete from " . $dbname . ".kebun_5hargaangkut where blok = '" . $blok . "'  and pkstujuan='" . $pkstujuan . "' and jenisvhc='" . $jenisvhc . "' and tanggalberlaku='" . $tanggalberlaku . "' and nospk='" . $param['nospk'] . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}

		$str = "delete from " . $dbname . ".kebun_5hargaangkut_additional where blok = '" . $blok . "'  and pkstujuan='" . $pkstujuan . "' and jenisvhc='" . $jenisvhc . "' and tanggalberlaku='" . $tanggalberlaku . "' and nospk='" . $param['nospk'] . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}

		break;
	case 'loaddata':
		if ($param['jenis'] != 'excel') {
			$border = '0';
		} else {
			$border = '1';
		}
		$tab = "<div class='table-scroll' style='height:65vh'><table border=" . $border . " cellpadding=5 class=sortable cellspacing=1>
				<thead>
					<tr class=rowheader style=font-weight:bold>
						<th align=center rowspan=3>No</th> 
						<th align=center rowspan=3>" . $_SESSION['lang']['blok'] . "</th> 
						<th align=center rowspan=3 width=50px>" . $_SESSION['lang']['tahuntanam'] . "</th> 
						<th align=center rowspan=3>" . $_SESSION['lang']['luas'] . "</th> 
						<th align=center rowspan=3>PKS Tujuan</th> 
						<th align=center rowspan=3>Jenis<br>Kendaraan</th> 
						<th align=center colspan=14>Upah Muat</th> 
						<th align=center colspan=14>Upah Angkut</th> 
						<th align=center rowspan=3>" . $_SESSION['lang']['tanggalberlaku'] . "</th> 
						<th align=center rowspan=3>" . $_SESSION['lang']['nospk'] . "</th> 
						<th align=center rowspan=3>Approval</th> 
						<th align=center rowspan=3>" . $_SESSION['lang']['status'] . "</th> 
						<th align=center rowspan=3 colspan=4>" . $_SESSION['lang']['action'] . "</th> 
					</tr>
					<tr class=rowheader style=align:center;font-weight:bold>
						<th align=center colspan=2>TPH-PKS 1</th> 
						<th align=center colspan=2>TPH-PKS 2</th> 
						<th align=center colspan=2>TPH-PKS 3</th> 
						<th align=center colspan=2>TPH-PKS 4</th>
						<th align=center colspan=2>TPH-PKS 5</th>
						<th align=center colspan=2>TPH-PKS 6</th>
						<th align=center colspan=2>TPH-PKS 7</th>
						<th align=center colspan=2>TPH-PKS 1</th> 
						<th align=center colspan=2>TPH-PKS 2</th> 
						<th align=center colspan=2>TPH-PKS 3</th> 
						<th align=center colspan=2>TPH-PKS 4</th>
						<th align=center colspan=2>TPH-PKS 5</th>
						<th align=center colspan=2>TPH-PKS 6</th>
						<th align=center colspan=2>TPH-PKS 7</th>
					</tr>
					<tr class=rowheader style=align:center;font-weight:bold>
						<th align=center>Kegiatan</th> 
						<th align=center>Harga</th> 
						<th align=center>Kegiatan</th> 
						<th align=center>Harga</th> 
						<th align=center>Kegiatan</th> 
						<th align=center>Harga</th> 
						<th align=center>Kegiatan</th> 
						<th align=center>Harga</th> 
						<th align=center>Kegiatan</th> 
						<th align=center>Harga</th> 
						<th align=center>Kegiatan</th> 
						<th align=center>Harga</th> 
						<th align=center>Kegiatan</th> 
						<th align=center>Harga</th> 
						<th align=center>Kegiatan</th> 
						<th align=center>Harga</th>
						<th align=center>Kegiatan</th> 
						<th align=center>Harga</th> 
						<th align=center>Kegiatan</th> 
						<th align=center>Harga</th> 
						<th align=center>Kegiatan</th> 
						<th align=center>Harga</th> 
						<th align=center>Kegiatan</th> 
						<th align=center>Harga</th> 
						<th align=center>Kegiatan</th> 
						<th align=center>Harga</th> 
						<th align=center>Kegiatan</th> 
						<th align=center>Harga</th> 
					</tr>
				</thead>
			<tbody>";
		$limit = 20;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = intval($_POST['page']);
			if ($page < 0)
				$page = 0;
		}
		$offset = $page * $limit;
		$maxdisplay = ($page * $limit);

		$where = "";
		if ($find_divisi != '') {
			$where .= " and blok LIKE  '" . $find_divisi . "%'";
		}
		if ($find_blok != '') {
			$where .= " and (blok LIKE  '%" . $find_blok . "%' or blok in (select kodeorganisasi from " . $dbname . ".organisasi where namaorganisasi like '%" . $find_blok . "%'))";
		}
		if ($param['find_stat'] != '') {
			$where .= " and (posting = '" . $param['find_stat'] . "' or postingadd = '" . $param['find_stat'] . "')";
		}
		if ($param['find_nope'] != '') {
			$where .= " and nopengajuan LIKE  '%" . $param['find_nope'] . "%'";
		}
		if ($find_tt != '') {
			$where .= " or blok in (select kodeorg from " . $dbname . ".setup_blok where tahuntanam='" . $find_tt . "')";
		}
		if ($param['find_tanggalberlaku'] != '') {
			$where .= " and tanggalberlaku ='" . tanggaldb($param['find_tanggalberlaku']) . "'";
		}

		$where .= " and substr(blok,1,4) in (" . getOrgDetail(2) . ")";

		$arrapproval = array("0" => "Belum diajukan", "1" => $_SESSION['lang']['disetujui'], "2" => $_SESSION['lang']['koreksi'], "3" => $_SESSION['lang']['ditolak'], '9' => 'Proses Persetujuan');

		$ql2 = "select count(distinct blok,pkstujuan,jenisvhc, posting, nopengajuan, tanggalberlaku,postingadd,nospk) as jmlhrow from " . $dbname . ".kebun_5hargaangkut where 0=0 " . $where . "";
		$query2 = $owlPDO->query($ql2) or die(print " Gagal: " . PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
			$jlhbrs = $jsl->jmlhrow;
		}

		$lmt = "LIMIT " . $offset . "," . $limit . "";
		if ($param['jenis'] == 'excel') {
			$lmt = "";
		}


		$no = $maxdisplay;
		$optkeg = makeOption($dbname, "setup_kegiatan", 'kodekegiatan,namakegiatan');
		$str = "select distinct blok,pkstujuan,jenisvhc, posting, nopengajuan, tanggalberlaku,postingadd,nospk from " . $dbname . ".kebun_5hargaangkut where 1=1 " . $where . " order by blok asc, tanggalberlaku desc " . $lmt . "";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$opttt = makeOption($dbname, "setup_blok", 'kodeorg,tahuntanam', "kodeorg='" . $bar['blok'] . "'");
			$optluas = makeOption($dbname, "setup_blok", 'kodeorg,luasareaproduktif', "kodeorg='" . $bar['blok'] . "'");
			$nminduk = makeOption($dbname, 'organisasi', 'indukblok,namaindukblok', "indukblok='" . $bar['blok'] . "'");
			$nmcust	= makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer');
			$qry = "select * from " . $dbname . ".setup_blok where kodeorg like '" . $bar['blok'] . "%'";
			$hsl = fetchdata($qry);
			foreach ($hsl as $v) {
				$tatan[$v['tahuntanam']] = $v['tahuntanam'];
				$luas[$v['indukblok']] += $v['luasareaproduktif'];
			}

			$stri = "select * from " . $dbname . ".kebun_5hargaangkut where blok = '" . $bar['blok'] . "' and jenisvhc='" . $bar['jenisvhc'] . "' and tanggalberlaku='" . $bar['tanggalberlaku'] . "' and nospk='" . $bar['nospk'] . "' and pkstujuan='" . $bar['pkstujuan'] . "'"; #echo $stri.";<br>";
			$isi = fetchdata($stri);
			$isimtp1 = $isimtp2 = $isimtp3 = $isimrp = $isimtp5 = $isimtp6 = $isimtp6 = $isiatp1 = $isiatp2 = $isiatp3 = $isiarp = $isiatp5 = $isiatp6 = $isiatp7 = '';
			foreach ($isi as $val) {
				if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks1') {
					$isimtp1 = $val['harga'];
					$kegmtp1 = $val['kodekeg'];
				}
				if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks2') {
					$isimtp2 = $val['harga'];
					$kegmtp2 = $val['kodekeg'];
				}
				if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks3') {
					$isimtp3 = $val['harga'];
					$kegmtp3 = $val['kodekeg'];
				}
				if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks4') {
					$isimrp = $val['harga'];
					$kegmrp = $val['kodekeg'];
				}
				if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks5') {
					$isimtp5 = $val['harga'];
					$kegmtp5 = $val['kodekeg'];
				}
				if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks6') {
					$isimtp6 = $val['harga'];
					$kegmtp6 = $val['kodekeg'];
				}
				if ($val['jenis'] == 'muat' and $val['tujuan'] == 'tphpks7') {
					$isimtp7 = $val['harga'];
					$kegmtp7 = $val['kodekeg'];
				}


				if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks1') {
					$isiatp1 = $val['harga'];
					$kegatp1 = $val['kodekeg'];
				}
				if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks2') {
					$isiatp2 = $val['harga'];
					$kegatp2 = $val['kodekeg'];
				}
				if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks3') {
					$isiatp3 = $val['harga'];
					$kegatp3 = $val['kodekeg'];
				}
				if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks4') {
					$isiarp = $val['harga'];
					$kegarp = $val['kodekeg'];
				}
				if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks5') {
					$isiatp5 = $val['harga'];
					$kegatp5 = $val['kodekeg'];
				}
				if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks6') {
					$isiatp6 = $val['harga'];
					$kegatp6 = $val['kodekeg'];
				}
				if ($val['jenis'] == 'angkut' and $val['tujuan'] == 'tphpks7') {
					$isiatp7 = $val['harga'];
					$kegatp7 = $val['kodekeg'];
				}
			}

			$no++;
			$tab .= "<tr style=vertical-align:top class=rowcontent id=tr_$no>";
			$tab .= "<td align=center>" . $no . "</td>";
			$tab .= "<td>" . $nminduk[$bar['blok']] . "</td>";
			$tab .= "<td align=center>";
			foreach ($tatan as $v) {
				$tab .= $v . "<br>";
			}
			$tab .= "</td>";
			$tab .= "<td align=right>" . @number_format($luas[$bar['blok']], 2) . "</td>";
			$tab .= "<td align=center>" . $bar['pkstujuan'] . "<br>" . $nmcust[$bar['pkstujuan']] . "</td>";
			$tab .= "<td align=center>" . $nmvhc[$bar['jenisvhc']] . "</td>";
			// $tab.="<td>".$kegmtp."</td>";
			$tab .= "<td style=font-size:9px>" . ($isimtp1 != '' ? $kegmtp1 . " " . $optkeg[$kegmtp1] : '') . "</td>";
			$tab .= "<td align=right>" . @number_format($isimtp1, 2) . "</td>";
			$tab .= "<td style=font-size:9px>" . ($isimtp2 != '' ? $kegmtp2 . " " . $optkeg[$kegmtp2] : '') . "</td>";
			$tab .= "<td align=right>" . @number_format($isimtp2, 2) . "</td>";
			$tab .= "<td style=font-size:9px>" . ($isimtp3 != '' ? $kegmtp3 . " " . $optkeg[$kegmtp3] : '') . "</td>";
			$tab .= "<td align=right>" . @number_format($isimtp3, 2) . "</td>";
			// $tab.="<td>".$kegmrp."</td>";
			$tab .= "<td style=font-size:9px>" . ($isimrp != '' ? $kegmrp . " " . $optkeg[$kegmrp] : '') . "</td>";
			$tab .= "<td align=right>" . @number_format($isimrp, 2) . "</td>";
			$tab .= "<td style=font-size:9px>" . ($isimtp5 != '' ? $kegmtp5 . " " . $optkeg[$kegmtp5] : '') . "</td>";
			$tab .= "<td align=right>" . @number_format($isimtp5, 2) . "</td>";
			$tab .= "<td style=font-size:9px>" . ($isimtp6 != '' ? $kegmtp6 . " " . $optkeg[$kegmtp6] : '') . "</td>";
			$tab .= "<td align=right>" . @number_format($isimtp6, 2) . "</td>";
			$tab .= "<td style=font-size:9px>" . ($isimtp7 != '' ? $kegmtp7 . " " . $optkeg[$kegmtp7] : '') . "</td>";
			$tab .= "<td align=right>" . @number_format($isimtp7, 2) . "</td>";
			//$tab.="<td>".$kegatp."</td>";
			$tab .= "<td style=font-size:9px>" . ($isiatp1 != '' ? $kegatp1 . " " . $optkeg[$kegatp1] : '') . "</td>";
			$tab .= "<td align=right>" . @number_format($isiatp1, 2) . "</td>";
			$tab .= "<td style=font-size:9px>" . ($isiatp2 != '' ? $kegatp2 . " " . $optkeg[$kegatp2] : '') . "</td>";
			$tab .= "<td align=right>" . @number_format($isiatp2, 2) . "</td>";
			$tab .= "<td style=font-size:9px>" . ($isiatp3 != '' ? $kegatp3 . " " . $optkeg[$kegatp3] : '') . "</td>";
			$tab .= "<td align=right>" . @number_format($isiatp3, 2) . "</td>";
			// $tab.="<td>".$kegarp."</td>";
			$tab .= "<td style=font-size:9px>" . ($isiarp != '' ? $kegarp . " " . $optkeg[$kegarp] : '') . "</td>";
			$tab .= "<td align=right>" . @number_format($isiarp, 2) . "</td>";
			$tab .= "<td style=font-size:9px>" . ($isiatp5 != '' ? $kegatp5 . " " . $optkeg[$kegatp5] : '') . "</td>";
			$tab .= "<td align=right>" . @number_format($isiatp5, 2) . "</td>";
			$tab .= "<td style=font-size:9px>" . ($isiatp6 != '' ? $kegatp6 . " " . $optkeg[$kegatp6] : '') . "</td>";
			$tab .= "<td align=right>" . @number_format($isiatp6, 2) . "</td>";
			$tab .= "<td style=font-size:9px>" . ($isiatp7 != '' ? $kegatp7 . " " . $optkeg[$kegatp7] : '') . "</td>";
			$tab .= "<td align=right>" . @number_format($isiatp7, 2) . "</td>";
			if ($bar['posting'] == '0' or $bar['posting'] == '2') {
				$color = "background-color:yellow;";
			} elseif ($bar['posting'] == '3') {
				$color = "background-color:red;color:white;";
			} elseif ($bar['posting'] == '9') {
				$color = "background-color:orange;";
			} elseif ($bar['posting'] == '1') {
				$color = "background-color:green;color:white;";
			}

			$tab .= "<td style=text-align:center;>" . tglnmbln($bar['tanggalberlaku'], 'I', 'long') . "</td>";
			$tab .= "<td style=text-align:center;cursor:pointer; title='Click...' onclick=ceknospk('" . $bar['nospk'] . "');><font style=color:blue>" . substr($bar['nospk'], 0, 3) . "</font></td>";
			$app = $arrapproval[$bar['posting']];
			if ($bar['posting'] == 9) {
				$app = $arrapproval[$bar['posting']];
			}
			$addt = $bar['posting'];

			$stradd = "select * from " . $dbname . ".kebun_5hargaangkut_additional where 0=0 and blok = '" . $bar['blok'] . "' and jenisvhc='" . $bar['jenisvhc'] . "' and pkstujuan='" . $bar['pkstujuan'] . "' and tanggalberlaku='" . $bar['tanggalberlaku'] . "' and nospk='" . $bar['nospk'] . "'";
			$resadd = fetchdata($stradd);
			if (count($resadd) > 0) {
				if ($bar['postingadd'] == 0 or $bar['postingadd'] == '2') {
					$app = "Tambahan Biaya " . $arrapproval[$bar['postingadd']];
					$color = "background-color:yellow;";
					$addt = $bar['postingadd'];
				} elseif ($bar['postingadd'] == 9) {
					$app = "Tambahan Biaya " . $arrapproval[$bar['postingadd']];
					$color = "background-color:orange;";
				} elseif ($bar['postingadd'] == 3) {
					$app = "Tambahan Biaya " . $arrapproval[$bar['postingadd']];
					$color = "background-color:red;color:white;";
				}
			}
			$tab .= "<td style=text-align:center;cursor:pointer;font-size:9px; title='Click...' onclick=getdatapengajuan('" . $bar['nopengajuan'] . "');><font style=color:blue>" . $bar['nopengajuan'] . "</font></td>";
			$tab .= "<td style=text-align:center;cursor:pointer;" . $color . " title='Click...' onclick=gethistoriapproval('" . $bar['nopengajuan'] . "');>" . $app . "</td>";


			if ($param['jenis'] != 'excel') {
				if ($bar['posting'] == '0' or $bar['postingadd'] == '0' or $bar['posting'] == '2' or $bar['postingadd'] == '2') {
					$tab .= "<td align=center style=width:20px><img src='images/skyblue/submit.jpg' class='zImgBtn' height='30' title='Ajukan' onclick=\"form_ajukan('" . substr($bar['blok'], 0, 4) . "','" . $addt . "');\"></td>";
				} else {
					$tab .= "<td align=center></td>";
				}
				if ($bar['posting'] == '0' or $bar['posting'] == '2') {
					#if($bar['posting']!='9' and $bar['postingadd']!='9'){	
					$tab .= "<td align=center style=width:20px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"edit('" . $bar['blok'] . "','" . $bar['jenisvhc'] . "','" . $bar['pkstujuan'] . "','" . $opttt[$bar['blok']] . "','" . tanggalnormal($bar['tanggalberlaku']) . "','" . $bar['nospk'] . "');\" ></td>";

					$tab .= "<td align=center style=width:20px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"del('" . $bar['blok'] . "','" . $bar['pkstujuan'] . "','" . $bar['jenisvhc'] . "','" . $bar['tanggalberlaku'] . "','" . $bar['nospk'] . "');\" ></td>";
				} else {
					$tab .= "<td align=center></td>";
					$tab .= "<td align=center></td>";
				}

				if ($bar['posting'] == '1') {
					$tab .= "<td align=center style=width:20px><img src=images/plus.png class=zImgBtn  title='Tambahan biaya' onclick=\"tambahbiaya('" . $bar['blok'] . "','" . $bar['pkstujuan'] . "','" . $bar['jenisvhc'] . "','" . $bar['tanggalberlaku'] . "','" . $bar['nospk'] . "');\" ></td>";
				} else {
					$tab .= "<td align=center></td>";
				}
			}

			$tab .= "</tr>";
		}
		$totrows = ceil($jlhbrs / $limit);
		if ($totrows == 0) {
			$totrows = 1;
		}
		$isiRow = '';
		for ($er = 1; $er <= $totrows; $er++) {
			$sel = ($page == $er - 1) ? 'selected' : '';
			$isiRow .= "<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
		}

		$tab .= "</tbody>";
		if ($param['jenis'] != 'excel') {
			$tab .= createpaging($jlhbrs, $limit, $page, '26', 'loaddata', 'getPage');
		}
		$tab .= "</table></div>";

		if ($param['jenis'] != 'excel') {
			echo $tab;
		} else {
			$stream = $tab;
			$stream .= "</table>Print Time:" . date('Y-m-d H:i:s') . "<br>By:" . $_SESSION['empl']['name'];
			$nop = "harga_angkut_tbs.xls";
			if (strlen($stream) > 0) {
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/' . $file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/" . $nop . ".xls", 'w');
				if (!fwrite($handle, $stream)) {
					echo "<script language=javascript1.2>
							parent.window.alert('Can't convert to excel format');
							</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
							window.location='tempExcel/" . $nop . ".xls';
							</script>";
				}
				fclose($handle);
			}
		}
		break;
	case 'form_ajukan':
		$kodeapproval = "ATBS";

		$optKry = "";
		$str = "select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from " . $dbname . ".setup_approval a
				  left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid where
				  a.karyawanid!='" . $_SESSION['standard']['userid'] . "' and a.jenispersetujuan='" . $kodeapproval . "' and a.level='1' and a.kodeunit='" . $param['unit'] . "'  order by b.namakaryawan asc"; // exit('error'.$str);
		$res = fetchdata($str);
		if (count($res) == 0) {
			$tab .= "Silahkan lakukan setup terlebih dahulu melalui menu :<br><b>Setup - Persetujuan</b>, dengan data sebagai berikut :<br>Kode Organisasi : <b>" . $param['unit'] . "</b><br>Kode Approval : <b>" . $kodeapproval . "</b>";
		} else {
			foreach ($res as $val) {
				$optKry .= "<option value='" . $val['karyawanid'] . "'>" . $val['namakaryawan'] . " [" . $val['lokasitugas'] . "]</option>";
			}
			$tab .= "<table><input hidden id=unitajukan value=" . $param['unit'] . ">
					<tr>
						<td>No Pengajuan</td><td>:</td> 
						<td id=nopengajuan>" . $param['unit'] . "/" . $kodeapproval . "/" . date("YmdHis") . "</td> 
					</tr>
					<tr>
						<td>Kepada</td><td>:</td> 
						<td><select id=kepada style=\"width:200px;\">" . $optKry . "</select></td> 
					</tr>
					<tr>
						<td valign=top>Keterangan</td><td valign=top>:</td> 
						<td><textarea rows=3 maxlength=400 id=komentar  type='text' onkeypress='return tanpa_kutip(event)' style='width:180px;'></textarea></td> 
					</tr>
					<tr>
						<td valign=top></td><td valign=top></td> 
						<td><button onclick=ajukan('" . $kodeapproval . "','" . $param['stats'] . "') class=mybutton style=width:200px>Ajukan</button></td> 
					</tr>
				</table>";
		}
		echo $tab;
		break;
	case 'ajukan':
		try {
			$owlPDO->beginTransaction();
			if ($param['kepada'] == '') {
				throw new PDOException('Isikan nama penyetuju.');
			}
			if ($param['nopengajuan'] == '') {
				throw new PDOException('Nomor pengajuan wajib terisi.');
			}
			if ($param['jenispersetujuan'] == '') {
				throw new PDOException('Jenis Persetujuan wajib terisi.');
			}

			$str = "select * from " . $dbname . ".approval where jenispersetujuan = '" . $param['jenispersetujuan'] . "' and notransaksi like '" . $param['unit'] . "%'";
			$res = fetchdata($str);
			if (count($res) > 0) {
				foreach ($res as $val) {
					$data = array(
						'notransaksi'     => $val['notransaksi'],
						'jenispersetujuan' => $val['jenispersetujuan'],
						'level'           => $val['level'],
						'karyawanid'      => $val['karyawanid'],
						'status'          => $val['status'],
						'komentar'        => $val['komentar'],
						'keterangan'      => $val['keterangan'],
						'tanggal'         => $val['tanggal'],
						'nourut'          => $val['nourut']
					);

					// $str = insertQuery($dbname,'approval_return',$data,array_keys($data));
					// $owlPDO->exec($str);
				}
				// $str = "delete from ".$dbname.".approval where notransaksi  = '".$val['notransaksi']."' and jenispersetujuan='".$val['jenispersetujuan']."'";
				// $owlPDO->exec($str);

			}

			# update flag menjadi 1
			$str = "update " . $dbname . ".kebun_5hargaangkut set posting='9', nopengajuan ='" . $param['nopengajuan'] . "' where blok  like '" . $param['unit'] . "%' and posting = '" . $param['stats'] . "'";
			$owlPDO->exec($str);

			# update flag menjadi 1
			$str = "update " . $dbname . ".kebun_5hargaangkut set postingadd='9', nopengajuan ='" . $param['nopengajuan'] . "' where blok  like '" . $param['unit'] . "%' and postingadd ='" . $param['stats'] . "'";
			$owlPDO->exec($str);

			$str = "update " . $dbname . ".kebun_5hargaangkut_additional set posting='9', nopengajuan ='" . $param['nopengajuan'] . "' where blok  like '" . $param['unit'] . "%' and posting ='" . $param['stats'] . "'";
			$owlPDO->exec($str);

			$str = "delete from " . $dbname . ".approval where jenispersetujuan='ATBS' and status='0' and notransaksi not in (select nopengajuan from " . $dbname . ".kebun_5hargaangkut)";
			$owlPDO->exec($str);

			# insert ke table approval
			$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`,
					`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
				values ('','" . $param['nopengajuan'] . "','ATBS','1','" . $param['kepada'] . "','0','" . $param['komentar'] . "','','')";
			$owlPDO->exec($str);

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		break;
	case 'tambahbiaya':

		$str = "select * from " . $dbname . ".kebun_5hargaangkut where blok = '" . $blok . "' and pkstujuan='" . $pkstujuan . "' and jenisvhc='" . $jenisvhc . "' and tanggalberlaku='" . $param['tanggalberlaku'] . "' and nospk='" . $param['nospk'] . "' and (posting <> '1' or postingadd = '9')";
		$res = fetchdata($str);
		$poststat = count($res);

		$nminduk = makeOption($dbname, 'organisasi', 'indukblok,namaindukblok', "indukblok='" . $param['blok'] . "'");


		$tab .= "<table border=0 cellpadding=1 class=sortable cellspacing=1>
				<thead>
					<tr class=rowheader style=font-weight:bold>
						<th align=center rowspan=3>No</th> 
						<th align=center rowspan=3>Blok</th> 
						<th align=center rowspan=3 colspan=2>Tanggal Berlaku</th> 
						<th align=center colspan=7>Upah Muat</th> 
						<th align=center colspan=7>Upah Angkut</th> 
						<th align=center rowspan=3  colspan=2>" . $_SESSION['lang']['action'] . "</th> 
					</tr>
					<tr class=rowheader style=align:center;font-weight:bold>
						<th align=center>TPH-PKS 1</th> 
						<th align=center>TPH-PKS 2</th> 
						<th align=center>TPH-PKS 3</th> 
						<th align=center>TPH-PKS 4</th>
						<th align=center>TPH-PKS 5</th> 
						<th align=center>TPH-PKS 6</th> 
						<th align=center>TPH-PKS 7</th> 
						<th align=center>TPH-PKS 1</th> 
						<th align=center>TPH-PKS 2</th> 
						<th align=center>TPH-PKS 3</th> 
						<th align=center>TPH-PKS 4</th>	
						<th align=center>TPH-PKS 5</th> 
						<th align=center>TPH-PKS 6</th> 
						<th align=center>TPH-PKS 7</th> 
					</tr>
					<tr class=rowheader style=align:center;font-weight:bold>
						<th align=center>Tambahan (Rp)</th> 
						<th align=center>Tambahan (Rp)</th> 
						<th align=center>Tambahan (Rp)</th> 
						<th align=center>Tambahan (Rp)</th> 
						<th align=center>Tambahan (Rp)</th> 
						<th align=center>Tambahan (Rp)</th> 
						<th align=center>Tambahan (Rp)</th> 
						<th align=center>Tambahan (Rp)</th> 
						<th align=center>Tambahan (Rp)</th> 
						<th align=center>Tambahan (Rp)</th> 
						<th align=center>Tambahan (Rp)</th> 
						<th align=center>Tambahan (Rp)</th> 
						<th align=center>Tambahan (Rp)</th> 
						<th align=center>Tambahan (Rp)</th> 
					</tr>
				</thead>
			<tbody>";

		$tab .= "<tr class=rowcontent>";
		$tab .= "<td align=center>#</td>
				<input hidden id=nospksdd value=" . $param['nospk'] . "> 
				<input hidden id=pkstujuanadd value=" . $param['pkstujuan'] . "> 
				<input hidden id=jenisvhcadd value=" . $param['jenisvhc'] . "> 
				<input hidden id=tanggalberlakuadd value=" . $param['tanggalberlaku'] . "> 
				<input hidden id=blokadd value=" . $param['blok'] . "> 
				<input hidden id=methodadd value=insertadd> 
				<input hidden id=modeadd value=new> 
				<input hidden id=tglawallamaadd value=''> 
				";
		$tab .= "<td align=center><input style='width:80px;' class=myinputtext style=align:center; disabled value='" . $nminduk[$param['blok']] . "'></td>";
		$tab .= "<td align=center><input type='text' readonly=readonly class='myinputtext' id='tglmulai' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:100px;' value='" . date('d-m-Y') . "' /></td>";
		$tab .= "<td align=center><input type='text' readonly=readonly class='myinputtext' id='tglakhir' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:100px;' value='" . date('d-m-Y') . "' /></td>";
		$tab .= "<td align=center><input style='width:80px' id=muattphpks1add nkeypress='return_tanpa_kutip(event);' class='myinputtextnumber' onkeypress='return angka_doang(event);'></td>";
		$tab .= "<td align=center><input style='width:80px' id=muattphpks2add nkeypress='return_tanpa_kutip(event);' class='myinputtextnumber' onkeypress='return angka_doang(event);'></td>";
		$tab .= "<td align=center><input style='width:80px' id=muattphpks3add nkeypress='return_tanpa_kutip(event);' class='myinputtextnumber' onkeypress='return angka_doang(event);'></td>";
		$tab .= "<td align=center><input style='width:80px' id=muatramppksadd nkeypress='return_tanpa_kutip(event);' class='myinputtextnumber' onkeypress='return angka_doang(event);'></td>";
		$tab .= "<td align=center><input style='width:80px' id=muattphpks5add nkeypress='return_tanpa_kutip(event);' class='myinputtextnumber' onkeypress='return angka_doang(event);'></td>";
		$tab .= "<td align=center><input style='width:80px' id=muattphpks6add nkeypress='return_tanpa_kutip(event);' class='myinputtextnumber' onkeypress='return angka_doang(event);'></td>";
		$tab .= "<td align=center><input style='width:80px' id=muattphpks7add nkeypress='return_tanpa_kutip(event);' class='myinputtextnumber' onkeypress='return angka_doang(event);'></td>";
		$tab .= "<td align=center><input style='width:80px' id=angpkspks1add nkeypress='return_tanpa_kutip(event);' class='myinputtextnumber' onkeypress='return angka_doang(event);'></td>";
		$tab .= "<td align=center><input style='width:80px' id=angpkspks2add nkeypress='return_tanpa_kutip(event);' class='myinputtextnumber' onkeypress='return angka_doang(event);'></td>";
		$tab .= "<td align=center><input style='width:80px' id=angpkspks3add nkeypress='return_tanpa_kutip(event);' class='myinputtextnumber' onkeypress='return angka_doang(event);'></td>";
		$tab .= "<td align=center><input style='width:80px' id=angramppksadd nkeypress='return_tanpa_kutip(event);' class='myinputtextnumber' onkeypress='return angka_doang(event);'></td>";
		$tab .= "<td align=center><input style='width:80px' id=angpkspks5add nkeypress='return_tanpa_kutip(event);' class='myinputtextnumber' onkeypress='return angka_doang(event);'></td>";
		$tab .= "<td align=center><input style='width:80px' id=angpkspks6add nkeypress='return_tanpa_kutip(event);' class='myinputtextnumber' onkeypress='return angka_doang(event);'></td>";
		$tab .= "<td align=center><input style='width:80px' id=angpkspks7add nkeypress='return_tanpa_kutip(event);' class='myinputtextnumber' onkeypress='return angka_doang(event);'></td>";

		if ($poststat > 0) {
			$tab .= "<td colspan=2></td>";
		} else {
			$tab .= "<td colspan=2><button class=mybutton onclick=simpanadd()>" . $_SESSION['lang']['save'] . "</button></td>";
		}

		$tab .= "</tr>";

		$str = "select blok,pkstujuan,jenisvhc, posting, nopengajuan, tanggalberlaku, tglakhir, tglawal, nospk, max(lastupdate) as lastupdate from " . $dbname . ".kebun_5hargaangkut_additional where 0=0 and blok = '" . $param['blok'] . "' and jenisvhc='" . $param['jenisvhc'] . "' and pkstujuan='" . $param['pkstujuan'] . "' and tanggalberlaku='" . $param['tanggalberlaku'] . "' and nospk='" . $param['nospk'] . "' group by blok,pkstujuan,jenisvhc,posting,nopengajuan,tanggalberlaku,tglakhir,tglawal,nospk order by lastupdate desc";
		$res = fetchdata($str);
		foreach ($res as $val) {
			$no++;
			$tab .= "<tr class=rowcontent style=height:25px>";
			$tab .= "<td align=center>" . $no . "</td>";
			$tab .= "<td align=center>" . $nminduk[$val['blok']] . "</td>";
			$tab .= "<td align=center>" . tanggalnormal($val['tglawal']) . "</td>";
			$tab .= "<td align=center>" . tanggalnormal($val['tglakhir']) . "</td>";

			$isimtp1 = $isimtp2 = $isimtp3 = $isimrp = $isimtp5 = $isimtp6 = $isimtp7 = 0;
			$isiatp1 = $isiatp2 = $isiatp3 = $isiarp = $isiatp5 = $isiatp6 = $isiatp7 = 0;

			$i = "select * from " . $dbname . ".kebun_5hargaangkut_additional where blok = '" . $val['blok'] . "' and jenisvhc='" . $val['jenisvhc'] . "' and pkstujuan='" . $val['pkstujuan'] . "' and tanggalberlaku='" . $val['tanggalberlaku'] . "' and tglawal='" . $val['tglawal'] . "' and nospk='" . $val['nospk'] . "'";
			$req = fetchdata($i);
			foreach ($req as $baradd) {
				$tujuanAdd = $baradd['tujuan'];
				// Data lama yang gagal masuk ke ENUM rampks tersimpan sebagai string kosong.
				if ($tujuanAdd == '') {
					$tujuanAdd = 'rampks';
				}

				if ($baradd['jenis'] == 'muat' and $tujuanAdd == 'tphpks1') $isimtp1 = $baradd['harga'];
				if ($baradd['jenis'] == 'muat' and $tujuanAdd == 'tphpks2') $isimtp2 = $baradd['harga'];
				if ($baradd['jenis'] == 'muat' and $tujuanAdd == 'tphpks3') $isimtp3 = $baradd['harga'];
				if ($baradd['jenis'] == 'muat' and $tujuanAdd == 'rampks')  $isimrp  = $baradd['harga'];
				if ($baradd['jenis'] == 'muat' and $tujuanAdd == 'tphpks5') $isimtp5 = $baradd['harga'];
				if ($baradd['jenis'] == 'muat' and $tujuanAdd == 'tphpks6') $isimtp6 = $baradd['harga'];
				if ($baradd['jenis'] == 'muat' and $tujuanAdd == 'tphpks7') $isimtp7 = $baradd['harga'];

				if ($baradd['jenis'] == 'angkut' and $tujuanAdd == 'tphpks1') $isiatp1 = $baradd['harga'];
				if ($baradd['jenis'] == 'angkut' and $tujuanAdd == 'tphpks2') $isiatp2 = $baradd['harga'];
				if ($baradd['jenis'] == 'angkut' and $tujuanAdd == 'tphpks3') $isiatp3 = $baradd['harga'];
				if ($baradd['jenis'] == 'angkut' and $tujuanAdd == 'rampks')  $isiarp  = $baradd['harga'];
				if ($baradd['jenis'] == 'angkut' and $tujuanAdd == 'tphpks5') $isiatp5 = $baradd['harga'];
				if ($baradd['jenis'] == 'angkut' and $tujuanAdd == 'tphpks6') $isiatp6 = $baradd['harga'];
				if ($baradd['jenis'] == 'angkut' and $tujuanAdd == 'tphpks7') $isiatp7 = $baradd['harga'];
			}

			foreach (array($isimtp1, $isimtp2, $isimtp3, $isimrp, $isimtp5, $isimtp6, $isimtp7, $isiatp1, $isiatp2, $isiatp3, $isiarp, $isiatp5, $isiatp6, $isiatp7) as $nilaiAdd) {
				$tab .= "<td align=right>" . number_format((float)$nilaiAdd, 2) . "</td>";
			}

			if (in_array((string)$val['posting'], array('0', '2', '3'))) {
				$tab .= "<td align=center style=width:20px><img src=images/application/application_edit.png class=zImgBtn title='Edit' onclick=\"editadd('" . $val['blok'] . "','" . $val['jenisvhc'] . "','" . $val['pkstujuan'] . "','" . $val['tanggalberlaku'] . "','" . $isimtp1 . "','" . $isimtp2 . "','" . $isimtp3 . "','" . $isimrp . "','" . $isimtp5 . "','" . $isimtp6 . "','" . $isimtp7 . "','" . $isiatp1 . "','" . $isiatp2 . "','" . $isiatp3 . "','" . $isiarp . "','" . $isiatp5 . "','" . $isiatp6 . "','" . $isiatp7 . "','" . tanggalnormal($val['tglawal']) . "','" . tanggalnormal($val['tglakhir']) . "');\"></td>";
				$tab .= "<td align=center style=width:20px><img src=images/application/application_delete.png class=zImgBtn title='Delete' onclick=\"deladd('" . $val['blok'] . "','" . $val['pkstujuan'] . "','" . $val['jenisvhc'] . "','" . $val['tanggalberlaku'] . "','" . $val['tglawal'] . "','" . $val['nospk'] . "');\"></td>";
			} else {
				$tab .= "<td align=center style=width:20px></td>";
				$tab .= "<td align=center style=width:20px></td>";
			}
		}

		echo $tab;
		break;
	case 'deladd':
		try {
			$owlPDO->beginTransaction();
			$str = "delete from " . $dbname . ".kebun_5hargaangkut_additional where blok = '" . $blok . "' and pkstujuan='" . $pkstujuan . "' and jenisvhc='" . $jenisvhc . "' and tanggalberlaku='" . $param['tanggalberlaku'] . "' and tglawal='" . $param['tglawal'] . "' and nospk='" . $param['nospk'] . "' and posting in ('0','2','3')";
			$owlPDO->exec($str);

			$str = "select posting,nopengajuan from " . $dbname . ".kebun_5hargaangkut_additional where blok = '" . $blok . "' and pkstujuan='" . $pkstujuan . "' and jenisvhc='" . $jenisvhc . "' and tanggalberlaku='" . $param['tanggalberlaku'] . "' and nospk='" . $param['nospk'] . "' order by field(posting,'0','2','3','9','1'), lastupdate desc limit 1";
			$sisa = fetchdata($str);
			$postingadd = '1';
			$nopengajuanadd = '';
			if (count($sisa) > 0) {
				$postingadd = $sisa[0]['posting'];
				$nopengajuanadd = $sisa[0]['nopengajuan'];
			}

			$str = "update " . $dbname . ".kebun_5hargaangkut set postingadd='" . $postingadd . "', nopengajuan='" . $nopengajuanadd . "' where blok = '" . $blok . "' and pkstujuan='" . $pkstujuan . "' and jenisvhc='" . $jenisvhc . "' and tanggalberlaku='" . $param['tanggalberlaku'] . "' and nospk='" . $param['nospk'] . "'";
			$owlPDO->exec($str);
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Warning, " . addslashes($e->getMessage());
		}

		break;
	case 'insertadd':
		try {
			$owlPDO->beginTransaction();

			if ($muat_tphpks1 == '') {
				$muat_tphpks1 = '0';
			}
			if ($muat_tphpks2 == '') {
				$muat_tphpks2 = '0';
			}
			if ($muat_tphpks3 == '') {
				$muat_tphpks3 = '0';
			}
			if ($muat_rampks == '') {
				$muat_rampks = '0';
			}
			if ($muat_tphpks5 == '') {
				$muat_tphpks5 = '0';
			}
			if ($muat_tphpks6 == '') {
				$muat_tphpks6 = '0';
			}
			if ($muat_tphpks7 == '') {
				$muat_tphpks7 = '0';
			}
			if ($angkut_tphpks1 == '') {
				$angkut_tphpks1 = '0';
			}
			if ($angkut_tphpks2 == '') {
				$angkut_tphpks2 = '0';
			}
			if ($angkut_tphpks3 == '') {
				$angkut_tphpks3 = '0';
			}
			if ($angkut_rampks == '') {
				$angkut_rampks = '0';
			}
			if ($angkut_tphpks5 == '') {
				$angkut_tphpks5 = '0';
			}
			if ($angkut_tphpks6 == '') {
				$angkut_tphpks6 = '0';
			}
			if ($angkut_tphpks7 == '') {
				$angkut_tphpks7 = '0';
			}

			if ($param['tglmulai'] == "") {
				throw new PDOException("Tanggal mulai berlaku wajib diisi.");
			}
			if ($param['tglakhir'] == "") {
				throw new PDOException("Tanggal akhir berlaku wajib diisi.");
			}
			if (tanggalsystemn($param['tglmulai']) > tanggalsystemn($param['tglakhir'])) {
				throw new PDOException("Tanggal mulai tidak boleh lebih besar dari tanggal akhir.");
			}
			$nilaiTambahan = array(
				$muat_tphpks1,
				$muat_tphpks2,
				$muat_tphpks3,
				$muat_rampks,
				$muat_tphpks5,
				$muat_tphpks6,
				$muat_tphpks7,
				$angkut_tphpks1,
				$angkut_tphpks2,
				$angkut_tphpks3,
				$angkut_rampks,
				$angkut_tphpks5,
				$angkut_tphpks6,
				$angkut_tphpks7
			);
			$adaTambahan = false;
			foreach ($nilaiTambahan as $nilai) {
				if ((float)$nilai != 0) {
					$adaTambahan = true;
					break;
				}
			}
			if (!$adaTambahan) {
				throw new PDOException("Tambahan biaya wajib diisi.");
			}

			$tglawalBaru = tanggalsystemn($param['tglmulai']);
			$tglakhirBaru = tanggalsystemn($param['tglakhir']);
			$tglawalLama = $tglawalBaru;
			$wh = '';
			if ($param['modeadd'] == 'edit') {
				if ($param['tglawallama'] != '') {
					$tglawalLama = tanggalsystemn($param['tglawallama']);
				}
				$wh = "and tglawal!='" . $tglawalLama . "'";
			}

			$hargaTambahan = array(
				'muat' => array(
					'tphpks1' => $muat_tphpks1,
					'tphpks2' => $muat_tphpks2,
					'tphpks3' => $muat_tphpks3,
					'rampks'  => $muat_rampks,
					'tphpks5' => $muat_tphpks5,
					'tphpks6' => $muat_tphpks6,
					'tphpks7' => $muat_tphpks7
				),
				'angkut' => array(
					'tphpks1' => $angkut_tphpks1,
					'tphpks2' => $angkut_tphpks2,
					'tphpks3' => $angkut_tphpks3,
					'rampks'  => $angkut_rampks,
					'tphpks5' => $angkut_tphpks5,
					'tphpks6' => $angkut_tphpks6,
					'tphpks7' => $angkut_tphpks7
				)
			);

			$data = array();
			$str = "select * from " . $dbname . ".kebun_5hargaangkut_additional where blok = '" . $blok . "' and pkstujuan='" . $pkstujuan . "' and jenisvhc='" . $jenisvhc . "' and tanggalberlaku='" . $param['tanggalberlaku'] . "' " . $wh . " and nospk='" . $param['nospk'] . "'";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$data[] = rangeTanggal($bar['tglawal'], $bar['tglakhir']);
			}

			$lsdate = array();
			foreach ($data as $key => $v1) {
				foreach ($v1 as $date) {
					$lsdate[$date] = $date;
				}
			}

			$tglada = array();
			$lsadd = rangeTanggal($tglawalBaru, $tglakhirBaru);
			foreach ($lsadd as $tgladd) {
				if (isset($lsdate[$tgladd])) {
					$tglada[tanggalnormal($tgladd)] = tanggalnormal($tgladd);
				}
			}

			if (count($tglada) > 0) {
				throw new PDOException("Tambahan biaya tanggal : " . implode(", ", $tglada) . " sudah ada.");
			}

			$tglawalCari = ($param['modeadd'] == 'edit') ? $tglawalLama : $tglawalBaru;
			$str = "select * from " . $dbname . ".kebun_5hargaangkut_additional where blok = '" . $blok . "' and pkstujuan='" . $pkstujuan . "' and jenisvhc='" . $jenisvhc . "' and tglawal='" . $tglawalCari . "' and tanggalberlaku='" . $param['tanggalberlaku'] . "' and nospk='" . $param['nospk'] . "'";
			$res = fetchdata($str);
			if (count($res) > 0) {
				if ($param['modeadd'] == 'edit') {
					foreach ($res as $val) {
						$tujuanAdd = $val['tujuan'];
						if ($tujuanAdd == '' or $tujuanAdd == 'tphpks4') {
							$tujuanAdd = 'rampks';
						}
						$data = array(
							'tglawal'       => $tglawalBaru,
							'tglakhir'      => $tglakhirBaru,
							'tujuan'        => $tujuanAdd,
							'updateby'      => $_SESSION['standard']['userid'],
							'lastupdate'    => date("Y-m-d H:i:s"),
							'posting'       => '0'
						);
						$data['harga'] = isset($hargaTambahan[$val['jenis']][$tujuanAdd]) ? $hargaTambahan[$val['jenis']][$tujuanAdd] : 0;

						$where = "blok = '" . $blok . "' and pkstujuan='" . $pkstujuan . "' and jenisvhc='" . $jenisvhc . "' and tglawal='" . $tglawalLama . "' and tanggalberlaku='" . $param['tanggalberlaku'] . "' and nospk='" . $param['nospk'] . "' and jenis='" . $val['jenis'] . "' and tujuan='" . $val['tujuan'] . "'";
						$str = updateQuery($dbname, 'kebun_5hargaangkut_additional', $data, $where);
						$owlPDO->exec($str);

						$str = "update " . $dbname . ".kebun_5hargaangkut set postingadd='0', nopengajuan='' where blok = '" . $blok . "' and pkstujuan='" . $pkstujuan . "' and jenisvhc='" . $jenisvhc . "' and tanggalberlaku='" . $param['tanggalberlaku'] . "' and nospk='" . $param['nospk'] . "'";
						$owlPDO->exec($str);
					}
				} else {
					throw new PDOException("Data sudah pernah diinput.");
				}
			} else {
				$str = "select distinct blok, jenis, tujuan, kodekeg, pkstujuan, jenisvhc, nospk from " . $dbname . ".kebun_5hargaangkut where blok = '" . $blok . "' and pkstujuan='" . $pkstujuan . "' and jenisvhc='" . $jenisvhc . "' and tanggalberlaku='" . $param['tanggalberlaku'] . "' and nospk='" . $param['nospk'] . "'";
				$res = fetchdata($str);
				foreach ($res as $val) {
					$tujuanAdd = ($val['tujuan'] == 'tphpks4') ? 'rampks' : $val['tujuan'];
					$data = array();
					$data = array(
						'blok'          => $val['blok'],
						'tanggalberlaku' => $param['tanggalberlaku'],
						'tglawal'       => $tglawalBaru,
						'tglakhir'      => $tglakhirBaru,
						'jenis'         => $val['jenis'],
						'tujuan'        => $tujuanAdd,
						'kodekeg'       => $val['kodekeg'],
						'pkstujuan'     => $val['pkstujuan'],
						'jenisvhc'      => $val['jenisvhc'],
						'nospk'         => $val['nospk'],
						'updateby'      => $_SESSION['standard']['userid'],
						'lastupdate'    => date("Y-m-d H:i:s"),
						'posting'       => '0'
					);
					$data['harga'] = isset($hargaTambahan[$val['jenis']][$tujuanAdd]) ? $hargaTambahan[$val['jenis']][$tujuanAdd] : 0;

					$str = insertQuery($dbname, 'kebun_5hargaangkut_additional', $data, array_keys($data));
					$owlPDO->exec($str);
				}
				// echo $str; exit("error");
				$str = "update " . $dbname . ".kebun_5hargaangkut set postingadd='0', nopengajuan='' where blok = '" . $blok . "' and pkstujuan='" . $pkstujuan . "' and jenisvhc='" . $jenisvhc . "' and tanggalberlaku='" . $param['tanggalberlaku'] . "' and nospk='" . $param['nospk'] . "'";
				$owlPDO->exec($str);
			}

			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Warning, " . addslashes($e->getMessage());
			die();
		}
		break;
	case 'getdatapengajuan':
		$tab .= "<table border=0 cellpadding=5 class=sortable cellspacing=1 width=100%>
				<thead>
					<tr class=rowheader style=font-weight:bold>
						<th align=center rowspan=3>No</th> 
						<th align=center rowspan=3>Blok</th> 
						<th align=center rowspan=3>No SPK</th> 
						<th align=center rowspan=3 colspan=2>Tanggal Berlaku</th> 
						<th align=center colspan=8>Upah Muat</th> 
						<th align=center colspan=8>Upah Angkut</th> 
					</tr>
					<tr class=rowheader style=align:center;font-weight:bold>
						<th align=center colspan=2>TPH-PKS 1</th> 
						<th align=center colspan=2>TPH-PKS 2</th> 
						<th align=center colspan=2>TPH-PKS 3</th> 
						<th align=center colspan=2>TPH-PKS 4</th>
						<th align=center colspan=2>TPH-PKS 1</th> 
						<th align=center colspan=2>TPH-PKS 2</th> 
						<th align=center colspan=2>TPH-PKS 3</th> 
						<th align=center colspan=2>TPH-PKS 4</th>	
					</tr>
					<tr class=rowheader style=align:center;font-weight:bold>
						<th align=center>Lama</th> 
						<th align=center>Baru</th> 
						<th align=center>Lama</th> 
						<th align=center>Baru</th> 
						<th align=center>Lama</th> 
						<th align=center>Baru</th> 
						<th align=center>Lama</th> 
						<th align=center>Baru</th> 
						<th align=center>Lama</th> 
						<th align=center>Baru</th> 
						<th align=center>Lama</th> 
						<th align=center>Baru</th> 
						<th align=center>Lama</th> 
						<th align=center>Baru</th> 
						<th align=center>Lama</th> 
						<th align=center>Baru</th> 
					</tr>
				</thead>
			<tbody>";
		if ($param['nopengajuan'] == '') {
			exit("warning : nopengajuan tidak boleh kosong");
		}

		$where = "and nopengajuan='" . $param['nopengajuan'] . "'";
		$str = "select * from " . $dbname . ".kebun_5hargaangkut where 0=0 " . $where . " order by blok asc, tanggalberlaku desc ";
		$res = fetchdata($str);
		$data = array();
		foreach ($res as $bar) {
			$data[$bar['blok']][$bar['pkstujuan']][$bar['jenisvhc']][$bar['tanggalberlaku']][$bar['nospk']] = $bar['nospk'];
			$rupiah[$bar['blok']][$bar['pkstujuan']][$bar['jenisvhc']][$bar['tanggalberlaku']][$bar['nospk']][$bar['jenis']][$bar['tujuan']] = $bar['harga'];

			$kodeorg = substr($bar['blok'], 0, 4);
		}
		if ($kodeorg != '') {
			//$str = "select * from ".$dbname.".kebun_5hargaangkut_hist where 0=0 and blok like '".$kodeorg."%' order by id asc";

			$whr = "and nopengajuan!='" . $param['nopengajuan'] . "'";
			$str = "select * from " . $dbname . ".kebun_5hargaangkut where 0=0 " . $whr . " and blok like '" . $kodeorg . "%' and posting=1 order by blok asc, tanggalberlaku asc ";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$rpold[$bar['blok']][$bar['pkstujuan']][$bar['jenisvhc']][$bar['jenis']][$bar['tujuan']][$bar['nospk']] = $bar['harga'];
			}
		}

		$addrp = $cek = $addrpold = array();
		$str = "select * from " . $dbname . ".kebun_5hargaangkut_additional where 0=0 " . $where . " order by blok asc, tanggalberlaku desc ";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$tujuanAdd = ($bar['tujuan'] == '') ? 'rampks' : $bar['tujuan'];
			$addrp[$bar['blok']][$bar['pkstujuan']][$bar['jenisvhc']][$bar['tanggalberlaku']][$bar['nospk']][$bar['jenis']][$tujuanAdd] = $bar['harga'];
			$cek[$bar['blok']][$bar['pkstujuan']][$bar['jenisvhc']][$bar['tanggalberlaku']][$bar['nospk']] = 1;
			$tgladd[$bar['blok']][$bar['pkstujuan']][$bar['jenisvhc']][$bar['tanggalberlaku']][$bar['nospk']]['awal'] = $bar['tglawal'];
			$tgladd[$bar['blok']][$bar['pkstujuan']][$bar['jenisvhc']][$bar['tanggalberlaku']][$bar['nospk']]['akhir'] = $bar['tglakhir'];
		}

		$str = "select * from " . $dbname . ".kebun_5hargaangkut_additional where 0=0 and nopengajuan!='" . $param['nopengajuan'] . "' order by tglawal asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$tujuanAdd = ($bar['tujuan'] == '') ? 'rampks' : $bar['tujuan'];
			$addrpold[$bar['blok']][$bar['pkstujuan']][$bar['jenisvhc']][$bar['jenis']][$tujuanAdd] = $bar['harga'];
		}
		// echo "<pre>";
		// print_r($cek);

		$arrjenis  = array('muat'  => 'Upah Muat', 'angkut' => 'Upah Angkut');
		$arrtujuanMain = array('tphpks1' => 'TPH-PKS 1', 'tphpks2' => 'TPH-PKS 2', 'tphpks3' => 'TPH-PKS 3', 'tphpks4' => 'TPH-PKS 4');
		$arrtujuanAdd  = array('tphpks1' => 'TPH-PKS 1', 'tphpks2' => 'TPH-PKS 2', 'tphpks3' => 'TPH-PKS 3', 'rampks'  => 'TPH-PKS 4');

		foreach ($data as $blok => $v1) {
			foreach ($v1 as $pkstujuan => $v2) {
				foreach ($v2 as $jenisvhc => $v3) {
					foreach ($v3 as $tanggalberlaku => $v4) {
						foreach ($v4 as $nospk) {
							$no++;
							$tab .= "<tr style=vertical-align:top class=rowcontent>";
							$tab .= "<td align=center>" . $no . "</td>";
							$tab .= "<td>" . getIndukBlok($blok) . "</td>";
							$tab .= "<td>" . $nospk . "</td>";
							$tab .= "<td align=center colspan=2>" . tglnmbln($tanggalberlaku, 'I', 'long') . "</td>";
							foreach ($arrjenis as $jenis => $nmjenis) {
								foreach ($arrtujuanMain as $tujuan => $nmtujuan) {
									$col = "";
									if ($rpold[$blok][$pkstujuan][$jenisvhc][$jenis][$tujuan][$nospk] != $rupiah[$blok][$pkstujuan][$jenisvhc][$tanggalberlaku][$nospk][$jenis][$tujuan]) {
										$col = "style=background-color:#03F9F7";
									}
									$tab .= "<td align=right>" . $rpold[$blok][$pkstujuan][$jenisvhc][$jenis][$tujuan][$nospk] . "</td>";
									$tab .= "<td " . $col . " align=right>" . $rupiah[$blok][$pkstujuan][$jenisvhc][$tanggalberlaku][$nospk][$jenis][$tujuan] . "</td>";
								}
							}
							$tab .= "</tr>";
							$hide = "hidden";
							if ($cek[$blok][$pkstujuan][$jenisvhc][$tanggalberlaku][$nospk] != '') {
								$hide = "";
							}
							$tab .= "<tr " . $hide . " class=rowcontent>";
							$tab .= "<td align=center colspan=3>Biaya Tambahan</td>";
							$tab .= "<td align=center >" . $tgladd[$blok][$pkstujuan][$jenisvhc][$tanggalberlaku][$nospk]['awal'] . "</td>";
							$tab .= "<td align=center >" . $tgladd[$blok][$pkstujuan][$jenisvhc][$tanggalberlaku][$nospk]['akhir'] . "</td>";
							foreach ($arrjenis as $jenis => $nmjenis) {
								foreach ($arrtujuanAdd as $tujuan => $nmtujuan) {
									$col = "";
									if ($addrpold[$blok][$pkstujuan][$jenisvhc][$jenis][$tujuan] != $addrp[$blok][$pkstujuan][$jenisvhc][$tanggalberlaku][$nospk][$jenis][$tujuan]) {
										$col = "style=background-color:#03F96A";
									}
									$tab .= "<td align=right>" . $addrpold[$blok][$pkstujuan][$jenisvhc][$jenis][$tujuan] . "</td>";
									$tab .= "<td " . $col . " align=right>" . $addrp[$blok][$pkstujuan][$jenisvhc][$tanggalberlaku][$nospk][$jenis][$tujuan] . "</td>";
								}
							}
							$tab .= "</tr>";
						}
					}
				}
			}
		}

		if ($namafile != '') {
			$dompdf = new Dompdf();
			$dompdf->load_html($tab);
			//$customPaper = array(0,0,850,1500);
			$dompdf->set_paper('A4', 'landscape');
			$dompdf->render();
			$canvas = $dompdf->get_canvas();

			if (file_exists($namafile)) {
				unlink($namafile);
			}
			file_put_contents($namafile, $dompdf->output());
		} else {
			echo $tab;
		}
		break;
	case 'unduhformat':
		$fname = 'HARGA_LOADING_DAN_ANGKUT_TBS.xlsx';
		$arrmuat = array('tphpks1' => 'TPH-PKS 1', 'tphpks2' => 'TPH-PKS 2', 'tphpks3' => 'TPH-PKS 3', 'tphpks4'  => 'TPH-PKS 4', 'tphpks5'  => 'TPH-PKS 5', 'tphpks6'  => 'TPH-PKS 6', 'tphpks7'  => 'TPH-PKS 7');
		$wh = "";
		$whdt = "";
		if ($divisi != '') {
			$wh .= " and kodeorg like '" . $divisi . "%'";
		}
		if ($tahuntanam != '') {
			$wh .= " and tahuntanam = '" . $tahuntanam . "'";
		}
		if ($param['blok'] != '') {
			$wh .= " and indukblok = '" . $param['blok'] . "'";
		}

		$header1 = [
			'blok' 				=> 'string',
			'jenis' 			=> 'string',
			'tujuan' 			=> 'string',
			'pkstujuan' 		=> 'string',
			'kodekeg' 			=> 'string',
			'jenisvhc' 			=> 'string',
			'tanggalberlaku' 	=> 'date',
			'harga' 			=> 'price'
		];

		$arrisi = array();
		$sql = selectQuery($dbname, 'setup_blok', 'indukblok', "kodeorg like '" . $unit . "%' " . $wh . " and tahuntanam != '0' ", '', true);
		$rst = fetchData($sql);
		foreach ($rst as $v) {
			foreach ($arrmuat as $key => $value) {
				$arrisi[] = [$v['indukblok'], 'angkut', $key, $pkstujuan, '611020101', $param['jeniskendaraan'], tanggaldb($param['tanggalberlaku']), 0];
			}
		}

		$styles2 = array(['font-size' => 6], ['font-size' => 8], ['font-size' => 10], ['font-size' => 16]);

		$writer = new XLSXWriter();
		$writer->setAuthor(getNamaKaryawan($_SESSION['standard']['userid']));
		$writer->writeSheet($arrisi, 'FormatAngkutTBS', $header1);

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $fname . '"');
		header('Cache-Control: max-age=0');
		$writer->writeToStdOut();
		break;

	case 'fileSelected':
		$data = $_POST;
		$arrmuat = array('tphpks1' => 'TPH-PKS 1', 'tphpks2' => 'TPH-PKS 2', 'tphpks3' => 'TPH-PKS 3', 'tphpks4'  => 'TPH-PKS 4', 'tphpks5'  => 'TPH-PKS 5', 'tphpks6'  => 'TPH-PKS 6', 'tphpks7'  => 'TPH-PKS 7');
		$param['kodeorg'] = $_SESSION['empl']['lokasitugas'];
		$kodeorg         = $_SESSION['empl']['lokasitugas'];

		$str = "select * from " . $dbname . ".vhc_5jenisvhc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$kelvhc[$bar['jenisvhc']] = $bar['kelompokvhc'];
		}


		if ($_FILES['file']['error'] == 0) {
			$filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
			$file    = $_FILES['file']['tmp_name'];

			if ($filetype == '.xlsx') {
				$load = PHPExcel_IOFactory::load($file);
				$sheets = $load->getActiveSheet()->toArray(null, true, true, true);

				# New 
				function createRange($start, $end)
				{
					$range = [];
					$current = $start;
					while ($current !== $end) {
						$range[] = $current;
						$current = ++$current; // Increment column
					}
					$range[] = $end; // Add the end column
					return $range;
				}

				// Tes fungsi createRange
				$range = createRange('A', 'F');

				$header = array('blok', 'jenis', 'tujuan', 'pkstujuan', 'kodekeg', 'harga');

				foreach ($header as $head) {
					$cekhead[$head] = $head;
				}
				$arritem = $bloklist = $kodeorglist = array();
				$validasiht = "";
				$err = "0";
				foreach ($sheets as $noitem => $sheet) {
					if ($noitem > 1) {
						$jenis[$sheet['A']] = $sheet['B'];
						$tujuan[$sheet['A']] = $sheet['C'];
						$pkstujuanexcl[$sheet['A']] = $sheet['D'];
						$bloknya[$sheet['A']] = $sheet['A'];
						$kodeorglist[$sheet['B']] = $sheet['B'];
						$bloklist[$sheet['A']][$sheet['B']][$sheet['C']] = $sheet['A'];
						$harganya[$sheet['A']][$sheet['B']][$sheet['C']] = $sheet['H'];
						$kodekeg = $sheet['E'];
					}
				}

				if (count($kodeorglist) != 1) {
					$validasiht .= "Kodeorganisasi tidak boleh lebih dari satu.<br>";
					$err++;
				}

				$optpks = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
				$sql = "SELECT * FROM " . $dbname . ".organisasi where tipe='PABRIK' and namaorganisasi not like '%BULKING%'";
				$res = fetchdata($sql);
				foreach ($res as $bar) {
					if ($pkstujuanht == $bar['kodeorganisasi']) {
						$i = "selected";
					} else {
						$i = "";
					}
					$d = "PKS INTERNAL dan AFILIASI";
					if ($d != $n) {
						$optpks .= "<optgroup label='" . $d . "'>";
					}
					$optpks .= "<option value=" . $bar['kodeorganisasi'] . " " . $i . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
					$n = $d;
					if ($d != $n) {
						$optpks .= "</optgroup>";
					}
				}
				#== Keluarkan yang mau ditampilkan 
				$iPks = "select distinct b.* from " . $dbname . ".pmn_4komoditi a left join " . $dbname . ".pmn_4customer b
				ON a.kodecustomer=b.kodecustomer where a.kodebarang='40000003'";
				$rPks = fetchData($iPks);
				foreach ($rPks as $dPks) {
					if ($pkstujuanht == $dPks['kodecustomer']) {
						$select = "selected=selected";
					} else {
						$select = "";
					}
					$optpks .= "<option " . $select . " value='" . $dPks['kodecustomer'] . "'>" . $dPks['namacustomer'] . "</option>";
				}

				$optjns = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
				$optjns .= "<option value='GLOBAL' selected>GLOBAL</option>";
				$sql = "SELECT * FROM " . $dbname . ".vhc_5jenisvhc";
				$res = fetchdata($sql);
				foreach ($res as $bar) {
					if ($bar['jenisvhc'] == $jnskendht) {
						$i = "selected";
					} else {
						$i = "";
					}
					$optjns .= "<option value=" . $bar['jenisvhc'] . " " . $i . ">" . $bar['jenisvhc'] . " - " . $bar['namajenisvhc'] . "</option>";
				}

				$optmtp1 = $optmtp2 = $optmtp3 = $optmtp4 = $optmrp = $optatp1 = $optatp2 = $optatp3 = $optatp4 = $optarp = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
				$str = "select * from " . $dbname . ".setup_kegiatan where noakun like '611%'";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					if ($bar['kodekegiatan'] == $kodekeg) {
						$sel = 'selected';
					} else {
						$sel = '';
					}
					$optmtp1 .= "<option value=" . $bar['kodekegiatan'] . " " . $sel . ">" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
					$optmtp2 .= "<option value=" . $bar['kodekegiatan'] . " " . $sel . ">" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
					$optmtp3 .= "<option value=" . $bar['kodekegiatan'] . " " . $sel . ">" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
					$optmtp4 .= "<option value=" . $bar['kodekegiatan'] . " " . $sel . ">" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
					$optatp1 .= "<option value=" . $bar['kodekegiatan'] . " " . $sel . ">" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
					$optatp2 .= "<option value=" . $bar['kodekegiatan'] . " " . $sel . ">" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
					$optatp3 .= "<option value=" . $bar['kodekegiatan'] . " " . $sel . ">" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
					$optatp4 .= "<option value=" . $bar['kodekegiatan'] . " " . $sel . ">" . $bar['namakegiatan'] . " [" . $bar['kodekegiatan'] . "]</option>";
				}

				$tab .= "<tr class=rowcontent>";
				$tab .= "<td valign=top align=center colspan=6></td>";
				$tab .= "<td valign=top align=center><select class=select2 id=muattphpks1 style=\"width:150px;\">" . $optmtp1 . "</select></td>";
				$tab .= "<td valign=top align=center><select class=select2 id=muattphpks2 style=\"width:150px;\">" . $optmtp2 . "</select></td>";
				$tab .= "<td valign=top align=center><select class=select2 id=muattphpks3 style=\"width:150px;\">" . $optmtp3 . "</select></td>";
				$tab .= "<td valign=top align=center><select class=select2 id=muatramppks style=\"width:150px;\">" . $optmtp4 . "</select></td>";
				$tab .= "<td valign=top align=center><select class=select2 id=muattphpks5 style=\"width:150px;\">" . $optmtp3 . "</select></td>";
				$tab .= "<td valign=top align=center><select class=select2 id=muattphpks6 style=\"width:150px;\">" . $optmtp3 . "</select></td>";
				$tab .= "<td valign=top align=center><select class=select2 id=muattphpks7 style=\"width:150px;\">" . $optmtp3 . "</select></td>";
				$tab .= "<td valign=top align=center><select class=select2 id=angkuttphpks1 style=\"width:150px;\">" . $optatp1 . "</select></td>";
				$tab .= "<td valign=top align=center><select class=select2 id=angkuttphpks2 style=\"width:150px;\">" . $optatp2 . "</select></td>";
				$tab .= "<td valign=top align=center><select class=select2 id=angkuttphpks3 style=\"width:150px;\">" . $optatp3 . "</select></td>";
				$tab .= "<td valign=top align=center><select class=select2 id=angkutramppks style=\"width:150px;\">" . $optatp4 . "</select></td>";
				$tab .= "<td valign=top align=center><select class=select2 id=angkuttphpks5 style=\"width:150px;\">" . $optatp3 . "</select></td>";
				$tab .= "<td valign=top align=center><select class=select2 id=angkuttphpks6 style=\"width:150px;\">" . $optatp3 . "</select></td>";
				$tab .= "<td valign=top align=center><select class=select2 id=angkuttphpks7 style=\"width:150px;\">" . $optatp3 . "</select></td>";
				$tab .= "<td valign=top align=center></td>";
				$tab .= "</tr>";

				foreach ($bloknya as $blk => $sheet) {
					// if($noitem==1){
					// $tab.="<table class='sortable' cellspacing=1 cellpadding=5 border=0 >
					// <thead>
					// 	<tr class=rowheader style=height:25px>";
					// 	$tab.="<th align=center width=30px>No.</th>";
					// 	foreach($range as $idcol => $col){
					// 		$style="";
					// 		if($cekhead[$sheet[$col]]==""){
					// 			$style="style=color:red; title='Kolom header mengalami perubahan.'";
					// 		}
					// 		$tab.="<th align=center ".$style.">".$sheet[$col]."</th>";
					// 	}								
					// 	$tab.="<th align=center>Status</th>";
					// 	$tab.="<th align=center>Selisih <br/> (Jjg)</th>";
					// 	$tab.="<th align=center>Selisih <br/> (Kg)</th>";
					// 	$tab.="<th align=center>Selisih <br/> (Kg Bruto)</th>";
					// $tab.="</tr>
					// </thead>";

					// $str = "select * from ".$dbname.".bgt_masterbarang where regional='".$region."' and tahunbudget='".$tahun."'";
					// $res = fetchData($str);
					// foreach($res as $bar){
					// 	$hargabarang[$bar['kodebarang']]=$bar['hargasatuan'];
					// }

					// $str = "select * from ".$dbname.".bgt_blok where tahunbudget='".$tahun."' and kodeblok like '".$param['kodeorg']."%' and closed='1'";
					// $res = fetchData($str);
					// foreach($res as $bar){
					// 	$listtt[$bar['thntnm']]=$bar['thntnm'];
					// 	$listdiv[substr($bar['kodeblok'],0,6)]=substr($bar['kodeblok'],0,6);
					// }

					// $str = "select * from ".$dbname.".bgt_kode";
					// $res = fetchData($str);
					// foreach($res as $bar){
					// 	$namakdbgt[$bar['kodebudget']]=$bar['nama'];
					// 	$akunkdbgt[$bar['kodebudget']]=$bar['noakun'];
					// }
					// $namakdbgt['MATERIAL']='MATERIAL';

					// }else{

					if (count($bloklist[$blk]['angkut']) > 7) {
						$validasiht .= "Blok " . getIndukBlok($blk) . " terdapat lebih dari 1 blok di list Excel.<br>";
						$err++;
					}
					$validasi  						= "";

					$uploadblok   			= $blk;
					$uploadjenis     		= $jenis[$blk];
					$uploadtujuan         	= $tujuan[$blk];
					$uploadpkstujuan    	= $pkstujuanexcl[$blk];
					$uploadharga    		= $harganya[$blk]['angkut']['tphpks1'];


					if ($uploadblok == '') {
						$validasi .= "Blok Kosong.<br>";
						$err++;
					}
					if (strlen($uploadblok) != 9) {
						$validasi .= "Panjang Blok tidak sesuai dengan template yang di download.<br>";
						$err++;
					}
					if ($uploadjenis == '') {
						$validasi .= "Jenis Kosong.<br>";
						$err++;
					}
					if ($uploadtujuan == '') {
						$validasi .= "Tujuan Kosong.<br>";
						$err++;
					}
					if ($uploadpkstujuan == '') {
						$validasi .= "PKS Tujuan Kosong.<br>";
						$err++;
					}
					if (strlen($uploadblok) != 9) {
						$validasi .= "Panjang Kode Kegiatan tidak sesuai dengan template yang di download.<br>";
						$err++;
					}
					if ($kodekeg == '') {
						$validasi .= "Kode Kegiatan Kosong.<br>";
						$err++;
					}

					$sql = "select indukblok from " . $dbname . ".setup_blok where indukblok='" . $uploadblok . "'";
					$res = fetchData($sql);
					$kodeblokval = $res[0]['indukblok'];

					if ($uploadblok != $kodeblokval) {
						$validasi .= "Kode Blok " . $uploadblok . " tersebut tidak terdaftar di Master Blok";
					}

					$method = "simpan";

					$color = "";
					if ($validasiht != '' or $validasi != '') {
						$color = "style=color:red";
					}
					$tatan = array();
					$str = "select * from " . $dbname . ".setup_blok where kodeorg like '" . $blk . "%' and tahuntanam != '0'";
					$res = fetchdata($str);
					foreach ($res as $v) {
						$tatan[$v['tahuntanam']] = $v['tahuntanam'];
						$luas[$v['indukblok']] += $v['luasareaproduktif'];
					}
					$no++;
					$tab .= "<tr class=rowcontent id=tr_" . $no . ">";
					$tab .= "<td valign=top align=center>" . $no . "</td>";
					$tab .= "<td valign=top align=center hidden id=blok_" . $no . ">" . $blk . "</td>";
					$tab .= "<td valign=top align=center >" . getIndukBlok($blk) . "</td>";
					$tab .= "<td valign=top align=center>";
					foreach ($tatan as $val) {
						$tab .= $val . "<br>";
					}
					$tab .= "</td>";
					$tab .= "<td valign=top align=right>" . number_format($luas[$blk], 2) . "</td>";
					$tab .= "<td valign=top align=center><select disabled id=pkstujuan" . $no . " style=\"width:100px;\">" . @$optpks . "</select></td>";
					$tab .= "<td valign=top align=center><select id=jenisvhc" . $no . " disabled style=\"width:80px;\">" . @$optjns . "</select></td>";

					$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('muat_tphpks1_" . $no . "',2)\" type=text id=muat_tphpks1_" . $no . " class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$harganya[$blk]['muat']['tphpks1']) . "></td>";
					$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('muat_tphpks2_" . $no . "',2)\" type=text id=muat_tphpks2_" . $no . " class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$harganya[$blk]['muat']['tphpks2']) . "></td>";
					$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('muat_tphpks3_" . $no . "',2)\" type=text id=muat_tphpks3_" . $no . " class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$harganya[$blk]['muat']['tphpks3']) . "></td>";
					$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('muat_rampks_" . $no . "',2)\" type=text id=muat_rampks_" . $no . " class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$harganya[$blk]['muat']['tphpks4']) . "></td>";
					$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('muat_tphpks5_" . $no . "',2)\" type=text id=muat_tphpks5_" . $no . " class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$harganya[$blk]['muat']['tphpks5']) . "></td>";
					$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('muat_tphpks6_" . $no . "',2)\" type=text id=muat_tphpks6_" . $no . " class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$harganya[$blk]['muat']['tphpks6']) . "></td>";
					$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('muat_tphpks7_" . $no . "',2)\" type=text id=muat_tphpks7_" . $no . " class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$harganya[$blk]['muat']['tphpks7']) . "></td>";
					$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('angkut_tphpks1_" . $no . "',2)\" type=text id=angkut_tphpks1_" . $no . " class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$harganya[$blk]['angkut']['tphpks1']) . "></td>";
					$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('angkut_tphpks2_" . $no . "',2)\" type=text id=angkut_tphpks2_" . $no . " class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$harganya[$blk]['angkut']['tphpks2']) . "></td>";
					$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('angkut_tphpks3_" . $no . "',2)\" type=text id=angkut_tphpks3_" . $no . " class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$harganya[$blk]['angkut']['tphpks3']) . "></td>";
					$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('angkut_rampks_" . $no . "',2)\" type=text id=angkut_rampks_" . $no . " class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$harganya[$blk]['angkut']['tphpks4']) . "></td>";
					$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('angkut_tphpks5_" . $no . "',2)\" type=text id=angkut_tphpks5_" . $no . " class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$harganya[$blk]['angkut']['tphpks5']) . "></td>";
					$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('angkut_tphpks6_" . $no . "',2)\" type=text id=angkut_tphpks6_" . $no . " class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$harganya[$blk]['angkut']['tphpks6']) . "></td>";
					$tab .= "<td valign=top><input style=width:145px onkeyup=\"z.numberFormat('angkut_tphpks7_" . $no . "',2)\" type=text id=angkut_tphpks7_" . $no . " class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/ value=" . (@$harganya[$blk]['angkut']['tphpks7']) . "></td>";
					if ($color != '') {
						$tab .= "<td " . $color . " align=left id=validasi_" . $no . ">" . trim(nl2br($validasiht)) . trim(nl2br($validasi)) . $selisih . $varvhc . $varupah . "</td>";
					} else {
						$tab .= "<td valign=top><button class=mybutton onclick=savedetail(" . $no . ")>" . $_SESSION['lang']['save'] . "</button></td>";
					}
					// $tab.="<td ".$color." align=center id=selisihjjg_".$no.">".($uploadtotaljjg-$ttljjg)."</td>";
					// $tab.="<td ".$color." align=center id=selisihkg_".$no.">".($uploadtotalkg-$ttlkg)."</td>";
					// $tab.="<td ".$color." align=center id=selisihkgbruto_".$no.">".($uploadtotalkgbruto-$ttlkgbruto)."</td>";

					$tab .= "</tr>";


					$cekduplicate[$uploadblok][$uploadjenis][$uploadtujuan] += 1;
					$barisduplicate[$uploadblok][$uploadjenis][$uploadtujuan] = $no;
					// }
				}

				$duplicate = "<br>";
				foreach ($cekduplicate as $t => $v1) {
					foreach ($v1 as $d => $v2) {
						foreach ($v2 as $k => $v3) {
							// foreach($v3 as $g => $v4){
							// foreach($v4 as $b => $v5){
							// 	foreach($v5 as $v => $nilai){
							if ($v3 > 1) {
								//$duplicate.=$barisduplicate[$t][$d][$k][$g][$b][$v].", ";
								$duplicate .= $t . "," . $d . "," . $k . "," . $g . ";<br>";
							}
							// 	}
							// }
							// }
						}
					}
				}

				if (trim($duplicate) != '') {
					// $tab.="<tr class=rowcontent>";
					// $tab.="<td colspan=49 style=background-color:#fcdede;color:blue;>Ada data yang double : <b>".$duplicate."</b> (jika ada data duplicate maka data pada baris sebelumnya akan di replace dengan data baris terakhir)</td>";
					// $tab.="</tr>";
				}

				$tab .= "</tbody>";
				$tab .= "<tfoot>";

				if ($err > 0 or $color != '') {
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td colspan=100 align=center style=color:black;font-size:20px;><b>Tombol simpan akan muncul jika tidak ditemukan baris yg berwarna merah.</b></td>";
					$tab .= "</tr>";
				} else {
					$tab .= "<tr class=rowcontent>";
					// $tab.="<td colspan=100 align=center><button id=btnsubmit class=mybutton onclick=\"simpanupload(".$no.")\">SaveAll</button></td>";
					$tab .= "<tr class=rowcontent><input hidden id=method value=insert>";
					$tab .= "<td colspan=21 align=right><button class=mybutton onclick=saveAll(" . $no . ")>" . $_SESSION['lang']['saveall'] . "</button></td>";
					$tab .= "</tr>";
				}
				$tab .= "</tfoot>";
				$tab .= "</table>";
			} else {
				exit("Warning : Format file upload harus .xlsx");
			}
		}

		echo $tab;
		break;
}
