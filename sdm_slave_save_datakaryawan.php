<?
ini_set('display_errors', 0);
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

// require_once('lib/validasiktp.php');
// use ZerosDev\NikReader\Reader;
// $reader = new Reader();

?>
<?php
$photo = "";
if (isset($_POST['photo'])) {
	$photo = checkPostGet('photo', '');
}

$nourut			= $_POST['nourut'];
$nik			= $_POST['nik'];
$namakaryawan	= $_POST['namakaryawan'];
$tempatlahir	= $_POST['tempatlahir'];
$tanggallahir	= tanggalsystem($_POST['tanggallahir']);
$noktp			= trim($_POST['noktp']);
$nopassport		= $_POST['nopassport'];
$npwp			= $_POST['npwp'];
$bpjs			= $_POST['bpjs'];
$kodepos		= $_POST['kodepos'];
$alamataktif	= $_POST['alamataktif'];
$kota			= $_POST['kota'];
$noteleponrumah	= $_POST['noteleponrumah'];
$nohp			= $_POST['nohp'];
$nohp2			= $_POST['nohp2'];
$norekeningbank	= $_POST['norekeningbank'];
$namabank		= $_POST['namabank'];
$pemilikrekening = $_POST['anrekening'];
$alokasi		= $_POST['alokasi'];
$jms            = $_POST['jms'];
$kppnpwp        = $_POST['kppnpwp'];
$statuskaryawan	= $_POST['statuskaryawan'];
$subbpjs	= $_POST['subbpjs'];
$periodeakhirgaji	= $_POST['periodeakhirgaji'];


$tanggalmasuk	= tanggalsystem($_POST['tanggalmasuk']);
if ($_POST['tanggalpengangkatan'] == '')
	$_POST['tanggalpengangkatan'] = '00-00-0000';
$tanggalpengangkatan = tanggalsystem($_POST['tanggalpengangkatan']);
if ($_POST['tanggalkeluar'] == '')
	$_POST['tanggalkeluar'] = '00-00-0000';
$tanggalkeluar	= tanggalsystem($_POST['tanggalkeluar']);
$jumlahanak		= $_POST['jumlahanak'];
if ($jumlahanak == '')
	$jumlahanak = 0;
$jumlahtanggungan = $_POST['jumlahtanggungan'];
if ($jumlahtanggungan == '')
	$jumlahtanggungan = 0;
if ($_POST['tanggalmenikah'] == '')
	$_POST['tanggalmenikah'] = '00-00-0000';
$tanggalmenikah  = tanggalsystem($_POST['tanggalmenikah']);
if ($_POST['tanggalpengangkatannonstaff'] == '')
	$_POST['tanggalpengangkatannonstaff'] = '00-00-0000';
$tanggalpengangkatannonstaff = tanggalsystem($_POST['tanggalpengangkatannonstaff']);
$notelepondarurat = $_POST['notelepondarurat'];
$email           = $_POST['email'];
$emailkantor     = $_POST['emailkantor'];
$jeniskelamin    = $_POST['jeniskelamin'];
$agama           = $_POST['agama'];
$bagian          = $_POST['bagian'];
$subdept         = $_POST['subdept'];
$kodejabatan     = $_POST['kodejabatan'];
$kodegolongan    = $_POST['kodegolongan'];
$lokasitugas     = $_POST['lokasitugas'];
$kodeorganisasi  = $_POST['kodeorganisasi'];
$tipekaryawan    = $_POST['tipekaryawan'];
$warganegara     = $_POST['warganegara'];
$suku            = $_POST['suku'];
$sim             = $_POST['sim'];
$lokasipenerimaan = $_POST['lokasipenerimaan'];
$statuspajak     = $_POST['statuspajak'];
$insstatuspajak  = $_POST['insstatuspajak'];
$provinsi        = $_POST['provinsi'];
$sistemgaji      = $_POST['sistemgaji'];
$golongandarah   = $_POST['golongandarah'];
$statusperkawinan = $_POST['statusperkawinan'];
$levelpendidikan = $_POST['levelpendidikan'];
$levelkaryawan    = $_POST['levelkaryawan'];

$method          = checkPostGet('method', '');
$karyawanid      = $_POST['karyawanid'];
$subbagian       = $_POST['subbagian'];
$catu            = $_POST['catu'];
$pensiun         = $_POST['pensiun'];
$noerf           = $_POST['noerf'];
$supbpjs         = $_POST['supbpjs'];
$supbpjs         = $_POST['subbpjs'];
if ($subbagian == '0') {
	$subbagian = '';
}

$param = $_POST;
$param['tanggallahir'] = tanggalsystemn($_POST['tanggallahir']);
$param['tanggalmasuk'] = tanggalsystemn($_POST['tanggalmasuk']);
$param['tanggalpengangkatan'] = tanggalsystemn($_POST['tanggalpengangkatan']);
$param['tanggalkeluar'] = tanggalsystemn($_POST['tanggalkeluar']);
$param['tanggalmenikah'] = tanggalsystemn($_POST['tanggalmenikah']);
$param['bulandaftarbpjs'] = tanggalsystemn($_POST['bulandaftarbpjs']);
$param['tanggalpengangkatannonstaff'] = tanggalsystemn($_POST['tanggalpengangkatannonstaff']);

$param = $_POST;
if (count($param) == 0) {
	$param = $_GET;
}

function upload_photo($pathlocation, $dataphoto, $rename)
{
	$result = "";
	if (isset($dataphoto)) {
		$photo = $dataphoto;
		if ($photo != "") {
			$path = $pathlocation;
			if (!file_exists($path)) {
				mkdir($path, 0777, true);
			}

			$file = preg_replace('#^data:image/\w+;base64,#i', '', $photo);
			$file = str_replace(' ', '+', $file);
			$stream = base64_decode($file);

			// Cek ukuran file
			$sizeKB = strlen($stream) / 1024;
			$maxSizeKB = 2048;
			if ($sizeKB > $maxSizeKB) {
				return "ERROR: File terlalu besar (" . round($sizeKB, 2) . " KB)";
			}

			// Cek MIME
			$f = finfo_open();
			$mime_type = finfo_buffer($f, $stream, FILEINFO_MIME_TYPE);

			$ext = "";
			if ($mime_type == "image/jpeg") {
				$ext = ".jpeg";
			} elseif ($mime_type == "image/png") {
				$ext = ".png";
			} elseif ($mime_type == "image/gif") {
				$ext = ".gif";
			} elseif ($mime_type == "image/wbmp") {
				$ext = ".wbmp";
			} else {
				## Kalau kasih warning error waktu update datakaryawan gak kena
				// return "ERROR: Format sekarang adalah [$mime_type], seharusnya salah satu dari: image/jpeg, image/png, image/gif, image/wbmp";
				$ext = "";
			}

			$filename = $path . $rename . $ext;
			file_put_contents($filename, $stream);

			$result = $rename . $ext;
		}
	}
	return $result;
}


