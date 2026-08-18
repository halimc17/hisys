<?php
//session_start();
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
require_once('dompdfv2/autoload.inc.php');
require_once('lib/HtmlExcel.php');

use Dompdf\Dompdf;

$proses = checkPostGet('proses', '');
$id = checkPostGet('id', '');
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

switch ($proses) {
	case 'submitfile':
		@$tgl = date("YmdHis");
		@$his = date("His");
		$data = $_POST;
		if ($data['fileupload'] != '') {
			if ($_FILES['file']['error'] == 0) {
				$filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
				$nmfile = substr($_FILES['file']['name'], 0, strripos($_FILES['file']['name'], '.'));
				$nama = preg_replace("/[^a-zA-Z0-9]/", "", $nmfile);
				$filename = $nama . "" . $filetype;
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
		$no = 0;
		$tab = $icon = "";
		$str = "select * from " . $dbname . ".listfile_kebun_spb where nospb='" . $param['nospb'] . "'";
		@$res = fetchData($str);
		foreach (@$res as $key => $val) {
			$no++;
			$tab .= "<tr class=rowcontent>
				<td style='text-align:center'>" . $no . "</td>";
			$icon = seticonfile($val['formaticon']);
			$tab .= "<td align=left>
				<a href='" . $path . $val['namafile'] . "' download>" . (strlen($val['namafile']) > 35 ? substr($val['namafile'], 0, 35) . "..." : $val['namafile']) . "</a></td>";
			$tab .= "<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('" . $val['id'] . "','" . $val['namafile'] . "');\" >";
			$tab . "	</td>
			</tr>";
		}
		echo $tab;
		break;
	case 'deletefile':
		$str = "delete from " . $dbname . ".listfile_kebun_spb where id='" . $id . "'"; //exit('error'.$str);
		try {
			$owlPDO->exec($str);
			$pathx = $path . $namafile;
			unlink($pathx);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;
	case 'deletefileall':
		# delete file
		$sql = "select * from " . $dbname . ".listfile_kebun_spb where nospb='" . $param['nospb'] . "'"; //exit('error'.$sql);
		$res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$str = "delete from " . $dbname . ".listfile_kebun_spb where id='" . $bar['id'] . "' and namafile='" . $bar['namafile'] . "'";
			try {
				$owlPDO->exec($str);
				$pathx = $path . $bar['namafile'];
				unlink($pathx);
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

		// ambil fraksi atau potongan
		$str2 = "select * from " . $dbname . ".pabrik_5fraksi2 where pt = '" . getindukPT($bar1['kodeorg']) . "'";
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
				<input type='text' onblur='getPotongan()' class='myinputtextnumber' onkeypress='return angka_doang(event)' id='dt_potongan_" . $no_dt . "' name='dt_potongan_" . $no_dt . "' style='width:150px;' />
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

		$sDtSpb = "select nospb,kodeorg from " . $dbname . ".kebun_spbht where left(tanggal,7)='" . $tglpr[2] . "-" . $tglpr[1] . "' and tanggal = '" . tanggalsystemn($param['tgl']) . "' and kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' and tujuan=3 and posting='0'"; //exit('error'.$sDtSpb);
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
						<input type='text' onblur='getPotongan()' value='" . $nilai . "' class='myinputtextnumber' onkeypress='return angka_doang(event)' id='dt_edit_potongan_" . $no_dt . "' name='dt_edit_potongan_" . $no_dt . "' style='width:150px;' />
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

		$owlPDO->beginTransaction();
		$str = "select * from " . $dbname . ".kebun_spbht where nospb = '" . $param['spbId'] . "'";
		$res = fetchData($str);
		foreach ($res as $bar) {
			$optpks = $bar['penerimatbs'];
			$kodeorg = $bar['kodeorg'];
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
		//||($param['spbId']=='')

		// if($optCust[$param['spbId']]==''){
		// $optCust[$param['spbId']]='EBL';
		// }

		$arrorg = explode("/", $param['spbId']);
		$kodeorg = substr($arrorg[1], 0, 4);
		$kodediv = $arrorg[1];

		$str3 = "select * from " . $dbname . ".pabrik_timbangan WHERE nospb = '" . $param['spbId'] . "' and millcode != 'EXTM' ";
		$res3 = fetchdata($str3);

		$kode_cus = $res3[0]['kodecustomer'];
		$pabrik_tujuan = $res3[0]['pabriktujuan'];


		$sins = "insert into " . $dbname . ".pabrik_timbangan (notransaksi, tanggal, kodeorg, kodecustomer, jumlahtandan1, kodebarang,jammasuk, beratmasuk, jamkeluar, beratkeluar, nokendaraan, supir, nospb, timbangonoff, intex, millcode, beratbersih, jjgsortasi,kgpotsortasi, username, norefrensi, nokontrak, nodo, beratmasukpmks, beratkeluarpmks, beratbersihpmks, divcode,tanggalpks,kgpembeli,buahdikembalikan,spbpabrik,tahuntanam,pabriktujuan,nospbmobile) values ";
		// $sins.="('".$notrans."','".tanggalsystemn($param['tgl'])."','".$_SESSION['empl']['lokasitugas']."','".$optCust[$param['spbId']]."','".$param['jmlhJjg']."','40000003','".$param['jamMasuk']."','".$param['brtMsk']."','".$param['jamKeluar']."','".$param['brtKlr']."','".$param['kdKend']."','".$param['nmSupir']."','".$param['spbId']."','1','0','EXTM','".$param['brtBrsh']."','".$param['JjgSortasi']."','".$param['potKg']."','".$_SESSION['standard']['username']."','".$param['notiket']."','".$param['nokontrak']."','".$param['nodo']."','".$param['brtMskpmks']."','".$param['brtKlrpmks']."','".$param['brtBrshpmks']."','".$kodediv."','".tanggalsystem($param['tanggalpks'])."','".$param['kgJual']."','".$param['buahdikembalikan']."','".$param['spbpabrik']."','".$param['tahuntanam2']."')";
		$sins .= "('" . $notrans . "','" . tanggalsystemn($param['tgl']) . "','" . $_SESSION['empl']['lokasitugas'] . "','" . $kode_cus . "','" . $param['jmlhJjg'] . "','40000003','" . $param['jamMasuk'] . "','" . $param['brtMsk'] . "','" . $param['jamKeluar'] . "','" . $param['brtKlr'] . "','" . $param['kdKend'] . "','" . $param['nmSupir'] . "','" . $param['spbId'] . "','1','0','EXTM','" . $param['brtBrsh'] . "','" . $param['JjgSortasi'] . "','" . $param['potKg'] . "','" . $_SESSION['standard']['username'] . "','" . $param['notiket'] . "','" . $param['nokontrak'] . "','" . $param['nodo'] . "','" . $param['brtMskpmks'] . "','" . $param['brtKlrpmks'] . "','" . $param['brtBrshpmks'] . "','" . $kodediv . "','" . tanggalsystem($param['tanggalpks']) . "','" . $param['kgJual'] . "','" . $param['buahdikembalikan'] . "','" . $param['spbpabrik'] . "','" . $param['tahuntanam2'] . "','" . $pabrik_tujuan . "','" . $notransMobile . "')";
		// exit("error: ".$sins);
		try {
			$owlPDO->exec($sins);

			// insert sortasi
			$kodePotonganAll = checkPostGet('kode_potongan', '');
			$nilaiPotonganAll = checkPostGet('nilai_potongan', '');
			$t_dataa = count($kodePotonganAll) - 1;

			if ($t_dataa > 0) {
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

		$owlPDO->beginTransaction();
		if (($param['tgl'] == '') || ($param['kdKend'] == '') || ($param['nmSupir'] == '') || ($param['jmlhJjg'] == '') || ($param['brtMsk'] == '') || ($param['brtKlr'] == '')) {
			exit("error: Seluruh field tidak boleh kosong");
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
					kodeorg='" . $_SESSION['empl']['lokasitugas'] . "',
					jumlahtandan1='" . $param['jmlhJjg'] . "',jammasuk='" . $param['jamMasuk'] . "',
					beratmasuk='" . $param['brtMsk'] . "',jamkeluar='" . $param['jamKeluar'] . "',
					beratkeluar='" . $param['brtKlr'] . "',nokendaraan='" . $param['kdKend'] . "',
					supir='" . $param['nmSupir'] . "',nospb='" . $param['spbId'] . "',
					beratbersih='" . $param['brtBrsh'] . "',jjgsortasi='" . $param['JjgSortasi'] . "',
					kgpotsortasi='" . $param['potKg'] . "',username='" . $_SESSION['standard']['username'] . "',
					norefrensi='" . $param['notiket'] . "',nokontrak='" . $param['nokontrak'] . "',nodo='" . $param['nodo'] . "',
					beratmasukpmks='" . $param['brtMskpmks'] . "',beratkeluarpmks='" . $param['brtKlrpmks'] . "',
					beratbersihpmks='" . $param['brtBrshpmks'] . "',divcode='" . $kodediv . "', tanggalpks='" . tanggalsystem($param['tanggalpks']) . "',kgpembeli='" . $param['kgJual'] . "',
					tahuntanam='" . $param['tahuntanam2'] . "',spbpabrik='" . $param['spbpabrik'] . "',buahdikembalikan='" . $param['buahdikembalikan'] . "'
					
					where notransaksi='" . $param['notransaksi'] . "'";
		// exit("Error:$sins");
		try {
			$owlPDO->exec($sins);


			// insert sortasi
			$kodePotonganAll = checkPostGet('kode_potongan', '');
			$nilaiPotonganAll = checkPostGet('nilai_potongan', '');
			$t_dataa = count($kodePotonganAll) - 1;

			if ($t_dataa > 0) {
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
		$str2 = "select * from " . $dbname . ".pabrik_5fraksi2 where pt ='" . getindukPT($_SESSION['empl']['lokasitugas']) . "'";
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

		$whrCr = "";
		if ($param['nosbpCr'] != '') {
			$whrCr .= " and nospb like '%" . $param['nosbpCr'] . "%'";
		}

		if ($param['tahuntanamsrc'] != '') {
			if ($param['tahuntanamsrc'] != 'Kosong') {
				$whrCr .= " and tahuntanam like '%" . $param['tahuntanamsrc'] . "%'";
			} else {
				$whrCr .= " and tahuntanam=''";
			}
		}

		if ($param['tgl_cari'] != '' &&  $param['tgl_cari_sampai'] == '') {
			// $whrCr.=" and tanggal like '%".tanggalsystemn($param['tgl_cari'])."%'";
			$whrCr .= " and tanggal = '" . tanggalsystemn($param['tgl_cari']) . "'";
		} else if ($param['tgl_cari_sampai'] != '') {
			$whrCr .= " and tanggal>='" . tanggalsystemn($param['tgl_cari']) . "' and tanggal<='" . tanggalsystemn($param['tgl_cari_sampai']) . "' ";
		}

		if ($param['tgl_cari_sampai'] != '' and $param['tgl_cari'] == '') {
			exit("warning : Jika tanggal sampai terisi maka tanggal dari nya harus terisi!!! ");
		}


		$limit = 20;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0) $page = 0;
		}
		$offset = $page * $limit;
		$maxdisplay = ($page * $limit);
		$ql2 = "select count(*) as jmlhrow from " . $dbname . ".pabrik_timbangan 
			  where kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' and char_length(notransaksi)>7  " . $whrCr . " order by left(`tanggal`,10) desc";

		if ($tipe == 'html') {
			$slvhc = "select * from " . $dbname . ".pabrik_timbangan 
					where kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' and char_length(notransaksi)>7 " . $whrCr . "
					order by left(`tanggal`,10) desc limit " . $offset . "," . $limit . "";
		} else {
			$slvhc = "select * from " . $dbname . ".pabrik_timbangan 
					where kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' and char_length(notransaksi)>7 " . $whrCr . "
					order by left(`tanggal`,10) desc";
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
		while ($rData = $qlvhc->fetch()) {
			$no += 1;

			$tab .= "
			<tr class=rowcontent>
			<td align=center>" . $no . "</td>
			<td align='center'>" . $rData['notransaksi'] . "</td>
			<td align='center'>" . tanggalnormal(substr($rData['tanggal'], 0, 10)) . "</td>
			<td align='center'>" . $rData['nospb'] . "</td>
			<td align='center'>" . $rData['spbpabrik'] . "</td>
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

			$str3 = "select * from " . $dbname . ".pabrik_5fraksi2 where pt = '" . getindukPT($rData['kodeorg']) . "'";
			$res3 = fetchData($str3);
			$trpotongan_nilai = "";
			foreach ($res3 as $bar2) {
				$nilai = 0;
				$str3 = "select * from " . $dbname . ".pabrik_sortasi WHERE notiket = '" . $rData['notransaksi'] . "' and kodefraksi = '" . $bar2['kode'] . "' ";
				$res3 = fetchdata($str3);
				if (count($res3) > 0) {
					$nilai = $res3[0]['kg'];
					$trpotongan_nilai .= "
						<td align=center width=50px>" . number_format($nilai, 0) . "</td>
					";

					# Denda
					if ($bar2['kode'] == 'BL') {
						$tpbl += $nilai;
					}

					if ($bar2['kode'] == 'KM') {
						$tpkm += $nilai;
					}

					if ($bar2['kode'] == 'SPH') {
						$tpss += $nilai;
					}

					if ($bar2['kode'] == 'TP') {
						$tptp += $nilai;
					}
					# End Denda

				} else {
					$trpotongan_nilai .= "
						<td align=center width=50px>0</td>
					";
				}
			}
			$tab .= $trpotongan_nilai;


			$tab .= "
			<td align='right'>" . number_format($rData['kgpotsortasi'], 0) . "</td>
			<td align='right'>" . @number_format(@$rData['beratbersih'], 0) . "</td>
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
				if (($_SESSION['standard']['username'] == @$rData['username']) || ($optStat[$rData['nospb']] == '0')) {
					$tab .= "<td align=center width=25px><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $rData['tahuntanam'] . "','" . $rData['spbpabrik'] . "','" . $rData['notransaksi'] . "',
					'" . $rData['jammasuk'] . "','" . $rData['jamkeluar'] . "','" . $rData['nokendaraan'] . "','" . $rData['supir'] . "',
					'" . $rData['norefrensi'] . "','" . $rData['jumlahtandan1'] . "','" . $rData['beratmasuk'] . "','" . $rData['beratkeluar'] . "',
					'" . $rData['beratbersih'] . "','" . $rData['jjgsortasi'] . "','" . $rData['kgpotsortasi'] . "','" . $rData['nospb'] . "',
					'" . @tanggalnormal(substr($rData['tanggal'], 0, 10)) . "','" . $rData['nokontrak'] . "','" . $rData['nodo'] . "','" . $rData['pabriktujuan'] . "');\"></td>";
					$tab .= "<td align=center width=25px><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deleteData('" . $rData['notransaksi'] . "','" . $rData['nospb'] . "');\"></td>";
					$tab .= "<td align=center width=25px><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_timbangan','" . $rData['notransaksi'] . "','','pabrik_timbanganPdf',event)\"></td>";
				} else {
					// $tab.="<td align=center width=25px></td>";
					$tab .= "<td align=center width=25px><img src=images/application/application_edit_gray.png class=resicon  title='Edit Tahun Tanam' onclick=\"fillFieldTahunTanam('" . $rData['tahuntanam'] . "','" . $rData['spbpabrik'] . "','" . $rData['notransaksi'] . "',
					'" . $rData['jammasuk'] . "','" . $rData['jamkeluar'] . "','" . $rData['nokendaraan'] . "','" . $rData['supir'] . "',
					'" . $rData['norefrensi'] . "','" . $rData['jumlahtandan1'] . "','" . $rData['beratmasuk'] . "','" . $rData['beratkeluar'] . "',
					'" . $rData['beratbersih'] . "','" . $rData['jjgsortasi'] . "','" . $rData['kgpotsortasi'] . "','" . $rData['nospb'] . "',
					'" . @tanggalnormal(substr($rData['tanggal'], 0, 10)) . "','" . $rData['nokontrak'] . "','" . $rData['nodo'] . "','" . $rData['pabriktujuan'] . "');\"></td>";
					$tab .= "<td align=center width=25px></td>";
					$tab .= "<td align=center width=25px><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_timbangan','" . $rData['notransaksi'] . "','','pabrik_timbanganPdf',event)\"></td>";
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
		$tab .= "<td colspan=" . ($tipe == 'html' ? '9' : '11') . " align=center><b>TOTAL</b></td>";
		$tab .= "<td align=right style='font-weight:800;'>" . number_format($tjjg) . "</td>";
		$tab .= "<td align=right style='font-weight:800;'>" . number_format($tbm) . "</td>";
		$tab .= "<td align=right style='font-weight:800;'>" . number_format($tbk) . "</td>";
		$tab .= "<td align=right style='font-weight:800;'>" . number_format($tbb) . " </td>";
		$tab .= "<td align=right style='font-weight:800;'>" . number_format($tpbl) . "</td>";
		$tab .= "<td hidden align=right style='font-weight:800;'>" . number_format($tpkm) . "</td>";
		$tab .= "<td hidden align=right style='font-weight:800;'>" . number_format($tpss) . "</td>";
		$tab .= "<td hidden align=right style='font-weight:800;'>" . number_format($tptp) . "</td>";
		$tab .= "<td hidden align=right style='font-weight:800;'>" . number_format($tp) . "</td>";
		$tab .= "<td align=right style='font-weight:800;'>" . number_format($tpbn) . "</td>";
		$tab .= "<td colspan=5></td>";
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
			$nop = "Laporan_Pendapatan_" . $param['tgl_cari'] . "_" . $param['tgl_cari_sampai'] . ".xls";
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
		$str = "update " . $dbname . ".pabrik_timbangan set tanggalpks='" . $tanggalpks . "', beratmasukpmks='" . $kgin . "', beratkeluarpmks='" . $kgout . "',buahdikembalikan='" . $buahdikembalikan . "',spbpabrik='" . $spbpabrik . "',tahuntanam='" . $tahuntanam2 . "', beratbersihpmks='" . $kgnet . "',kgpotsortasi='" . $potongx . "' where notransaksi='" . $notiket . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo "DB Error : " . $e->getMessage();
			die();
		}
		break;
	case 'deleteData':
		$sDel = "delete from " . $dbname . ".pabrik_timbangan where notransaksi='" . $param['notransaksi'] . "'";
		try {
			$owlPDO->exec($sDel);

			$sDel = "delete from " . $dbname . ".pabrik_sortasi where notiket='" . $param['notransaksi'] . "'";
			$owlPDO->exec($sDel);
		} catch (PDOException $e) {
			echo "DB Error : " . $e->getMessage();
			die();
		}
		break;

	case 'updThnTnm':
		$optStat = makeOption($dbname, 'kebun_spbht', 'nospb,posting', "nospb='" . $param['spbId'] . "'");
		if ($optStat[$param['spbId']] == 0) {
			exit("Warning: Untuk Melakukan aksi ini hanya untuk SPB yang sudah diposting !");
		}
		$str = "update " . $dbname . ".pabrik_timbangan set tahuntanam='" . $param['tahuntanam2'] . "' where notransaksi='" . $param['notransaksi'] . "'";
		// exit("Warning: ".$str);
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo "DB Error : " . $e->getMessage();
			die();
		}
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
