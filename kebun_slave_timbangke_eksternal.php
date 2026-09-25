<?php
//session_start();
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
require_once('dompdfv2/autoload.inc.php');
require_once('lib/HtmlExcel.php');
require_once('kebun_timbangke_eksternal_filter.php');

use Dompdf\Dompdf;

$proses = checkPostGet('proses', '');
$id = (int)checkPostGet('id', '');
$namafile = checkPostGet('namafile', '');
$nokontrak = checkPostGet('nokontrak', '');
$notiket = checkPostGet('notiket', '');
$numrow = checkPostGet('numrow', '');
$tanggalpks = tanggalsystem(checkPostGet('tanggalpks', ''));

$kgin = checkPostGet('kgin', '');
$kgout = checkPostGet('kgout', '');
$buahdikembalikan = checkPostGet('buahdikembalikan', '');
$spbpabrik = checkPostGet('spbpabrik', '');
$tahuntanam2 = checkPostGet('tahuntanam2', '');
$kgnet = checkPostGet('kgnet', '');
$potongx = checkPostGet('potongx', '');
$potongx = checkPostGet('potongx', '');
$tipe = checkPostGet('tipe', '');

$kgin = str_replace(',', '', $kgin);
$kgout = str_replace(',', '', $kgout);
$buahdikembalikan = str_replace(',', '', $buahdikembalikan);
$kgnet = str_replace(',', '', $kgnet);

if (count($_POST) > 0) {
	$param = $_POST;
} else {
	$param = $_GET;
}
$path	= "fileupload/kebun_spb/";

$strap = "select nilai from " . $dbname . ".setup_parameterappl where kodeaplikasi='ES' and kodeparameter='ESEXT'";
@$resap = fetchData($strap);
//print_r ($proses);

#escape seluruh isi parameter (string maupun array) sebelum dipakai di SQL
function tkeEscape($p)
{
	if (is_array($p)) {
		return array_map('tkeEscape', $p);
	}
	return addslashes($p);
}
#kunci transaksi hanya huruf/angka (maks 12); selain itu ditolak supaya tidak bisa melebar ke baris lain
function tkeNoTrans($v)
{
	if (!is_string($v) || !preg_match('/^[A-Za-z0-9]{1,12}$/', $v)) {
		exit('Error : No. transaksi tidak valid.');
	}
	return $v;
}
#unit SPB harus termasuk unit yang bisa diakses user (lokasi tugas + detail akses)
function tkeUnitBoleh($kodeorg)
{
	return strpos(getOrgDetail(2), "'" . $kodeorg . "'") !== false;
}
#cek periode akuntansi (setup_periodeakuntansi.tutupbuku=1) untuk unit dan periode YYYY-MM
function tkeTutupBuku($kodeorg, $periode)
{
	global $dbname;
	$r = fetchData("select tutupbuku from " . $dbname . ".setup_periodeakuntansi where periode='" . addslashes($periode) . "' and kodeorg='" . addslashes($kodeorg) . "'");
	return (count($r) > 0 && $r[0]['tutupbuku'] == '1');
}
#tiket yang sudah ada berada di periode yang sudah ditutup
function tkeTiketTutup($notransaksi)
{
	global $dbname;
	$r = fetchData("select kodeorg,tanggal from " . $dbname . ".pabrik_timbangan where notransaksi='" . addslashes($notransaksi) . "' and millcode='EXTM'");
	return (count($r) > 0 && tkeTutupBuku($r[0]['kodeorg'], substr($r[0]['tanggal'], 0, 7)));
}
#SPB yang sudah punya tiket External di periode yang sudah ditutup (untuk lampiran)
function tkeSpbTutup($nospb)
{
	global $dbname;
	if ($nospb == '') {
		return false;
	}
	$r = fetchData("select kodeorg,tanggal from " . $dbname . ".pabrik_timbangan where nospb='" . addslashes($nospb) . "' and millcode='EXTM'");
	foreach ($r as $t) {
		if (tkeTutupBuku($t['kodeorg'], substr($t['tanggal'], 0, 7))) {
			return true;
		}
	}
	return false;
}