switch ($method) {
	case 'savePhoto':
		$sKaryawan = selectQuery($dbname, 'datakaryawan', 'karyawanid', "nik='" . $nik . "'");
		$rKaryawan = fetchData($sKaryawan);
		$karyawanid = $rKaryawan[0]['karyawanid'];
		$pathlocation = "./photokaryawan/";
		$dataphoto = $photo;
		$renamephoto = "photo_" . $karyawanid;

		if ($dataphoto != "") {
			$photo = upload_photo($pathlocation, $dataphoto, $renamephoto);
			$param['photo'] = $photo;
		} else {
			$param['photo'] = "";
		}
		$data = [
			'photo' => $param['photo'],
		];
		if (stripos($photo, "error") !== false) {
			exit($photo);
		}
		$updatePhoto = updateQuery($dbname, 'datakaryawan', $data, "karyawanid='" . $karyawanid . "'");
		if ($owlPDO->exec($updatePhoto)) {
			$result = array('success' => true, 'message' => 'Photo berhasil diupdate', 'photo' => $param['photo']);
		} else {
			$result = array('success' => false, 'message' => 'Photo gagal diupdate', 'error' => $updatePhoto);
		}
		echo json_encode($result);
		break;
	case 'deletePhoto':
		$sKaryawan = selectQuery($dbname, 'datakaryawan', 'photo', "nik='" . $nik . "'");
		$rKaryawan = fetchData($sKaryawan);
		$pathlocation = "./photokaryawan/";
		$dataphoto = $rKaryawan[0]['photo'];
		unlink($pathlocation . $dataphoto);
		$data = [
			'photo' => '',
		];
		$updatePhoto = updateQuery($dbname, 'datakaryawan', $data, "nik='" . $nik . "'");
		if ($owlPDO->exec($updatePhoto)) {
			$result = array('success' => true, 'message' => 'Photo berhasil dihapus', 'photo' => $param['photo']);
		} else {
			$result = array('success' => false, 'message' => 'Photo gagal dihapus', 'error' => $updatePhoto);
		}
		echo json_encode($result);
		break;
	case 'getDivisi';
		$divisi = "<option value=''></option>";

		$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where LENGTH(kodeorganisasi)=6  and tipe in('AFDELING','TRAKSI','GUDANG','WORKSHOP','BIBITAN','STATION','SIPIL','MAINTENANCE') and induk = '" . $param['lokasitugas'] . "'";
		$res = fetchdata($str); //exit("error.$str");
		foreach ($res as $bar) {
			$x = "";
			if ($param['subbagian'] == $bar['kodeorganisasi']) {
				$x = "selected";
			}
			$divisi .= "<option value=" . $bar['kodeorganisasi'] . " " . $x . ">" . $bar['namaorganisasi'] . "</option>";
		}

		echo $divisi;
		break;
	case 'getdetailtipekary';
		$optstatkaryawan = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$namagolongan = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$arrstatkaryawan = getEnum($dbname, 'datakaryawan', 'statuskaryawan');
		foreach ($arrstatkaryawan as $key => $value) {
			if ($param['tipekaryawan'] == '4') {
				$caption_tambahan = "";
				if ($value == "Aktif" or $value == "Keluar" or $value == "Percobaan") {
					$caption_tambahan = "(Khusus KHL)";
					//$value = $_SESSION['lang'][strtolower($value)];
					$optstatkaryawan .= "<option value='" . $value . "'>" . $value . " " . $caption_tambahan . "</option>";
				}
			} else {
				$caption_tambahan = "";
				if ($value != "Aktif" and $value != "Keluar") {
					$optstatkaryawan .= "<option value='" . $value . "'>" . $value . " " . $caption_tambahan . "</option>";
				}
			}
		}

		if ($param['tipekaryawan'] == '4' or $param['tipekaryawan'] == '14') {
			$sistemgaji .= "<option value='Harian'>Harian</option>";
		} else {
			$sistemgaji .= "<option value='Bulanan'>Bulanan</option>";
		}

		$str = "select * from " . $dbname . ".sdm_5golongan where 1=1 order by namagolongan asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$namagolongan .= "<option value=" . $bar['kodegolongan'] . " " . $x . ">" . $bar['namagolongan'] . "</option>";
		}

		echo $optstatkaryawan . "####" . $sistemgaji . "####" . $namagolongan;
		break;
	case 'getkab';
		$option = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		if ($param['jenis'] == 'kab') {
			$str = "select * from " . $dbname . ".kabupaten where 1=1 and id_prov='" . $param['provinsi'] . "' order by kabupaten asc";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$x = "";
				if ($param['value'] == $bar['id']) {
					$x = "selected";
				}
				$option .= "<option value=" . $bar['id'] . " " . $x . ">" . $bar['kabupaten'] . "</option>";
			}
		}

		if ($param['jenis'] == 'kec') {
			$str = "select * from " . $dbname . ".kecamatan where 1=1 and id_kab='" . $param['kabupaten'] . "' order by kecamatan asc";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$x = "";
				if ($param['value'] == $bar['idkec']) {
					$x = "selected";
				}
				$option .= "<option value=" . $bar['idkec'] . " " . $x . ">" . $bar['kecamatan'] . "</option>";
			}
		}
		if ($param['jenis'] == 'des') {
			$str = "select * from " . $dbname . ".desa where 1=1 and id_kec='" . $param['kecamatan'] . "' order by desa asc";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$x = "";
				if ($param['value'] == $bar['iddes']) {
					$x = "selected";
				}
				$option .= "<option value=" . $bar['iddes'] . " " . $x . ">" . $bar['desa'] . "</option>";
			}
		}

		echo $option;
		break;
	case 'getpopupalamat';

		$optdes = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$optkec = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$optkab = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$optprov = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "select * from " . $dbname . ".provinsi where 1=1 order by provinsi asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$x = "";
			if ($param['value'] == $bar['id']) {
				$x = "selected";
			}
			$optprov .= "<option value=" . $bar['id'] . " " . $x . ">" . $bar['provinsi'] . "</option>";
		}

		$str = "select * from " . $dbname . ".kabupaten where 1=1 and id_prov='" . $param['provinsi'] . "' order by kabupaten asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$x = "";
			if ($param['kabupaten'] == $bar['id']) {
				$x = "selected";
			}
			$optkab .= "<option value=" . $bar['id'] . " " . $x . ">" . $bar['kabupaten'] . "</option>";
		}

		$str = "select * from " . $dbname . ".kecamatan where 1=1 and id_kab='" . $param['kabupaten'] . "' order by kecamatan asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$x = "";
			if ($param['kecamatan'] == $bar['idkec']) {
				$x = "selected";
			}
			$optkec .= "<option value=" . $bar['idkec'] . " " . $x . ">" . $bar['kecamatan'] . "</option>";
		}
		$str = "select * from " . $dbname . ".desa where 1=1 and id_kec='" . $param['kecamatan'] . "' order by desa asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$x = "";
			if ($param['desa'] == $bar['iddes']) {
				$x = "selected";
			}
			$optdes .= "<option value=" . $bar['iddes'] . " " . $x . ">" . $bar['desa'] . "</option>";
		}

		$tab = "<table>";
		$tab .= "	<tr>
					<td>" . $_SESSION['lang']['provinsi'] . "</td><td>:</td>
					<td><select class='select2' onchange=getkab('kab','" . $param['kabupaten'] . "'); style=width:300px id=prov2 >" . $optprov . "</select></td> 
				</tr>
				
				<tr>
					<td>" . $_SESSION['lang']['kabupaten'] . "</td><td>:</td>
					<td><select class='select2' onchange=getkab('kec','" . $param['kecamatan'] . "'); style=width:300px id=kab2 >" . $optkab . "</select></td> 
				</tr>
				<tr>
					<td>" . $_SESSION['lang']['kecamatan'] . "</td><td>:</td>
					<td><select class='select2' onchange=getkab('des','" . $param['desa'] . "'); style=width:300px id=kec2 >" . $optkec . "</select></td> 
				</tr>
				<tr>
					<td>" . $_SESSION['lang']['desa'] . " / " . $_SESSION['lang']['kelurahan'] . "</td><td>:</td>
					<td><select class='select2' style=width:300px id=des2 >" . $optdes . "</select></td> 
				</tr>
				<tr>
					<td>" . $_SESSION['lang']['kodepos'] . "</td><td>:</td>
					<td><input id=kopos2 class=myinputtextnumber style=width:295px></td> 
				</tr>
				<tr style=vertical-align:top>
					<td>" . $_SESSION['lang']['alamat'] . "</td><td>:</td>
					<td><textarea id=alamat2 style=width:280px rows=5></textarea>
					</td>
				</tr>
				
				<tr>
					<td></td><td></td>
					<td><button class=mybutton onclick=setalamat();>Tambahkan</button></td> 
				</tr>
				";

		$tab .= "</table>";
		echo $tab;
		break;
	case 'form_ajukan';
		$kodeorg = $_POST['lokasitugas'];
		$version = $_POST['version'];

		$str = "select karyawanid from " . $dbname . ".sdm_riwayatjabatan where posting='0' and karyawanid='" . $karyawanid . "' and mulaiberlaku >= '" . $periodegaji . "-01' and mulaiberlaku <= '" . $periodegaji . "-31' ";
		$res = fetchdata($str);
		if (count($res) > 0) {
			exit("Warning : Masih terdapat promosi,demosi,mutasi datakaryawan pada periode ini yang belum di posting");
		}

		$jenispersetujuanx = '';
		$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='DTK1'";
		$res = fetchdata($str);
		if (count($res) > 0) {
			@$arrdtk = explode(',', $res[0]['nilai']);
		} else {
			@$arrdtk[0] = '9999';
			@$arrdtk[1] = '9999';
		}
		if (!in_array($_POST['tipekaryawan'], $arrdtk)) {
			$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='DTK2'";
			$res = fetchdata($str);
			if (count($res) > 0) {
				@$arrdtk = explode(',', $res[0]['nilai']);
			} else {
				@$arrdtk[0] = '9999';
				@$arrdtk[1] = '9999';
			}
			if (!in_array($_POST['tipekaryawan'], $arrdtk)) {
				$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='DTK3'";
				$res = fetchdata($str);
				if (count($res) > 0) {
					@$arrdtk = explode(',', $res[0]['nilai']);
				} else {
					@$arrdtk[0] = '9999';
					@$arrdtk[1] = '9999';
				}
				if (!in_array($_POST['tipekaryawan'], $arrdtk)) {
					exit('Error,Tipe karyawan ini belum didaftarkan di parameter applikasi dengan kode parameter DTK1,DTK2,DTK3');
				} else {
					$jenispersetujuanx = 'DTK3';
				}
			} else {
				$jenispersetujuanx = 'DTK2';
			}
		} else {
			$jenispersetujuanx = 'DTK1';
		}

		$str = "select * from " . $dbname . ".datakaryawan where karyawanid='" . $karyawanid . "'";
		$res = fetchData($str);
		$departemen = $res[0]['bagian'];
		$kodegolongan = $res[0]['kodegolongan'];

		## CEK PER DEPARTEMEN
		$str = "select count(kodeunit) as kodeunit from " . $dbname . ".setup_approval where kodeunit='" . $lokasitugas . "' and jenispersetujuan='IJS' and departemen='" . $departemen . "'";
		$res = fetchdata($str);
		$perdepartemen = $res[0]['kodeunit'];
		$where = "";
		if ($perdepartemen > 0) {
			$where .= " and departemen='" . $departemen . "'";
		} else {
			$where .= " and departemen=''";
		}

		## CEK PER GOLONGAN
		$str = "select count(kodeunit) as kodeunit from " . $dbname . ".setup_approval where kodeunit='" . $lokasitugas . "' and jenispersetujuan='" . $jenispersetujuanx . "' and golongan='" . $kodegolongan . "'";
		$res = fetchdata($str);
		$pergolongan = $res[0]['kodeunit'];
		if ($pergolongan > 0) {
			$where .= " and golongan='" . $golongan . "'";
		} else {
			$where .= " and golongan=''";
		}

		$str = "select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from " . $dbname . ".setup_approval a
				  left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid where
				  a.karyawanid!='" . $_SESSION['standard']['userid'] . "' and a.jenispersetujuan='" . $jenispersetujuanx . "' and a.level='1' and a.kodeunit='" . $kodeorg . "' " . $where . "  order by b.namakaryawan asc";

		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optKry = "";
		while ($rkry = $res->fetch()) {
			$optKry .= "<option value='" . $rkry['karyawanid'] . "'>" . $rkry['namakaryawan'] . " [" . $rkry['lokasitugas'] . "]</option>";
		}

		$tab = "<table cellspacing=1 cellpadding=5 border=0 width=100%>
				<tr class=rowcontent>
					<td>" . $_SESSION['lang']['namakaryawan'] . "</td>
					<td width=5px>:</td>
					<td id=notran_aju>" . $_POST['namakaryawan'] . "</td>
				</tr>

				<tr class=rowcontent>
					<td>" . $_SESSION['lang']['kepada'] . "</td>
					<td width=5px>:</td>
					<td><select id=kepada style='width:99%;'>" . $optKry . "</select></td>
				</tr>
				<tr class=rowcontent>
					<td style=display:none><input id=jenispersetujuanx style=display:none value=" . $jenispersetujuanx . "></td><td><input id=numrow style=display:none value=" . $_POST['nourut'] . "></td>
					<td></td><td align=left colspan=5><button id=tomboldetail class=mybutton onclick=ajukan('" . $karyawanid . "')>" . $_SESSION['lang']['diajukan'] . "</button></td>
				</tr>
				</table>";

		echo $tab;
		break;
	case 'ajukan':

		// ## Cek nama ibu wajib diisi
		// $str = "select karyawanid from ".$dbname.".sdm_karyawankeluarga where karyawanid='".$karyawanid."' and hubungankeluarga = 'Ibu'";
		// $res = fetchdata($str);
		// if(count($res) == 0){ 
		// 	exit("Warning : Nama ibu wajib diisi !");
		// }

		try {
			$owlPDO->beginTransaction();
			if ($_POST['kepada'] == '' or $_POST['nourut'] == '') {
				throw new PDOException('Isikan nama penyetuju.');
			}
			//update flag menjadi 1
			$str = "update " . $dbname . ".datakaryawan_hist set approval_status='9', diajukan='" . $_SESSION['standard']['userid'] . "' where nourut = '" . $_POST['nourut'] . "'";
			$owlPDO->exec($str);
			//insert ke table approval
			$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`,
                `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
            values ('','" . $_POST['nourut'] . "','" . $_POST['jenispersetujuanx'] . "','1','" . $_POST['kepada'] . "','0','','','')";
			$owlPDO->exec($str);
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		break;
	case 'postingdata':
		try {
			$owlPDO->beginTransaction();

			$str = "update " . $dbname . ".datakaryawan_hist set approval_status='8' where nourut = '" . $_POST['nourut'] . "'";
			$owlPDO->exec($str);

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		break;
	case 'unpostingdata':
		try {
			$owlPDO->beginTransaction();

			$str = "update " . $dbname . ".datakaryawan_hist set approval_status='7' where nourut = '" . $_POST['nourut'] . "'";
			$owlPDO->exec($str);

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		break;
	case 'delete':
		$strx = "delete from " . $dbname . ".datakaryawan where karyawanid=" . $karyawanid;
		break;
	case 'update':

		if ($tipekaryawan == '4' and $sistemgaji != 'Harian') {
			exit("Warning : Tipe karyawan dan Sistem Gaji tidak sesuai.");
		}
		if ($tipekaryawan != '4' and $sistemgaji == 'Harian') {
			exit("Warning : Tipe karyawan dan Sistem Gaji tidak sesuai.");
		}

		if ($tipekaryawan == '4' and ($jms != '' or $bpjs != '') and ($param['bulandaftarbpjs'] == '--' or $param['bulandaftarbpjs'] == '')) {
			exit("Warning : Tanggal daftar BPJS harus diisi.");
		}
		if ($kodejabatan == '') {
			exit("Warning : Jabatan harus diisi.");
		}

		$cekloktugas = false;
		$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where induk = '" . $param['kodeorganisasi'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($param['lokasitugas'] == $bar['kodeorganisasi']) {
				$cekloktugas = true;
			}
		}

		if ($cekloktugas == false) {
			exit("Warning : Lokasi tugas tidak sesuai.");
		}
		if ($param['subbagian'] != '0' and $param['subbagian'] != '') {
			$ceksubbagian = false;
			$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where induk = '" . $param['lokasitugas'] . "' or kodeorganisasi like '" . $param['lokasitugas'] . "%'";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				if ($param['subbagian'] == $bar['kodeorganisasi']) {
					$ceksubbagian = true;
				}
			}
			if ($ceksubbagian == false) {
				exit("Warning : Divisi tidak sesuai.<br>Lokasi Tugas : " . $param['lokasitugas'] . "<br>Divisi : " . $param['subbagian'] . "");
			}
		}

		if ($provinsi == '') {
			exit("Warning : Propinsi harus diisi.");
		}
		if ($param['kabupaten'] == '') {
			exit("Warning : Kabupaten / Kota harus diisi.");
		}
		if ($param['kecamatan'] == '') {
			exit("Warning : Kecamatan harus diisi.");
		}
		if ($param['desa'] == '') {
			exit("Warning : Desa / Kelurahan harus diisi.");
		}
		// if($kodepos==''){
		// 	exit("Warning : Kode pos harus diisi.");
		// }
		if ($alamataktif == '') {
			exit("Warning : Alamat KTP harus diisi.");
		}

		if ($param['kodeorganisasi'] != 'PPP') {
			if ($levelkaryawan == '') {
				exit("Warning : Level Karyawan harus diisi.");
			}
		}

		// exit('warning:'.$tanggalkeluar);

		if ($tanggalkeluar == '00000000' && $statuskaryawan == 'Keluar') {
			exit("Warning : Tanggal keluar harus diisi saat status karyawan Keluar.");
		}
		if ($tanggalkeluar != '00000000' && $statuskaryawan != 'Keluar') {
			exit("Warning : Tanggal keluar tidak boleh diisi saat status karyawan tidak keluar.");
		}

		if (strlen($noktp) != 16) {
			exit("Warning : Nomor KTP harus 16 digit.");
		}

		if ($tipekaryawan == '0' or $tipekaryawan == '1') {
			if ($nopassport == '') {
				exit("Warning : Karyawan STAFF dan NON Staff, Nomor Kartu Keluarga harus diisi.");
			}
			if ($tanggalpengangkatan == '') {
				exit("Warning : Karyawan STAFF dan NON Staff, Tanggal pengangkatan harus diisi.");
			}
			if (strlen($nopassport) != 16) {
				exit("Warning : Karyawan STAFF dan NON Staff, Nomor Kartu Keluarga harus 16 digit.");
			}
			if ($norekeningbank == '') {
				exit("Warning : Karyawan STAFF dan NON Staff, Nomor rekening wajib diisi");
			}
			if ($namabank == '') {
				exit("Warning : Karyawan STAFF dan NON Staff, Nama bank wajib diisi");
			}
			if ($pemilikrekening == '') {
				exit("Warning : Karyawan STAFF dan NON Staff, Pemilik rekening wajib diisi");
			}
		}

		validasiktp($noktp);

		$str = "select version_type from " . $dbname . ".datakaryawan_hist where karyawanid='" . $karyawanid . "' and nourut='" . $nourut . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$versiontype = $bar['version_type'];


		$periodexxx = '';
		$cekperiode = "select periodegaji from " . $dbname . ".datakaryawan_hist where lokasitugas='" . $lokasitugas . "' and version_type='B' group by periodegaji ";

		$resperiode = $owlPDO->query($cekperiode) or die(print " Gagal: " . PDOException::getMessage());
		$resperiode->setFetchMode(PDO::FETCH_OBJ);
		while ($barperiode = $resperiode->fetch()) {
			if ($periodexxx == '') {
				$periodexxx = "'" . $barperiode->periodegaji . "'";
			} else {
				$periodexxx .= ",'" . $barperiode->periodegaji . "'";
			}
		}

		$periodegaji = '';
		if ($nourut != '' and $versiontype == 'B') {
			$str = "select version_type,periodegaji from " . $dbname . ".datakaryawan_hist where karyawanid='" . $karyawanid . "' and nourut='" . $nourut . "' and approval_status=7 ";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$periodegaji = $bar['periodegaji'];
			$version_type = 'B';
		} elseif ($tipekaryawan == '4') {
			if ($periodexxx == '') {
				$str = "select min(periode) as periode from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $lokasitugas . "' and jenisgaji='H' and sudahproses=0 order by periode desc";
			} else {
				$str = "select min(periode) as periode from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $lokasitugas . "' and jenisgaji='H' and sudahproses=0 and periode not in (" . $periodexxx . ") order by periode desc";
			}
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$periodegaji = $bar['periode'];
		} else {
			if ($periodexxx == '') {
				$str = "select min(periode) as periode from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $lokasitugas . "' and jenisgaji='B' and sudahproses=0 order by periode desc";
			} else {
				$str = "select min(periode) as periode from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $lokasitugas . "' and jenisgaji='B' and sudahproses=0 and periode not in (" . $periodexxx . ") order by periode desc";
			}
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$periodegaji = $bar['periode'];
		}

		if ($nourut != '' and $versiontype == 'B') {
			$qData = selectQuery($dbname, 'datakaryawan_hist', '*', "karyawanid='" . $karyawanid . "' and nourut='" . $nourut . "' and approval_status=7 ");
		} else {
			$qData = selectQuery($dbname, 'datakaryawan', '*', "karyawanid='" . $karyawanid . "'");
		}
		$resData = fetchData($qData);
		$oldData = $resData[0];

		// if($periodegaji==''){
		// 	exit('Warning : Tidak ditemukan periode gaji yang belum ditutup, silahkan buat baru periode gaji berikutnya/buka kembali periode gaji terakhir');
		// }

		$qData = selectQuery($dbname, 'datakaryawan_hist', '*', "karyawanid='" . $karyawanid . "' and periodegaji ='" . $periodegaji . "' and approval_status='9' ");
		$resData = fetchData($qData);
		if (count($resData) > 0  and $versiontype != 'B') {
			exit("Warning : Perubahan data tidak dapat dilakukan , data karyawan ini, masih ada yang dalam tahap approval");
		}

		$qData = selectQuery($dbname, 'datakaryawan_hist', '*', "karyawanid='" . $karyawanid . "' and periodegaji ='" . $periodegaji . "' and approval_status='8' and version_type='B' ");
		$resData = fetchData($qData);
		if (count($resData) > 0  and $versiontype != 'B') {
			exit("Warning : Perubahan data tidak dapat dilakukan, sudah terdapat data karyawan history dengan version proses bulanan, untuk melakukan perubahan pada periode " . $periodegaji . " silahkan ke history data karyawan dan lakukan edit di version proses bulanan data ini ");
		}

		if ($nik == '') {
			$no = 1;
			$str = "select * from " . $dbname . ".datakaryawan where kodeorganisasi = '" . $kodeorganisasi . "' and lokasitugas='" . $lokasitugas . "'";
			$res = fetchData($str);
			$nik = getnomornik($no);
			foreach ($res as $bar) {
				if (ceknonik($nik) > 0) {
					$no += 1;
					$nik = getnomornik($no);
				} else {
					continue;
				}
			}
		}

		if ($photo == '') {
			exit("Warning : Photo wajib diisi !");
		}

		if ($photo != "") {
			$path = "./photokaryawan/";
			$renamephoto = "photo_" . $karyawanid;
			$oldfile = $oldData['photo'];
			if ($oldfile != "") {
				unlink($path . $oldfile);
			}
			$uploadname = upload_photo($path, $photo, $renamephoto);
			$upload_update = "`photo`='" . $uploadname . "',";
		} else {
			$upload_update = "";
		}
		if (stripos($uploadname, "error") !== false) {
			exit($uploadname);
		}
		$loktgs = $nmkaryktp = "";
		$cekktp = "select noktp,namakaryawan,lokasitugas,tanggalkeluar from " . $dbname . ".datakaryawan where noktp='" . $noktp . "' and karyawanid!='" . $karyawanid . "'AND (tanggalkeluar IS NULL OR tanggalkeluar='0000-00-00')";
		$resktp = $owlPDO->query($cekktp) or die(print " Gagal: " . PDOException::getMessage());
		$resktp->setFetchMode(PDO::FETCH_OBJ);
		while ($baru = $resktp->fetch()) {
			$ktp = $baru->noktp;
			$nmkaryktp = $baru->namakaryawan;
			$loktgs = $baru->lokasitugas;
			$tanggalkeluar = $baru->tanggalkeluar;
		}

		if ($ktp == '' || $ktp == 0) {

			if ($_POST['suku'] == '') {
				$suku = "0";
				$_POST['suku'] = '0';
			} else {
				$suku = $_POST['suku'];
			}

			$arrdatabaru = array(
				"nik"                => $nik,
				"namakaryawan"       => $namakaryawan,
				"tempatlahir"        => $tempatlahir,
				"tanggallahir"       => tanggalsystemn($param['tanggallahir']),
				"warganegara"        => $warganegara,
				"jeniskelamin"       => $jeniskelamin,
				"statusperkawinan"   => $statusperkawinan,
				"tanggalmenikah"     => tanggalsystemn($param['tanggalmenikah']),
				"agama"              => $agama,
				"golongandarah"      => $golongandarah,
				"levelpendidikan"    => $levelpendidikan,
				"alamataktif"        => $alamataktif,
				"provinsi"           => $provinsi,
				"kota"               => $kota,
				"kodepos"            => $kodepos,
				"noteleponrumah"     => $noteleponrumah,
				"nohp"               => $nohp,
				"nohp2"              => $nohp2,
				"norekeningbank"     => $norekeningbank,
				"namabank"           => $namabank,
				"pemilikrekening"    => $pemilikrekening,
				"sistemgaji"         => $sistemgaji,
				"no_keluarga"        => $nopassport,
				"noktp"              => $noktp,
				"notelepondarurat"   => $notelepondarurat,
				"tanggalmasuk"       => tanggalsystemn($param['tanggalmasuk']),
				"tanggalpengangkatan" => tanggalsystemn($param['tanggalpengangkatan']),
				"tanggalpengangkatannonstaff" => tanggalsystemn($param['tanggalpengangkatannonstaff']),
				"tanggalkeluar"      => tanggalsystemn($param['tanggalkeluar']),
				"tipekaryawan"       => $tipekaryawan,
				"jumlahanak"         => $jumlahanak,
				"jumlahtanggungan"   => $jumlahtanggungan,
				"statuspajak"        => $statuspajak,
				"npwp"               => $npwp,
				"bpjs"               => $bpjs,
				"lokasipenerimaan"   => $lokasipenerimaan,
				"kodeorganisasi"     => $kodeorganisasi,
				"bagian"             => $bagian,
				"kodejabatan"        => $kodejabatan,
				"kodegolongan"       => $kodegolongan,
				"pensiun"            => $pensiun,
				"lokasitugas"        => $lokasitugas,
				"email"              => $email,
				"emailkantor"        => $emailkantor,
				"alokasi"            => $alokasi,
				"subbagian"          => $subbagian,
				"subdept"            => $subdept,
				"jms"                => $jms,
				"kodecatu"           => $catu,
				"statpremi"          => $_POST['statPremi'],
				"suku"               => $_POST['suku'],
				"sim"                => $_POST['sim'],
				"statuskaryawan"     => $_POST['statuskaryawan'],
				"insstatuspajak"     => $insstatuspajak,
				"supbpjs"            => $supbpjs,
				"kppnpwp"            => $kppnpwp,
				"periodeakhirgaji"   => $periodeakhirgaji,
				"kabupaten"          => $param['kabupaten'],
				"kecamatan"          => $param['kecamatan'],
				"desa"               => $param['desa'],
				"bulandaftarbpjs"    => $param['bulandaftarbpjs'],
				"levelkaryawan"      => $param['levelkaryawan']

			);

			$textchange = '';
			$changetmkjmk = 0;
			foreach ($arrdatabaru as $field => $val) {
				$oldData[$field] = preg_replace("/\r|\n/", "", $oldData[$field]);
				if ($val == '*') {
					$val = '';
				}

				if ($oldData[$field] != $val) {
					if ($field == 'tanggalmasuk') {
						$changetmkjmk = 1;
					}
					if ($textchange == '') {

						$textchange = '###' . $field . '###';
					} else {
						$textchange .= $field . '###';
					}
				}
			}
			if ($textchange == '') {
				exit('Warning : Tidak ada data perubahan');
			}

			$tipeloktugaskary = getNamaOrg($lokasitugas, 'tipe');

			// if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			// 	$statusnonapproval='nonapproval';
			// }elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL' and ($tipeloktugaskary != 'KANWIL' and $tipeloktugaskary != 'HOLDING')){
			$statusnonapproval = 'nonapproval';
			// }else{
			// 	$statusnonapproval='approval';
			// }

			switch ($statusnonapproval) {
				case 'nonapproval':
					$strx = "update " . $dbname . ".datakaryawan set 
						`nik`                ='" . $nik . "',
						`namakaryawan`       ='" . $namakaryawan . "',
						`tempatlahir`        ='" . $tempatlahir . "',
						$upload_update
						`tanggallahir`       ='" . $tanggallahir . "',
						`warganegara`        ='" . $warganegara . "',
						`jeniskelamin`       ='" . $jeniskelamin . "',
						`statusperkawinan`   ='" . $statusperkawinan . "',
						`tanggalmenikah`     ='" . $tanggalmenikah . "',
						`agama`              ='" . $agama . "',
						`golongandarah`      ='" . $golongandarah . "',
						`levelpendidikan`    ='" . $levelpendidikan . "',
						`alamataktif`        ='" . $alamataktif . "',
						`provinsi`           ='" . $provinsi . "',
						`kota`               ='" . $kota . "',
						`kodepos`            ='" . $kodepos . "',
						`noteleponrumah`     ='" . $noteleponrumah . "',
						`nohp`               ='" . $nohp . "',
						`nohp2`              ='" . $nohp2 . "',
						`norekeningbank`     ='" . $norekeningbank . "',
						`namabank`           ='" . $namabank . "',
						`pemilikrekening`    ='" . $pemilikrekening . "',
						`sistemgaji`         ='" . $sistemgaji . "',
						`no_keluarga`        ='" . $nopassport . "',
						`noktp`              ='" . $noktp . "',
						`notelepondarurat`   ='" . $notelepondarurat . "',
						`tanggalmasuk`       ='" . $tanggalmasuk . "',
						`tanggalpengangkatan`='" . $tanggalpengangkatan . "',
						`tanggalpengangkatannonstaff`='" . $tanggalpengangkatannonstaff . "',
						`tanggalkeluar`      ='" . $tanggalkeluar . "',
						`tipekaryawan`       ='" . $tipekaryawan . "',
						`jumlahanak`         ='" . $jumlahanak . "',
						`jumlahtanggungan`   ='" . $jumlahtanggungan . "',
						`statuspajak`        ='" . $statuspajak . "',
						`npwp`               ='" . $npwp . "',
						`kppnpwp`            ='" . $kppnpwp . "',
						`bpjs`               ='" . $bpjs . "',
						`lokasipenerimaan`   ='" . $lokasipenerimaan . "',
						`kodeorganisasi`     ='" . $kodeorganisasi . "',
						`bagian`             ='" . $bagian . "',
						`subdept`            ='" . $subdept . "',
						`kodejabatan`        ='" . $kodejabatan . "',
						`kodegolongan`       ='" . $kodegolongan . "',
						`pensiun`            ='" . $pensiun . "',
						`lokasitugas`        ='" . $lokasitugas . "',
						`email`              ='" . $email . "',
						`emailkantor`        ='" . $emailkantor . "',
						`alokasi`            ='" . $alokasi . "',
						`subbagian`          ='" . $subbagian . "',
						`jms`                ='" . $jms . "' , 
						`kodecatu`           ='" . $catu . "', 
						`statpremi`          ='" . $_POST['statPremi'] . "',
						`suku`               ='" . $_POST['suku'] . "',
						`statuskaryawan`     ='" . $_POST['statuskaryawan'] . "',
						`sim`                ='" . $_POST['sim'] . "',
						`supbpjs`            ='" . $supbpjs . "',
						`updateby`           ='" . $_SESSION['standard']['userid'] . "',
						`insstatuspajak`     ='" . $insstatuspajak . "', 
						`periodeakhirgaji`   ='" . $periodeakhirgaji . "',
						`kabupaten`          ='" . $param['kabupaten'] . "',
						`kecamatan`          ='" . $param['kecamatan'] . "',
						`desa`               ='" . $param['desa'] . "',
						`bulandaftarbpjs`    ='" . $param['bulandaftarbpjs'] . "',
						`levelkaryawan`      ='" . $param['levelkaryawan'] . "'

						 where karyawanid     ='" . $karyawanid . "'";


					$strxhist = "insert into " . $dbname . ".datakaryawan_hist(
						  `nik`,`namakaryawan`,`karyawanid`,
						  `tempatlahir`,`tanggallahir`,
						  `warganegara`,`jeniskelamin`,
						  `statusperkawinan`,`tanggalmenikah`,
						  `agama`,`golongandarah`,
						  `levelpendidikan`,`alamataktif`,
						  `provinsi`,`kota`,`kodepos`,
						  `noteleponrumah`,`nohp`,`nohp2`,
						  `norekeningbank`,`namabank`,`pemilikrekening`,
						  `sistemgaji`,`no_keluarga`,
						  `noktp`,`notelepondarurat`,
						  `tanggalmasuk`,`tanggalpengangkatan`,`tanggalkeluar`,
						  `tipekaryawan`,`jumlahanak`,
						  `jumlahtanggungan`,`statuspajak`,
						  `npwp`,`kppnpwp`,`bpjs`,`lokasipenerimaan`,`kodeorganisasi`,
						  `bagian`,`subdept`,`kodejabatan`,`kodegolongan`,`pensiun`,
						  `lokasitugas`,`email`,`emailkantor`,`alokasi`,`subbagian`,
						  `jms`,kodecatu,statpremi,suku,statuskaryawan,sim,updateby,
						  insstatuspajak,supbpjs,updatetime,approval_status,periodegaji,
						  version_type,datachange,periodeakhirgaji,
						  kabupaten,kecamatan,desa,diajukan,bulandaftarbpjs,levelkaryawan,`tanggalpengangkatannonstaff`)
						
						values('" . $nik . "','" . $namakaryawan . "','" . $karyawanid . "',
						  '" . $tempatlahir . "','" . $tanggallahir . "',
						  '" . $warganegara . "','" . $jeniskelamin . "',
						  '" . $statusperkawinan . "'," . $tanggalmenikah . ",
						  '" . $agama . "','" . $golongandarah . "',
						  '" . $levelpendidikan . "','" . $alamataktif . "',
						  '" . $provinsi . "','" . $kota . "','" . $kodepos . "',
						  '" . $noteleponrumah . "','" . $nohp . "','" . $nohp2 . "',
						  '" . $norekeningbank . "','" . $namabank . "','" . $pemilikrekening . "',
						  '" . $sistemgaji . "','" . $nopassport . "',
						  '" . $noktp . "','" . $notelepondarurat . "',
						  '" . $tanggalmasuk . "','" . $tanggalpengangkatan . "','" . $tanggalkeluar . "',
						  '" . $tipekaryawan . "','" . $jumlahanak . "',
						  '" . $jumlahtanggungan . "','" . $statuspajak . "',
						  '" . $npwp . "','" . $kppnpwp . "','" . $bpjs . "','" . $lokasipenerimaan . "','" . $kodeorganisasi . "',
						  '" . $bagian . "','" . $subdept . "','" . $kodejabatan . "','" . $kodegolongan . "','" . $pensiun . "',
						  '" . $lokasitugas . "','" . $email . "','" . $emailkantor . "','" . $alokasi . "',
						  '" . $subbagian . "','" . $jms . "','" . $catu . "','" . $_POST['statPremi'] . "',
						  '" . $suku . "','" . $statuskaryawan . "','" . $sim . "','" . $_SESSION['standard']['userid'] . "',
						  '" . $insstatuspajak . "','" . $subbpjs . "','" . date('Y-m-d') . "','1','" . $periodegaji . "',
						  'C','" . $textchange . "','" . $periodeakhirgaji . "',
						  '" . $param['kabupaten'] . "','" . $param['kecamatan'] . "','" . $param['desa'] . "','" . $_SESSION['standard']['userid'] . "',
						  '" . $param['bulandaftarbpjs'] . "','" . $param['levelkaryawan'] . "','" . $tanggalpengangkatannonstaff . "')";
					break;
				default:
					$strhist = "select karyawanid,version_type,version,periodegaji,approval_status from " . $dbname . ".datakaryawan_hist where karyawanid = '" . $karyawanid . "' and approval_status='0' and version='' and periodegaji='" . $periodegaji . "' ";
					$reshist = fetchData($strhist);
					if ($nourut != ''  and $versiontype == 'B') {
						$textchange = '';
						if ($changetmkjmk == 0) {
							$strxhist = "update " . $dbname . ".datakaryawan_hist set 
							`nik`                ='" . $nik . "',
							`namakaryawan`       ='" . $namakaryawan . "',
							`tempatlahir`        ='" . $tempatlahir . "',
							$upload_update
							`tanggallahir`       ='" . $tanggallahir . "',
							`warganegara`        ='" . $warganegara . "',
							`jeniskelamin`       ='" . $jeniskelamin . "',
							`statusperkawinan`   ='" . $statusperkawinan . "',
							`tanggalmenikah`     ='" . $tanggalmenikah . "',
							`agama`              ='" . $agama . "',
							`golongandarah`      ='" . $golongandarah . "',
							`levelpendidikan`    ='" . $levelpendidikan . "',
							`alamataktif`        ='" . $alamataktif . "',
							`provinsi`           ='" . $provinsi . "',
							`kota`               ='" . $kota . "',
							`kodepos`            ='" . $kodepos . "',
							`noteleponrumah`     ='" . $noteleponrumah . "',
							`nohp`               ='" . $nohp . "',
							`nohp2`              ='" . $nohp2 . "',
							`norekeningbank`     ='" . $norekeningbank . "',
							`namabank`           ='" . $namabank . "',
							`pemilikrekening`    ='" . $pemilikrekening . "',
							`sistemgaji`         ='" . $sistemgaji . "',
							`no_keluarga`        ='" . $nopassport . "',
							`noktp`              ='" . $noktp . "',
							`notelepondarurat`   ='" . $notelepondarurat . "',
							`tanggalmasuk`       ='" . $tanggalmasuk . "',
							`tanggalpengangkatan`='" . $tanggalpengangkatan . "',
							`tanggalpengangkatannonstaff`='" . $tanggalpengangkatannonstaff . "',
							`tanggalkeluar`      ='" . $tanggalkeluar . "',
							`tipekaryawan`       ='" . $tipekaryawan . "',
							`jumlahanak`         ='" . $jumlahanak . "',
							`jumlahtanggungan`   ='" . $jumlahtanggungan . "',
							`statuspajak`        ='" . $statuspajak . "',
							`npwp`               ='" . $npwp . "',
							`kppnpwp`            ='" . $kppnpwp . "',
							`bpjs`               ='" . $bpjs . "',
							`lokasipenerimaan`   ='" . $lokasipenerimaan . "',
							`kodeorganisasi`     ='" . $kodeorganisasi . "',
							`bagian`             ='" . $bagian . "',
							`subdept`            ='" . $subdept . "',
							`kodejabatan`        ='" . $kodejabatan . "',
							`kodegolongan`       ='" . $kodegolongan . "',
							`pensiun`            ='" . $pensiun . "',
							`lokasitugas`        ='" . $lokasitugas . "',
							`email`              ='" . $email . "',
							`emailkantor`        ='" . $emailkantor . "',
							`alokasi`            ='" . $alokasi . "',
							`subbagian`          ='" . $subbagian . "',
							`jms`                ='" . $jms . "' , 
							`kodecatu`           ='" . $catu . "', 
							`statpremi`          ='" . $_POST['statPremi'] . "',
							`suku`               ='" . $_POST['suku'] . "',
							`statuskaryawan`     ='" . $_POST['statuskaryawan'] . "',
							`sim`                ='" . $_POST['sim'] . "',
							`supbpjs`            ='" . $supbpjs . "',
							`updateby`           ='" . $_SESSION['standard']['userid'] . "',
							`insstatuspajak`     ='" . $insstatuspajak . "', 
							`datachange`         ='" . $textchange . "', 
							`periodeakhirgaji`   ='" . $periodeakhirgaji . "',
							`kabupaten`          ='" . $param['kabupaten'] . "',
							`kecamatan`          ='" . $param['kecamatan'] . "',
							`desa`               ='" . $param['desa'] . "',
							`bulandaftarbpjs`    ='" . $param['bulandaftarbpjs'] . "',
							`levelkaryawan`    	 ='" . $param['levelkaryawan'] . "'

							where karyawanid     ='" . $karyawanid . "' and
							nourut               ='" . $nourut . "'";
						} else {
							$strxhist = "update " . $dbname . ".datakaryawan_hist set 
							`nik`                ='" . $nik . "',
							`namakaryawan`       ='" . $namakaryawan . "',
							`tempatlahir`        ='" . $tempatlahir . "',
							$upload_update
							`tanggallahir`       ='" . $tanggallahir . "',
							`warganegara`        ='" . $warganegara . "',
							`jeniskelamin`       ='" . $jeniskelamin . "',
							`statusperkawinan`   ='" . $statusperkawinan . "',
							`tanggalmenikah`     ='" . $tanggalmenikah . "',
							`agama`              ='" . $agama . "',
							`golongandarah`      ='" . $golongandarah . "',
							`levelpendidikan`    ='" . $levelpendidikan . "',
							`alamataktif`        ='" . $alamataktif . "',
							`provinsi`           ='" . $provinsi . "',
							`kota`               ='" . $kota . "',
							`kodepos`            ='" . $kodepos . "',
							`noteleponrumah`     ='" . $noteleponrumah . "',
							`nohp`               ='" . $nohp . "',
							`nohp2`              ='" . $nohp2 . "',
							`norekeningbank`     ='" . $norekeningbank . "',
							`namabank`           ='" . $namabank . "',
							`pemilikrekening`    ='" . $pemilikrekening . "',
							`sistemgaji`         ='" . $sistemgaji . "',
							`no_keluarga`        ='" . $nopassport . "',
							`noktp`              ='" . $noktp . "',
							`notelepondarurat`   ='" . $notelepondarurat . "',
							`tanggalmasuk`       ='" . $tanggalmasuk . "',
							`tanggalpengangkatan`='" . $tanggalpengangkatan . "',
							`tanggalpengangkatannonstaff`='" . $tanggalpengangkatannonstaff . "',
							`tanggalkeluar`      ='" . $tanggalkeluar . "',
							`tipekaryawan`       ='" . $tipekaryawan . "',
							`jumlahanak`         ='" . $jumlahanak . "',
							`jumlahtanggungan`   ='" . $jumlahtanggungan . "',
							`statuspajak`        ='" . $statuspajak . "',
							`npwp`               ='" . $npwp . "',
							`kppnpwp`            ='" . $kppnpwp . "',
							`bpjs`               ='" . $bpjs . "',
							`lokasipenerimaan`   ='" . $lokasipenerimaan . "',
							`kodeorganisasi`     ='" . $kodeorganisasi . "',
							`bagian`             ='" . $bagian . "',
							`subdept`            ='" . $subdept . "',
							`kodejabatan`        ='" . $kodejabatan . "',
							`kodegolongan`       ='" . $kodegolongan . "',
							`pensiun`            ='" . $pensiun . "',
							`lokasitugas`        ='" . $lokasitugas . "',
							`email`              ='" . $email . "',
							`emailkantor`        ='" . $emailkantor . "',
							`alokasi`            ='" . $alokasi . "',
							`subbagian`          ='" . $subbagian . "',
							`jms`                ='" . $jms . "' , 
							`kodecatu`           ='" . $catu . "', 
							`statpremi`          ='" . $_POST['statPremi'] . "',
							`suku`               ='" . $_POST['suku'] . "',
							`statuskaryawan`     ='" . $_POST['statuskaryawan'] . "',
							`sim`                ='" . $_POST['sim'] . "',
							`supbpjs`            ='" . $supbpjs . "',
							`updateby`           = '" . $_SESSION['standard']['userid'] . "',
							`insstatuspajak`     = '" . $insstatuspajak . "', 
							`datachange`         = '" . $textchange . "', 
							`periodeakhirgaji`   ='" . $periodeakhirgaji . "', 
							`tmkjamsostek`       ='" . $tanggalmasuk . "',
							`kabupaten`          ='" . $param['kabupaten'] . "',
							`kecamatan`          ='" . $param['kecamatan'] . "',
							`desa`               ='" . $param['desa'] . "',
							`bulandaftarbpjs`    ='" . $param['bulandaftarbpjs'] . "',
							`levelkaryawan`      ='" . $param['levelkaryawan'] . "'
							where karyawanid     ='" . $karyawanid . "' and
							nourut               ='" . $nourut . "'";
						}
					} elseif (count($reshist) > 0) {
						if ($changetmkjmk == 0) {
							$strxhist = "update " . $dbname . ".datakaryawan_hist set 
							`nik`                ='" . $nik . "',
							`namakaryawan`       ='" . $namakaryawan . "',
							`tempatlahir`        ='" . $tempatlahir . "',
							$upload_update
							`tanggallahir`       ='" . $tanggallahir . "',
							`warganegara`        ='" . $warganegara . "',
							`jeniskelamin`       ='" . $jeniskelamin . "',
							`statusperkawinan`   ='" . $statusperkawinan . "',
							`tanggalmenikah`     ='" . $tanggalmenikah . "',
							`agama`              ='" . $agama . "',
							`golongandarah`      ='" . $golongandarah . "',
							`levelpendidikan`    ='" . $levelpendidikan . "',
							`alamataktif`        ='" . $alamataktif . "',
							`provinsi`           ='" . $provinsi . "',
							`kota`               ='" . $kota . "',
							`kodepos`            ='" . $kodepos . "',
							`noteleponrumah`     ='" . $noteleponrumah . "',
							`nohp`               ='" . $nohp . "',
							`nohp2`              ='" . $nohp2 . "',
							`norekeningbank`     ='" . $norekeningbank . "',
							`namabank`           ='" . $namabank . "',
							`pemilikrekening`    ='" . $pemilikrekening . "',
							`sistemgaji`         ='" . $sistemgaji . "',
							`no_keluarga`        ='" . $nopassport . "',
							`noktp`              ='" . $noktp . "',
							`notelepondarurat`   ='" . $notelepondarurat . "',
							`tanggalmasuk`       ='" . $tanggalmasuk . "',
							`tanggalpengangkatan`='" . $tanggalpengangkatan . "',
							`tanggalpengangkatannonstaff`='" . $tanggalpengangkatannonstaff . "',
							`tanggalkeluar`      ='" . $tanggalkeluar . "',
							`tipekaryawan`       ='" . $tipekaryawan . "',
							`jumlahanak`         ='" . $jumlahanak . "',
							`jumlahtanggungan`   ='" . $jumlahtanggungan . "',
							`statuspajak`        ='" . $statuspajak . "',
							`npwp`               ='" . $npwp . "',
							`kppnpwp`            ='" . $kppnpwp . "',
							`bpjs`               ='" . $bpjs . "',
							`lokasipenerimaan`   ='" . $lokasipenerimaan . "',
							`kodeorganisasi`     ='" . $kodeorganisasi . "',
							`bagian`             ='" . $bagian . "',
							`subdept`            ='" . $subdept . "',
							`kodejabatan`        ='" . $kodejabatan . "',
							`kodegolongan`       ='" . $kodegolongan . "',
							`pensiun`            ='" . $pensiun . "',
							`lokasitugas`        ='" . $lokasitugas . "',
							`email`              ='" . $email . "',
							`emailkantor`        ='" . $emailkantor . "',
							`alokasi`            ='" . $alokasi . "',
							`subbagian`          ='" . $subbagian . "',
							`jms`                ='" . $jms . "' , 
							`kodecatu`           ='" . $catu . "', 
							`statpremi`          ='" . $_POST['statPremi'] . "',
							`suku`               ='" . $_POST['suku'] . "',
							`statuskaryawan`     ='" . $_POST['statuskaryawan'] . "',
							`sim`                ='" . $_POST['sim'] . "',
							`supbpjs`            ='" . $supbpjs . "',
							`updateby`           = '" . $_SESSION['standard']['userid'] . "',
							`insstatuspajak`     = '" . $insstatuspajak . "', 
							`datachange`         = '" . $textchange . "', 
							`periodeakhirgaji`   ='" . $periodeakhirgaji . "',
							`kabupaten`          ='" . $param['kabupaten'] . "',
							`kecamatan`          ='" . $param['kecamatan'] . "',
							`desa`               ='" . $param['desa'] . "',
							`bulandaftarbpjs`    ='" . $param['bulandaftarbpjs'] . "',
							`levelkaryawan`    ='" . $param['levelkaryawan'] . "'
							where karyawanid     ='" . $karyawanid . "' and
							approval_status      ='0' and 
							version              ='' and 
							periodegaji          ='" . $periodegaji . "'";
						} else {
							$strxhist = "update " . $dbname . ".datakaryawan_hist set 
							`nik`                ='" . $nik . "',
							`namakaryawan`       ='" . $namakaryawan . "',
							`tempatlahir`        ='" . $tempatlahir . "',
							$upload_update
							`tanggallahir`       ='" . $tanggallahir . "',
							`warganegara`        ='" . $warganegara . "',
							`jeniskelamin`       ='" . $jeniskelamin . "',
							`statusperkawinan`   ='" . $statusperkawinan . "',
							`tanggalmenikah`     ='" . $tanggalmenikah . "',
							`agama`              ='" . $agama . "',
							`golongandarah`      ='" . $golongandarah . "',
							`levelpendidikan`    ='" . $levelpendidikan . "',
							`alamataktif`        ='" . $alamataktif . "',
							`provinsi`           ='" . $provinsi . "',
							`kota`               ='" . $kota . "',
							`kodepos`            ='" . $kodepos . "',
							`noteleponrumah`     ='" . $noteleponrumah . "',
							`nohp`               ='" . $nohp . "',
							`nohp2`              ='" . $nohp2 . "',
							`norekeningbank`     ='" . $norekeningbank . "',
							`namabank`           ='" . $namabank . "',
							`pemilikrekening`    ='" . $pemilikrekening . "',
							`sistemgaji`         ='" . $sistemgaji . "',
							`no_keluarga`        ='" . $nopassport . "',
							`noktp`              ='" . $noktp . "',
							`notelepondarurat`   ='" . $notelepondarurat . "',
							`tanggalmasuk`       ='" . $tanggalmasuk . "',
							`tanggalpengangkatan`='" . $tanggalpengangkatan . "',
							`tanggalpengangkatannonstaff`='" . $tanggalpengangkatannonstaff . "',
							`tanggalkeluar`      ='" . $tanggalkeluar . "',
							`tipekaryawan`       ='" . $tipekaryawan . "',
							`jumlahanak`         ='" . $jumlahanak . "',
							`jumlahtanggungan`   ='" . $jumlahtanggungan . "',
							`statuspajak`        ='" . $statuspajak . "',
							`npwp`               ='" . $npwp . "',
							`kppnpwp`            ='" . $kppnpwp . "',
							`bpjs`               ='" . $bpjs . "',
							`lokasipenerimaan`   ='" . $lokasipenerimaan . "',
							`kodeorganisasi`     ='" . $kodeorganisasi . "',
							`bagian`             ='" . $bagian . "',
							`subdept`            ='" . $subdept . "',
							`kodejabatan`        ='" . $kodejabatan . "',
							`kodegolongan`       ='" . $kodegolongan . "',
							`pensiun`            ='" . $pensiun . "',
							`lokasitugas`        ='" . $lokasitugas . "',
							`email`              ='" . $email . "',
							`emailkantor`        ='" . $emailkantor . "',
							`alokasi`            ='" . $alokasi . "',
							`subbagian`          ='" . $subbagian . "',
							`jms`                ='" . $jms . "' , 
							`kodecatu`           ='" . $catu . "', 
							`statpremi`          ='" . $_POST['statPremi'] . "',
							`suku`               ='" . $_POST['suku'] . "',
							`statuskaryawan`     ='" . $_POST['statuskaryawan'] . "',
							`sim`                ='" . $_POST['sim'] . "',
							`supbpjs`            ='" . $supbpjs . "',
							`updateby`           = '" . $_SESSION['standard']['userid'] . "',
							`insstatuspajak`     = '" . $insstatuspajak . "', 
							`datachange`         = '" . $textchange . "', 
							`periodeakhirgaji`   ='" . $periodeakhirgaji . "', 
							`tmkjamsostek`       ='" . $tanggalmasuk . "',
							`kabupaten`          ='" . $param['kabupaten'] . "',
							`kecamatan`          ='" . $param['kecamatan'] . "',
							`desa`               ='" . $param['desa'] . "',
							`bulandaftarbpjs`    ='" . $param['bulandaftarbpjs'] . "',
							`levelkaryawan`      ='" . $param['levelkaryawan'] . "'
							where karyawanid     ='" . $karyawanid . "' and
							approval_status      ='0' and 
							version              ='' and 
							periodegaji          ='" . $periodegaji . "'";
						}
					} else {
						if ($changetmkjmk == 0) {
							$strxhist = "insert into " . $dbname . ".datakaryawan_hist(
							  `nik`,`namakaryawan`,`karyawanid`,
							  `tempatlahir`,`tanggallahir`,
							  `warganegara`,`jeniskelamin`,
							  `statusperkawinan`,`tanggalmenikah`,
							  `agama`,`golongandarah`,
							  `levelpendidikan`,`alamataktif`,
							  `provinsi`,`kota`,`kodepos`,
							  `noteleponrumah`,`nohp`,`nohp2`,
							  `norekeningbank`,`namabank`,`pemilikrekening`,
							  `sistemgaji`,`no_keluarga`,
							  `noktp`,`notelepondarurat`,
							  `tanggalmasuk`,`tanggalpengangkatan`,`tanggalkeluar`,
							  `tipekaryawan`,`jumlahanak`,
							  `jumlahtanggungan`,`statuspajak`,
							  `npwp`,`kppnpwp`,`bpjs`,`lokasipenerimaan`,`kodeorganisasi`,
							  `bagian`,`subdept`,`kodejabatan`,`kodegolongan`,`pensiun`,
							  `lokasitugas`,`email`,`emailkantor`,`alokasi`,`subbagian`,
							  `jms`,kodecatu,statpremi,suku,statuskaryawan,sim,updateby,
							  insstatuspajak,supbpjs,updatetime,approval_status,periodegaji,
							  version_type,datachange,periodeakhirgaji,
							  kabupaten,kecamatan,desa,`bulandaftarbpjs`,`levelkaryawan`,`tanggalpengangkatannonstaff`)
							
							values('" . $nik . "','" . $namakaryawan . "','" . $karyawanid . "',
							  '" . $tempatlahir . "','" . $tanggallahir . "',
							  '" . $warganegara . "','" . $jeniskelamin . "',
							  '" . $statusperkawinan . "','" . $tanggalmenikah . "',
							  '" . $agama . "','" . $golongandarah . "',
							  '" . $levelpendidikan . "','" . $alamataktif . "',
							  '" . $provinsi . "','" . $kota . "','" . $kodepos . "',
							  '" . $noteleponrumah . "','" . $nohp . "','" . $nohp2 . "',
							  '" . $norekeningbank . "','" . $namabank . "','" . $pemilikrekening . "',
							  '" . $sistemgaji . "','" . $nopassport . "',
							  '" . $noktp . "','" . $notelepondarurat . "',
							  '" . $tanggalmasuk . "','" . $tanggalpengangkatan . "','" . $tanggalkeluar . "',
							  '" . $tipekaryawan . "','" . $jumlahanak . "',
							  '" . $jumlahtanggungan . "','" . $statuspajak . "',
							  '" . $npwp . "','" . $kppnpwp . "','" . $bpjs . "','" . $lokasipenerimaan . "','" . $kodeorganisasi . "',
							  '" . $bagian . "','" . $subdept . "','" . $kodejabatan . "','" . $kodegolongan . "','" . $pensiun . "',
							  '" . $lokasitugas . "','" . $email . "','" . $emailkantor . "','" . $alokasi . "',
							  '" . $subbagian . "','" . $jms . "','" . $catu . "','" . $_POST['statPremi'] . "',
							  '" . $suku . "','" . $statuskaryawan . "','" . $sim . "','" . $_SESSION['standard']['userid'] . "',
							  '" . $insstatuspajak . "','" . $subbpjs . "','" . date('Y-m-d') . "','0','" . $periodegaji . "',
							  'C','" . $textchange . "','" . $periodeakhirgaji . "',
							  '" . $param['kabupaten'] . "','" . $param['kecamatan'] . "','" . $param['desa'] . "',
							  '" . $param['bulandaftarbpjs'] . "','" . $param['levelkaryawan'] . "','" . $tanggalpengangkatannonstaff . "')";
						} else {
							$strxhist = "insert into " . $dbname . ".datakaryawan_hist(
							  `nik`,`namakaryawan`,`karyawanid`,
							  `tempatlahir`,`tanggallahir`,
							  `warganegara`,`jeniskelamin`,
							  `statusperkawinan`,`tanggalmenikah`,
							  `agama`,`golongandarah`,
							  `levelpendidikan`,`alamataktif`,
							  `provinsi`,`kota`,`kodepos`,
							  `noteleponrumah`,`nohp`,`nohp2`,
							  `norekeningbank`,`namabank`,`pemilikrekening`,
							  `sistemgaji`,`no_keluarga`,
							  `noktp`,`notelepondarurat`,
							  `tanggalmasuk`,`tanggalpengangkatan`,`tanggalkeluar`,
							  `tipekaryawan`,`jumlahanak`,
							  `jumlahtanggungan`,`statuspajak`,
							  `npwp`,`kppnpwp`,`bpjs`,`lokasipenerimaan`,`kodeorganisasi`,
							  `bagian`,`subdept`,`kodejabatan`,`kodegolongan`,`pensiun`,
							  `lokasitugas`,`email`,`emailkantor`,`alokasi`,`subbagian`,
							  `jms`,kodecatu,statpremi,suku,statuskaryawan,sim,updateby,
							  insstatuspajak,supbpjs,updatetime,approval_status,periodegaji,
							  version_type,datachange,periodeakhirgaji,tmkjamsostek,
							  kabupaten,kecamatan,desa,bulandaftarbpjs,levelkaryawan,tanggalpengangkatannonstaff)
								  
							values('" . $nik . "','" . $namakaryawan . "','" . $karyawanid . "',
							  '" . $tempatlahir . "','" . $tanggallahir . "',
							  '" . $warganegara . "','" . $jeniskelamin . "',
							  '" . $statusperkawinan . "','" . $tanggalmenikah . "',
							  '" . $agama . "','" . $golongandarah . "',
							  '" . $levelpendidikan . "','" . $alamataktif . "',
							  '" . $provinsi . "','" . $kota . "','" . $kodepos . "',
							  '" . $noteleponrumah . "','" . $nohp . "','" . $nohp2 . "',
							  '" . $norekeningbank . "','" . $namabank . "','" . $pemilikrekening . "',
							  '" . $sistemgaji . "','" . $nopassport . "',
							  '" . $noktp . "','" . $notelepondarurat . "',
							  '" . $tanggalmasuk . "','" . $tanggalpengangkatan . "','" . $tanggalkeluar . "',
							  '" . $tipekaryawan . "','" . $jumlahanak . "',
							  '" . $jumlahtanggungan . "','" . $statuspajak . "',
							  '" . $npwp . "','" . $kppnpwp . "','" . $bpjs . "','" . $lokasipenerimaan . "','" . $kodeorganisasi . "',
							  '" . $bagian . "','" . $subdept . "','" . $kodejabatan . "','" . $kodegolongan . "','" . $pensiun . "',
							  '" . $lokasitugas . "','" . $email . "','" . $emailkantor . "','" . $alokasi . "',
							  '" . $subbagian . "','" . $jms . "','" . $catu . "','" . $_POST['statPremi'] . "',
							  '" . $suku . "','" . $statuskaryawan . "','" . $sim . "','" . $_SESSION['standard']['userid'] . "',
							  '" . $insstatuspajak . "','" . $subbpjs . "','" . date('Y-m-d') . "','0','" . $periodegaji . "',
							  'C','" . $textchange . "','" . $periodeakhirgaji . "','" . $tanggalmasuk . "',
							  '" . $param['kabupaten'] . "','" . $param['kecamatan'] . "','" . $param['desa'] . "',
							  '" . $param['bulandaftarbpjs'] . "','" . $param['levelkaryawan'] . "','" . $tanggalpengangkatannonstaff . "')";
						}
					}
					break;
			}

			logData($oldData, $param);
		} else {
			exit("error : Cek Nomor KTP, sudah pernah terdaftar di sistem an. " . $nmkaryktp . ", Unit : " . $loktgs . "");
		}
		break;
	case 'insert':

		if ($tanggalmasuk == '0000-00-00' or $tanggalmasuk == '--' or $tanggalmasuk == '' or $tanggalmasuk == '00000000') {
			exit("Warning : Tanggal Masuk tidak boleh kosong.");
		}
		if ($lokasitugas == '') {
			exit("Warning: Lokasi tugas masih kosong.");
		}
		if ($kodeorganisasi == '') {
			exit("Warning: PT masih kosong");
		}
		if ($kodejabatan == '') {
			exit("Warning : Jabatan harus diisi.");
		}
		if ($tipekaryawan != '14') {
			if ($tipekaryawan == '4' and $sistemgaji != 'Harian') {
				exit("Warning : Tipe karyawan dan Sistem Gaji tidak sesuai.");
			}
			if ($tipekaryawan != '4' and $sistemgaji == 'Harian') {
				exit("Warning : Tipe karyawan dan Sistem Gaji tidak sesuai.");
			}
		}


		if ($tipekaryawan == '4' and ($jms != '' or $bpjs != '') and ($param['bulandaftarbpjs'] == '--' or $param['bulandaftarbpjs'] == '')) {
			exit("Warning : Tanggal daftar BPJS harus diisi.");
		}

		if ($provinsi == '') {
			exit("Warning : Propinsi harus diisi.");
		}

		if ($param['kabupaten'] == '') {
			exit("Warning : Kabupaten / Kota harus diisi.");
		}

		if ($param['kecamatan'] == '') {
			exit("Warning : Kecamatan harus diisi.");
		}

		if ($param['desa'] == '') {
			exit("Warning : Desa / Kelurahan harus diisi.");
		}

		if ($param['kodegolongan'] == '') {
			exit("Warning : Golongan wajib diisi.");
		}

		// if($kodepos==''){
		// 	exit("Warning : Kode pos harus diisi.");
		// }

		if ($alamataktif == '') {
			exit("Warning : Alamat KTP harus diisi.");
		}

		if ($param['kodeorganisasi'] != 'PPP') {
			if ($levelkaryawan == '') {
				exit("Warning : Level Karyawan harus diisi.");
			}
		}

		if (strlen($noktp) != 16) {
			exit("Warning : Nomor KTP harus 16 digit.");
		}

		if ($tipekaryawan == '0' or $tipekaryawan == '1') {

			if ($nopassport == '') {
				exit("Warning : Karyawan STAFF dan NON Staff, Nomor Kartu Keluarga harus diisi.");
			}
			if ($tanggalpengangkatan == '') {
				exit("Warning : Karyawan STAFF dan NON Staff, Tanggal pengangkatan harus diisi.");
			}
			if (strlen($nopassport) != 16) {
				exit("Warning : Karyawan STAFF dan NON Staff, Nomor Kartu Keluarga harus 16 digit.");
			}
			if ($norekeningbank == '') {
				exit("Warning : Karyawan STAFF dan NON Staff, Nomor rekening wajib diisi");
			}
			if ($namabank == '') {
				exit("Warning : Karyawan STAFF dan NON Staff, Nama bank wajib diisi");
			}

			if ($pemilikrekening == '') {
				exit("Warning : Karyawan STAFF dan NON Staff, Pemilik rekening wajib diisi");
			}
		}

		if ($tanggalkeluar == '00000000' && $statuskaryawan == 'Keluar') {
			exit("Warning : Tanggal keluar harus diisi saat status karyawan Keluar.");
		}
		if ($tanggalkeluar != '00000000' && $statuskaryawan != 'Keluar') {
			exit("Warning : Tanggal keluar tidak boleh diisi saat status karyawan tidak keluar.");
		}

		validasiktp($noktp);

		$cekloktugas = false;
		$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where induk = '" . $param['kodeorganisasi'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($param['lokasitugas'] == $bar['kodeorganisasi']) {
				$cekloktugas = true;
			}
		}

		if ($cekloktugas == false) {
			exit("Warning : Lokasi tugas tidak sesuai.");
		}

		if ($param['subbagian'] != '0' and $param['subbagian'] != '') {
			$ceksubbagian = false;
			$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where induk = '" . $param['lokasitugas'] . "'";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				if ($param['subbagian'] == $bar['kodeorganisasi']) {
					$ceksubbagian = true;
				}
			}
			if ($ceksubbagian == false) {
				exit("Warning : Divisi tidak sesuai.<br>Lokasi Tugas : " . $param['lokasitugas'] . "<br>Divisi : " . $param['subbagian'] . "");
			}
		}

		$periodexxx = "''";
		$cekperiode = "select periodegaji from " . $dbname . ".datakaryawan_hist where lokasitugas='" . $lokasitugas . "' and version_type='B' group by periodegaji ";
		$resperiode = $owlPDO->query($cekperiode) or die(print " Gagal: " . PDOException::getMessage());
		$resperiode->setFetchMode(PDO::FETCH_OBJ);
		while ($barperiode = $resperiode->fetch()) {
			if ($periodexxx == '') {
				$periodexxx = "'" . $barperiode->periodegaji . "'";
			} else {
				$periodexxx .= ",'" . $barperiode->periodegaji . "'";
			}
		}

		$periodegaji = '';
		if ($tipekaryawan == '4') {
			$str = "select min(periode) as periode from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $lokasitugas . "' and jenisgaji='H' and sudahproses=0 and periode not in (" . $periodexxx . ") 
	          order by periode desc";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$periodegaji = $bar['periode'];
		} else {
			$str = "select min(periode) as periode from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $lokasitugas . "' and jenisgaji='B' and sudahproses=0 and periode not in (" . $periodexxx . ") 
	          order by periode desc";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$periodegaji = $bar['periode'];
		}

		$no = 1;
		$str = "select * from " . $dbname . ".datakaryawan where kodeorganisasi = '" . $kodeorganisasi . "' and lokasitugas='" . $lokasitugas . "'";
		$res = fetchData($str);
		$genNik = getnomornik($no);
		foreach ($res as $bar) {
			if (ceknonik($genNik) > 0) {
				$no += 1;
				$genNik = getnomornik($no);
			} else {
				continue;
			}
		}

		if (substr($genNik, 0, 1) == '-') {
			exit("Warning : Format NIK salah.");
		}

		## Validasi photo wajib diisi
		if ($photo == '') {
			exit("Warning : Foto wajib di upload...");
		}

		if ($photo != "") {
			$renamephoto = "photo_" . $genNik;
			$path = "./photokaryawan/";
			$uploadname = upload_photo($path, $photo, $renamephoto);
		} else {
			$uploadname = "";
		}
		if (stripos($uploadname, "error") !== false) {
			exit($uploadname);
		}
		$ktp = $nmkaryktp = $loktgs = '';
		$cekktp = "select noktp, namakaryawan, lokasitugas from " . $dbname . ".datakaryawan where noktp='" . $noktp . "' ";
		$resktp = $owlPDO->query($cekktp) or die(print " Gagal: " . PDOException::getMessage());
		$resktp->setFetchMode(PDO::FETCH_OBJ);
		while ($baru = $resktp->fetch()) {
			$ktp = $baru->noktp;
			$nmkaryktp = $baru->namakaryawan;
			$loktgs = $baru->lokasitugas;
		}

		if ($tanggalkeluar == '') {
			$tanggalkeluar = '0000-00-00';
		}

		if ($ktp == '' || $ktp == 0) {
			$strx = "insert into " . $dbname . ".datakaryawan(
			  `nik`,`namakaryawan`,`photo`,
			  `tempatlahir`,`tanggallahir`,
			  `warganegara`,`jeniskelamin`,
			  `statusperkawinan`,`tanggalmenikah`,
			  `agama`,`golongandarah`,
			  `levelpendidikan`,`alamataktif`,
			  `provinsi`,`kota`,`kodepos`,
			  `noteleponrumah`,`nohp`,`nohp2`,
			  `norekeningbank`,`namabank`,`pemilikrekening`,
			  `sistemgaji`,`no_keluarga`,
			  `noktp`,`notelepondarurat`,
			  `tanggalmasuk`,`tanggalpengangkatan`,`tanggalkeluar`,
			  `tipekaryawan`,`jumlahanak`,
			  `jumlahtanggungan`,`statuspajak`,
			  `npwp`,`kppnpwp`,`bpjs`,`lokasipenerimaan`,`kodeorganisasi`,
			  `bagian`,`subdept`,`kodejabatan`,`kodegolongan`,`pensiun`, 
			  `lokasitugas`,`email`,`emailkantor`,`alokasi`, `subbagian`, 
			  `jms`,kodecatu,statpremi,suku,statuskaryawan,sim,updateby,
			  insstatuspajak,supbpjs,noerf,periodeakhirgaji,tmkjamsostek,
			  kabupaten,kecamatan,desa,bulandaftarbpjs,statusapproval,levelkaryawan,tanggalpengangkatannonstaff)
		
			values('" . $genNik . "','" . $namakaryawan . "','" . $uploadname . "',
			  '" . $tempatlahir . "'," . $tanggallahir . ",
			  '" . $warganegara . "','" . $jeniskelamin . "',
			  '" . $statusperkawinan . "'," . $tanggalmenikah . ",
			  '" . $agama . "','" . $golongandarah . "',
			  " . $levelpendidikan . ",'" . $alamataktif . "',
			  '" . $provinsi . "','" . $kota . "','" . $kodepos . "',
			  '" . $noteleponrumah . "','" . $nohp . "','" . $nohp2 . "',
			  '" . $norekeningbank . "','" . $namabank . "','" . $pemilikrekening . "',
			  '" . $sistemgaji . "','" . $nopassport . "',
			  '" . $noktp . "','" . $notelepondarurat . "',
			  " . $tanggalmasuk . "," . $tanggalpengangkatan . ",'1990-01-01',
			  " . $tipekaryawan . "," . $jumlahanak . ",
			  " . $jumlahtanggungan . ",'" . $statuspajak . "',
			  '" . $npwp . "','" . $kppnpwp . "','" . $bpjs . "','" . $lokasipenerimaan . "','" . $kodeorganisasi . "',
			  '" . $bagian . "','" . $subdept . "'," . $kodejabatan . ",'" . $kodegolongan . "','" . $pensiun . "',
			  '" . $lokasitugas . "','" . $email . "','" . $emailkantor . "'," . $alokasi . ",
			  '" . $subbagian . "','" . $jms . "','" . $catu . "','" . $_POST['statPremi'] . "',
			  '" . $suku . "','" . $statuskaryawan . "','" . $sim . "','" . $_SESSION['standard']['userid'] . "',
			  '" . $insstatuspajak . "','" . $subbpjs . "','" . $noerf . "','" . $periodeakhirgaji . "',
			  '" . $tanggalmasuk . "','" . $param['kabupaten'] . "','" . $param['kecamatan'] . "',
			  '" . $param['desa'] . "','" . $param['bulandaftarbpjs'] . "','0','" . $param['levelkaryawan'] . "','" . $tanggalpengangkatannonstaff . "')";

			if ($strx != '') {
				try {
					$owlPDO->exec($strx);
					$karyawanidz = $owlPDO->lastInsertId();
					$strx = '';
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
				}
			}

			$strxhist = "insert into " . $dbname . ".datakaryawan_hist(`karyawanid`,
			  `nik`,`namakaryawan`,`photo`,
			  `tempatlahir`,`tanggallahir`,
			  `warganegara`,`jeniskelamin`,
			  `statusperkawinan`,`tanggalmenikah`,
			  `agama`,`golongandarah`,
			  `levelpendidikan`,`alamataktif`,
			  `provinsi`,`kota`,`kodepos`,
			  `noteleponrumah`,`nohp`,`nohp2`,
			  `norekeningbank`,`namabank`,`pemilikrekening`,
			  `sistemgaji`,`no_keluarga`,
			  `noktp`,`notelepondarurat`,
			  `tanggalmasuk`,`tanggalpengangkatan`,`tanggalkeluar`,
			  `tipekaryawan`,`jumlahanak`,
			  `jumlahtanggungan`,`statuspajak`,
			  `npwp`,`kppnpwp`,`bpjs`,`lokasipenerimaan`,`kodeorganisasi`,
			  `bagian`,`subdept`,`kodejabatan`,`kodegolongan`,`pensiun`,
			  `lokasitugas`,`email`,`emailkantor`,`alokasi`,`subbagian`,
			  `jms`,kodecatu,statpremi,suku,statuskaryawan,sim,updateby,
			  insstatuspajak,supbpjs,updatetime,approval_status,periodegaji,
			  version_type,periodeakhirgaji,tmkjamsostek,kabupaten,kecamatan,
			  desa,bulandaftarbpjs,levelkaryawan,tanggalpengangkatannonstaff)
			values('" . $karyawanidz . "','" . $genNik . "','" . $namakaryawan . "','" . $uploadname . "',
			  '" . $tempatlahir . "','" . $tanggallahir . "',
			  '" . $warganegara . "','" . $jeniskelamin . "',
			  '" . $statusperkawinan . "'," . $tanggalmenikah . ",
			  '" . $agama . "','" . $golongandarah . "',
			  '" . $levelpendidikan . "','" . $alamataktif . "',
			  '" . $provinsi . "','" . $kota . "','" . $kodepos . "',
			  '" . $noteleponrumah . "','" . $nohp . "','" . $nohp2 . "',
			  '" . $norekeningbank . "','" . $namabank . "','" . $pemilikrekening . "',
			  '" . $sistemgaji . "','" . $nopassport . "',
			  '" . $noktp . "','" . $notelepondarurat . "',
			  '" . $tanggalmasuk . "','" . $tanggalpengangkatan . "','" . $tanggalkeluar . "',
			  '" . $tipekaryawan . "','" . $jumlahanak . "',
			  '" . $jumlahtanggungan . "','" . $statuspajak . "',
			  '" . $npwp . "','" . $kppnpwp . "','" . $bpjs . "','" . $lokasipenerimaan . "','" . $kodeorganisasi . "',
			  '" . $bagian . "','" . $subdept . "','" . $kodejabatan . "','" . $kodegolongan . "','" . $pensiun . "',
			  '" . $lokasitugas . "','" . $email . "','" . $emailkantor . "','" . $alokasi . "',
			  '" . $subbagian . "','" . $jms . "','" . $catu . "','" . $_POST['statPremi'] . "','" . $suku . "',
			  '" . $statuskaryawan . "','" . $sim . "','" . $_SESSION['standard']['userid'] . "',
			  '" . $insstatuspajak . "','" . $subbpjs . "','" . date('Y-m-d') . "','0','" . $periodegaji . "',
			  'N','" . $periodeakhirgaji . "','" . $tanggalmasuk . "','" . $param['kabupaten'] . "',
			  '" . $param['kecamatan'] . "','" . $param['desa'] . "','" . $param['bulandaftarbpjs'] . "','" . $param['levelkaryawan'] . "','" . $tanggalpengangkatannonstaff . "')";
		} else {
			exit("error : Nomor KTP sudah pernah terdaftar an. " . $nmkaryktp . ", Unit : " . $loktgs . "");
		}
		break;
	default:
		$strx = "";
		break;
}

// echo $strxhist;
// exit("error");

if ($strx != '') {
	try {
		$owlPDO->exec($strx);
	} catch (PDOException $e) {
		print " Gagal  !: " . $e->getMessage() . "\n";
	}
}
if ($strxhist != '') {
	try {
		$owlPDO->exec($strxhist);
	} catch (PDOException $e) {
		print " Gagal  !: " . $e->getMessage() . "\n";
	}
}

//whenever not deleting, return value as below to javascript
if ($method != 'delete' and $method != 'form_ajukan') {
	$karid = '';
	$nama = '';
	$str = "select karyawanid,namakaryawan,nik from " . $dbname . ".datakaryawan where
			  namakaryawan='" . $namakaryawan . "' and tanggallahir='" . $tanggallahir . "'";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while ($bar = $res->fetch()) {
		$karid = $bar->karyawanid;
		$nama = $bar->namakaryawan;
		$nik = $bar->nik;
	}
	//return XML format
	echo "<?xml version='1.0' ?>
			 <karyawan>
			 <karyawanid>" . $karid . "</karyawanid>
			 <namakaryawan>" . $nama . "</namakaryawan>
			 <nik>" . $nik . "</nik>
			 </karyawan>";
}

if ($method == 'update') {
	$str = "update " . $dbname . ".user set kodeorg = '" . $lokasitugas . "' where karyawanid = '" . $karyawanid . "'";
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		print " Gagal  !: " . $e->getMessage() . "\n";
	}
}