switch ($proses) {
	case 'submitfile':
		$param = tkeEscape($param);
		if (tkeSpbTutup(@$_POST['nospb'])) {
			exit('Error : Periode Akuntansi Sudah di Tutup.');
		}
		@$tgl = date("YmdHis");
		@$his = date("His");
		$data = $_POST;
		if ($data['fileupload'] != '') {
			if ($_FILES['file']['error'] == 0) {
				$filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
				$nmfile = substr($_FILES['file']['name'], 0, strripos($_FILES['file']['name'], '.'));
				$nama = preg_replace("/[^a-zA-Z0-9]/", "", $nmfile);
				if (!in_array($filetype, array('.jpg', '.jpeg', '.png', '.gif', '.bmp', '.pdf', '.doc', '.docx', '.xls', '.xlsx', '.csv', '.txt', '.zip', '.rar'))) {
					exit("Warning : Tipe file tidak diizinkan !!!");
				}
				$filename = preg_replace("/[^a-zA-Z0-9]/", "", $param['nospb']) . "_" . $nama . $filetype;
				$str = " select * from " . $dbname . ".listfile_kebun_spb where  nospb='" . $param['nospb'] . "' and namafile='" . $filename . "'";
				$bar = fetchdata($str);
				if (count($bar) > 0) {
					exit("Warning : File sudah pernah di upload !!!");
				}
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
				if ($_FILES['file']['size'] <= 25000000) {
					$str = "insert into " . $dbname . ".listfile_kebun_spb values ('','" . $param['nospb'] . "','" . $filename . "','" . $filetype . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')"; //exit('error'.$str);
					try {
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path . $filename, $file_tmpname);
					} catch (PDOException $e) {
						echo " Gagal," . addslashes($e->getMessage());
					}
				} else {
					exit("warning : Ukuran file upload maksimal 25MB");
				}
			}
		}
		break;
	case 'loadfiles':
		$param = tkeEscape($param);
		$no = 0;
		$tab = $icon = "";
		$str = "select * from " . $dbname . ".listfile_kebun_spb where nospb='" . $param['nospb'] . "'";
		@$res = fetchData($str);
		foreach (@$res as $key => $val) {
			$no++;
			$tab .= "<tr class=rowcontent>
				<td style='text-align:center'>" . $no . "</td>";
			$icon = seticonfile($val['formaticon']);
			$namatampil = (strpos($val['namafile'], '_') !== false) ? substr($val['namafile'], strpos($val['namafile'], '_') + 1) : $val['namafile'];
			$tab .= "<td align=left>
				<a href='" . $path . $val['namafile'] . "' download>" . (strlen($namatampil) > 35 ? substr($namatampil, 0, 35) . "..." : $namatampil) . "</a></td>";
			$tab .= "<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('" . $val['id'] . "','" . $val['namafile'] . "');\" >";
			$tab .= "	</td>
			</tr>";
		}
		echo $tab;
		break;
	case 'deletefile':
		$rfile = fetchdata("select namafile,nospb from " . $dbname . ".listfile_kebun_spb where id='" . $id . "'");
		if (count($rfile) > 0 && tkeSpbTutup($rfile[0]['nospb'])) {
			exit('Error : Periode Akuntansi Sudah di Tutup.');
		}
		$namafile = (count($rfile) > 0) ? basename($rfile[0]['namafile']) : '';
		$str = "delete from " . $dbname . ".listfile_kebun_spb where id='" . $id . "'"; //exit('error'.$str);
		try {
			$owlPDO->exec($str);
			$pathx = $path . $namafile;
			if (is_file($pathx)) { unlink($pathx); }
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;
	case 'deletefileall':
		$param = tkeEscape($param);
		if (tkeSpbTutup(@$param['nospb'])) {
			exit('Error : Periode Akuntansi Sudah di Tutup.');
		}
		# delete file
		$sql = "select * from " . $dbname . ".listfile_kebun_spb where nospb='" . $param['nospb'] . "'"; //exit('error'.$sql);
		$res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$str = "delete from " . $dbname . ".listfile_kebun_spb where id='" . $bar['id'] . "' and namafile='" . $bar['namafile'] . "'";
			try {
				$owlPDO->exec($str);
				$pathx = $path . $bar['namafile'];
				if (is_file($pathx)) { unlink($pathx); }
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		break;
	case 'getjjg':
		$str = " select sum(jjg) as jjg,sum(brondolan) as brondolan from " . $dbname . ".kebun_spb_vw where  nospb='" . $param['nospb'] . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();

		// supir dan kendaraan
		$str1 = " select * from " . $dbname . ".pabrik_timbangan where nospb='" . $param['nospb'] . "' or notransaksi='" . $param['tktkebun'] . "'";
		$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_ASSOC);
		$bar1 = $res1->fetch();

		// ambil fraksi atau potongan (PT dari unit SPB; tiket kebun tidak selalu ada)
		$rOrgSpb = fetchdata("select kodeorg from " . $dbname . ".kebun_spbht where nospb='" . $param['nospb'] . "'");
		$orgSpb = (count($rOrgSpb) > 0) ? $rOrgSpb[0]['kodeorg'] : $bar1['kodeorg'];
		$str2 = "select * from " . $dbname . ".pabrik_5fraksi2 where pt = '" . getindukPT($orgSpb) . "'";
		$res2 = fetchData($str2);
		$trpotongan = "";
		$no_dt = 0;
		foreach ($res2 as $bar2) {
			$no_dt++;
			$trpotongan .= "
				<tr>
				<td>" . $bar2['keterangan'] . " (" . $bar2['type'] . ")</td>
				<td>:</td>
				<td>
				<input type='text' onblur='getPotongan()' onkeyup='z.numberFormat(this.id,2);getPotongan()' class='myinputtextnumber' onkeypress='return angka_doang(event)' id='dt_potongan_" . $no_dt . "' name='dt_potongan_" . $no_dt . "' style='width:150px;' />
				<input type='hidden' value='" . $bar2['kode'] . "'  id='kode_potongan_" . $no_dt . "' />
				</td>
				</tr>
			";
		}

		echo $bar['jjg'] . "####" . $bar['brondolan'] . "####" . $bar1['supir'] . "####" . $bar1['nokendaraan'] . "####" . $trpotongan . "####" . $bar1['pabriktujuan'];
		break;
	case 'getNosbp':
		$tglpr = explode("-", $param['tgl']);
		$optDtSpb = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		if (@$param['nospb'] != '') {
			$optDtSpb .= "<option value='" . $param['nospb'] . "' selected>" . $param['nospb'] . "</option>";
		}

		$sDtSpb = "select nospb,kodeorg from " . $dbname . ".kebun_spbht where left(tanggal,7)='" . $tglpr[2] . "-" . $tglpr[1] . "' and tanggal = '" . tanggalsystemn($param['tgl']) . "' and kodeorg IN (" . getOrgDetail(2) . ") and tujuan=3 and posting='0'"; //exit('error'.$sDtSpb);
		$qDtSpb = $owlPDO->query($sDtSpb) or die(print " Gagal: " . PDOException::getMessage());
		$qDtSpb->setFetchMode(PDO::FETCH_ASSOC);
		while ($rDtSpb = $qDtSpb->fetch()) {
			$sCek = "select * from " . $dbname . ".pabrik_timbangan where nospb='" . $rDtSpb['nospb'] . "' and millcode='EXTM'";
			$qCek = $owlPDO->query($sCek) or die(print " Gagal: " . PDOException::getMessage());
			$rCek = owlBaris($qCek);
			if ($rCek == 0) {
				$optDtSpb .= "<option value='" . $rDtSpb['nospb'] . "'>" . $rDtSpb['nospb'] . "</option>";
			}
			$org_induk = $rDtSpb['kodeorg'];
		}

		// ambil fraksi atau potongan
		$str2 = "select * from " . $dbname . ".pabrik_5fraksi2 where pt = '" . getindukPT($org_induk) . "'";
		$res2 = fetchData($str2);
		$trpotongan = "";
		$no_dt = 0;
		foreach ($res2 as $bar2) {

			$str2 = "select * from " . $dbname . ".pabrik_sortasi WHERE notiket in (select notransaksi from " . $dbname . ".pabrik_timbangan where nospb = '" . $param['nospb'] . "' and millcode='EXTM' ) and kodefraksi = '" . $bar2['kode'] . "' ";
			$res2 = fetchdata($str2);
			$nilai = $res2[0]['kg'];

			if ($nilai != 0 || $nilai != '') {
				$nilai = $nilai;
			} else {
				$nilai = 0;
			}

			$no_dt++;
			$trpotongan .= "
						<tr>
						<td>" . $bar2['keterangan'] . " (" . $bar2['type'] . ")</td>
						<td>:</td>
						<td>
						<input type='text' onblur='getPotongan()' onkeyup='z.numberFormat(this.id,2);getPotongan()' value='" . number_format((float)$nilai, 0) . "' class='myinputtextnumber' onkeypress='return angka_doang(event)' id='dt_edit_potongan_" . $no_dt . "' name='dt_edit_potongan_" . $no_dt . "' style='width:150px;' />
						<input type='hidden' value='" . $bar2['kode'] . "'  id='kode_potongan_" . $no_dt . "' />
						</td>
						</tr>
					";
		}


		echo $optDtSpb . "####" . $trpotongan;
		break;
	case 'getnodo':
		$optDtSpb = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

		$sDtSpb = "select nodo from " . $dbname . ".pmn_suratperintahpengiriman	 where nokontrak='" . $nokontrak . "'"; //exit('error'.$sDtSpb);
		$qDtSpb = $owlPDO->query($sDtSpb) or die(print " Gagal: " . PDOException::getMessage());
		$qDtSpb->setFetchMode(PDO::FETCH_ASSOC);
		while ($rDtSpb = $qDtSpb->fetch()) {
			$optDtSpb .= "<option value='" . $rDtSpb['nodo'] . "'>" . $rDtSpb['nodo'] . "</option>";
		}
		echo $optDtSpb;
		break;

	case 'insert':
		$param = tkeEscape($param);

		$owlPDO->beginTransaction();
		$kodeorgspb = $_SESSION['empl']['lokasitugas'];
		$str = "select * from " . $dbname . ".kebun_spbht where nospb = '" . $param['spbId'] . "'";
		$res = fetchData($str);
		foreach ($res as $bar) {
			$optpks = $bar['penerimatbs'];
			$kodeorg = $bar['kodeorg'];
			$kodeorgspb = $bar['kodeorg'];
			$notransMobile = $bar['noreferensi'];
		}

		$whr = "nospb='" . $param['spbId'] . "'";
		$optCust = makeOption($dbname, 'kebun_spbht', 'nospb,penerimatbs', $whr);
		$scek = "select max(notransaksi) as notransaksi from " . $dbname . ".pabrik_timbangan where char_length(notransaksi)>7 and notransaksi like'%" . $kodeorg . "%'";
		$qcek = $owlPDO->query($scek) or die(print " Gagal: " . PDOException::getMessage());
		$qcek->setFetchMode(PDO::FETCH_ASSOC);
		$rcek = $qcek->fetch();
		if ($rcek['notransaksi'] == '') {
			$rcek['notransaksi'] = 0;
		} else {
			$rcek['notransaksi'] = substr($rcek['notransaksi'], -6, 6);
		}
		$notrans = $kodeorg . addZero((intval($rcek['notransaksi']) + 1), 6);
		// $notrans=
		if (($param['tgl'] == '') || ($param['kdKend'] == '') || ($param['nmSupir'] == '') || ($param['jmlhJjg'] == '') || ($param['brtMsk'] == '') || ($param['brtKlr'] == '')) {
			exit("warning: Seluruh field tidak boleh kosong");
		}
		if ($param['spbId'] == '') {
			exit("warning: No. SPB harus dipilih");
		}
		foreach (array('jmlhJjg', 'brtMsk', 'brtKlr', 'potKg', 'buahdikembalikan') as $kNum) {
			if (isset($param[$kNum])) {
				$param[$kNum] = str_replace(',', '', $param[$kNum]);
			}
		}
		$param['brtBrsh'] = (float)$param['brtMsk'] - (float)$param['brtKlr'] - (float)@$param['potKg'];
		if ($param['brtBrsh'] < 0) {
			exit("warning: Berat Bersih tidak boleh kurang dari 0");
		}

		// if($optCust[$param['spbId']]==''){
		// $optCust[$param['spbId']]='EBL';
		// }

		$arrorg = explode("/", $param['spbId']);
		$kodeorg = substr($arrorg[1], 0, 4);
		$kodediv = $arrorg[1];

		$str3 = "select * from " . $dbname . ".pabrik_timbangan WHERE nospb = '" . $param['spbId'] . "' and millcode != 'EXTM' ";
		$res3 = fetchdata($str3);

		$kode_cus = $res3[0]['kodecustomer'];
		$pabrik_tujuan = (@$param['pabriktujuan'] != '') ? $param['pabriktujuan'] : @$res3[0]['pabriktujuan'];
		if ($pabrik_tujuan == '') {
			exit("warning: Tujuan Pabrik harus dipilih");
		}
		if (!tkeUnitBoleh($kodeorgspb)) {
			exit("warning: SPB bukan milik unit yang bisa Anda akses");
		}
		if (tkeTutupBuku($kodeorgspb, substr(tanggalsystemn($param['tgl']), 0, 7))) {
			exit('Error : Periode Akuntansi Sudah di Tutup.');
		}


		$sins = "insert into " . $dbname . ".pabrik_timbangan (notransaksi, tanggal, kodeorg, kodecustomer, jumlahtandan1, kodebarang,jammasuk, beratmasuk, jamkeluar, beratkeluar, nokendaraan, supir, nospb, timbangonoff, intex, millcode, beratbersih, jjgsortasi,kgpotsortasi, username, norefrensi, nokontrak, nodo, beratmasukpmks, beratkeluarpmks, beratbersihpmks, divcode,tanggalpks,kgpembeli,buahdikembalikan,spbpabrik,tahuntanam,pabriktujuan,nospbmobile) values ";
		// $sins.="('".$notrans."','".tanggalsystemn($param['tgl'])."','".$_SESSION['empl']['lokasitugas']."','".$optCust[$param['spbId']]."','".$param['jmlhJjg']."','40000003','".$param['jamMasuk']."','".$param['brtMsk']."','".$param['jamKeluar']."','".$param['brtKlr']."','".$param['kdKend']."','".$param['nmSupir']."','".$param['spbId']."','1','0','EXTM','".$param['brtBrsh']."','".$param['JjgSortasi']."','".$param['potKg']."','".$_SESSION['standard']['username']."','".$param['notiket']."','".$param['nokontrak']."','".$param['nodo']."','".$param['brtMskpmks']."','".$param['brtKlrpmks']."','".$param['brtBrshpmks']."','".$kodediv."','".tanggalsystem($param['tanggalpks'])."','".$param['kgJual']."','".$param['buahdikembalikan']."','".$param['spbpabrik']."','".$param['tahuntanam2']."')";
		$sins .= "('" . $notrans . "','" . tanggalsystemn($param['tgl']) . "','" . $kodeorgspb . "','" . $kode_cus . "','" . $param['jmlhJjg'] . "','40000003','" . $param['jamMasuk'] . "','" . $param['brtMsk'] . "','" . $param['jamKeluar'] . "','" . $param['brtKlr'] . "','" . $param['kdKend'] . "','" . $param['nmSupir'] . "','" . $param['spbId'] . "','1','0','EXTM','" . $param['brtBrsh'] . "','" . $param['JjgSortasi'] . "','" . $param['potKg'] . "','" . $_SESSION['standard']['username'] . "','" . $param['notiket'] . "','" . $param['nokontrak'] . "','" . $param['nodo'] . "','" . $param['brtMskpmks'] . "','" . $param['brtKlrpmks'] . "','" . $param['brtBrshpmks'] . "','" . $kodediv . "','" . tanggalsystem($param['tanggalpks']) . "','" . $param['kgJual'] . "','" . $param['buahdikembalikan'] . "','" . $param['spbpabrik'] . "','" . $param['tahuntanam2'] . "','" . $pabrik_tujuan . "','" . $notransMobile . "')";
		// exit("error: ".$sins);
		try {
			$owlPDO->exec($sins);

			// insert sortasi
			$kodePotonganAll = checkPostGet('kode_potongan', '');
			if (is_array($kodePotonganAll)) {
				$kodePotonganAll = array_map('addslashes', $kodePotonganAll);
			}
			$nilaiPotonganAll = checkPostGet('nilai_potongan', '');
			if (is_array($nilaiPotonganAll)) {
				$nilaiPotonganAll = array_map('floatval', str_replace(',', '', $nilaiPotonganAll));
			}
			$t_dataa = is_array($kodePotonganAll) ? count($kodePotonganAll) - 1 : -1;

			if ($t_dataa >= 0) {
				for ($i = 0; $i <= $t_dataa; $i++) {

					$sDel = "delete from " . $dbname . ".pabrik_sortasi where notiket='" . $notrans . "' and kodefraksi = '" . $kodePotonganAll[$i] . "' ";
					$owlPDO->exec($sDel);

					$str_pot = "insert into " . $dbname . ".pabrik_sortasi values ('" . $notrans . "','" . $kodePotonganAll[$i] . "','','','" . $nilaiPotonganAll[$i] . "')";
					try {
						$owlPDO->exec($str_pot);
					} catch (PDOException $e) {
						echo "DB Error : " . $e->getMessage();
						die();
					}
				}
			}

			$owlPDO->commit();
		} catch (PDOException $e) {
			exit("error: " . $e->getMessage() . "__" . $sins);
		}
		break;
	case 'update':
		$param = tkeEscape($param);
		$param['notransaksi'] = tkeNoTrans(@$param['notransaksi']);

		$owlPDO->beginTransaction();
		if (($param['tgl'] == '') || ($param['kdKend'] == '') || ($param['nmSupir'] == '') || ($param['jmlhJjg'] == '') || ($param['brtMsk'] == '') || ($param['brtKlr'] == '')) {
			exit("error: Seluruh field tidak boleh kosong");
		}
		if ($param['spbId'] == '') {
			exit("error: No. SPB harus dipilih");
		}
		$rSpb = fetchData("select kodeorg from " . $dbname . ".kebun_spbht where nospb='" . $param['spbId'] . "'");
		$kodeorgspb = (count($rSpb) > 0) ? $rSpb[0]['kodeorg'] : $_SESSION['empl']['lokasitugas'];
		$setTujuan = (@$param['pabriktujuan'] != '') ? ",pabriktujuan='" . $param['pabriktujuan'] . "'" : '';
		if (@$param['pabriktujuan'] == '') {
			exit("error: Tujuan Pabrik harus dipilih");
		}
		if (!tkeUnitBoleh($kodeorgspb)) {
			exit("error: SPB bukan milik unit yang bisa Anda akses");
		}
		if (tkeTiketTutup($param['notransaksi']) || tkeTutupBuku($kodeorgspb, substr(tanggalsystemn($param['tgl']), 0, 7))) {
			exit('Error : Periode Akuntansi Sudah di Tutup.');
		}
		foreach (array('jmlhJjg', 'brtMsk', 'brtKlr', 'potKg', 'buahdikembalikan') as $kNum) {
			if (isset($param[$kNum])) {
				$param[$kNum] = str_replace(',', '', $param[$kNum]);
			}
		}
		$param['brtBrsh'] = (float)$param['brtMsk'] - (float)$param['brtKlr'] - (float)@$param['potKg'];
		if ($param['brtBrsh'] < 0) {
			exit("warning: Berat Bersih tidak boleh kurang dari 0");
		}

		// untuk numeric
		$numericFields = ['JjgSortasi', 'brtMskpmks', 'brtKlrpmks', 'brtBrshpmks', 'kgJual'];

		foreach ($numericFields as $field) {
			if ($param[$field] === '' || $param[$field] === null) {
				$param[$field] = 0;
			}
		}

		// untuk tanggal
		$dateFields = ['tanggalpks'];
		foreach ($dateFields as $field) {
			if ($param[$field] === '' || $param[$field] === null || $param[$field] === '0') {
				$param[$field] = '0000-00-00'; // biar ke-query jadi NULL, bukan ''
			}
		}

		$arrorg = explode("/", $param['spbId']);
		$kodediv = $arrorg[1];
		$whr = "nospb='" . $param['spbId'] . "'";
		$optCust = makeOption($dbname, 'kebun_spbht', 'nospb,penerimatbs', $whr);
		$sins = "update " . $dbname . ".pabrik_timbangan set tanggal='" . tanggalsystem($param['tgl']) . "',
					kodeorg='" . $kodeorgspb . "',
					jumlahtandan1='" . $param['jmlhJjg'] . "',jammasuk='" . $param['jamMasuk'] . "',
					beratmasuk='" . $param['brtMsk'] . "',jamkeluar='" . $param['jamKeluar'] . "',
					beratkeluar='" . $param['brtKlr'] . "',nokendaraan='" . $param['kdKend'] . "',
					supir='" . $param['nmSupir'] . "',nospb='" . $param['spbId'] . "',
					beratbersih='" . $param['brtBrsh'] . "',jjgsortasi='" . $param['JjgSortasi'] . "',
					kgpotsortasi='" . $param['potKg'] . "',username='" . $_SESSION['standard']['username'] . "',
					norefrensi='" . $param['notiket'] . "',nokontrak='" . $param['nokontrak'] . "',nodo='" . $param['nodo'] . "',
					beratmasukpmks='" . $param['brtMskpmks'] . "',beratkeluarpmks='" . $param['brtKlrpmks'] . "',
					beratbersihpmks='" . $param['brtBrshpmks'] . "',divcode='" . $kodediv . "', tanggalpks='" . tanggalsystem($param['tanggalpks']) . "',kgpembeli='" . $param['kgJual'] . "',
					tahuntanam='" . $param['tahuntanam2'] . "',spbpabrik='" . $param['spbpabrik'] . "',buahdikembalikan='" . $param['buahdikembalikan'] . "'" . $setTujuan . "
					
					where notransaksi='" . $param['notransaksi'] . "' and millcode='EXTM' and kodeorg IN (" . getOrgDetail(2) . ") limit 1";
		// exit("Error:$sins");
		try {
			$owlPDO->exec($sins);


			// insert sortasi
			$kodePotonganAll = checkPostGet('kode_potongan', '');
			if (is_array($kodePotonganAll)) {
				$kodePotonganAll = array_map('addslashes', $kodePotonganAll);
			}
			$nilaiPotonganAll = checkPostGet('nilai_potongan', '');
			if (is_array($nilaiPotonganAll)) {
				$nilaiPotonganAll = array_map('floatval', str_replace(',', '', $nilaiPotonganAll));
			}
			$t_dataa = is_array($kodePotonganAll) ? count($kodePotonganAll) - 1 : -1;

			if ($t_dataa >= 0) {
				for ($i = 0; $i <= $t_dataa; $i++) {

					$sDel = "delete from " . $dbname . ".pabrik_sortasi where notiket='" . $param['notransaksi'] . "' and kodefraksi = '" . $kodePotonganAll[$i] . "' ";
					$owlPDO->exec($sDel);

					$str_pot = "insert into " . $dbname . ".pabrik_sortasi values ('" . $param['notransaksi'] . "','" . $kodePotonganAll[$i] . "','','','" . $nilaiPotonganAll[$i] . "')";
					try {
						$owlPDO->exec($str_pot);
					} catch (PDOException $e) {
						echo "DB Error : " . $e->getMessage();
						die();
					}
				}
			}

			$owlPDO->commit();
		} catch (PDOException $e) {
			exit("error: " . $e->getMessage() . "__" . $sins);
		}
		break;
	case 'loadNewData':

		// ambil fraksi atau potongan
		$str2 = "select kode,max(keterangan) as keterangan,max(type) as type from " . $dbname . ".pabrik_5fraksi2 where pt in (" . getOrgDetail(4) . ") group by kode order by kode";
		$res2 = fetchData($str2);
		$total_a = count($res2);

		$tab = "<table cellspacing='1' border='0' class='sortable' cellpadding='5' style='width:100%'>
			 <thead>
			 <tr class=rowheader>
			 <th rowspan='2' align=center>No.</th>
			 <th rowspan='2' align=center>" . $_SESSION['lang']['noTiket'] . "</th>
			 <th rowspan='2' align=center>" . $_SESSION['lang']['tanggal'] . "</th>
			 <th rowspan='2' align=center>" . $_SESSION['lang']['nospb'] . "</th>
			 <th rowspan='2' align=center>SPB Pabrik</th>
			 <th rowspan='2' align=center>Tujuan Pabrik</th>
			 <th rowspan='2' align=center>" . $_SESSION['lang']['nomor'] . " " . $_SESSION['lang']['ticket'] . "</th>
			 <th rowspan='2' hidden align=center>" . $_SESSION['lang']['kontrak'] . "</th>
			 <th rowspan='2' hidden align=center>" . $_SESSION['lang']['nodo'] . "</th>
			 <th rowspan='2' align=center>" . $_SESSION['lang']['nopol'] . "</th>
			 <th rowspan='2' align=center>" . $_SESSION['lang']['supir'] . "</th>
			 <th rowspan='2' align=center>" . $_SESSION['lang']['tahuntanam'] . "</th>
			 <th rowspan='2' align=center>" . $_SESSION['lang']['jjg'] . "</th>
			 <th rowspan='2' align=center width=50px>" . $_SESSION['lang']['beratMasuk'] . "</th>
			 <th rowspan='2' align=center width=50px>" . $_SESSION['lang']['beratKeluar'] . "</th>
			 <th rowspan='2' align=center width=50px>" . $_SESSION['lang']['beratkotor'] . "</th>";

		if ($total_a > 0) {
			$tab .= "<th align=center width=50px rowspan='1' colspan='" . $total_a . "'>Potongan</th>";
		}

		$tab .= "<th rowspan='2' align=center width=50px>Total " . $_SESSION['lang']['potongan'] . "</th>
			 <th rowspan='2' align=center width=50px>" . $_SESSION['lang']['beratBersih'] . "</th>
			 <th rowspan='2' align=center>User</th>
			 <th rowspan='2' hidden align=center width=50px>" . $_SESSION['lang']['jjgpenalty'] . "</th>
			 <th rowspan='2' align=center colspan=3>Action</th>
			 </tr>";

		$trpotongan = "";
		foreach ($res2 as $bar2) {
			$trpotongan .= "
					<th align=center width=50px>" . $bar2['keterangan'] . "</th>
				";
		}
		$tab .= $trpotongan;

		$tab .= "</thead><tbody>";

		$flt = tkeFilter($param);
		if ($flt['error'] != '') {
			exit($flt['error']);
		}
		$whrCr = $flt['where'];

		$limit = 20;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0) $page = 0;
		}
		$offset = $page * $limit;
		$maxdisplay = ($page * $limit);
		$ql2 = "select count(*) as jmlhrow from " . $dbname . ".pabrik_timbangan 
			  where kodeorg IN (" . getOrgDetail(2) . ") and millcode='EXTM' and char_length(notransaksi)>7  " . $whrCr . " order by left(`tanggal`,10) desc";

		if ($tipe == 'html') {
			$slvhc = "select * from " . $dbname . ".pabrik_timbangan 
					where kodeorg IN (" . getOrgDetail(2) . ") and millcode='EXTM' and char_length(notransaksi)>7 " . $whrCr . "
					order by left(`tanggal`,10) desc, notransaksi desc limit " . $offset . "," . $limit . "";
		} else {
			$slvhc = "select * from " . $dbname . ".pabrik_timbangan 
					where kodeorg IN (" . getOrgDetail(2) . ") and millcode='EXTM' and char_length(notransaksi)>7 " . $whrCr . "
					order by left(`tanggal`,10) desc, notransaksi desc";
		}

		$query2 = $owlPDO->query($ql2) or die(print " Gagal: " . PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
			$jlhbrs = $jsl->jmlhrow;
		}
		// $tab.= $slvhc;
		$qlvhc = $owlPDO->query($slvhc) or die(print " Gagal: " . PDOException::getMessage());
		$qlvhc->setFetchMode(PDO::FETCH_ASSOC);
		$user_online = $_SESSION['standard']['userid'];
		$no = 0;
		$no = $maxdisplay;
		$totFraksi = array();
		$tutupMap = array();
		foreach (fetchData("select kodeorg,periode from " . $dbname . ".setup_periodeakuntansi where tutupbuku='1' and kodeorg IN (" . getOrgDetail(2) . ")") as $rTutup) {
			$tutupMap[$rTutup['kodeorg'] . '|' . $rTutup['periode']] = 1;
		}
		$mapTujuan = array();
		foreach (fetchData("select distinct kodecustomer,namacustomer from " . $dbname . ".pmn_4customer") as $rCus) {
			$mapTujuan[$rCus['kodecustomer']] = $rCus['namacustomer'];
		}
		while ($rData = $qlvhc->fetch()) {
			$no += 1;
			$namaTujuan = isset($mapTujuan[$rData['pabriktujuan']]) ? $mapTujuan[$rData['pabriktujuan']] : $rData['pabriktujuan'];

			$tab .= "
			<tr class=rowcontent>
			<td align=center>" . $no . "</td>
			<td align='center'>" . $rData['notransaksi'] . "</td>
			<td align='center'>" . tanggalnormal(substr($rData['tanggal'], 0, 10)) . "</td>
			<td align='center'>" . $rData['nospb'] . "</td>
			<td align='center'>" . $rData['spbpabrik'] . "</td>
			<td align='center'>" . $namaTujuan . "</td>
			<td align='center'>" . $rData['norefrensi'] . "</td>
			<td hidden>" . $rData['nokontrak'] . "</td>
			<td hidden>" . $rData['nodo'] . "</td>
			<td align='center'>" . $rData['nokendaraan'] . "</td>
			<td align='center'>" . $rData['supir'] . "</td>
			<td align='center'>" . $rData['tahuntanam'] . "</td>
			<td align='right'>" . $rData['jumlahtandan1'] . "</td>
			<td align='right'>" . @number_format(@$rData['beratmasuk'], 0) . "</td>
			<td align='right'>" . @number_format(@$rData['beratkeluar'], 0) . "</td>
			<td align='right'>" . @number_format((@$rData['beratmasuk'] - @$rData['beratkeluar']), 0) . "</td>";

			$mapFraksi = array();
			foreach (fetchData("select kodefraksi,kg from " . $dbname . ".pabrik_sortasi where notiket = '" . $rData['notransaksi'] . "'") as $rSrt) {
				$mapFraksi[$rSrt['kodefraksi']] = $rSrt['kg'];
			}
			$trpotongan_nilai = "";
			foreach ($res2 as $bar2) {
				$nilai = isset($mapFraksi[$bar2['kode']]) ? $mapFraksi[$bar2['kode']] : 0;
				$trpotongan_nilai .= "<td align=center width=50px>" . number_format($nilai, 0) . "</td>";
				$totFraksi[$bar2['kode']] = (isset($totFraksi[$bar2['kode']]) ? $totFraksi[$bar2['kode']] : 0) + $nilai;
			}
			$tab .= $trpotongan_nilai;


			$tab .= "
			<td align='right'>" . number_format($rData['kgpotsortasi'], 0) . "</td>
			<td align='right'>" . @number_format(@$rData['beratbersih'], 0) . "</td>
			<td align='center'>" . $rData['username'] . "</td>
			<td hidden align='right'>" . $rData['jjgsortasi'] . "</td>
			";


			# Total
			$tjjg += $rData['jumlahtandan1'];
			$tbm += $rData['beratmasuk'];
			$tbk += $rData['beratkeluar'];
			$tbb += ($rData['beratmasuk'] - $rData['beratkeluar']);

			$tp += $rData['kgpotsortasi'];
			$tpbn += $rData['beratbersih'];

			$whr = "nospb='" . $rData['nospb'] . "'";
			$optStat = makeOption($dbname, 'kebun_spbht', 'nospb,posting', $whr);

			if ($tipe == 'html') {
				if (isset($tutupMap[$rData['kodeorg'] . '|' . substr($rData['tanggal'], 0, 7)])) {
					$tab .= "<td align=center width=25px><img src=images/application/application_edit_gray.png class=resicon  title='Periode akuntansi sudah ditutup'></td>";
					$tab .= "<td align=center width=25px></td>";
					$tab .= "<td align=center width=25px><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_timbangan','" . $rData['notransaksi'] . "','','kebun_timbangke_eksternalPdf',event)\"></td>";
				} elseif (($_SESSION['standard']['username'] == @$rData['username']) || ($optStat[$rData['nospb']] == '0')) {
					$tab .= "<td align=center width=25px><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $rData['tahuntanam'] . "','" . $rData['spbpabrik'] . "','" . $rData['notransaksi'] . "',
					'" . $rData['jammasuk'] . "','" . $rData['jamkeluar'] . "','" . $rData['nokendaraan'] . "','" . $rData['supir'] . "',
					'" . $rData['norefrensi'] . "','" . $rData['jumlahtandan1'] . "','" . $rData['beratmasuk'] . "','" . $rData['beratkeluar'] . "',
					'" . $rData['beratbersih'] . "','" . $rData['jjgsortasi'] . "','" . $rData['kgpotsortasi'] . "','" . $rData['nospb'] . "',
					'" . @tanggalnormal(substr($rData['tanggal'], 0, 10)) . "','" . $rData['nokontrak'] . "','" . $rData['nodo'] . "','" . $rData['pabriktujuan'] . "','" . $rData['buahdikembalikan'] . "');\"></td>";
					$tab .= "<td align=center width=25px><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deleteData('" . $rData['notransaksi'] . "','" . $rData['nospb'] . "');\"></td>";
					$tab .= "<td align=center width=25px><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_timbangan','" . $rData['notransaksi'] . "','','kebun_timbangke_eksternalPdf',event)\"></td>";
				} else {
					// $tab.="<td align=center width=25px></td>";
					$tab .= "<td align=center width=25px><img src=images/application/application_edit_gray.png class=resicon  title='Edit Tahun Tanam' onclick=\"fillFieldTahunTanam('" . $rData['tahuntanam'] . "','" . $rData['spbpabrik'] . "','" . $rData['notransaksi'] . "',
					'" . $rData['jammasuk'] . "','" . $rData['jamkeluar'] . "','" . $rData['nokendaraan'] . "','" . $rData['supir'] . "',
					'" . $rData['norefrensi'] . "','" . $rData['jumlahtandan1'] . "','" . $rData['beratmasuk'] . "','" . $rData['beratkeluar'] . "',
					'" . $rData['beratbersih'] . "','" . $rData['jjgsortasi'] . "','" . $rData['kgpotsortasi'] . "','" . $rData['nospb'] . "',
					'" . @tanggalnormal(substr($rData['tanggal'], 0, 10)) . "','" . $rData['nokontrak'] . "','" . $rData['nodo'] . "','" . $rData['pabriktujuan'] . "','" . $rData['buahdikembalikan'] . "');\"></td>";
					$tab .= "<td align=center width=25px></td>";
					$tab .= "<td align=center width=25px><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_timbangan','" . $rData['notransaksi'] . "','','kebun_timbangke_eksternalPdf',event)\"></td>";
				}
			} else {
				$tab .= "<td align=center width=25px></td>";
				$tab .= "<td align=center width=25px></td>";
				$tab .= "<td align=center width=25px></td>";
			}

			$tab .= "</tr>";
		}

		# Total 1 Page
		$tab .= "<tr class=rowcontent>";
		$tab .= "<td colspan=" . ($tipe == 'html' ? '10' : '12') . " align=center><b>TOTAL</b></td>";
		$tab .= "<td align=right style='font-weight:800;'>" . number_format($tjjg) . "</td>";
		$tab .= "<td align=right style='font-weight:800;'>" . number_format($tbm) . "</td>";
		$tab .= "<td align=right style='font-weight:800;'>" . number_format($tbk) . "</td>";
		$tab .= "<td align=right style='font-weight:800;'>" . number_format($tbb) . " </td>";
		foreach ($res2 as $bar2) {
			$tab .= "<td align=right style='font-weight:800;'>" . number_format(isset($totFraksi[$bar2['kode']]) ? $totFraksi[$bar2['kode']] : 0) . "</td>";
		}
		$tab .= "<td align=right style='font-weight:800;'>" . number_format($tp) . "</td>";
		$tab .= "<td align=right style='font-weight:800;'>" . number_format($tpbn) . "</td>";
		$tab .= "<td colspan=" . ($tipe == 'html' ? '4' : '5') . "></td>";
		$tab .= "</tr>";

		if ($tipe == 'html') {
			$tab .= "</tbody><tfoot>
			<tr><td colspan=25 align=center>
			" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " .  $jlhbrs . "<br />
			<button class=mybutton onclick=loadData(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
			<button class=mybutton onclick=loadData(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
			</td>
			</tr>";
			$tab .= "</tfoot></table>";
		} else {
			$tab .= "</tbody></table>";
		}



		if ($tipe == 'html') {
			echo $tab;
		} else {
			$nop = "Timbangan_Eksternal_" . date('Ymd_His') . ".xls";
			$ptkode = getindukPT($_SESSION['empl']['lokasitugas']);
			$hd = setheadreport($ptkode, $ptkode);
			$logourl = '';
			if (file_exists($hd['logo'])) {
				$skema = (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';
				$logourl = $skema . "://" . @$_SERVER['HTTP_HOST'] . rtrim(dirname(@$_SERVER['SCRIPT_NAME']), '/') . "/" . $hd['logo'];
			}
			$kop = "<table>
			<tr><td colspan=10 height='70' style='height:52pt'>" . ($logourl != '' ? "<img src='" . $logourl . "' height='60'>" : "") . "</td></tr>
			<tr><td colspan=10><b>" . $hd['nama'] . "</b></td></tr>
			<tr><td colspan=10><b>HASIL TIMBANG TBS KE EKSTERNAL</b></td></tr>
			<tr><td colspan=10>" . ($flt['info'] != '' ? $flt['info'] : 'Seluruh data') . "</td></tr>
			<tr><td colspan=10>Ditarik oleh " . $_SESSION['empl']['name'] . " (" . $_SESSION['standard']['username'] . ") pada " . date('d-m-Y H:i:s') . "</td></tr>
			<tr><td colspan=10>&nbsp;</td></tr>
			</table>";
			$tab = $kop . $tab;
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("lap_timbangexternal", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}



		break;
	case 'addkgpks':
		echo "<table>";
		echo "<tr><td>" . $_SESSION['lang']['tanggal'] . " PKS</td>
				 <td>:</td>
				 <td>
				 <input type=text class=myinputtext id=tglpksx onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=\"width:100px;\" readonly/>
				 </td><td></td>
			 </tr>";
		echo "<tr><td>" . $_SESSION['lang']['beratMasuk'] . "</td>
				 <td>:</td>
				 <td><input style=width:100px id=brmsk class=myinputtextnumber onblur=getnetkgpks() onkeyup=\"z.numberFormat('brmsk',2)\" onkeypress='return angka_doang(event)'/></td><td>Kg</td>
			 </tr>";
		echo "<tr><td>" . $_SESSION['lang']['beratKeluar'] . "</td>
				 <td>:</td>
				 <td><input style=width:100px id=brklr class=myinputtextnumber onblur=getnetkgpks() onkeyup=\"z.numberFormat('brklr',2)\" onkeypress='return angka_doang(event)'/></td><td>Kg</td>	 
			</tr>";
		echo "<tr><td>" . $_SESSION['lang']['beratBersih'] . "</td>
				 <td>:</td>
				 <td><input style=width:100px id=brnet disabled class=myinputtextnumber onblur=\"z.numberFormat('brnet',2)\" onkeypress='return angka_doang(event)'/></td><td>Kg</td>
			</tr>";
		echo "<tr><td>" . $_SESSION['lang']['potongan'] . "</td>
				 <td>:</td>
				 <td><input style=width:100px id=potongx class=myinputtextnumber onkeyup=\"z.numberFormat('potongx',2)\" onkeypress='return angka_doang(event)'/></td><td>Kg</td>	 
			</tr>";
		echo "<tr><td></td>
				 <td></td>
				 <td colspan=2>
					<input id=notiketkgpks type=hidden value=" . $notiket . ">
					<input id=numrow type=hidden value=" . $numrow . ">
					<button class=mybutton onclick=savekgpks()>" . $_SESSION['lang']['save'] . "</button>
					<button class=mybutton onclick=cancelkgpks()>" . $_SESSION['lang']['cancel'] . "</button>
				</td>
			</tr>";
		echo "</table>";
		break;
	case 'saveaddkgpks':
		$notiket = tkeNoTrans($notiket);
		if (tkeTiketTutup($notiket)) {
			exit('Error : Periode Akuntansi Sudah di Tutup.');
		}
		$spbpabrik = addslashes($spbpabrik);
		$tahuntanam2 = addslashes($tahuntanam2);
		foreach (array('kgin', 'kgout', 'buahdikembalikan', 'kgnet', 'potongx') as $kNum) {
			$$kNum = (float)$$kNum;
		}
		$str = "update " . $dbname . ".pabrik_timbangan set tanggalpks='" . $tanggalpks . "', beratmasukpmks='" . $kgin . "', beratkeluarpmks='" . $kgout . "',buahdikembalikan='" . $buahdikembalikan . "',spbpabrik='" . $spbpabrik . "',tahuntanam='" . $tahuntanam2 . "', beratbersihpmks='" . $kgnet . "',kgpotsortasi='" . $potongx . "' where notransaksi='" . $notiket . "' and millcode='EXTM' and kodeorg IN (" . getOrgDetail(2) . ") limit 1";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo "DB Error : " . $e->getMessage();
			die();
		}
		break;
	case 'deleteData':
		$param = tkeEscape($param);
		$param['notransaksi'] = tkeNoTrans(@$param['notransaksi']);
		if (tkeTiketTutup($param['notransaksi'])) {
			exit('Error : Periode Akuntansi Sudah di Tutup.');
		}
		$sDel = "delete from " . $dbname . ".pabrik_timbangan where notransaksi='" . $param['notransaksi'] . "' and millcode='EXTM' and kodeorg IN (" . getOrgDetail(2) . ") limit 1";
		try {
			$jmlhapus = $owlPDO->exec($sDel);

			if ($jmlhapus > 0) {
				$sDel = "delete from " . $dbname . ".pabrik_sortasi where notiket='" . $param['notransaksi'] . "'";
				$owlPDO->exec($sDel);
			}
		} catch (PDOException $e) {
			echo "DB Error : " . $e->getMessage();
			die();
		}
		break;

	case 'updThnTnm':
		$param = tkeEscape($param);
		$param['notransaksi'] = tkeNoTrans(@$param['notransaksi']);
		if (tkeTiketTutup($param['notransaksi'])) {
			exit('Error : Periode Akuntansi Sudah di Tutup.');
		}
		$optStat = makeOption($dbname, 'kebun_spbht', 'nospb,posting', "nospb='" . $param['spbId'] . "'");
		if ($optStat[$param['spbId']] == 0) {
			exit("Warning: Untuk Melakukan aksi ini hanya untuk SPB yang sudah diposting !");
		}
		$str = "update " . $dbname . ".pabrik_timbangan set tahuntanam='" . $param['tahuntanam2'] . "' where notransaksi='" . $param['notransaksi'] . "' and millcode='EXTM' and kodeorg IN (" . getOrgDetail(2) . ") limit 1";
		// exit("Warning: ".$str);
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo "DB Error : " . $e->getMessage();
			die();
		}
		break;

	case 'countBelumTimbang':
	case 'listBelumTimbang':
		#SPB External (tujuan 3) periode berjalan yang belum punya tiket timbang External
		$perBerjalan = date('Y-m');
		$sqlBelum = "from " . $dbname . ".kebun_spbht h where h.tujuan='3' and h.kodeorg IN (" . getOrgDetail(2) . ") and left(h.tanggal,7)='" . $perBerjalan . "' and not exists (select 1 from " . $dbname . ".pabrik_timbangan t where t.nospb=h.nospb and t.millcode='EXTM')";
		if ($proses == 'countBelumTimbang') {
			$rc = fetchData("select count(*) as jml " . $sqlBelum);
			echo (int)$rc[0]['jml'];
			break;
		}
		$rows = fetchData("select h.nospb,h.tanggal,h.kodeorg,h.posting,(select sum(d.jjg) from " . $dbname . ".kebun_spbdt d where d.nospb=h.nospb) as jjg " . $sqlBelum . " order by h.tanggal desc,h.nospb");
		if (count($rows) == 0) {
			echo "<div>Periode <b>" . $perBerjalan . "</b>: semua SPB External sudah ada hasil timbangnya.</div>";
			break;
		}
		$tab = "<div style='margin-bottom:6px'>Periode <b>" . $perBerjalan . "</b> : <b>" . count($rows) . "</b> SPB External belum ada hasil timbang</div>";
		$tab .= "<div style='max-height:60vh;overflow:auto'><table cellspacing=1 border=0 style='width:100%'>
			<thead><tr class=rowheader>
			<td align=center width=40px>No.</td><td align=center>SPB No.</td><td align=center>Tanggal</td><td align=center>Unit</td><td align=center>Jjg</td><td align=center>Status Posting</td>
			</tr></thead><tbody>";
		$no = 0;
		$totJjg = 0;
		foreach ($rows as $r) {
			$no++;
			$totJjg += $r['jjg'];
			$tab .= "<tr class=rowcontent>
			<td align=center>" . $no . "</td>
			<td align=center>" . $r['nospb'] . "</td>
			<td align=center>" . tanggalnormal($r['tanggal']) . "</td>
			<td align=center>" . $r['kodeorg'] . "</td>
			<td align=right>" . number_format($r['jjg'], 0) . "</td>
			<td align=center>" . ($r['posting'] == '1' ? 'Posted' : 'Belum Posting') . "</td>
			</tr>";
		}
		$tab .= "<tr class=rowcontent><td colspan=4 align=center><b>TOTAL</b></td><td align=right><b>" . number_format($totJjg, 0) . "</b></td><td></td></tr>";
		$tab .= "</tbody></table></div>";
		echo $tab;
		break;
	case 'getFormNosipb':
		$optSupplierCr = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$sSuplier = $owlPDO->query("select distinct kodecustomer,namacustomer from " . $dbname . ".pmn_4customer order by namacustomer asc");
		$sSuplier->setFetchMode(PDO::FETCH_ASSOC);
		while ($rSupplier = $sSuplier->fetch()) {
			$optSupplierCr .= "<option value='" . $rSupplier['kodecustomer'] . "'>" . $rSupplier['namacustomer'] . "</option>";
		}

		$optjenis .= "<option value='kontrak'>" . $_SESSION['lang']['NoKontrak'] . "</option>";
		$optjenis .= "<option value='disposal'>" . $_SESSION['lang']['disposal'] . "</option>";

		$form = "<fieldset style=float: left;>
               <legend><i>" . $_SESSION['lang']['find'] . "</i></legend>
                   <table>
                   <tr><td>" . $_SESSION['lang']['nodok'] . "</td><td>:</td><td><input type=text class=myinputtext id=nosipbcr onkeypress='enterkey(event,findNosipb)' style='width:145px' /></td></tr>
                   <tr><td>" . $_SESSION['lang']['nmcust'] . "</td><td>:</td><td><select id=custId style='width:150px'>" . $optSupplierCr . "</select></td></tr>
                   <tr><td colspan=2></td><td><button class=mybutton onclick=findNosipb()>" . $_SESSION['lang']['find'] . "</button></td></tr></table></fieldset>
               <fieldset><legend><i>" . $_SESSION['lang']['result'] . "</i></legend><div id=container2 style=overflow:auto;max-width:578px;max-height:400px;></fieldset></div>";
		echo $form;
		break;

	case 'getnosibp':
		$tab = "<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
		$tab .= "<thead><tr>";
		$tab .= "<td align=center>" . $_SESSION['lang']['NoKontrak'] . "</td>";
		$tab .= "<td align=center>" . $_SESSION['lang']['NoKontrak'] . " eksternal</td>";
		$tab .= "<td align=center>" . $_SESSION['lang']['kodecustomer'] . "</td>";
		$tab .= "<td align=center>" . $_SESSION['lang']['namacust'] . "</td>";
		$tab .= "</tr></thead><tbody>";

		if ($param['custId'] != '') {
			$whr .= " and koderekanan='" . $param['custId'] . "'";
		}
		if ($param['txtfind'] != '') {
			$whr .= " and nokontrakexternal like '%" . $param['txtfind'] . "%'";
		}

		$sdata = " select * from " . $dbname . ".pmn_kontrakjual where 1=1 " . $whr . " and kodept in (" . getOrgDetail(4) . ") and nokontrakexternal!='' and kodebarang='40000003'";
		$sdata = $owlPDO->query($sdata) or die(print " Gagal: " . PDOException::getMessage());
		$sdata->setFetchMode(PDO::FETCH_ASSOC);
		while ($rdata = $sdata->fetch()) {

			$whrCus = "kodecustomer='" . $rdata['koderekanan'] . "'";
			$optnmcust = makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer', $whrCus);

			$brt = "style=cursor:pointer; onclick=setData('" . $rdata['nokontrakexternal'] . "','" . $rdata['nokontrak'] . "')";
			$tab .= "<tr " . $brt . " class=rowcontent><td style=cursor:pointer>" . $rdata['nokontrak'] . "</td>";
			$tab .= "<td style=cursor:pointer>" . $rdata['nokontrakexternal'] . "</td>";
			$tab .= "<td style=cursor:pointer>" . $rdata['koderekanan'] . "</td>";
			$tab .= "<td style=cursor:pointer>" . $optnmcust[$rdata['koderekanan']] . "</td></tr>";
		}

		echo $tab;

		break;












	default:
		break;
}