/**
 * Log History
 */
function logData($oldData, $newData)
{
	global $karyawanid;
	global $dbname;
	global $owlPDO;
	// Stat Premi
	$newData['statpremi'] = $newData['statPremi'];
	unset($newData['statPremi']);

	// Cek Data
	$dataChange = array();
	foreach ($oldData as $key => $row) {
		if (isset($newData[$key])) {
			if ($row != $newData[$key]) {
				$dataChange[$key] = array(
					'old' => $row,
					'new' => $newData[$key]
				);
			}
		}
	}

	if (!empty($dataChange)) {
		$dataHist = array(
			'updatetime' => date('Y-m-d H:i:s'),
			'updateby' => $_SESSION['standard']['userid'],
			'karyawanid' => $karyawanid,
			'data' => json_encode($dataChange)
		);
		$qHist = insertQuery($dbname, 'hist_datakaryawan', $dataHist);
		$owlPDO->exec($qHist);
	}
}

function getnomornik($tambah = 1)
{
	global $karyawanid;
	global $kodeorganisasi;
	global $lokasitugas;
	global $tanggalmasuk;
	global $tipekaryawan;
	global $dbname;
	global $owlPDO;

	$time = strtotime($tanggalmasuk);

	$firstnik = date('y', $time);
	$sectnik = date('m', $time);
	// exit("error: tanggalmasuk ".$sectnik." ");

	$nik = '';
	$jumlahcounter = '';
	$counter = '';
	$tmk = '';

	## Ambil setup format NIK
	$str = "select count(kodept) as jlhitem,tipekaryawan,jumlahcounter, counter, tmk,tglbulan from " . $dbname . ".sdm_5formatnik where kodept = '" . $kodeorganisasi . "' and tipekaryawan like '%" . $tipekaryawan . "%'";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();
	$cek_data = $bar['jlhitem'];
	$list_tpkar = $bar['tipekaryawan'];
	$jumlahcounter = $bar['jumlahcounter'];
	$counter = $bar['counter'];
	$tmk = $bar['tmk'];
	$tglbulan = $bar['tglbulan'];

	## Cek sudah disetup kan atau belum
	if ($cek_data == '' || $cek_data == 0 || $cek_data < 1) {
		exit("Warning : Setup format NIK belum ada, silahkan disetupkan terlebih dahulu untuk PT = <b> " . getNamaOrg($kodeorganisasi) . "");
	}

	$str = "select max(substr(nik,- " . $jumlahcounter . ")) as noUrut from " . $dbname . ".datakaryawan where kodeorganisasi = '" . $kodeorganisasi . "' and lokasitugas='" . $lokasitugas . "' and tipekaryawan in (" . $list_tpkar . ") ";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();
	$nourut = intval($bar['noUrut']);
	$nourut = $nourut + $counter;
	$nourut = str_pad($nourut, $jumlahcounter, "0", STR_PAD_LEFT);

	## Cek pakai TMK tidak
	if ($tmk == 1) {
		if ($tglbulan == 1) {
			$firstnik = date('m', $time);
			$sectnik = date('y', $time);
		}
		$nik = $kodeorganisasi . $lokasitugas . $firstnik . $sectnik . $nourut;
	} else {
		$nik = $kodeorganisasi . $lokasitugas . $nourut;
	}

	return $nik;
}

function ceknonik($nik)
{
	global $karyawanid;
	global $kodeorganisasi;
	global $lokasitugas;
	global $tanggalmasuk;
	global $dbname;
	global $owlPDO;

	$str = "select nik from " . $dbname . ".datakaryawan where nik = '" . $nik . "'";
	$res = fetchData($str);
	if (count($res) > 0) {
		$adanik = 1;
	} else {
		$adanik = 0;
	}
	return $adanik;
}


function validasiktp($noktp)
{

	global $reader;
	global $karyawanid;
	global $dbname;
	global $owlPDO;
	global $tanggallahir;
	global $jeniskelamin;
	global $method;


	$str = "select noktp,karyawanid from " . $dbname . ".datakaryawan where noktp = '" . $noktp . "' AND tanggalkeluar = '0000-00-00'";
	$res = fetchData($str)[0];
	if (count($res) > 0) {
		if ($method != 'update') {
			exit("warning : No KTP sudah ada");
		}
	}
}
